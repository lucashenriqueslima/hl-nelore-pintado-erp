<?php

namespace App\Filament\Resources\CattleResource\RelationManagers;

use App\Enums\CattleType;
use App\Enums\Gender;
use App\Filament\Helpers\RouterHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmbryoMotherRelationManager extends RelationManager
{
    protected static string $relationship = 'embryoDaughters';
    protected static ?string $title = 'Filhos / Embrião';
    protected static ?string $modelLabel = 'Embrião';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->gender == Gender::FEMALE && $ownerRecord->type == CattleType::PO;
    }



    public function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('rgd')
            ->columns([
                Tables\Columns\TextColumn::make('rgd')
                    ->label('RGD')
                    ->url(fn(?Model $record): ?string => $record ? RouterHelper::getRoute('embryos', ['record' => $record->id]) : null)
                    ->label('Pai')
                    ->url(fn(?Model $record): ?string => $record?->father_id ?  RouterHelper::getRoute('cattle', ['record' => $record->father_id]) : null),
                Tables\Columns\TextColumn::make('receiver.full_name')
                    ->label('Receptora')
                    ->url(fn(?Model $record): ?string => $record?->mother_id ? RouterHelper::getRoute('cattle', ['record' => $record->mother_id]) : null),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                // Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
