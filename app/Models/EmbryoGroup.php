<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmbryoGroup extends Model
{
    protected $guarded = [];

    public function embryos(): HasMany
    {
        return $this->hasMany(Embryo::class);
    }
}
