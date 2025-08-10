<?php

namespace App\Filament\Resources\PayinResource\Pages;

use App\Filament\Resources\PayinResource;
use App\Filament\Resources\PayinResource\Widgets\PayinStatsOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayins extends ListRecords
{
    protected static string $resource = PayinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PayinStatsOverview::class,
        ];
    }
}
