<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payin extends Model
{
    protected $fillable = [
        'description',
        'price',
        'bukti',
    ];

    protected $casts = [
        'bukti' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        // When a payin is created, update the funds table
        static::created(function ($payin) {
            $fund = Fund::latest()->first();

            if (!$fund) {
                // Create a new fund record if none exists
                $fund = Fund::create([
                    'in' => 0,
                    'out' => 0,
                ]);
            }

            // Add the payin amount to the 'in' field
            $fund->update([
                'in' => $fund->in + $payin->price,
                'out' => $fund->out
            ]);
        });

        // When a payin is updated, adjust the funds table
        static::updated(function ($payin) {
            $fund = Fund::latest()->first();

            if ($fund) {
                // Calculate the difference in price
                $priceDifference = $payin->price - $payin->getOriginal('price');

                // Update the 'in' field with the difference
                $fund->update([
                    'in' => $fund->in + $priceDifference,
                    'out' => $fund->out
                ]);
            }
        });

        // When a payin is deleted, reverse the funds update
        static::deleted(function ($payin) {
            $fund = Fund::latest()->first();

            if ($fund) {
                // Subtract the payin amount from the 'in' field
                $fund->update([
                    'in' => $fund->in - $payin->price,
                    'out' => $fund->out
                ]);
            }
        });
    }
}
