<?php

namespace App\Models;

use App\Enums\EmbryoStatus;
use App\Enums\Gender;
use App\Observers\EmbryoObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([EmbryoObserver::class])]
class Embryo extends Model
{

    protected $guarded = [];

    // public static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($model) {
    //         $model->embryo_group_id = Embryo::where('embryo_group_id', $model->embryo_group_id)
    //             ->count() + 1;
    //     });
    // }
    protected function casts(): array
    {
        return [
            'status' => EmbryoStatus::class,
            'gender' => Gender::class
        ];
    }

    protected function fullName(): Attribute
    {
        return Attribute::get(fn() => $this->rgd);
    }

    public function father(): BelongsTo
    {
        return $this->belongsTo(Cattle::class);
    }

    public function financialMovements()
    {
        return $this->morphMany(FinancialMovement::class, 'movementable');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(EmbryoGroup::class);
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
