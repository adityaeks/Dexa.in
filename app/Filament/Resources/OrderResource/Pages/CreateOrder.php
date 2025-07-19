<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Harga;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set kode customer otomatis dari relasi customer
        if (!empty($data['customer_id'])) {
            $customer = \App\Models\Customer::find($data['customer_id']);
            $data['customer_code'] = $customer?->code;
        }
        $data['nomer_nota'] = $this->generateNomerNota();
        // Pastikan price selalu terisi dari harga terkait
        if (empty($data['price']) && !empty($data['nama'])) {
            $harga = Harga::find($data['nama']);
            $data['price'] = $harga?->harga ?? 0;
        }
        // Hitung dan set price_dexain & price_akademisi di backend agar selalu tersimpan
        $price = isset($data['price']) ? (int) $data['price'] : 0;
        if ($price > 0) {
            if ($price <= 100000) {
                $dexain = (int) round($price * 0.1);
            } else {
                $dexain = (int) round($price * 0.2);
            }
            $akademisi = $price - $dexain;
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
}
