<?php

namespace App\Filament\Resources\PaymentResource\Widgets;

use App\Models\Payment;
use App\Models\Order;
use Carbon\CarbonPeriod;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class PaymentStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $start = now()->subMonths(6)->startOfMonth();
        $end = now()->endOfMonth();
        $months = [];
        $period = CarbonPeriod::create($start, '1 month', $end);
        foreach ($period as $dt) {
            $months[] = $dt->format('Y-m');
        }

        $cacheKey = 'payment_stats_overview_' . $start->format('Ymd') . '_' . $end->format('Ymd');
        $stats = Cache::remember($cacheKey, 600, function () use ($start, $end, $months) {
            $orderDataRaw = Order::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('ym')
                ->pluck('total', 'ym');

            // Pastikan data orders tidak kosong agar statistik tidak error
            $hasOrder = Order::whereBetween('created_at', [$start, $end])->exists();

            $lunasDataRaw = Order::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
                ->where('status_payment', 'lunas')
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('ym')
                ->pluck('total', 'ym');

            $dpDataRaw = Order::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
                ->where('status_payment', 'dp')
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('ym')
                ->pluck('total', 'ym');

            $belumDataRaw = Order::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
                ->where('status_payment', 'belum')
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('ym')
                ->pluck('total', 'ym');

            $orderData = [];
            $lunasData = [];
            $dpData = [];
            $belumData = [];
            foreach ($months as $month) {
                $orderData[] = $orderDataRaw[$month] ?? 0;
                $lunasData[] = $lunasDataRaw[$month] ?? 0;
                $dpData[] = $dpDataRaw[$month] ?? 0;
                $belumData[] = $belumDataRaw[$month] ?? 0;
            }

            // Pastikan semua key statistik selalu ada dan tidak undefined
            $safe = fn($v, $default = 0) => isset($v) ? $v : $default;
            return [
                'orderData' => $safe($orderData, array_fill(0, count($months), 0)),
                'lunasData' => $safe($lunasData, array_fill(0, count($months), 0)),
                'dpData' => $safe($dpData, array_fill(0, count($months), 0)),
                'belumData' => $safe($belumData, array_fill(0, count($months), 0)),
                'totalOrders' => Order::count() ?? 0,
                'lunasOrders' => Order::where('status_payment', 'lunas')->count() ?? 0,
                'dpOrders' => Order::where('status_payment', 'dp')->count() ?? 0,
                'belumOrders' => Order::where('status_payment', 'belum')->count() ?? 0,
            ];
        });

        return [
            Stat::make('Lunas', $stats['lunasOrders'] ?? 0)
                ->chart($stats['lunasData'] ?? []),
            Stat::make('DP', $stats['dpOrders'] ?? 0)
                ->chart($stats['dpData'] ?? []),
            Stat::make('Belum', $stats['belumOrders'] ?? 0)
                ->chart($stats['belumData'] ?? []),
        ];
    }
}
