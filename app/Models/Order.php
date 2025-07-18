<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Order extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'nama',
        'nomer_nota',
        'customer_id',
        'status',
        'prioritas',
        'status_payment',
        'price',
        'price_dexain',
        'price_akademisi',
        'due_days',
        'contact',
        'akademisi_id',
        'file_tambahan',
        'link_tambahan',
        'bukti_payment',
        'note',
        'amt_reff',
        'payment_ids', // <--- tambahkan field payment_ids agar bisa diisi
    ];

    protected $casts = [
        'file_tambahan' => 'array',
        'link_tambahan' => 'array',
        'payment_ids' => 'array',
    ];

    public function harga()
    {
        return $this->belongsTo(Harga::class, 'nama', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Jika ingin relasi ke payment terakhir, gunakan accessor berikut (opsional)
    // public function lastPayment()
    // {
    //     return $this->hasOne(Payment::class)->latestOfMany();
    // }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }
}
