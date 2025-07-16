<?php

namespace App\Filament\Resources\AkademisiResource\Pages;

use App\Filament\Resources\AkademisiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAkademisi extends EditRecord
{
    protected static string $resource = AkademisiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
