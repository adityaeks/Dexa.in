<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Customer extends Model
{
    use LogsActivity;

    protected $fillable = [
        'code',
        'name',
        'nomor',
        'description',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            if (empty($customer->code)) {
                $customer->code = static::generateCustomerCode();
            }
        });
    }

    private static function generateCustomerCode()
    {
        $lastCustomer = static::orderBy('id', 'desc')->first();

        if (!$lastCustomer) {
            return 'CUST#001';
        }

        // Extract nomor dari kode terakhir
        $lastCode = $lastCustomer->code;
        $lastNumber = (int) substr($lastCode, 5); // Ambil nomor setelah "CUST#"
        $newNumber = $lastNumber + 1;

        // Format dengan 3 digit
        return 'CUST#' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }
}
