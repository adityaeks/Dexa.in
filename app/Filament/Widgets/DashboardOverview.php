<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\Order;
use App\Models\Customer;

class DashboardOverview extends StatsOverviewWidget
{
    // protected function getCards(): array
    // {
    //     return [
    //         Card::make('Kas Dexain', 'Rp' . number_format(Order::sum('price_dexain')))
    //             ->description('Total kas Dexain dari orders')
    //             ->descriptionIcon('heroicon-o-banknotes')
    //             ->color('info'),
    //         Card::make('Pemasukan', 'Rp' . number_format(Order::sum('price')))
    //             ->description('Total pemasukan dari order')
    //             ->descriptionIcon('heroicon-o-arrow-trending-up')
    //             ->color('success'),
    //         Card::make('Total Pengeluaran', 'Rp' . number_format(Order::sum('price_akademisi')))
    //             ->description('Total pengeluaran dari order')
    //             ->descriptionIcon('heroicon-o-arrow-trending-down')
    //             ->color('danger'),
    //         Card::make('Belum Bayar', 'Rp' . number_format(Order::sum('price') - Order::sum('amt_reff')))
    //             ->description('Total tagihan belum dibayar')
    //             ->descriptionIcon('heroicon-o-exclamation-triangle')
    //             ->color('warning'),
    //     ];
    // }
}
