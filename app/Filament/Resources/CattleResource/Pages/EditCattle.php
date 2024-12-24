<?php

namespace App\Filament\Resources\CattleResource\Pages;

use App\Filament\Resources\CattleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCattle extends EditRecord
{
    protected static string $resource = CattleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
