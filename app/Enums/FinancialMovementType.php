<?php

namespace App\Enums;


use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;

enum FinancialMovementType: string implements HasLabel, HasColor, HasIcon
{
    case Income = 'income';
    case Expense = 'expense';

    public function getLabel(): string
    {
        return match ($this) {
            self::Income => 'Entrada',
            self::Expense => 'Saída',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Income => 'success',
            self::Expense => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Income => 'heroicon-o-arrow-up-circle',
            self::Expense => 'heroicon-o-arrow-down-circle',
        };
    }
}
