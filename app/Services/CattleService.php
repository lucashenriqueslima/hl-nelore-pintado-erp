<?php

namespace App\Services;

use App\Models\Cattle;

class CattleService
{
    public function getFinancialMovementsSum(Cattle $cattle): float
    {
        $sum = 0;

        // Sum for the cattle itself
        $sum += $cattle->financialMovements()->sum('value');

        // Sum for children and daughters with eager loading
        $sum += $cattle->children()->with('financialMovements')->get()
            ->sum(function ($child) {
                return $child->financialMovements->sum('value');
            });

        $sum += $cattle->daughters()->with('financialMovements')->get()
            ->sum(function ($daughter) {
                return $daughter->financialMovements->sum('value');
            });

        // Sum for embryo relationships with eager loading
        $sum += $cattle->embryoChildren()->with('financialMovements')->get()
            ->sum(function ($embryoChild) {
                return $embryoChild->financialMovements->sum('value');
            });

        $sum += $cattle->embryoDaughters()->with('financialMovements')->get()
            ->sum(function ($embryoDaughter) {
                return $embryoDaughter->financialMovements->sum('value');
            });

        $sum += $cattle->embryoReceiver()->with('financialMovements')->get()
            ->sum(function ($embryoReceiver) {
                return $embryoReceiver->financialMovements->sum('value');
            });

        return $sum;
    }

    public function getFinancialMovementsByEntity(Cattle $cattle): array
    {
        $financialMovements = [];

        $financialMovements['cattle'] = $cattle->financialMovements()->sum('value');

        $financialMovements['children'] = $cattle->children()->withSum('financialMovements as fm_sum', 'value')->get()
            ->sum('fm_sum');

        $financialMovements['daughters'] = $cattle->daughters()->withSum('financialMovements as fm_sum', 'value')->get()
            ->sum('fm_sum');

        if ($cattle->embryoChildren->isNotEmpty()) {
            $financialMovements['embryoChildren'] = $cattle->embryoChildren?->financialMovements()->sum('value');
        }

        if ($cattle->embryoDaughters->isNotEmpty()) {
            $financialMovements['embryoDaughters'] = $cattle->embryoDaughters?->financialMovements()->sum('value');
        }

        if ($cattle->embryoReceiver->isNotEmpty()) {
            $financialMovements['embryoReceiver'] = $cattle->embryoReceiver->financialMovements()->sum('value');
        }

        return $financialMovements;
    }
}
