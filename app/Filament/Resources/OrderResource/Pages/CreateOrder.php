<?php

namespace App\Filament\Resources\OrderResource\Pages;


use App\Filament\Resources\OrderResource;
use App\Models\Harga;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set kode customer otomatis dari relasi customer
        if (!empty($data['customer_id'])) {
            $customer = \App\Models\Customer::find($data['customer_id']);
            if ($customer && $customer instanceof \App\Models\Customer) {
                $data['customer_code'] = $customer->code;
            }
        }
        $data['nomer_nota'] = $this->generateNomerNota();
        // Hitung total harga dari array id harga (multi-jokian)
        $totalHarga = 0;
        if (!empty($data['nama']) && is_array($data['nama'])) {
            $totalHarga = Harga::whereIn('id', $data['nama'])->get()->sum('harga');
        } elseif (!empty($data['nama'])) {
            $harga = Harga::find($data['nama']);
            $totalHarga = $harga?->harga ?? 0;
        }
        $data['price'] = $totalHarga;
        // Hitung dan set price_dexain & price_akademisi di backend agar selalu tersimpan
        if ($totalHarga > 0) {
            if ($totalHarga <= 100000) {
                $dexain = (int) round($totalHarga * 0.1);
            } else {
                $dexain = (int) round($totalHarga * 0.2);
            }
            $akademisi = $totalHarga - $dexain;
            $data['price_dexain'] = $dexain;
            $data['price_akademisi'] = $akademisi;
        } else {
            $data['price_dexain'] = null;
            $data['price_akademisi'] = null;
        }
        // Set default status jika belum ada
        if (empty($data['status'])) {
            $data['status'] = 'Not started';
        }
        // Set default status_payment jika belum ada (dan pastikan selalu tersimpan di DB)
        if (empty($data['status_payment'])) {
            $data['status_payment'] = 'belum';
        }
        // Ubah agar field 'nama' berisi array nama harga, bukan id
        if (!empty($data['nama']) && is_array($data['nama'])) {
            $hargaList = \App\Models\Harga::whereIn('id', $data['nama'])->get();
            $data['nama'] = $hargaList->pluck('nama')->toArray();
        }
        return $data;
    }

    private function generateNomerNota(): string
    {
        $month = date('n');
        $year = date('y');
        $prefix = chr(64 + $month); // A=Jan, B=Feb, dst
        $lastOrder = \App\Models\Order::whereYear('created_at', date('Y'))
            ->whereMonth('created_at', $month)
            ->orderByDesc('id')
            ->first();
        $lastNumber = 0;
        if ($lastOrder && preg_match('/^[A-Z](\d{2})(\d{3})$/', $lastOrder->nomer_nota, $matches)) {
            $lastNumber = (int) $matches[2];
        }
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        return $prefix . $year . $newNumber;
    }

    protected function afterCreate(): void
    {
        $user = Auth::user();
        Log::info('Filament Notification Debug', [
            'user' => $user,
            'user_id' => $user?->id,
            'user_class' => $user ? get_class($user) : null,
        ]);
        Notification::make()
            ->title('Order berhasil dibuat!')
            ->success()
            ->sendToDatabase($user);

        // Logic membuat Bill otomatis
        $order = $this->record;
        if ($order) {
            $akademisiIds = $order->akademisi_id ?: [];
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
            foreach (array_values($akademisiIds) as $i => $akademisiId) {
                $akademisi = \App\Models\Akademisi::find($akademisiId);
                $harga = $priceMap[$akademisiId] ?? 0;
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
            }
        }
    }
}
