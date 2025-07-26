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
        'price_base',
        'price',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function akademisi()
    {
        return $this->belongsTo(Akademisi::class);
    }
}
