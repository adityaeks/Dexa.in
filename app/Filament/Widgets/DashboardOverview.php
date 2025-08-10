<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Fund;
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

        // Ambil kas dari tabel funds
        $fund = Fund::latest()->first();
        $kasDexain = $fund ? $fund->in : 0;

        return [
            Card::make('Kas Dexain', 'Rp ' . number_format($kasDexain, 0, ',', '.'))
                ->description('Total kas saat ini')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('primary'),
            Card::make('Today Order', $todayOrders)
                ->description('Order hari ini')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('success'),
            Card::make('Total Order', $totalOrders)
                ->description('Total order')
                ->descriptionIcon('heroicon-o-shopping-cart')
                ->color('info'),
            Card::make('Total Customers', $totalCustomers)
                ->description('Total customer')
                ->descriptionIcon('heroicon-o-user')
                ->color('warning'),
        ];
    }
}
