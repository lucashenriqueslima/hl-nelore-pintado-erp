<?php

namespace App\Models;

use App\Enums\CattleType;
use App\Enums\Gender;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Cattle extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'type' => CattleType::class,
            'gender' => Gender::class,
            'attachments' => 'array',
            'aquisition_date' => 'date',
            'birth_date' => 'date',
            'death_date' => 'date',
            'first_installment_date' => 'date',
            'commision_percentage' => 'float',
            'number_installments' => 'integer',
        ];
    }

    protected function fullName(): Attribute
    {
        return Attribute::get(fn() => "{$this->rgd} | {$this->name}");
    }

    public function embryoFather(): HasMany
    {
        return $this->hasMany(Embryo::class, 'father_id');
    }

    public function embryoMother(): HasMany
    {
        return $this->hasMany(Embryo::class, 'mother_id');
    }

    public function embryoReceiver(): HasMany
    {
        return $this->hasMany(Embryo::class, 'receiver_id');
    }
    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function father(): BelongsTo
    {
        return $this->belongsTo(Cattle::class);
    }

    public function mother(): BelongsTo
    {
        return $this->belongsTo(Cattle::class);
    }

    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(related: Partner::class)
            ->withPivot('percentage')
            ->using(PartnerCattle::class);
    }

    public function weights(): HasMany
    {
        return $this->hasMany(Weight::class);
    }
}
