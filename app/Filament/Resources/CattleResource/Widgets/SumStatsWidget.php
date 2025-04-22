<?php

namespace App\Filament\Resources\CattleResource\Widgets;

use App\Helpers\NumberHelper;
use App\Models\Cattle;
use App\Services\CattleService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class SumStatsWidget extends BaseWidget
{
    public ?Cattle $record = null;
    public ?float $sum = null;

    protected function getStats(): array
    {
        if (!$this->record) {
            return [];
        }

        $this->sum = (new CattleService())->getFinancialMovementsSum($this->record);

        return [
            Stat::make('Lucro', NumberHelper::formatToCurrency($this->sum, true))
                ->icon('heroicon-o-currency-dollar')
                ->description('Soma total de movimentações financeiras'),
        ];
    }
}
