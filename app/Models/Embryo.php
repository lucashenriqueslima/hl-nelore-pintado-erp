<?php

namespace App\Models;

use App\Enums\EmbryoStatus;
use App\Enums\Gender;
use App\Observers\EmbryoObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([EmbryoObserver::class])]
class Embryo extends Model
{

    protected $guarded = [];
    protected function casts(): array
    {
        return [
            'status' => EmbryoStatus::class,
            'gender' => Gender::class
        ];
    }

    public function father(): BelongsTo
    {
        return $this->belongsTo(Cattle::class);
    }

    public function mother(): BelongsTo
    {
        return $this->belongsTo(Cattle::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(Cattle::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(EmbryoStatusHistory::class);
    }
}
