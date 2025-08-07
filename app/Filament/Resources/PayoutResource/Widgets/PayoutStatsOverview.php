<?php

namespace App\Filament\Resources\PayoutResource\Widgets;

use App\Models\Fund;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PayoutStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Get the latest fund record or create default values
        $fund = Fund::latest()->first();

        $totalIn = $fund ? $fund->in : 0;
        $totalOut = $fund ? $fund->out : 0;

        return [
            Stat::make('Total Kas', 'Rp ' . number_format($totalIn, 0, ',', '.'))
                ->description('Kas')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Total Pengeluaran', 'Rp ' . number_format($totalOut, 0, ',', '.'))
                ->description('Total dana keluar')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->chart([17, 16, 14, 15, 14, 13, 12]),
        ];
    }
}
