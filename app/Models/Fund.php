<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fund extends Model
{
    protected $fillable = [
        'in',
        'out',
    ];

    protected $casts = [
        'in' => 'decimal:2',
        'out' => 'decimal:2',
    ];
}
