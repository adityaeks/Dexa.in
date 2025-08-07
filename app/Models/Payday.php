<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payday extends Model
{
    protected $fillable = [
        'order_id',
        'tr_code',
        'akademisi_id',
        'akademisi_name',
        'price_order',
        'price',
        'amt_reff',
        'status',
        'bukti_pembayaran',
        'seq',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function akademisi()
    {
        return $this->belongsTo(Akademisi::class);
    }

    protected $casts = [
        'bukti_pembayaran' => 'array',
    ];
}
