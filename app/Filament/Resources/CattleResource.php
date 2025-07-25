<?php

namespace App\Filament\Resources;

use App\Enums\CattleType;
use App\Enums\Gender;
use App\Filament\Helpers\RouterHelper;
use App\Filament\Resources\CattleResource\Pages;
use App\Filament\Resources\CattleResource\RelationManagers\EmbryoFatherRelationManager;
use App\Filament\Resources\CattleResource\RelationManagers\EmbryoMotherRelationManager;
use App\Filament\Resources\CattleResource\RelationManagers\EmbryoReceiverRelationManager;
use App\Filament\Resources\CattleResource\RelationManagers\MotherRelationManager;
use App\Filament\Resources\CattleResource\RelationManagers\WeightsRelationManager;
use App\Models\Cattle;
use App\Models\Farm;
use Carbon\CarbonInterface;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Leandrocfe\FilamentPtbrFormFields\Money;
use Illuminate\Support\HtmlString;

class CattleResource extends Resource
{
    protected static ?string $model = Cattle::class;

    protected static ?string $navigationIcon = 'fas-cow';

    protected static ?string $modelLabel = 'Animal';
    protected static ?string $pluralModelLabel = 'Rebanhos';

    public static function form(Form $form): Form
    {


        return $form
            ->schema([
                Tabs::make()
                    ->columnSpanFull()
                    ->schema([
                        Tab::make('Dados Essenciais')
                            ->icon('heroicon-s-cube')
                            ->columns(2)
                            ->schema([
                                TextInput::make('rgd')
                                    ->label('RGD')
                                    ->unique(ignoreRecord: true)
                                    ->required(),
                                TextInput::make('name')
                                    ->label('Nome')
                                    ->required(),
                                Select::make('farm_id')
                                    ->label('Fazenda')
                                    ->relationship(name: 'farm')
                                    ->getOptionLabelFromRecordUsing(fn(Farm $cattle) => "{$cattle->name} " . ($cattle->farm_is_from_group ? '*' : ''))
                                    ->columnSpanFull()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label('Nome')
                                            ->required(),
                                        ToggleButtons::make('farm_is_from_group')
                                            ->label('Fazenda do Grupo?')
                                            ->inline()
                                            ->boolean(),
                                    ]),
                                DatePicker::make('birth_date')
                                    ->label('Data de Nascimento'),
                                DatePicker::make('death_date')
                                    ->label('Data de Morte'),
                                ToggleButtons::make('gender')
                                    ->label('Sexo')
                                    ->inline()
                                    ->options(Gender::class)
                                    ->required(),
                                ToggleButtons::make('type')
                                    ->label('Tipo')
                                    ->inline()
                                    ->options(CattleType::class),
                            ]),
                        Tab::make('Dados Financeiros')
                            ->icon('heroicon-o-currency-dollar')
                            ->columns(2)
                            ->schema([
                                Money::make('value')
                                    ->label('Valor'),
                                Money::make('commision_percentage')
                                    ->label('Porcentagem de Comissão')
                                    ->prefix(null)
                                    ->suffix('%')
                                    ->minValue(0)
                                    ->maxValue(100),
                                DatePicker::make('aquisition_date')
                                    ->label('Data da Aquisição')
                                    ->columnSpanFull(),
                                TextInput::make('number_installments')
                                    ->label('Quantidade de Parcelas')
                                    ->numeric()
                                    ->minValue(1),
                                DatePicker::make('first_installment_date')
                                    ->label('Data da Primeira Parcela'),
                                FileUpload::make('aquisition_contract_path')
                                    ->label('Contrato de Aquisição')
                                    ->columnSpanFull()
                                    ->visibility('public')
                                    ->maxSize(5 * 1024)
                                    ->directory('public/cattle/contracts')
                                    ->downloadable(),
                                FileUpload::make('sell_contract_path')
                                    ->label('Contrato de Venda')
                                    ->columnSpanFull()
                                    ->visibility('public')
                                    ->maxSize(5 * 1024)
                                    ->directory('public/cattle/contracts')
                                    ->downloadable(),


                            ]),
                        Tab::make('Dados Parentais')
                            ->icon('fas-arrows-turn-to-dots')
                            ->columns(2)
                            ->schema([
                                Select::make('father_id')
                                    ->label('Pai')
                                    ->relationship(
                                        name: 'father',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn(Cattle $cattle, Builder $query) => $query->where('gender', Gender::MALE)
                                            ->where('id', '!=', $cattle->id),
                                    )
                                    ->preload()
                                    ->searchable(['rgd', 'name'])
                                    ->getOptionLabelFromRecordUsing(fn(Cattle $cattle) => "{$cattle->rgd} | {$cattle->name}")
                                    ->optionsLimit(10),
                                Select::make('mother_id')
                                    ->label('Mãe')
                                    ->relationship(
                                        name: 'mother',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn(Cattle $cattle, Builder $query) => $query->where('gender', Gender::FEMALE)
                                            ->where('id', '!=', $cattle->id),
                                    )
                                    ->preload()
                                    ->searchable(['rgd', 'name'])
                                    ->getOptionLabelFromRecordUsing(fn(Cattle $cattle) => "{$cattle->rgd} | {$cattle->name}")
                                    ->optionsLimit(10),
                                Group::make()
                                    ->schema(
                                        [
                                            Fieldset::make('Família do Pai')
                                                ->schema([
                                                    Placeholder::make('Pai')
                                                        ->content(
                                                            fn(Cattle $cattle): ?HtmlString => self::getAHtmlString($cattle->father?->id, "{$cattle->father?->rgd} {$cattle->father?->name}")
                                                        )
                                                        ->columnSpan(2),
                                                    Placeholder::make('Avô')
                                                        ->content(
                                                            fn(Cattle $cattle): ?HtmlString => self::getAHtmlString($cattle->father?->father?->id, "{$cattle->father?->father?->rgd} {$cattle->father?->father?->name}")
                                                        )
                                                        ->columnSpan(1),
                                                    Placeholder::make('Avó')
                                                        ->content(fn(Cattle $cattle): ?HtmlString => self::getAHtmlString($cattle->father?->mother?->id, "{$cattle->father?->mother?->rgd} {$cattle->father?->mother?->name}"))
                                                        ->columnSpan(1),
                                                ])
                                                ->columnSpan(2)
                                                ->columns(2),
                                            Fieldset::make('Família da Mãe')
                                                ->schema([
                                                    Placeholder::make('Mãe')
                                                        ->content(
                                                            fn(Cattle $cattle): ?HtmlString => self::getAHtmlString($cattle->mother?->id, "{$cattle->mother?->rgd} {$cattle->mother?->name}")
                                                        )
                                                        ->columnSpan(2),
                                                    Placeholder::make('Avô')
                                                        ->content(
                                                            fn(Cattle $cattle): ?HtmlString => self::getAHtmlString($cattle->mother?->father?->id, "{$cattle->mother?->father?->rgd} {$cattle->mother?->father?->name}")
                                                        )
                                                        ->columnSpan(1),
                                                    Placeholder::make('Avó / Mãe')
                                                        ->content(fn(Cattle $cattle): ?HtmlString => self::getAHtmlString($cattle->mother?->mother?->id, "{$cattle->mother?->mother?->rgd} {$cattle->mother?->mother?->name}"))
                                                        ->columnSpan(1),
                                                ])
                                                ->columnSpan(2)
                                                ->columns(2),



                                        ]
                                    )
                                    ->columns(4)
                                    ->columnSpanFull()
                                    ->hidden(fn(string $operation): bool => $operation !== 'edit'),
                            ]),
                        Tab::make('Anexos')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->columns(2)
                            ->schema([
                                Forms\Components\FileUpload::make('profile_photo_path')
                                    ->label('Foto de Perfil')
                                    ->columnSpanFull()
                                    ->visibility('public')
                                    ->directory('public/cattle/profile-photo')
                                    ->downloadable()
                                    ->image()
                                    ->imageEditor(),
                                FileUpload::make('attachments')
                                    ->label('Anexos')
                                    ->columnSpanFull()
                                    ->visibility('public')
                                    ->directory('public/cattle/attachments')
                                    ->multiple()
                                    ->downloadable(),
                            ])
                    ]),




            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_photo_path')
                    ->label('')
                    ->circular()
                    ->width(55)
                    ->height(55)
                    ->grow(false),
                TextColumn::make('full_name')
                    ->label('RGD')
                    ->searchable(['rgd', 'name'])
                    ->sortable(),
                TextColumn::make('farm.name')
                    ->label('Fazenda')
                    ->searchable()
                    ->sortable(),


                TextColumn::make('gender')
                    ->label('Sexo')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('birth_date')
                    ->label('Idade por extenso')
                    ->getStateUsing(function (Cattle $cattle) {
                        if ($cattle->birth_date == null) {
                            return null;
                        }

                        return $cattle->birth_date->diffForHumans(now(), [
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE,
                            'parts' => 4,
                            'join' => true,
                        ]);
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data de Criação')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Data da Ultima Atualização')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                //
            ])
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

        // $relations = self::handleRalations();
        return [
            WeightsRelationManager::class,
            // FatherRelationManager::class,
            MotherRelationManager::class,
            EmbryoFatherRelationManager::class,
            EmbryoMotherRelationManager::class,
            EmbryoReceiverRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCattle::route('/'),
            'create' => Pages\CreateCattle::route('/create'),
            'edit' => Pages\EditCattle::route('/{record}/edit'),
        ];
    }

    private static function getAHtmlString(
        ?string $id,
        ?string $content
    ): ?HtmlString {

        if (!$id || !$content) {
            return null;
        }

        return new HtmlString('<a style="text-decoration: underline !important" href="' . RouterHelper::getRoute('cattle', ['record' => $id]) . '" target="_blank">' . $content . ' </a>');
    }
}
