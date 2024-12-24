<?php

namespace App\Models;

use App\Enums\CattleType;
use App\Enums\Gender;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
