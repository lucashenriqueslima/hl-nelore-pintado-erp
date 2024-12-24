<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Farm extends Model
{
    protected $guarded = [];

    public function cattle(): HasMany
    {
        return $this->hasMany(Cattle::class);
    }
}
