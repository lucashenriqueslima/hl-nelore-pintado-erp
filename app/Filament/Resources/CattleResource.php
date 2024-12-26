<?php

namespace App\Filament\Resources;

use App\Enums\CattleType;
use App\Enums\Gender;
use App\Filament\Resources\CattleResource\Pages;
use App\Filament\Resources\CattleResource\RelationManagers;
use App\Filament\Resources\CattleResource\RelationManagers\WeightsRelationManager;
use App\Models\Cattle;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Leandrocfe\FilamentPtbrFormFields\Money;

class CattleResource extends Resource
{
    protected static ?string $model = Cattle::class;

    protected static ?string $navigationIcon = 'fas-cow';


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
                                    ->relationship('farm', 'name')
                                    ->columnSpanFull()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label('Nome')
                                            ->required(),
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
                                    ->options(CattleType::class)
                                    ->required(),


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
                                    ->directory('public/contracts')
                                    ->downloadable(),
                                FileUpload::make('sell_contract_path')
                                    ->label('Contrato de Venda')
                                    ->columnSpanFull()
                                    ->visibility('public')
                                    ->maxSize(5 * 1024)
                                    ->directory('public/contracts')
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
                                        modifyQueryUsing: fn(Builder $query) => $query->where('gender', Gender::MALE),
                                    )
                                    ->searchable(['rgd', 'name'])
                                    ->getOptionLabelFromRecordUsing(fn(Cattle $record) => "{$record->rgd} | {$record->name}")
                                    ->optionsLimit(10),
                                Select::make('mother_id')
                                    ->label('Mãe')
                                    ->relationship(
                                        name: 'mother',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn(Builder $query) => $query->where('gender', Gender::FEMALE),
                                    )
                                    ->searchable(['rgd', 'name'])
                                    ->getOptionLabelFromRecordUsing(fn(Cattle $record) => "{$record->rgd} | {$record->name}")
                                    ->optionsLimit(10),
                            ]),
                        Tab::make('Anexos')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->columns(2)
                            ->schema([
                                Forms\Components\FileUpload::make('image_path')
                                    ->label('Foto de Perfil')
                                    ->columnSpanFull()
                                    ->visibility('public')
                                    ->maxSize(8 * 1024)
                                    ->directory('public/cattle')
                                    ->image()
                                    ->imageEditor(),
                                FileUpload::make('attachments')
                                    ->label('Anexos')
                                    ->columnSpanFull()
                                    ->visibility('public')
                                    ->directory('public/cattle')
                                    ->downloadable(),
                            ])
                    ]),




            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rgd')
                    ->label('RGD')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
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
        return [
            WeightsRelationManager::class
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
}
