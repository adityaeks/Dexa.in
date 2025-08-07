<?php

namespace App\Filament\Resources\PaydayResource\Widgets;

use App\Models\Payday;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class PaydayRankingChartWidget extends ChartWidget
{
    // protected static ?string $heading = 'Peringkat Total Perolehan Akademisi';

    // protected static ?string $maxHeight = '400px';

    protected static string $color = 'success';

    // public function getDescription(): ?string
    // {
    //     return 'Total perolehan price berdasarkan akademisi';
    // }

    protected function getData(): array
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

        // Get ranking data
        $rankingData = $query
            ->select('akademisi_name', DB::raw('SUM(CAST(price AS DECIMAL(15,2))) as total_price'))
            ->whereNotNull('akademisi_name')
            ->where('akademisi_name', '!=', '')
            ->groupBy('akademisi_name')
            ->orderByDesc('total_price')
            ->limit(10)
            ->get();

        // Reorder data to put highest in the middle (3,1,2 order)
        $labels = $rankingData->pluck('akademisi_name')->toArray();
        $data = $rankingData->pluck('total_price')->toArray();

        // If we have at least 3 items, reorder them
        if (count($labels) >= 3) {
            // Store the first 3 items
            $firstLabel = $labels[0];
            $firstData = $data[0];
            $secondLabel = $labels[1];
            $secondData = $data[1];
            $thirdLabel = $labels[2];
            $thirdData = $data[2];

            // Reorder: 3,1,2 (highest in middle)
            $labels[0] = $thirdLabel;
            $data[0] = $thirdData;
            $labels[1] = $firstLabel;
            $data[1] = $firstData;
            $labels[2] = $secondLabel;
            $data[2] = $secondData;

            // Add ranking numbers to labels
            $labels[0] = "3. " . $labels[0];
            $labels[1] = "1. " . $labels[1];
            $labels[2] = "2. " . $labels[2];
        }

        // Define colors based on akademisi names to match the stats cards
        $colors = [];
        foreach ($labels as $label) {
            $lowerLabel = strtolower($label);
            if (str_contains($lowerLabel, 'eko')) {
                $colors[] = '#f87171'; // success (green) - same as Eko card
            } elseif (str_contains($lowerLabel, 'amar')) {
                $colors[] = '#F59E0B'; // warning (orange) - same as Amar card
            } elseif (str_contains($lowerLabel, 'cece')) {
                $colors[] = '#10B981'; // info (blue) - same as Cece card
            } else {
                // Default colors for other akademisi
                $colors[] = '#8B5CF6'; // purple
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Price (Rp)',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            return "Total: Rp " + new Intl.NumberFormat("id-ID").format(context.parsed.y);
                        }',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) {
                            return "Rp " + new Intl.NumberFormat("id-ID").format(value);
                        }',
                    ],
                ],
                'x' => [
                    'ticks' => [
                        'maxRotation' => 45,
                        'minRotation' => 0,
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}
