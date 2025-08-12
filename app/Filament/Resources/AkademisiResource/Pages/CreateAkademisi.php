<?php

namespace App\Filament\Resources\AkademisiResource\Pages;

use App\Filament\Resources\AkademisiResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAkademisi extends CreateRecord
{
    protected static string $resource = AkademisiResource::class;
    protected function afterCreate(): void
    {
        $this->record->notify(
            \Filament\Notifications\Notification::make()
                ->title('Akademisi Berhasil Dibuat!')
                ->body('Akademisi ' . $this->record->name . ' telah dibuat dengan ID: ' . $this->record->id)
                ->toDatabase()
        );
    }

}
