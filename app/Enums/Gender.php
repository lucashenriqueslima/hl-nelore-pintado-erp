<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;

enum Gender: string implements HasLabel, HasColor, HasIcon
{
    case MALE = 'male';
    case FEMALE = 'female';

    public function getLabel(): string
    {
        return match ($this) {
            self::MALE => 'Macho',
            self::FEMALE => 'Femea',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::MALE => 'primary',
            self::FEMALE => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::MALE => 'fas-mars',
            self::FEMALE => 'fas-venus',
        };
    }
}
