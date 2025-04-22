<?php

namespace App\Observers;

use App\Enums\EmbryoStatus;
use App\Models\Embryo;
use App\Models\EmbryoStatusHistory;
use Carbon\Carbon;

class EmbryoObserver
{


    public function updating(Embryo $embryo)
    {
        if ($embryo->isDirty('status')) {

            $now = now();

            EmbryoStatusHistory::where('embryo_id', $embryo->id)
                ->where('status', $embryo->getOriginal('status'))
                ->update([
                    'exited_at' => $now,
                ]);

            EmbryoStatusHistory::create([
                'embryo_id' => $embryo->id,
                'status' => $embryo->status,
            ]);
        }
    }
    /**
     * Handle the Embryo "created" event.
     */
    public function created(Embryo $embryo): void
    {

        EmbryoStatusHistory::create([
            'embryo_id' => $embryo->id,
            'status' => $embryo->status,
        ]);
    }

    /**
     * Handle the Embryo "updated" event.
     */
    public function updated(Embryo $embryo): void
    {
        //
    }

    /**
     * Handle the Embryo "deleted" event.
     */
    public function deleted(Embryo $embryo): void
    {
        //
    }

    /**
     * Handle the Embryo "restored" event.
     */
    public function restored(Embryo $embryo): void
    {
        //
    }

    /**
     * Handle the Embryo "force deleted" event.
     */
    public function forceDeleted(Embryo $embryo): void
    {
        //
    }
}
