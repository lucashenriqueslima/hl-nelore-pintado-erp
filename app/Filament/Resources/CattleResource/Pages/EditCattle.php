<?php

namespace App\Filament\Resources\CattleResource\Pages;

use App\Filament\Resources\CattleResource;
use App\Filament\Resources\CattleResource\Widgets\SumStatsWidget;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditCattle extends EditRecord
{
    protected static string $resource = CattleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Action::make('view')
                ->infolist([])
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SumStatsWidget::class,
        ];
    }
}
