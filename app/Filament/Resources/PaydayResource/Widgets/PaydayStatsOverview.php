<?php

namespace App\Filament\Resources\PaydayResource\Widgets;

use App\Models\Payday;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class PaydayStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $query = Payday::query();

        // Apply date filters if they exist in session
        $startDate = Session::get('payday_filter_start_date');
        $dueDate = Session::get('payday_filter_due_date');

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($dueDate) {
            $query->whereDate('created_at', '<=', $dueDate);
        }

        // Get filtered data from Payday table
        $ekoTotal = (clone $query)->where('akademisi_name', 'like', '%eko%')->sum('price');
        $amarTotal = (clone $query)->where('akademisi_name', 'like', '%amar%')->sum('price');
        $ceceTotal = (clone $query)->where('akademisi_name', 'like', '%cece%')->sum('price');

        // Get Kas Dexain from orders table with same date filters
        $orderQuery = Order::query();

        if ($startDate) {
            $orderQuery->whereDate('created_at', '>=', $startDate);
        }

        if ($dueDate) {
            $orderQuery->whereDate('created_at', '<=', $dueDate);
        }

        $totalKasDexain = $orderQuery->sum('price_dexain');

        // Bagi total Kas Dexain rata ke 4 bagian (Eko, Amar, Cece, Dexain)
        $dividedAmount = $totalKasDexain / 4;

        // Tambahkan bagian yang dibagi ke masing-masing total
        $ekoTotal += $dividedAmount;
        $amarTotal += $dividedAmount;
        $ceceTotal += $dividedAmount;
        $kasDexainTotal = $dividedAmount; // Kas Dexain hanya menampilkan bagiannya saja

        return [
            Stat::make('Eko', 'Rp ' . number_format($ekoTotal, 0, '', '.'))
                ->description("Pemasukan Eko")
                ->color('danger'),

            Stat::make('Amar', 'Rp ' . number_format($amarTotal, 0, '', '.'))
                ->description("Pemasukan Amar")
                ->color('warning'),

            Stat::make('Cece', 'Rp ' . number_format($ceceTotal, 0, '', '.'))
                ->description("Pemasukan Cece")
                ->color('success'),

            Stat::make('Kas Dexain', 'Rp ' . number_format($kasDexainTotal, 0, '', '.'))
                ->description("Total Kas Dexain")
                ->color('info'),
        ];
    }
}
