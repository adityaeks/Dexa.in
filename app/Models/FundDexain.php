<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundDexain extends Model
{
    use HasFactory;

    protected $table = 'fund_dexains';

    protected $fillable = [
        'order_id',
        'nomor_nota',
        'dexain',
        'eko',
        'amar',
        'cece',
    ];

    protected $casts = [
        'dexain' => 'decimal:2',
        'eko' => 'decimal:2',
        'amar' => 'decimal:2',
        'cece' => 'decimal:2',
    ];

    // Relasi ke Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
