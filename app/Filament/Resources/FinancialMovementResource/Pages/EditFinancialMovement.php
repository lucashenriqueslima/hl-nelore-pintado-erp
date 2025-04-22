<?php

namespace App\Filament\Resources\FinancialMovementResource\Pages;

use App\Filament\Resources\FinancialMovementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFinancialMovement extends EditRecord
{
    protected static string $resource = FinancialMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
