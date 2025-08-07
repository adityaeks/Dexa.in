<?php

namespace App\Filament\Resources\PaydayResource\Widgets;

use App\Models\Payday;
use App\Models\FundDexain;
use App\Models\Akademisi;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class PaydayStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Get akademisi IDs for Eko, Amar, and Cece
        $ekoId = Akademisi::where('name', 'Eko')->value('id') ?? null;
        $amarId = Akademisi::where('name', 'Amar')->value('id') ?? null;
        $ceceId = Akademisi::where('name', 'Cece')->value('id') ?? null;

        // Apply date filters if they exist in session
        $startDate = Session::get('payday_filter_start_date');
        $dueDate = Session::get('payday_filter_due_date');

        // Get Payday data for each akademisi
        $paydayQuery = Payday::query();
        if ($startDate) {
            $paydayQuery->whereDate('created_at', '>=', $startDate);
        }
        if ($dueDate) {
            $paydayQuery->whereDate('created_at', '<=', $dueDate);
        }

        $ekoPayday = $ekoId ? (clone $paydayQuery)->where('akademisi_id', $ekoId)->sum('price') : 0;
        $amarPayday = $amarId ? (clone $paydayQuery)->where('akademisi_id', $amarId)->sum('price') : 0;
        $cecePayday = $ceceId ? (clone $paydayQuery)->where('akademisi_id', $ceceId)->sum('price') : 0;

        // Get FundDexain data
        $fundQuery = FundDexain::query();
        if ($startDate) {
            $fundQuery->whereDate('created_at', '>=', $startDate);
        }
        if ($dueDate) {
            $fundQuery->whereDate('created_at', '<=', $dueDate);
        }

        $ekoFund = (clone $fundQuery)->sum('eko');
        $amarFund = (clone $fundQuery)->sum('amar');
        $ceceFund = (clone $fundQuery)->sum('cece');
        $kasDexainTotal = (clone $fundQuery)->sum('dexain');

        // Calculate totals (Payday + FundDexain)
        $ekoTotal = $ekoPayday + $ekoFund;
        $amarTotal = $amarPayday + $amarFund;
        $ceceTotal = $cecePayday + $ceceFund;

        // Calculate grand total
        $grandTotal = $ekoTotal + $amarTotal + $ceceTotal + $kasDexainTotal;

        return [
            Stat::make('Eko', 'Rp ' . number_format($ekoTotal, 0, '', '.'))
                ->description("+ Pajak: Rp " . number_format($ekoFund, 0, '', '.'))
                ->color('danger'),

            Stat::make('Amar', 'Rp ' . number_format($amarTotal, 0, '', '.'))
                ->description("+ Pajak: Rp " . number_format($amarFund, 0, '', '.'))
                ->color('warning'),

            Stat::make('Cece', 'Rp ' . number_format($ceceTotal, 0, '', '.'))
                ->description("+ Pajak: Rp " . number_format($ceceFund, 0, '', '.'))
                ->color('success'),

            Stat::make('Kas Dexain', 'Rp ' . number_format($kasDexainTotal, 0, '', '.'))
                ->description("Total Kas Dexain")
                ->color('info'),
        ];
    }
}
