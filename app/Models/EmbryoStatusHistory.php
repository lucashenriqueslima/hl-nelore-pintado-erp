<?php

namespace App\Models;

use App\Enums\EmbryoStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmbryoStatusHistory extends Model
{
    protected $guarded = [];


    protected $casts = [
        'status' => EmbryoStatus::class,
        'exited_at' => 'datetime',
        'time_in_status' => 'integer',
    ];
    public function embryo(): BelongsTo
    {
        return $this->belongsTo(Embryo::class);
    }
}
