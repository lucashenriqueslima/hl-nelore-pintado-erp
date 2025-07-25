<?php

namespace App\Filament\Resources\CattleResource\RelationManagers;

use App\Enums\CattleType;
use App\Enums\Gender;
use App\Filament\Helpers\RouterHelper;
use App\Models\Cattle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MotherRelationManager extends RelationManager
{
    protected static string $relationship = 'mother';
    protected static ?string $title = 'Filhos / Gado';
    protected static ?string $modelLabel = 'Filho';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->gender == Gender::FEMALE && $ownerRecord->type == CattleType::PO;
    }

    public function isReadOnly(): bool
    {
        return true;
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
                Tables\Columns\TextColumn::make('full_name')
                    ->label('RGD | Nome')
                    ->url(fn(?Cattle $record): ?string => $record ? RouterHelper::getRoute('cattle', ['record' => $record->id]) : null),
                Tables\Columns\TextColumn::make('father.full_name')
                    ->label('Pai')
                    ->url(fn(?Cattle $record): ?string => $record?->father_id ? RouterHelper::getRoute('cattle', ['record' => $record->father_id]) : null),
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
