<?php

namespace App\Filament\Resources\PaydayResource\Widgets;

use App\Models\Payday;
use App\Models\FundDexain;
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

        // Get ranking data with fund
        $rankingData = $query
            ->select('akademisi_name', DB::raw('SUM(CAST(price AS DECIMAL(15,2))) as total_price'))
            ->whereNotNull('akademisi_name')
            ->where('akademisi_name', '!=', '')
            ->groupBy('akademisi_name')
            ->orderByDesc('total_price')
            ->limit(10)
            ->get();

        // Get fund data for the same date range
        $fundQuery = FundDexain::query();

        if ($startDate) {
            $fundQuery->whereDate('created_at', '>=', $startDate);
        }

        if ($dueDate) {
            $fundQuery->whereDate('created_at', '<=', $dueDate);
        }

        $fundData = $fundQuery->select(
            DB::raw('SUM(CAST(dexain AS DECIMAL(15,2))) as total_dexain'),
            DB::raw('SUM(CAST(eko AS DECIMAL(15,2))) as total_eko'),
            DB::raw('SUM(CAST(amar AS DECIMAL(15,2))) as total_amar'),
            DB::raw('SUM(CAST(cece AS DECIMAL(15,2))) as total_cece')
        )->first();

        // Create a map of all akademisi with their fund data
        $allAkademisi = [
            // 'Dexain' => (float) $fundData->total_dexain,
            'Eko' => (float) $fundData->total_eko,
            'Amar' => (float) $fundData->total_amar,
            'Cece' => (float) $fundData->total_cece,
        ];

        // Combine payday and fund data
        $combinedData = [];

        // First, add akademisi from payday data
        foreach ($rankingData as $item) {
            $akademisiName = $item->akademisi_name;
            $totalPrice = (float) $item->total_price;
            $fundAmount = $allAkademisi[$akademisiName] ?? 0;

            $combinedData[] = [
                'name' => $akademisiName,
                'total' => $totalPrice + $fundAmount,
                'price' => $totalPrice,
                'fund' => $fundAmount
            ];
        }

        // Then, add akademisi that only have fund data (no payday)
        foreach ($allAkademisi as $akademisiName => $fundAmount) {
            // Check if this akademisi is already in combinedData
            $exists = false;
            foreach ($combinedData as $item) {
                if ($item['name'] === $akademisiName) {
                    $exists = true;
                    break;
                }
            }

            // If not exists and has fund, add it
            if (!$exists && $fundAmount > 0) {
                $combinedData[] = [
                    'name' => $akademisiName,
                    'total' => $fundAmount,
                    'price' => 0,
                    'fund' => $fundAmount
                ];
            }
        }

        // Sort by combined total
        usort($combinedData, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        // Take top 10
        $combinedData = array_slice($combinedData, 0, 10);

        // Reorder data to put highest in the middle (3,1,2 order)
        $labels = array_column($combinedData, 'name');
        $data = array_column($combinedData, 'total');
        $breakdown = array_map(function($item) {
            return "Price: Rp " . number_format($item['price'], 0, ',', '.') .
                   " | Fund: Rp " . number_format($item['fund'], 0, ',', '.');
        }, $combinedData);

        // If we have at least 3 items, reorder them
        if (count($labels) >= 3) {
            // Store the first 3 items
            $firstLabel = $labels[0];
            $firstData = $data[0];
            $firstBreakdown = $breakdown[0];
            $secondLabel = $labels[1];
            $secondData = $data[1];
            $secondBreakdown = $breakdown[1];
            $thirdLabel = $labels[2];
            $thirdData = $data[2];
            $thirdBreakdown = $breakdown[2];

            // Reorder: 3,1,2 (highest in middle)
            $labels[0] = $thirdLabel;
            $data[0] = $thirdData;
            $breakdown[0] = $thirdBreakdown;
            $labels[1] = $firstLabel;
            $data[1] = $firstData;
            $breakdown[1] = $firstBreakdown;
            $labels[2] = $secondLabel;
            $data[2] = $secondData;
            $breakdown[2] = $secondBreakdown;

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
                $colors[] = '#f87171'; // red - same as Eko card
            } elseif (str_contains($lowerLabel, 'amar')) {
                $colors[] = '#F59E0B'; // warning (orange) - same as Amar card
            } elseif (str_contains($lowerLabel, 'cece')) {
                $colors[] = '#10B981'; // green - same as Cece card
            } elseif (str_contains($lowerLabel, 'dexain')) {
                $colors[] = '#3B82F6'; // blue - same as Dexain card
            } else {
                // Default colors for other akademisi
                $colors[] = '#8B5CF6'; // purple
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Price + Fund (Rp)',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                    'borderWidth' => 2,
                    'breakdown' => $breakdown,
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
                        'afterLabel' => 'function(context) {
                            // This will be populated with price and fund breakdown
                            return context.dataset.breakdown ? context.dataset.breakdown[context.dataIndex] : "";
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
