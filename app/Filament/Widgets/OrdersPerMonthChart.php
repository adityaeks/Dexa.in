<?php

namespace App\Filament\Widgets;

use Filament\Widgets\LineChartWidget;
use App\Models\Order;
use Illuminate\Support\Carbon;

class OrdersPerMonthChart extends LineChartWidget
{
    protected static ?string $heading = 'Orders per month';
    protected int|string|array $columnSpan = '1/2';

    protected function getData(): array
    {
        $months = collect(range(0, 11))->map(function ($i) {
            return Carbon::now()->subMonths(11 - $i)->format('M Y');
        });

        $orders = Order::selectRaw('DATE_FORMAT(created_at, "%b %Y") as month, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->pluck('count', 'month');

        $data = $months->map(fn($month) => $orders[$month] ?? 0)->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $data,
                    'borderColor' => '#fbbf24',
                    'backgroundColor' => 'rgba(251,191,36,0.2)',
                ],
            ],
            'labels' => $months->toArray(),
        ];
    }
}
