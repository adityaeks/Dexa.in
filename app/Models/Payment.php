<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Payment extends Model
{
    use HasFactory, LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }
}
