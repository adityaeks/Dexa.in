<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment',
        'price_bayar',
        'price_normal',
        'price_sisa',
        'seq',
        'bukti_pembayaran',
        'tr_code',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
