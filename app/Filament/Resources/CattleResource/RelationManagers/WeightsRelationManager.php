<?php

namespace App\Filament\Resources\CattleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Leandrocfe\FilamentPtbrFormFields\Money;

class WeightsRelationManager extends RelationManager
{
    protected static string $relationship = 'weights';

    protected static ?string $title = 'Pesos';
    protected static ?string $modelLabel = 'Peso';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Money::make('weight')
                    ->label('Peso')
                    ->prefix(null)
                    ->suffix('KG')
                    ->default(0)
                    ->minValue(0)
                    ->required(),
                DatePicker::make('date')
                    ->label('Data da Pesagem')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('weight')
            ->columns([
                Tables\Columns\TextColumn::make('weight')
                    ->label('Peso')
                    ->suffix(' KG'),
                Tables\Columns\TextColumn::make('date')
                    ->label('Data da Pesagem')
                    ->date('d/m/Y'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
