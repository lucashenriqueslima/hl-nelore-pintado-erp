<?php

namespace App\Filament\Resources\FinancialMovementResource\Pages;

use App\Enums\FinancialMovementType;
use App\Filament\Resources\FinancialMovementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListFinancialMovements extends ListRecords
{
    protected static string $resource = FinancialMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todos')
                ->icon('heroicon-o-currency-dollar'),
            'income' => Tab::make('Entradas')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', FinancialMovementType::Income))
                ->icon('heroicon-o-arrow-up-circle'),
            'expense' => Tab::make('Saídas')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', FinancialMovementType::Expense))
                ->icon('heroicon-o-arrow-down-circle'),
        ];
    }
}
