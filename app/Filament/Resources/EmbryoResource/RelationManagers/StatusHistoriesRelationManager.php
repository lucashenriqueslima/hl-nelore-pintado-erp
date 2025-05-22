<?php

namespace App\Filament\Resources\EmbryoResource\RelationManagers;

use App\Models\EmbryoStatusHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\CarbonInterval;

class StatusHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'statusHistories';

    protected static ?string $title = 'Histórco';
    protected static ?string $modelLabel = 'Histórico';

    protected $listeners = ['refreshRelation' => '$refresh'];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->columns([
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Iniciado em')

                    ->date(),
                Tables\Columns\TextColumn::make('exited_at')
                    ->label('Finalizado em')
                    ->date(),
                Tables\Columns\TextColumn::make('time_in_status')
                    ->label('Duração')
                    ->getStateUsing(function (EmbryoStatusHistory $statusHistory) {
                        $secondsDifference = $statusHistory->created_at->diffInSeconds($statusHistory->exited_at);
                        $interval = CarbonInterval::seconds($secondsDifference);

                        return $interval->cascade()->forHumans();
                    })
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
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
