<?php

namespace App\Filament\Resources\AkademisiResource\Pages;

use App\Filament\Resources\AkademisiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAkademisis extends ListRecords
{
    protected static string $resource = AkademisiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
