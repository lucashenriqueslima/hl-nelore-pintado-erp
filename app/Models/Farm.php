<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Farm extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'farm_is_from_group' => 'boolean',
        ];
    }

    public function cattle(): HasMany
    {
        return $this->hasMany(Cattle::class);
    }
}
