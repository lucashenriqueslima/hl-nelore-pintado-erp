<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CattleType: string implements HasLabel, HasColor, HasIcon
{
    case PO = 'po';
    case RECEIVER = 'receiver';

    public function getLabel(): string
    {
        return match ($this) {
            self::PO => 'PO',
            self::RECEIVER => 'Receptora',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PO => 'primary',
            self::RECEIVER => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PO => 'fas-dna',
            self::RECEIVER => 'fas-arrows-rotate',
        };
    }
}
