<?php

namespace App\Filament\Resources\PaydayResource\Pages;

use App\Filament\Resources\PaydayResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayday extends EditRecord
{
    protected static string $resource = PaydayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
