<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EmbryoStatus: string implements HasLabel, HasColor, HasIcon
{
    case Collected = 'collected';
    case Frozen = 'frozen';
    case Fertilized = 'fertilized';
    case Selled = 'selled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Collected => 'Coletado',
            self::Frozen => 'Congelado',
            self::Fertilized => 'Fertilizado',
            self::Selled => 'Vendido',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Collected => 'primary',
            self::Frozen => 'info',
            self::Fertilized => 'danger',
            self::Selled => 'success',
        };
    }

    public function getNextStatus(): self
    {
        return match ($this) {
            self::Collected => self::Frozen,
            self::Frozen => self::Fertilized,
            self::Fertilized => self::Selled,
            self::Selled => throw new \Exception('No next status'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Collected => 'fas-dna',
            self::Frozen => 'fas-snowflake',
            self::Fertilized => 'fas-flask',
            self::Selled => 'fas-hand-holding-dollar',
        };
    }
}
