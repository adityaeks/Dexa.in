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
            $data['customer_code'] = $customer?->code;
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

        // Logic untuk update Bill dan Payday saat order diupdate
        $order = $this->record;
        if ($order) {
            // Hapus Bill dan Payday yang lama untuk order ini
            \App\Models\Bill::where('order_id', $order->id)->delete();
            \App\Models\Payday::where('order_id', $order->id)->delete();

            $akademisiIds = $order->akademisi_id ?: [];

            // Pastikan akademisiIds adalah array
            if (!is_array($akademisiIds)) {
                $akademisiIds = [$akademisiIds];
            }

            // Ambil array price_akademisi2 (bisa json string atau array)
            $priceAkademisi2 = $order->price_akademisi2;
            if (is_string($priceAkademisi2)) {
                $priceAkademisi2 = json_decode($priceAkademisi2, true);
            }

            $priceMap = [];
            if (is_array($priceAkademisi2)) {
                foreach ($priceAkademisi2 as $row) {
                    if (isset($row['akademisi_id']) && isset($row['harga'])) {
                        $priceMap[$row['akademisi_id']] = $row['harga'];
                    }
                }
            }

            // Daftar nama akademisi yang harus membuat Payday alih-alih Bill
            $paydayAkademisi = ['cece', 'eko', 'amar'];

            foreach (array_values($akademisiIds) as $i => $akademisiId) {
                $akademisi = \App\Models\Akademisi::find($akademisiId);

                // Jika tidak ada harga spesifik di price_akademisi2, gunakan pembagian rata
                $harga = $priceMap[$akademisiId] ?? 0;
                if ($harga == 0 && count($akademisiIds) > 0) {
                    $harga = (int) ($order->price_akademisi / count($akademisiIds));
                }

                $akademisiName = strtolower($akademisi?->name ?? '');

                // Cek apakah akademisi termasuk dalam daftar yang harus membuat Payday
                if (in_array($akademisiName, $paydayAkademisi)) {
                    // Buat data Payday untuk akademisi cece/eko/amar
                    \App\Models\Payday::create([
                        'order_id' => $order->id,
                        'tr_code' => $order->nomer_nota,
                        'akademisi_id' => $akademisiId,
                        'akademisi_name' => $akademisi?->name ?? '',
                        'price_order' => $order->price,
                        'price' => $harga,
                        'amt_reff' => 0,
                        'status' => 'belum',
                        'seq' => $i + 1,
                    ]);

                    Log::info('Payday updated for akademisi', [
                        'order_id' => $order->id,
                        'akademisi_name' => $akademisi?->name,
                        'price' => $harga,
                        'tr_code' => $order->nomer_nota
                    ]);
                } else {
                    // Buat data Bill untuk akademisi lainnya
                    \App\Models\Bill::create([
                        'akademisi_id' => $akademisiId,
                        'akademisi_name' => $akademisi?->name ?? '',
                        'tr_code' => $order->nomer_nota,
                        'price' => $harga,
                        'price_order' => $order->price,
                        'amt_reff' => 0,
                        'status' => 'belum',
                        'bukti_pembayaran' => null,
                        'order_id' => $order->id,
                        'seq' => $i + 1,
                    ]);

                    Log::info('Bill updated for akademisi', [
                        'order_id' => $order->id,
                        'akademisi_name' => $akademisi?->name,
                        'price' => $harga,
                        'tr_code' => $order->nomer_nota
                    ]);
                }
            }
        }
    }
}
