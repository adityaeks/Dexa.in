<?php
namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function handleRecordDeletion($record): ?\Illuminate\Database\Eloquent\Model
    {
        $order = \App\Models\Order::find($record->order_id);
        $result = parent::handleRecordDeletion($record);
        if ($order) {
            // Hitung ulang amt_reff setelah payment dihapus
            $amt_reff = $order->payments()->sum('price_bayar');
            $order->amt_reff = $amt_reff;
            // Update status_payment otomatis
            if ($amt_reff == 0) {
                $order->status_payment = 'belum';
            } elseif ($amt_reff < $order->price) {
                $order->status_payment = 'DP';
            } elseif ($amt_reff == $order->price) {
                $order->status_payment = 'Lunas';
            }
            $order->save();
        }
        return $result;
    }
}
