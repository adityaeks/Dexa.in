<?php

namespace App\Services;

use App\Models\Order;
use Spatie\GoogleCalendar\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    /**
     * Membuat event Google Calendar dari Order
     */
    public function createEventFromOrder(Order $order): ?Event
    {
        try {
            if (!$order->due_date) {
                Log::warning('Order tidak memiliki due_date', ['order_id' => $order->id]);
                return null;
            }

            // Cek apakah sudah ada Google Calendar event untuk order ini
            if ($order->google_calendar_event_id) {
                Log::info('Order sudah memiliki Google Calendar event, skip create', [
                    'order_id' => $order->id,
                    'existing_google_event_id' => $order->google_calendar_event_id
                ]);
                return null;
            }

            Log::info('Membuat Google Calendar event baru', [
                'order_id' => $order->id,
                'due_date' => $order->due_date
            ]);

            $event = new Event();

            // Set judul event
            $event->name = $order->nomer_nota . ' - ' . ($order->customer?->name ?? 'Tidak ada customer');

            // Set deskripsi
            $description = "Order: {$order->nomer_nota}\n";
            $description .= "Customer: " . ($order->customer?->name ?? 'Tidak ada customer') . "\n";

            // Tambahkan informasi Jokian
            $jokianNames = $this->getJokianNames($order);
            if (!empty($jokianNames)) {
                $description .= "Jokian: {$jokianNames}\n";
            }

            // Tambahkan informasi Akademisi
            $akademisiNames = $this->getAkademisiNames($order);
            if (!empty($akademisiNames)) {
                $description .= "Akademisi: {$akademisiNames}\n";
            }

            $description .= "Total: Rp " . number_format($order->total_harga, 0, ',', '.') . "\n";

            $event->description = $description;

            // Set waktu event
            $event->startDateTime = Carbon::parse($order->due_date);
            $event->endDateTime = Carbon::parse($order->due_date)->addHour(); // Default 1 jam

            // Set warna berdasarkan status pembayaran
            $event->setColorId($this->getColorIdByPaymentStatus($order->status_payment));

            // Set source URL (link ke order)
            $event->source = [
                'title' => 'Lihat Order di Sistem',
                'url' => route('filament.admin.resources.orders.edit', $order->id),
            ];

            // Simpan event
            $savedEvent = $event->save();

            // Simpan Google Calendar ID ke database
            $order->update([
                'google_calendar_event_id' => $savedEvent->id
            ]);

            Log::info('Event Google Calendar berhasil dibuat', [
                'order_id' => $order->id,
                'google_event_id' => $savedEvent->id
            ]);

            return $savedEvent;

        } catch (\Exception $e) {
            Log::error('Gagal membuat event Google Calendar', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

        /**
     * Update event Google Calendar dari Order
     */
    public function updateEventFromOrder(Order $order): bool
    {
        try {
            Log::info('Memulai proses update Google Calendar event', [
                'order_id' => $order->id,
                'google_calendar_event_id' => $order->google_calendar_event_id
            ]);

            if (!$order->google_calendar_event_id) {
                Log::info('Order belum memiliki Google Calendar event ID, buat baru', [
                    'order_id' => $order->id
                ]);
                // Jika belum ada event, buat baru
                return $this->createEventFromOrder($order) !== null;
            }

            $event = Event::find($order->google_calendar_event_id);

            if (!$event) {
                Log::warning('Event tidak ditemukan di Google Calendar, buat baru', [
                    'order_id' => $order->id,
                    'google_event_id' => $order->google_calendar_event_id
                ]);
                // Event tidak ditemukan, buat baru
                $order->update(['google_calendar_event_id' => null]);
                return $this->createEventFromOrder($order) !== null;
            }

                        // Update event
            $event->name = $order->nomer_nota . ' - ' . ($order->customer?->name ?? 'Tidak ada customer');

            $description = "Order: {$order->nomer_nota}\n";
            $description .= "Customer: " . ($order->customer?->name ?? 'Tidak ada customer') . "\n";

            // Tambahkan informasi Jokian
            $jokianNames = $this->getJokianNames($order);
            if (!empty($jokianNames)) {
                $description .= "Jokian: {$jokianNames}\n";
            }

            // Tambahkan informasi Akademisi
            $akademisiNames = $this->getAkademisiNames($order);
            if (!empty($akademisiNames)) {
                $description .= "Akademisi: {$akademisiNames}\n";
            }

            $description .= "Total: Rp " . number_format($order->total_harga, 0, ',', '.') . "\n";

            $event->description = $description;
            $event->startDateTime = Carbon::parse($order->due_date);
            $event->endDateTime = Carbon::parse($order->due_date)->addHour();
            $event->setColorId($this->getColorIdByPaymentStatus($order->status_payment));

            $event->save();

            Log::info('Event Google Calendar berhasil diupdate', [
                'order_id' => $order->id,
                'google_event_id' => $order->google_calendar_event_id
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Gagal update event Google Calendar', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

            /**
     * Hapus event Google Calendar
     */
    public function deleteEventFromOrder(Order $order): bool
    {
        try {
            Log::info('Memulai proses hapus Google Calendar event', [
                'order_id' => $order->id,
                'google_calendar_event_id' => $order->google_calendar_event_id
            ]);

            if (!$order->google_calendar_event_id) {
                Log::info('Order tidak memiliki Google Calendar event ID', [
                    'order_id' => $order->id
                ]);
                return true;
            }

            // Cek apakah event ada di Google Calendar
            try {
                $event = Event::find($order->google_calendar_event_id);

                if ($event) {
                    Log::info('Event ditemukan di Google Calendar, akan dihapus', [
                        'order_id' => $order->id,
                        'google_event_id' => $order->google_calendar_event_id
                    ]);

                    $event->delete();
                    Log::info('Event berhasil dihapus dari Google Calendar', [
                        'order_id' => $order->id,
                        'google_event_id' => $order->google_calendar_event_id
                    ]);
                } else {
                    Log::warning('Event tidak ditemukan di Google Calendar', [
                        'order_id' => $order->id,
                        'google_event_id' => $order->google_calendar_event_id
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error saat mencari/menghapus event di Google Calendar', [
                    'order_id' => $order->id,
                    'google_event_id' => $order->google_calendar_event_id,
                    'error' => $e->getMessage()
                ]);

                // Lanjutkan untuk hapus reference dari database meskipun error
            }

            // Hapus reference dari database (jika order masih ada)
            try {
                if ($order->exists) {
                    $order->update(['google_calendar_event_id' => null]);
                    Log::info('Reference Google Calendar event dihapus dari database', [
                        'order_id' => $order->id
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Tidak bisa update database (mungkin order sudah dihapus)', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Gagal hapus event Google Calendar', [
                'order_id' => $order->id,
                'google_event_id' => $order->google_calendar_event_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Get text status pembayaran
     */
    private function getPaymentStatusText(string $status): string
    {
        return match ($status) {
            'paid' => 'Lunas',
            'partial' => 'Sebagian',
            'unpaid' => 'Belum Bayar',
            default => 'Tidak Diketahui'
        };
    }

    /**
     * Get color ID berdasarkan status pembayaran
     * Google Calendar Color IDs: https://developers.google.com/calendar/api/v3/reference/colors
     */
    private function getColorIdByPaymentStatus(string $status): int
    {
        return match ($status) {
            'paid' => 2,      // Green
            'partial' => 5,   // Orange
            'unpaid' => 11,   // Red
            default => 8      // Gray
        };
    }

    /**
     * Sync semua order yang belum memiliki Google Calendar event
     */
    public function syncAllOrders(): array
    {
        $orders = Order::whereNull('google_calendar_event_id')
            ->whereNotNull('due_date')
            ->get();

        $success = 0;
        $failed = 0;

        foreach ($orders as $order) {
            if ($this->createEventFromOrder($order)) {
                $success++;
            } else {
                $failed++;
            }
        }

        return [
            'success' => $success,
            'failed' => $failed,
            'total' => $orders->count()
        ];
    }

        /**
     * Get nama-nama jokian dari order
     */
    private function getJokianNames(Order $order): string
    {
        try {
            if (empty($order->nama) || !is_array($order->nama)) {
                return '';
            }

            // Ambil data harga berdasarkan ID yang ada di kolom nama
            $hargas = \App\Models\Harga::whereIn('id', $order->nama)->get();

            if ($hargas->isEmpty()) {
                return '';
            }

            // Buat string nama jokian
            $jokianNames = $hargas->map(function($harga) {
                $name = $harga->nama;
                if (isset($harga->tingkat)) {
                    $name .= ' - ' . $harga->tingkat;
                }
                return $name;
            })->implode(', ');

            return $jokianNames;

        } catch (\Exception $e) {
            Log::error('Error saat mengambil nama jokian', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return '';
        }
    }

    /**
     * Get nama-nama akademisi dari order
     */
    private function getAkademisiNames(Order $order): string
    {
        try {
            if (empty($order->akademisi_id) || !is_array($order->akademisi_id)) {
                return '';
            }

            // Ambil data akademisi berdasarkan ID yang ada di kolom akademisi_id
            $akademisis = \App\Models\Akademisi::whereIn('id', $order->akademisi_id)->get();

            if ($akademisis->isEmpty()) {
                return '';
            }

            // Buat string nama akademisi
            $akademisiNames = $akademisis->pluck('name')->implode(', ');

            return $akademisiNames;

        } catch (\Exception $e) {
            Log::error('Error saat mengambil nama akademisi', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return '';
        }
    }
}
