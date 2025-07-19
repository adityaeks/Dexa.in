<?php

namespace App\Filament\Widgets;

use Filament\Widgets\LineChartWidget;
use App\Models\Customer;
use Illuminate\Support\Carbon;

class TotalCustomersChart extends LineChartWidget
{
    protected static ?string $heading = 'Total customers';
    protected int|string|array $columnSpan = '1/2';

    protected function getData(): array
    {
        $months = collect(range(0, 11))->map(function ($i) {
            return Carbon::now()->subMonths(11 - $i)->format('M Y');
        });

        $customers = Customer::selectRaw('DATE_FORMAT(created_at, "%b %Y") as month, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->pluck('count', 'month');

        $cumulative = 0;
        $data = $months->map(function ($month) use ($customers, &$cumulative) {
            $cumulative += $customers[$month] ?? 0;
            return $cumulative;
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Customers',
                    'data' => $data,
                    'borderColor' => '#fbbf24',
                    'backgroundColor' => 'rgba(251,191,36,0.2)',
                ],
            ],
            'labels' => $months->toArray(),
        ];
    }
}
