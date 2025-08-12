<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function afterCreate(): void
    {
        $user = Auth::user();

        // Kirim notifikasi ke database
        $user->notify(
            Notification::make()
                ->title('Customer Berhasil Dibuat!')
                ->body('Customer ' . $this->record->name . ' telah dibuat dengan ID: ' . $this->record->id)
                ->toDatabase()
        );

        // Tampilkan toast notification
        Notification::make()
            ->title('Customer Berhasil Dibuat!')
            ->success()
            ->send();
    }
}
