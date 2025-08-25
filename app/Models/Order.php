<?php

namespace App\Models;

use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\Fund;
use App\Models\FundDexain;
use App\Models\Payin;

class Order extends Model implements Eventable
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

        // Auto create Payin record when Turnitin is selected
        static::created(function ($order) {
            $hargaList = Harga::whereIn('id', (array)$order->nama)->get();
            $hasTurnitin = $hargaList->contains('nama', 'Turnitin');

            if ($hasTurnitin) {
                $turnitinHarga = $hargaList->where('nama', 'Turnitin')->first();
                if ($turnitinHarga) {
                    $description = $turnitinHarga->nama;
                    if ($turnitinHarga->tingkat) {
                        $description .= ' - ' . $turnitinHarga->tingkat;
                    }
                    // if ($turnitinHarga->harga) {
                    //     $description .= ' - Rp ' . number_format($turnitinHarga->harga, 0, ',', '.');
                    // }

                    // Create Payin record
                    Payin::create([
                        'description' => $description,
                        'price' => $order->price,
                    ]);
                }
            }
        });

        // Logic untuk mengembalikan value fund_dexain dan fund ketika order dihapus
        static::deleting(function ($order) {
            // Logic untuk mengembalikan value fund_dexain dan fund
            if ($order->price_dexain && $order->price_dexain > 0) {
                // Cari fund_dexain berdasarkan order_id (lebih akurat)
                $fundDexain = FundDexain::where('order_id', $order->id)->first();

                if ($fundDexain) {
                    // Kurangi nilai dexain dari funds (kembalikan ke nilai awal)
                    $fund = Fund::first();
                    if ($fund) {
                        $fund->decrement('in', $fundDexain->dexain);
                    }
                    // Note: fund_dexain akan otomatis terhapus oleh foreign key cascade
                }
            }

            // Logic untuk menghapus data Payin ketika order Turnitin dihapus
            $hargaList = Harga::whereIn('id', (array)$order->nama)->get();
            $hasTurnitin = $hargaList->contains('nama', 'Turnitin');

            if ($hasTurnitin) {
                // Hapus data Payin yang terkait dengan order Turnitin ini
                // Cari berdasarkan price yang sama dengan order dan description yang mengandung "Turnitin"
                Payin::where('price', $order->price)
                     ->where('description', 'like', '%Turnitin%')
                     ->delete();

                // Kurangi value 'in' di tabel fund untuk order Turnitin
                // Karena Turnitin langsung masuk ke dexain (tidak melalui fundDexain)
                $fund = Fund::first();
                if ($fund && $order->price_dexain > 0) {
                    $fund->decrement('in', $order->price_dexain);
                }
            }
        });

        static::deleted(function ($order) {
            // Jalankan invalidate order stats
            $start = now()->subMonths(6)->startOfMonth();
            $end = now()->endOfMonth();
            $cacheKey = 'order_stats_overview_' . $start->format('Ymd') . '_' . $end->format('Ymd');
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
            (new \App\Jobs\UpdateOrderStatisticsJob())->handle();
        });

        // Update funds table and fund_dexain when price_dexain is changed
        static::updated(function ($order) {
            if ($order->isDirty('price_dexain')) {
                // Skip jika ini adalah create pertama kali (oldValue adalah null dan newValue > 0)
                $oldValue = $order->getOriginal('price_dexain') ?? 0;
                $newValue = $order->price_dexain ?? 0;

                // Jika oldValue adalah 0 dan newValue > 0, ini kemungkinan create pertama kali
                // Biarkan CreateOrder.php yang menangani increment pertama
                if ($oldValue == 0 && $newValue > 0) {
                    return;
                }

                $fund = Fund::first();
                if (!$fund) {
                    $fund = Fund::create(['in' => 0, 'out' => 0]);
                }

                // Calculate the difference for fund_dexain (hanya 1/4 dari difference)
                $difference = ($newValue - $oldValue) / 4;

                // Update the funds table dengan nilai dexain (1/4 dari price_dexain)
                if ($difference != 0) {
                    $fund->increment('in', $difference);
                }

                // Update fund_dexain record jika ada
                $fundDexain = FundDexain::where('order_id', $order->id)->first();
                if ($fundDexain) {
                    $newDividedAmount = $newValue / 4;
                    $fundDexain->update([
                        'dexain' => $newDividedAmount,
                        'eko' => $newDividedAmount,
                        'amar' => $newDividedAmount,
                        'cece' => $newDividedAmount,
                    ]);
                }
            }
        });
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
        'price_akademisi2',
        'start_date',
        'due_date',
        'google_calendar_event_id',
        'contact',
        'akademisi_id',
        'file_tambahan',
        'link_tambahan',
        'note',
        'amt_reff',
        'payment_ids',
        'qty',
    ];

    protected $casts = [
        'nama' => 'array',
        'qty' => 'array',
        'file_tambahan' => 'array',
        'link_tambahan' => 'array',
        'payment_ids' => 'array',
        'akademisi_id' => 'array',
        'price_akademisi2' => 'array',
        'start_date' => 'date',
        'due_date' => 'datetime:Y-m-d H:i',
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

    public function fundDexain()
    {
        return $this->hasOne(FundDexain::class);
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

    public function toCalendarEvent(): array|CalendarEvent
    {
        $backgroundColor = match ($this->status_payment) {
            'paid' => '#10b981', // green-500
            'partial' => '#f59e0b', // amber-500
            'unpaid' => '#ef4444', // red-500
            default => '#6b7280', // gray-500
        };

        return CalendarEvent::make($this)
            ->title($this->nomer_nota)
            ->start($this->due_date->format('Y-m-d'))
            ->end($this->due_date->format('Y-m-d'))
            ->allDay()
            ->backgroundColor($backgroundColor)
            ->textColor('#ffffff')
            ->styles([
                'font-weight: bold',
                'border-radius: 4px',
            ])
            ->extendedProps([
                'order_id' => $this->id,
                'customer_name' => $this->customer?->name ?? 'Tidak ada customer',
                'total_amount' => $this->total_harga,
                'payment_status' => $this->status_payment,
            ]);
    }
}
