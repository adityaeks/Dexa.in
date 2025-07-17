<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Order;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        // Validasi data
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment' => 'required|string',
            'price_bayar' => 'required|numeric',
            'price_normal' => 'required|numeric',
            'price_sisa' => 'nullable|numeric', // tambahkan validasi ini
            'bukti_pembayaran' => 'nullable|string',
        ]);

        $order = Order::find($request->order_id);
        $lastPayment = $order->payments()->latest('id')->first();
        $sisaSebelumnya = $lastPayment ? (int) $lastPayment->price_sisa : (int) $order->price;
        $priceBayar = (int) $request->price_bayar;
        // Validasi: bayar tidak boleh lebih dari sisa
        if ($priceBayar > $sisaSebelumnya) {
            return response()->json([
                'success' => false,
                'message' => 'Nominal bayar tidak boleh lebih dari sisa pembayaran (' . number_format($sisaSebelumnya, 0, '', '.') . ')',
            ], 422);
        }
        $validated['price_sisa'] = $sisaSebelumnya - $priceBayar;
        if ($validated['price_sisa'] < 0) {
            $validated['price_sisa'] = 0;
        }

        // Tentukan seq otomatis
        $validated['seq'] = $order->payments()->count() + 1;
        // Set nomer_nota otomatis dari order
        $validated['tr_code'] = $order->nomer_nota;

        // Create payment
        $payment = Payment::create($validated);

        // Update amt_reff pada order terkait
        $order = Order::find($payment->order_id);
        if ($order) {
            $order->amt_reff = (int) $order->amt_reff + (int) $payment->price_bayar;
            // Update status_payment otomatis
            if ($order->amt_reff == 0) {
                $order->status_payment = 'belum';
            } elseif ($order->amt_reff < $order->price) {
                $order->status_payment = 'DP';
            } elseif ($order->amt_reff == $order->price) {
                $order->status_payment = 'Lunas';
            }
            $order->save();
        }

        return response()->json([
            'success' => true,
            'payment' => $payment,
        ]);
    }
}
