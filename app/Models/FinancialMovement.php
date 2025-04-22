<?php

namespace App\Models;

use App\Enums\FinancialMovementStatus;
use App\Enums\FinancialMovementType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialMovement extends Model
{
    protected $guarded = [];

    protected function installmentNumberWithTotal(): Attribute
    {
        return Attribute::get(
            fn() => "{$this->installment_number}/{$this->total_installments}"
        );
    }

    protected function casts(): array
    {
        return [
            'status' => FinancialMovementStatus::class,
            'type' => FinancialMovementType::class,
            'due_date' => 'date:Y-m-d',
            'payment_date' => 'date:Y-m-d',
        ];
    }


    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function financialMovementCategory(): BelongsTo
    {
        return $this->belongsTo(FinancialMovementCategory::class);
    }

    public function financialMovementGroup(): BelongsTo
    {
        return $this->belongsTo(FinancialMovementGroup::class);
    }

    public function movementable()
    {
        return $this->morphTo();
    }
}
