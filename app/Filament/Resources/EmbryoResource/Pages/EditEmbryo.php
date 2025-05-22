<?php

namespace App\Filament\Resources\EmbryoResource\Pages;

use App\Filament\Resources\EmbryoResource;
use App\Filament\Widgets\EmbryoStatusHistoryChart;
use Filament\Actions;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\EditRecord;

class EditEmbryo extends EditRecord
{
    protected static string $resource = EmbryoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            //
        ];
    }

    //after save
    protected function afterSave(): void
    {
        $this->dispatch('refreshRelation');
    }
}
