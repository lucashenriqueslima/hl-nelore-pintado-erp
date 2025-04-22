<?php

namespace App\Filament\Resources\FinancialMovementResource\Pages;

use App\Actions\GetNextInstallmentsDates;
use App\DTO\FinancialMovementDTO;
use App\Enums\FinancialMovementStatus;
use App\Enums\FinancialMovementType;
use App\Filament\Helpers\RouterHelper;
use App\Filament\Resources\FinancialMovementResource;
use App\Helpers\NumberHelper;
use App\Models\FinancialMovement;
use App\Models\FinancialMovementGroup;
use App\Services\FinancialMovementService;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Radio;
use Filament\Forms;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateFinancialMovement extends CreateRecord
{
    protected static string $resource = FinancialMovementResource::class;

    private array $formData = [];

    protected function getFormActions(): array
    {
        return [
            CreateAction::make()
                ->label('Parcelamento')
                ->model(FinancialMovement::class)
                ->mountUsing(function (Forms\Form $form, CreateAction $action): void {
                    try {
                        $this->form->validate();
                        $form->fill();
                    } catch (ValidationException $e) {
                        $this->setErrorBag($e->validator->errors());
                        $action->cancel();
                    }
                })
                ->form([
                    Forms\Components\Section::make()
                        ->schema([
                            Radio::make('is_to_generate_installments')
                                ->label('Gerar Parcelas?')
                                ->columnSpanFull()
                                ->live()
                                ->boolean()
                                ->descriptions([
                                    1 => 'Serão geradas parcelas a partir dos dados informados.',
                                    0 => 'Não será gerada nenhuma parcela.',
                                ])
                                ->default(1)
                                ->afterStateUpdated(function (Set $set) {
                                    $set('number_installments', null);
                                    $set('payment_date', null);
                                    $set('due_date', null);
                                })
                                ->required(),
                        ]),

                    Forms\Components\Section::make('Datas')
                        ->columns(2)
                        ->schema([
                            Forms\Components\DatePicker::make('payment_date')
                                ->label('Data de Pagamento')
                                ->disabled(fn() => $this->form->getState()['status'] !== FinancialMovementStatus::Paid->value),
                            Forms\Components\DatePicker::make('due_date')
                                ->label('Data de Vencimento'),
                        ])
                        ->visible(fn(Get $get) => $get('is_to_generate_installments') == false),

                    Forms\Components\Section::make('Parcelamento')
                        ->columns(2)
                        ->schema([
                            Forms\Components\TextInput::make('total_installments')
                                ->label('Número de Parcelas')
                                ->numeric()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                                    $value = $this->form->getState()['value'] ?? null;

                                    if (!$state || !$value || !$get('first_installment_date')) {
                                        return;
                                    }

                                    $this->formData = $this->form->getState();


                                    $installmentsDates = (new GetNextInstallmentsDates())->handle(
                                        Carbon::make($get('first_installment_date')),
                                        (int)$get('total_installments')
                                    );

                                    $value = NumberHelper::formatToCurrency(NumberHelper::formatToRaw($value) / $state);

                                    $instalmentsArray = array_map(
                                        function ($date, $index) use ($value) {
                                            return [
                                                'index' => $index,
                                                'value' => $value,
                                                'due_date' => $date,
                                            ];
                                        },
                                        $installmentsDates,
                                        array_keys($installmentsDates)
                                    );

                                    $set('installments', $instalmentsArray);
                                })
                                ->minValue(1)
                                ->required(),
                            Forms\Components\DatePicker::make('first_installment_date')
                                ->label('Data da 1ª Parcela')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                                    $value = $this->form->getState()['value'] ?? null;

                                    if (!$state || !$value || !$get('total_installments')) {
                                        return;
                                    }

                                    $this->formData = $this->form->getState();


                                    $installmentsDates = (new GetNextInstallmentsDates())->handle(
                                        Carbon::make($get('first_installment_date')),
                                        (int)$get('total_installments')
                                    );

                                    $value = NumberHelper::formatToCurrency(NumberHelper::formatToRaw($value) / $get('total_installments'));

                                    $instalmentsArray = array_map(
                                        function ($date, $index) use ($value) {
                                            return [
                                                'index' => $index,
                                                'value' => $value,
                                                'due_date' => $date,
                                            ];
                                        },
                                        $installmentsDates,
                                        array_keys($installmentsDates)
                                    );

                                    $set('installments', $instalmentsArray);
                                })
                                ->required(),
                            Forms\Components\Repeater::make('installments')
                                ->label('Parcelas')
                                ->columnSpanFull()
                                ->columns(2)
                                ->addable(false)
                                ->deletable(false)
                                ->reorderableWithDragAndDrop(false)
                                ->defaultItems(0)
                                ->itemLabel(fn(array $state): ?string => $state['index'] + 1 . 'ª Parcela')
                                ->schema(
                                    [
                                        Forms\Components\TextInput::make('value')
                                            ->label('Valor')
                                            ->mask(RawJs::make(
                                                <<<'JS'
                                                $money($input, ',', '.', 2)
                                                JS
                                            ))
                                            ->suffixIcon('heroicon-s-currency-dollar')
                                            ->suffixIconColor(fn() => $this->form->getState()['type'] === FinancialMovementType::Income->value ? 'success' : 'danger')
                                            ->required(),
                                        Forms\Components\DatePicker::make('due_date')
                                            ->label('Data de Vencimento')
                                            ->required(),
                                    ]
                                )
                        ])
                        ->visible(fn(Get $get) => $get('is_to_generate_installments')),
                ])
                ->using(function (array $data, string $model): Model {

                    $this->formData = array_merge($this->form->getState(), $data);

                    $this->formData['financial_movement_group_id'] = FinancialMovementGroup::create(
                        $this->formData['financialMovementGroup']
                    )->id;

                    $this->formData['installment_number'] = 1;


                    if (!$this->formData['is_to_generate_installments']) {
                        $this->formData['total_installments'] = 1;

                        $this->formData['value'] = self::getRawValueByType(
                            $this->formData['type'],
                            $this->formData['value']
                        );

                        return $model::create(
                            FinancialMovementDTO::fromFilamentForm($this->formData)
                                ->toCreateInDB()
                        );
                    }

                    $this->formData['value'] = self::getRawValueByType(
                        $this->formData['type'],
                        $this->formData['installments'][0]['value']
                    );

                    $this->formData['due_date'] = $this->formData['first_installment_date'];
                    $this->formData['payment_date'] = $this->formData['first_installment_date'];

                    return $model::create(
                        FinancialMovementDTO::fromFilamentForm($this->formData)
                            ->toCreateInDB()
                    );
                })
                ->after(function (): void {

                    if (!$this->formData['is_to_generate_installments'] || $this->formData['total_installments'] <= 1) {
                        return;
                    }

                    $financialMovements = [];
                    foreach ($this->formData['installments'] as $installment) {

                        if ($installment['index'] == 0) {
                            continue;
                        }

                        if ($this->formData['status'] == FinancialMovementStatus::Paid->value) {
                            $this->formData['payment_date'] = $installment['due_date'];
                        }

                        $this->formData['due_date'] = $installment['due_date'];

                        $this->formData['value'] = self::getRawValueByType(
                            $this->formData['type'],
                            $installment['value']
                        );

                        $this->formData['installment_number'] = $installment['index'] + 1;

                        $financialMovements[] = FinancialMovementDTO::fromFilamentForm($this->formData);
                    }

                    (new FinancialMovementService())->insertFinancialMovements($financialMovements);
                })
                ->successRedirectUrl($this->getResource()::getUrl('index'))

        ];
    }

    protected function handleRecordCreation(array $data): Model
    {

        $data['installment_number'] = 1;

        if ($data['is_to_generate_installments']) {
            $data['value'] = NumberHelper::formatToRaw($data['value']);
            $data['total_installments'] = 1;
        }

        return parent::handleRecordCreation($data);
    }

    private static function getRawValueByType(string $type, string $value): string
    {
        return match ($type) {
            FinancialMovementType::Expense->value => -abs(NumberHelper::formatToRaw($value)),
            FinancialMovementType::Income->value => abs(NumberHelper::formatToRaw($value)),
            default => NumberHelper::formatToRaw($value),
        };
    }
}
