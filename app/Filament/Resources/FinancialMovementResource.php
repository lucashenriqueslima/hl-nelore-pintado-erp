<?php

namespace App\Filament\Resources;

use App\Enums\FinancialMovementStatus;
use App\Enums\FinancialMovementType;
use App\Filament\Resources\FinancialMovementResource\Pages;
use App\Helpers\NumberHelper;
use App\Models\Cattle;
use App\Models\Embryo;
use App\Models\FinancialMovement;
use App\Models\FinancialMovementCategory;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FinancialMovementResource extends Resource
{
    protected static ?string $model = FinancialMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $modelLabel = 'Movimentação Financeira';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->columns(2)
                    ->schema([
                        Forms\Components\ToggleButtons::make('type')
                            ->label('Entrada/Saída')
                            ->columnSpanFull()
                            ->options(FinancialMovementType::class)
                            ->live()
                            ->grouped()
                            ->afterStateUpdated(fn(callable $set) => $set('financial_movement_category_id', null))
                            ->required()
                            ->hiddenOn('edit'),
                        Forms\Components\Select::make('dealer_id')
                            ->label('Responsável')
                            ->columnSpan(1)
                            ->relationship(
                                name: 'dealer',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn(Builder $query) => $query->orderBy('name'),
                            )
                            ->searchable(['name'])
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('financial_movement_category_id')
                            ->label('Categoria')
                            ->columnSpan(1)
                            ->preload()
                            ->relationship(
                                name: 'financialMovementCategory',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn(Builder $query, Get $get) => $query->orderBy('name')
                                    ->where('type', $get('type')),
                            )
                            ->searchable(['name'])
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn(FinancialMovementCategory $record) => "{$record->name} | {$record->type->getLabel()}")
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nome')
                                    ->unique()
                                    ->required(),
                                Forms\Components\Select::make('type')
                                    ->label('Entrada/Saída')
                                    ->options(FinancialMovementType::class)
                                    ->required(),
                            ])
                            ->disabled(fn(Get $get) => !$get('type'))
                            ->required()
                            ->hiddenOn('edit'),
                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->columnSpanFull()
                            ->maxLength(255),

                        Forms\Components\MorphToSelect::make('movementable')
                            ->label('Gado/Embrião')
                            ->searchable()
                            ->columnSpanFull()
                            ->types([
                                Forms\Components\MorphToSelect\Type::make(Cattle::class)
                                    ->label('Gado')
                                    ->getOptionLabelFromRecordUsing(fn(Cattle $record) => "{$record->rgd} | {$record->name}")
                                    ->searchColumns(['rgd', 'name']),
                                Forms\Components\MorphToSelect\Type::make(Embryo::class)
                                    ->label('Embrião')
                                    ->getOptionLabelFromRecordUsing(fn(Embryo $record) => "{$record->rgd}")
                                    ->searchColumns(['rgd']),
                            ])
                            ->hiddenOn('edit'),

                        Forms\Components\TextInput::make('value')
                            ->label('Valor')
                            ->mask(RawJs::make(
                                <<<'JS'
                $money($input, ',', '.', 2)
                JS
                            ))
                            ->suffixIcon('heroicon-s-currency-dollar')
                            ->suffixIconColor(fn(Get $get) => $get('type') === FinancialMovementType::Income->value ? 'success' : 'danger')
                            ->columnSpanFull()
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {

                                $totalInstallments = $get('total_installments');

                                if (!$totalInstallments || !$state) {
                                    return;
                                }

                                $rawValue = NumberHelper::formatToRaw($state);

                                $set('installment_value', NumberHelper::formatToCurrency($rawValue / $totalInstallments));
                            })
                            ->required()
                            ->hiddenOn('edit'),

                        Forms\Components\Select::make('status')
                            ->disabled(fn(Get $get) => !$get('type'))
                            ->columnSpanFull()
                            ->options(FinancialMovementStatus::class)
                            ->live()
                            ->afterStateUpdated(fn(Set $set) => $set('payment_date', null))
                            ->required(),
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Data de Pagamento')
                            ->disabled(fn(Get $get) => $get('status') !== FinancialMovementStatus::Paid->value)
                            ->hiddenOn('create'),
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Data de Vencimento')
                            ->hiddenOn('create'),
                        Section::make('Contrato')
                            ->relationship('financialMovementGroup')
                            ->schema([
                                Forms\Components\TextInput::make('contract_number')
                                    ->label('Número Identificador'),
                                Forms\Components\FileUpload::make('attachments')
                                    ->label('Anexos')
                                    ->columnSpanFull()
                                    ->multiple()
                                    ->visibility('public')
                                    ->directory('public/financial_movement/attachments')
                                    ->downloadable(),
                            ])

                    ]),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                TextColumn::make('financialMovementGroup.id')
                    ->label('Grupo ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('installment_number_with_total')
                    ->label('Parcela')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('financialMovementGroup.contract_number')
                    ->label('Contrato')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('dealer.name')
                    ->label('Responsável')
                    ->sortable()
                    ->searchable(['name']),
                TextColumn::make('financialMovementCategory.name')
                    ->label('Categoria')
                    ->searchable()
                    ->sortable()
                    ->searchable(['name']),
                TextColumn::make('movementable.full_name')
                    ->label('Gado/Embrião')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable()
                    ->sortable()
                    ->searchable(['description']),
                TextColumn::make('value')
                    ->label('Valor')
                    ->searchable()
                    ->sortable()
                    ->money('BRL', true)
                    ->color(
                        fn(FinancialMovement $record): string => $record->type === FinancialMovementType::Income ? 'success' : 'danger'
                    )->money('BRL', locale: 'pt-BR')
                    ->summarize([
                        Sum::make()
                            ->label('Saldo')
                            ->money('BRL', locale: 'pt-BR')
                            ->query(fn($query) => $query->where('status', FinancialMovementStatus::Paid)),
                        Sum::make()
                            ->label('Saldo Futuro')
                            ->money('BRL', locale: 'pt-BR')
                            ->query(fn($query) => $query->where('status', FinancialMovementStatus::Pending)),
                    ]),
                TextColumn::make('due_date')
                    ->label('Data de Vencimento')
                    ->sortable()
                    ->date('d/m/Y'),
                TextColumn::make('payment_date')
                    ->label('Data de Pagamento')
                    ->searchable()
                    ->sortable()
                    ->date('d/m/Y'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->searchable()
                    ->sortable()


            ])
            ->filters(
                [
                    SelectFilter::make('financial_movement_category_id')
                        ->label('Categoria')
                        ->relationship(
                            name: 'financialMovementCategory',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn(Builder $query) => $query->orderBy('name'),
                        )
                        ->searchable(['name'])
                        ->preload()
                        ->multiple()
                        ->getOptionLabelFromRecordUsing(fn(FinancialMovementCategory $record) => "{$record->name} | {$record->type->getLabel()}"),
                    SelectFilter::make('movementable')
                        ->label('Gado/Embrião')
                        ->relationship(
                            name: 'movementable',
                            titleAttribute: 'id',
                            modifyQueryUsing: fn(Builder $query) => $query->orderBy('id'),
                        )
                        ->searchable(['name'])
                        ->preload()
                        ->multiple()
                        ->getOptionLabelFromRecordUsing(fn($record) => "{$record->rgd} | {$record->name}"),
                    Filter::make('payment_date')
                        ->label('Data de Pagamento')
                        ->form([
                            Section::make('Data de Pagamento')
                                ->schema([
                                    DatePicker::make('initial_date')
                                        ->label('Data Inicial'),
                                    DatePicker::make('final_date')
                                        ->label('Data Final'),
                                ])
                                ->columns(2)
                                ->columnSpanFull(),
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            return $query
                                ->when($data['initial_date'], fn($query, $initial_date) => $query->where('payment_date', '>=', $initial_date))
                                ->when($data['final_date'], fn($query, $final_date) => $query->where('payment_date', '<=', $final_date));
                        }),
                    Filter::make('due_date')
                        ->label('Data de Vencimento')
                        ->form([
                            Section::make('Data de Vencimento')
                                ->schema([
                                    DatePicker::make('initial_date')
                                        ->label('Data Inicial'),
                                    DatePicker::make('final_date')
                                        ->label('Data Final'),
                                ])
                                ->columns(2)
                                ->columnSpanFull(),
                        ])
                        ->query(callback: function (Builder $query, array $data): Builder {
                            return $query
                                ->when($data['initial_date'], fn($query, $initial_date) => $query->where('due_date', '>=', $initial_date))
                                ->when($data['final_date'], fn($query, $final_date) => $query->where('due_date', '<=', $final_date));
                        })
                ],
                layout: FiltersLayout::AboveContentCollapsible,
            )
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinancialMovements::route('/'),
            'create' => Pages\CreateFinancialMovement::route('/create'),
            // 'edit' => Pages\EditFinancialMovement::route('/{record}/edit'),
        ];
    }
}
