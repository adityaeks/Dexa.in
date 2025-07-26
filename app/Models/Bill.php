<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $fillable = [
        'akademisi_id',
        'akademisi_name',
        'price_order',
        'price',
        'amt_reff',
        'status',
        'bukti_pembayaran',
        'tr_code',
        'seq',
        'order_id',
    ];
    protected $casts = [
        'bukti_pembayaran' => 'array',
    ];

    public function akademisi()
    {
        return $this->belongsTo(Akademisi::class, 'akademisi_id');
    }
}
