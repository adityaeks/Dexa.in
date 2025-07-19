<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use App\Models\OrderStatistic;
use Carbon\CarbonPeriod;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Facades\Cache;

class OrderStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $start = now()->subMonths(6)->startOfMonth();
        $end = now()->endOfMonth();

        $months = [];
        $period = \Carbon\CarbonPeriod::create($start, '1 month', $end);
        foreach ($period as $dt) {
            $months[] = $dt->format('Y-m');
        }

        // Cache the statistics for 10 minutes for efficiency
        $cacheKey = 'order_stats_overview_' . $start->format('Ymd') . '_' . $end->format('Ymd');
        $stats = Cache::remember($cacheKey, 600, function () use ($start, $end, $months) {
            $orderStats = OrderStatistic::whereIn('period', $months)->orderBy('period')->get()->keyBy('period');
            $orderData = [];
            $doneData = [];
            $openData = [];
            $totalOrders = 0;
            $doneOrders = 0;
            $openOrders = 0;
            foreach ($months as $month) {
                $stat = $orderStats[$month] ?? null;
                $orderData[] = $stat?->total_orders ?? 0;
                $doneData[] = $stat?->done_orders ?? 0;
                $openData[] = $stat?->open_orders ?? 0;
                $totalOrders += $stat?->total_orders ?? 0;
                $doneOrders += $stat?->done_orders ?? 0;
                $openOrders += $stat?->open_orders ?? 0;
            }
            return [
                'orderData' => $orderData,
                'doneData' => $doneData,
                'openData' => $openData,
                'totalOrders' => $totalOrders,
                'doneOrders' => $doneOrders,
                'openOrders' => $openOrders,
            ];
        });

        return [
            Stat::make('Total Orders', $stats['totalOrders'])
                ->chart($stats['orderData']),
            Stat::make('Order Selesai', $stats['doneOrders'])
                ->chart($stats['doneData']),
            Stat::make('Order Belum Selesai', $stats['openOrders'])
                ->chart($stats['openData']),
        ];
    }
}
