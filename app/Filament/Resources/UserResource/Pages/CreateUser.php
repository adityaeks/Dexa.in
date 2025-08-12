<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Log data sebelum create
        Log::info('Data sebelum create user:', ['data' => $data]);

        // Hash password if provided
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        $record = $this->getRecord();

        // Log data setelah create
        Log::info('User berhasil dibuat:', ['user' => $record->toArray()]);

        return Notification::make()
            ->success()
            ->title('User Created')
            ->body('User ' . $record->name . ' telah dibuat dengan ID: ' . $record->id);
    }
    
}
