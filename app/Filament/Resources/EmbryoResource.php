<?php

namespace App\Filament\Resources;

use App\Enums\CattleType;
use App\Enums\EmbryoStatus;
use App\Enums\Gender;
use App\Filament\Helpers\RouterHelper;
use App\Filament\Resources\EmbryoResource\Pages;
use App\Filament\Resources\EmbryoResource\RelationManagers;
use App\Filament\Resources\EmbryoResource\RelationManagers\StatusHistoriesRelationManager;
use App\Models\Cattle;
use App\Models\Embryo;
use App\Models\EmbryoStatusHistory;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmbryoResource extends Resource
{
    protected static ?string $model = Embryo::class;

    protected static ?string $navigationIcon = 'fas-dna';

    protected static ?string $modelLabel = 'Embrião';
    protected static ?string $pluralModelLabel = 'Embriões';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Embrião')
                    ->schema([
                        TextInput::make('rgd')
                            ->label('RGD')
                            ->unique(ignoreRecord: true)
                            ->required(),
                        ToggleButtons::make('is_sexed_semen')
                            ->label('Sêmen é Sexado?')
                            ->inline()
                            ->boolean(),
                        Select::make('father_id')
                            ->label('Pai')
                            ->relationship(
                                name: 'father',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn(Builder $query) => $query->where('gender', Gender::MALE),
                            )
                            ->preload()
                            ->searchable(['rgd', 'name'])
                            ->getOptionLabelFromRecordUsing(fn(Cattle $record) => "{$record->rgd} | {$record->name}")
                            ->optionsLimit(10),
                        Select::make('mother_id')
                            ->label('Mãe')
                            ->relationship(
                                name: 'mother',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn(Builder $query) => $query->where('gender', Gender::FEMALE)->where('type', CattleType::PO),
                            )
                            ->preload()
                            ->searchable(['rgd', 'name'])
                            ->getOptionLabelFromRecordUsing(fn(Cattle $record) => "{$record->rgd} | {$record->name}")
                            ->optionsLimit(10),
                        Select::make('receiver_id')
                            ->label('Receptora')
                            ->relationship(
                                name: 'receiver',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn(Builder $query) => $query->where('gender', Gender::FEMALE)->where('type', CattleType::RECEIVER)
                                    ->where('type', CattleType::RECEIVER),
                            )
                            ->preload()
                            ->searchable(['rgd', 'name'])
                            ->getOptionLabelFromRecordUsing(fn(Cattle $record) => "{$record->rgd} | {$record->name}")
                            ->optionsLimit(10),
                        Select::make('status')
                            ->label('Status')
                            ->options(EmbryoStatus::class)
                            ->disabled(fn(string $operation): bool => $operation == 'edit')
                            ->suffixAction(
                                Action::make('next_status')
                                    ->label('Passar p/ Próximo Status')
                                    ->icon('heroicon-o-arrow-right')
                                    ->requiresConfirmation()
                                    ->link()
                                    ->hidden(fn(Embryo $embryo, string $operation): bool => $operation !== 'edit' || $embryo->status == EmbryoStatus::Selled)
                                    ->color('success')
                                    ->action(function (Embryo $embryo) {
                                        if ($embryo->status == EmbryoStatus::Selled) {
                                            return;
                                        }

                                        $embryo->status = $embryo->status->getNextStatus();
                                        $embryo->save();

                                        redirect(static::getUrl('edit', ['record' => $embryo->id,]));
                                    }),
                            ),
                        DatePicker::make('mating_date')
                            ->label('Data do Cruzamento'),
                        ToggleButtons::make('gender')
                            ->label('Sexo')
                            ->inline()
                            ->options(Gender::class),
                    ])
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
                TextColumn::make('father.name')
                    ->label('Pai')
                    ->url(fn(Embryo $embryo): string => RouterHelper::getRoute('cattle', ['record' => $embryo->father_id]))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mother.name')
                    ->label('Mãe')
                    ->url(fn(Embryo $embryo): string => RouterHelper::getRoute('cattle', ['record' => $embryo->mother_id]))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('receiver.name')
                    ->label('Receptora')
                    ->url(fn(Embryo $embryo): string => RouterHelper::getRoute('cattle', ['record' => $embryo->receiver_id]))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mating_date')
                    ->label('Data do Cruzamento')
                    ->date()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('gender')
                    ->label('Sexo')
                    ->searchable()
                    ->sortable(),

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
            StatusHistoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmbryos::route('/'),
            'create' => Pages\CreateEmbryo::route('/create'),
            'edit' => Pages\EditEmbryo::route('/{record}/edit'),
        ];
    }
}
