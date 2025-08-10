<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\Order;
use App\Models\Customer;
use Carbon\Carbon;

class DashboardOverview extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        // Hitung order hari ini
        $todayOrders = Order::whereDate('created_at', Carbon::today())->count();

        $totalCustomers = Customer::count();

        // Hitung total order
        $totalOrders = Order::count();

        return [
            Card::make('Today Order', $todayOrders)
                ->description('Order hari ini')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('success'),
            Card::make('Total Order', $totalOrders)
                ->description('Total semua order')
                ->descriptionIcon('heroicon-o-shopping-cart')
                ->color('info'),
            Card::make('Total Customers', $totalCustomers)
                ->description('Total semua customer')
                ->descriptionIcon('heroicon-o-user')
                ->color('warning'),
        ];
    }
}
