<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
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

        // When a payout is created, update the funds table
        static::created(function ($payout) {
            $fund = Fund::latest()->first();

            if (!$fund) {
                // Create a new fund record if none exists
                $fund = Fund::create([
                    'in' => 0,
                    'out' => 0,
                ]);
            }

            // Add the payout amount to the 'out' field
            $fund->update([
                'in' => $fund->in - $payout->price,
                'out' => $fund->out + $payout->price
            ]);
        });

        // When a payout is updated, adjust the funds table
        static::updated(function ($payout) {
            $fund = Fund::latest()->first();

            if ($fund) {
                // Calculate the difference in price
                $priceDifference = $payout->price - $payout->getOriginal('price');

                // Update the 'out' field with the difference
                $fund->update([
                    'in' => $fund->in - $priceDifference,
                    'out' => $fund->out + $priceDifference
                ]);
            }
        });

        // When a payout is deleted, reverse the funds update
        static::deleted(function ($payout) {
            $fund = Fund::latest()->first();

            if ($fund) {
                // Subtract the payout amount from the 'out' field
                $fund->update([
                    'in' => $fund->in + $payout->price,
                    'out' => $fund->out - $payout->price
                ]);
            }
        });
    }
}
