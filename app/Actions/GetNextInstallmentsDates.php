<?php

namespace App\Actions;

use Carbon\Carbon;

class GetNextInstallmentsDates
{
    public function handle(Carbon $startDate, int $installments): array
    {
        $installmentDates = [];
        $dueDay = $startDate->day;

        for ($i = 0; $i < $installments; $i++) {
            $newDate = $startDate->copy()->addMonths($i);

            if ($newDate->day !== $dueDay) {
                $newDate->endOfMonth();
            }

            $installmentDates[] = $newDate->format('Y-m-d');
        }

        return $installmentDates;
    }
}
