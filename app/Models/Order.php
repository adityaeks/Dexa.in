<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

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
    ];

    protected $casts = [
        'file_tambahan' => 'array',
        'link_tambahan' => 'array',
    ];

    public function harga()
    {
        return $this->belongsTo(Harga::class, 'nama', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
