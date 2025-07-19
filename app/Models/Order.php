<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Order extends Model
{
    use HasFactory, LogsActivity;

    protected static function booted()
    {
        // Invalidate payment stats cache if status_payment changes
        static::updated(function ($order) {
            if ($order->isDirty('status_payment')) {
                $start = now()->subMonths(6)->startOfMonth();
                $end = now()->endOfMonth();
                $cacheKey = 'payment_stats_overview_' . $start->format('Ymd') . '_' . $end->format('Ymd');
                \Illuminate\Support\Facades\Cache::forget($cacheKey);
            }
        });

        // Invalidate order stats cache on create, update, delete
        $invalidateOrderStats = function () {
            $start = now()->subMonths(6)->startOfMonth();
            $end = now()->endOfMonth();
            $cacheKey = 'order_stats_overview_' . $start->format('Ymd') . '_' . $end->format('Ymd');
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
            // Jalankan job update statistik order secara langsung (sync, bukan queue)
            (new \App\Jobs\UpdateOrderStatisticsJob())->handle();
        };
        static::created($invalidateOrderStats);
        static::updated($invalidateOrderStats);
        static::deleted($invalidateOrderStats);
    }
    use HasFactory, LogsActivity;

    protected $fillable = [
        'nama',
        'nomer_nota',
        'customer_id',
        'customer_code', // kode customer
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
        'payment_ids',
    ];

    protected $casts = [
        'nama' => 'array',
        'file_tambahan' => 'array',
        'link_tambahan' => 'array',
        'payment_ids' => 'array',
    ];

    // Relasi ke banyak Harga (multi-jokian)
    public function hargas()
    {
        // return Collection Harga
        if (empty($this->nama) || !is_array($this->nama)) {
            return collect();
        }
        return Harga::whereIn('id', $this->nama)->get();
    }

    // Helper: total harga semua jokian
    public function getTotalHargaAttribute()
    {
        $hargas = $this->hargas();
        if ($hargas->isEmpty()) {
            return 0;
        }
        // Jika $hargas adalah Collection of Model Harga, pluck('harga') akan mengembalikan array harga
        // Jika $hargas adalah Collection of Collection (error), flatten dulu
        if ($hargas->first() instanceof \Illuminate\Database\Eloquent\Collection) {
            return $hargas->flatten(1)->pluck('harga')->sum();
        }
        return $hargas->sum(function($harga) {
            return is_object($harga) && isset($harga->harga) ? $harga->harga : 0;
        });
    }

    // Helper: label semua jokian
    public function getLabelJokianAttribute()
    {
        return $this->hargas()->map(function($harga) {
            $label = $harga->nama;
            if (isset($harga->tingkat)) {
                $label .= ' - ' . $harga->tingkat;
            }
            return $label;
        })->implode(', ');
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
