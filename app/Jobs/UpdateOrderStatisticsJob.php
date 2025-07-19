<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\OrderStatistic;
use Carbon\CarbonPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateOrderStatisticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $start = now()->subMonths(6)->startOfMonth();
        $end = now()->endOfMonth();
        $period = CarbonPeriod::create($start, '1 month', $end);

        foreach ($period as $dt) {
            $month = $dt->format('Y-m');
            $total = Order::whereYear('created_at', $dt->year)
                ->whereMonth('created_at', $dt->month)
                ->count();
            $done = Order::where('status', 'Done')
                ->whereYear('created_at', $dt->year)
                ->whereMonth('created_at', $dt->month)
                ->count();
            $open = Order::whereIn('status', ['Not started', 'Inprogress'])
                ->whereYear('created_at', $dt->year)
                ->whereMonth('created_at', $dt->month)
                ->count();

            OrderStatistic::updateOrCreate(
                ['period' => $month],
                [
                    'total_orders' => $total,
                    'done_orders' => $done,
                    'open_orders' => $open,
                ]
            );
        }
    }
}
