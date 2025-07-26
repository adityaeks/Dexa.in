<?php

namespace App\Filament\Resources\PaydayResource\Pages;

use App\Filament\Resources\PaydayResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaydays extends ListRecords
{
    protected static string $resource = PaydayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
