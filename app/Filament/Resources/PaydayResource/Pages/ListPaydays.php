<?php

namespace App\Filament\Resources\PaydayResource\Pages;

use App\Filament\Resources\PaydayResource;
use App\Filament\Resources\PaydayResource\Widgets\PaydayStatsOverview;
use App\Filament\Resources\PaydayResource\Widgets\PaydayFilterWidget;
use App\Filament\Resources\PaydayResource\Widgets\PaydayRankingChartWidget;
use App\Filament\Resources\PaydayResource\Widgets\PaydayPieChartWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\DatePicker;
use Carbon\Carbon;

class ListPaydays extends ListRecords
{
    protected static string $resource = PaydayResource::class;


    protected function getHeaderWidgets(): array
    {
        return [
            PaydayFilterWidget::class,
            PaydayRankingChartWidget::class,
            PaydayStatsOverview::class,
            // PaydayPieChartWidget::class,
        ];
    }

    protected function getListeners(): array
    {
        return [
            'payday-filter-updated' => '$refresh',
        ];
    }
}
