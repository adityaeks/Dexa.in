<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Notifications\Notifiable;


class Akademisi extends Model
{
    use LogsActivity;
    use Notifiable;


    protected $fillable = [
        'name',
        'nomor',
        'jurusan',
        'asal_kampus',
        'minat',
        'rekening'
    ];

    protected $casts = [
        'minat' => 'array',
    ];    protected static function boot()
    {
        parent::boot();

        static::creating(function ($akademisi) {
            // Pastikan nomor sudah dalam format +62
            if (!empty($akademisi->nomor) && !str_starts_with($akademisi->nomor, '+62')) {
                // Bersihkan dan tambahkan +62
                $nomor = preg_replace('/[^\d]/', '', $akademisi->nomor);
                $nomor = ltrim($nomor, '0');
                $akademisi->nomor = '+62' . $nomor;
            }
        });

        static::updating(function ($akademisi) {
            // Pastikan nomor sudah dalam format +62 saat update
            if (!empty($akademisi->nomor) && !str_starts_with($akademisi->nomor, '+62')) {
                // Bersihkan dan tambahkan +62
                $nomor = preg_replace('/[^\d]/', '', $akademisi->nomor);
                $nomor = ltrim($nomor, '0');
                $akademisi->nomor = '+62' . $nomor;
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }
}
