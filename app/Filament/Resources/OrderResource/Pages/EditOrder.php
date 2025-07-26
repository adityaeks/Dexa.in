<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Set kode customer otomatis dari relasi customer saat update
        if (!empty($data['customer_id'])) {
            $customer = \App\Models\Customer::find($data['customer_id']);
            $data['code'] = $customer?->code;
        }
        // Ubah agar field 'nama' berisi array nama harga, bukan id
        if (!empty($data['nama']) && is_array($data['nama'])) {
            $hargaList = \App\Models\Harga::whereIn('id', $data['nama'])->get();
            $data['nama'] = $hargaList->pluck('nama')->toArray();
        }
        return $data;
    }

    protected function afterSave(): void
    {
        $user = Auth::user();
        Log::info('Filament Notification Debug', [
            'user' => $user,
            'user_id' => $user?->getAuthIdentifier(),
            'user_class' => $user ? get_class($user) : null,
        ]);
        Notification::make()
            ->title('Order berhasil diupdate!')
            ->success()
            ->sendToDatabase($user);
    }
}
