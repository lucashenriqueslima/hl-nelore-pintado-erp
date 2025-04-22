<?php

namespace App\Models;

use App\Enums\FinancialMovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialMovementCategory extends Model
{
    protected $guarded = [];

    //casts
    protected function casts(): array
    {
        return [
            'type' => FinancialMovementType::class,
        ];
    }

    public function financialMovements(): HasMany
    {
        return $this->hasMany(FinancialMovement::class);
    }
}
