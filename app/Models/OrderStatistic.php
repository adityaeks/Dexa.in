<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatistic extends Model
{
    protected $fillable = [
        'period',
        'total_orders',
        'done_orders',
        'open_orders',
    ];
    public $timestamps = false;
}
