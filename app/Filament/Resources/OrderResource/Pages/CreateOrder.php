<?php

namespace App\Filament\Resources\OrderResource\Pages;


use App\Filament\Resources\OrderResource;
use App\Models\Harga;
use App\Models\FundDexain;
use App\Models\Fund;
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
        // Hitung total harga dari array id harga (multi-jokian) dengan qty JSON
        $totalHarga = 0;
        $hargaList = Harga::whereIn('id', (array)$data['nama'])->get();

        // Parse qty JSON
        $qtyJson = $data['qty'] ?? [];
        if (is_string($qtyJson)) {
            $qtyJson = json_decode($qtyJson, true) ?? [];
        }
        if (!is_array($qtyJson)) {
            $qtyJson = [];
        }
        $qtyTurnitin = (int) ($qtyJson['Turnitin'] ?? 0);
        $qtyParafrase = (int) ($qtyJson['Parafrase'] ?? 0);
        $qtyRevisi = (int) ($qtyJson['Revisi'] ?? 0);
        $qtyPerapian = (int) ($qtyJson['Perapian'] ?? 0);

        // Hitung total dengan qty per item
        $subsetNames = ['Turnitin', 'Parafrase', 'Revisi', 'Perapian'];
        $turnitinUnit = $hargaList->filter(fn ($h) => $h->nama === 'Turnitin')->sum('harga');
        $parafraseUnit = $hargaList->filter(fn ($h) => $h->nama === 'Parafrase')->sum('harga');
        $revisiUnit = $hargaList->filter(fn ($h) => $h->nama === 'Revisi')->sum('harga');
        $perapianUnit = $hargaList->filter(fn ($h) => $h->nama === 'Perapian')->sum('harga');
        $othersUnit = $hargaList->filter(fn ($h) => !in_array($h->nama, $subsetNames))->sum('harga');

        $turnitinTotal = $turnitinUnit * $qtyTurnitin;
        $parafraseTotal = $parafraseUnit * $qtyParafrase;
        $revisiTotal = $revisiUnit * $qtyRevisi;
        $perapianTotal = $perapianUnit * $qtyPerapian;
        $totalOthers = $othersUnit;

        $totalHarga = $turnitinTotal + $parafraseTotal + $revisiTotal + $perapianTotal + $totalOthers;
        $data['price'] = $totalHarga;

        // Cek apakah ada jokian Turnitin
        $hasTurnitin = $hargaList->contains('nama', 'Turnitin');

        // Hitung dan set price_dexain & price_akademisi di backend agar selalu tersimpan
        if ($totalHarga > 0) {
            if ($hasTurnitin) {
                // Alokasi fee: Turnitin 100% ke Dexain, sisanya normal 10%/20%
                $normalBase = $parafraseTotal + $revisiTotal + $perapianTotal + $totalOthers;
                if ($normalBase <= 100000) {
                    $normalFee = (int) round($normalBase * 0.1);
                } else {
                    $normalFee = (int) round($normalBase * 0.2);
                }
                $dexain = $turnitinTotal + $normalFee;
                $akademisi = $normalBase - $normalFee;
            } else {
                // Logic normal: price_dexain = 10% jika total <= 100000, 20% jika > 100000
                if ($totalHarga <= 100000) {
                    $dexain = (int) round($totalHarga * 0.1);
                } else {
                    $dexain = (int) round($totalHarga * 0.2);
                }
                $akademisi = $totalHarga - $dexain;
            }
            $data['price_dexain'] = $dexain;
            $data['price_akademisi'] = $akademisi;
        } else {
            $data['price_dexain'] = null;
            $data['price_akademisi'] = null;
        }

        // Set price_akademisi2 meskipun hanya satu akademisi
        $akademisiIds = $data['akademisi_id'] ?? [];
        if (!is_array($akademisiIds)) {
            $akademisiIds = [$akademisiIds];
        }
        $priceAkademisi2 = [];

        if (count($akademisiIds) === 1) {
            // Satu akademisi
            $akademisiHarga = $hasTurnitin ? 0 : ($data['price_akademisi'] ?? 0);
            $priceAkademisi2[] = [
                'akademisi_id' => $akademisiIds[0],
                'harga' => $akademisiHarga,
            ];
        } elseif (count($akademisiIds) > 1) {
            // Multi akademisi, gunakan input yang sudah ada atau default 0
            $existingPriceAkademisi2 = $data['price_akademisi2'] ?? [];
            if (is_string($existingPriceAkademisi2)) {
                $existingPriceAkademisi2 = json_decode($existingPriceAkademisi2, true);
            }

            // Buat map dari input yang sudah ada
            $existingPriceMap = [];
            if (is_array($existingPriceAkademisi2)) {
                foreach ($existingPriceAkademisi2 as $row) {
                    if (isset($row['akademisi_id']) && isset($row['harga'])) {
                        $existingPriceMap[$row['akademisi_id']] = $row['harga'];
                    }
                }
            }

            foreach ($akademisiIds as $id) {
                // Jika Turnitin dipilih, semua harga akademisi = 0
                $akademisiHarga = $hasTurnitin ? 0 : ($existingPriceMap[$id] ?? 0);
                $priceAkademisi2[] = [
                    'akademisi_id' => $id,
                    'harga' => $akademisiHarga,
                ];
            }
        }
        $data['price_akademisi2'] = $priceAkademisi2;
        // Set default status jika belum ada
        if (empty($data['status'])) {
            $data['status'] = 'Not started';
        }
        // Set default status_payment jika belum ada (dan pastikan selalu tersimpan di DB)
        if (empty($data['status_payment'])) {
            $data['status_payment'] = 'belum';
        }
        // Keep nama as array of IDs, don't convert to names
        // This ensures proper calculation in the form
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

        // Logic membuat Bill atau Payday otomatis berdasarkan nama akademisi
        $order = $this->record;
        if ($order) {
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
            $paydayAkademisi = ['cece', 'eko', 'amar', 'dexain'];

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

                    Log::info('Payday created for akademisi', [
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

                    Log::info('Bill created for akademisi', [
                        'order_id' => $order->id,
                        'akademisi_name' => $akademisi?->name,
                        'price' => $harga,
                        'tr_code' => $order->nomer_nota
                    ]);
                }
            }
        }

        // Logic untuk membuat data fund_dexain dan mengisi funds
        // Note: Event 'updated' di Order.php model akan skip increment jika ini adalah create pertama kali
        if ($order && $order->price_dexain > 0) {
            // Cek apakah order ini memiliki jokian Turnitin
            $hargaList = Harga::whereIn('id', (array)$order->nama)->get();
            $hasTurnitin = $hargaList->contains('nama', 'Turnitin');

            // Jika Turnitin dipilih, tidak perlu membuat fundDexain
            if (!$hasTurnitin) {
                $dividedAmount = $order->price_dexain / 4; // Bagi price_dexain menjadi 4 bagian

                $fundDexain = FundDexain::create([
                    'order_id' => $order->id,
                    'nomor_nota' => $order->nomer_nota,
                    'dexain' => $dividedAmount,
                    'eko' => $dividedAmount,
                    'amar' => $dividedAmount,
                    'cece' => $dividedAmount,
                ]);

                // Increment kolom 'in' di tabel fund (1/4 dari price_dexain)
                // Hanya untuk create pertama kali, update selanjutnya ditangani oleh Order model event
                $fund = \App\Models\Fund::first();
                if (!$fund) {
                    $fund = \App\Models\Fund::create(['in' => 0, 'out' => 0]);
                }
                $fund->increment('in', $dividedAmount);
            }
        }

        // Google Calendar event akan dibuat otomatis oleh OrderObserver
        // Tidak perlu manual create di sini untuk menghindari duplikasi
    }
}
