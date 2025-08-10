<?php

namespace App\Filament\Resources\PayinResource\Pages;

use App\Filament\Resources\PayinResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayin extends EditRecord
{
    protected static string $resource = PayinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
