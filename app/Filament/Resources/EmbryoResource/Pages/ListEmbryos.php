<?php

namespace App\Filament\Resources\EmbryoResource\Pages;

use App\Filament\Resources\EmbryoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmbryos extends ListRecords
{
    protected static string $resource = EmbryoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
