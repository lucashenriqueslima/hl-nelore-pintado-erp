<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialMovementGroup extends Model
{
    protected $guarded = [];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function financialMovements(): HasMany
    {
        return $this->hasMany(FinancialMovement::class);
    }
}
