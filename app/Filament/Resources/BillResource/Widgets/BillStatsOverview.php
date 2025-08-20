<?php

namespace App\Filament\Resources\BillResource\Widgets;

use App\Models\Bill;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BillStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalPemasukan = Bill::sum('price');
        $totalPengeluaran = Bill::sum('amt_reff');
        $sisa = $totalPemasukan - $totalPengeluaran;

        return [
            Stat::make('Total Pemasukan', 'Rp ' . number_format($totalPemasukan, 0, '', '.')),
            Stat::make('Total Pembayaran', 'Rp ' . number_format($totalPengeluaran, 0, '', '.')),
            Stat::make('Belum Dibayar', 'Rp ' . number_format($sisa, 0, '', '.'))
        ];
    }
}
