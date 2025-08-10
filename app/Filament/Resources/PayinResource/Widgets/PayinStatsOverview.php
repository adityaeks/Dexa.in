<?php

namespace App\Filament\Resources\PayinResource\Widgets;

use App\Models\Fund;
use App\Models\Payin;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PayinStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Get the latest fund record or create default values
        $fund = Fund::latest()->first();

        $totalIn = $fund ? $fund->in : 0;
        $totalPayins = Payin::sum('price');

        return [
            Stat::make('Total Kas', 'Rp ' . number_format($totalIn, 0, ',', '.'))
                ->description('Kas')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Total Pemasukan', 'Rp ' . number_format($totalPayins, 0, ',', '.'))
                ->description('Total dana masuk')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([17, 16, 14, 15, 14, 13, 12]),
        ];
    }
}
