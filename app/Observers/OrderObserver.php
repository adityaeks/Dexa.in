<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\GoogleCalendarService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    protected GoogleCalendarService $googleCalendarService;

    public function __construct(GoogleCalendarService $googleCalendarService)
    {
        $this->googleCalendarService = $googleCalendarService;
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        // Buat event Google Calendar saat order dibuat
        if ($order->due_date) {
            try {
                Log::info('OrderObserver::created triggered', [
                    'order_id' => $order->id,
                    'due_date' => $order->due_date
                ]);

                $googleEvent = $this->googleCalendarService->createEventFromOrder($order);

                if ($googleEvent) {
                    Log::info('Google Calendar event berhasil dibuat dari observer', [
                        'order_id' => $order->id,
                        'google_event_id' => $googleEvent->id
                    ]);
                } else {
                    Log::warning('Gagal membuat Google Calendar event dari observer', [
                        'order_id' => $order->id
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error saat membuat Google Calendar event di observer', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Update event Google Calendar saat order diupdate
        if ($order->due_date) {
            try {
                Log::info('OrderObserver::updated triggered', [
                    'order_id' => $order->id,
                    'due_date' => $order->due_date,
                    'google_calendar_event_id' => $order->google_calendar_event_id
                ]);

                // Hanya update jika sudah ada Google Calendar event ID
                if ($order->google_calendar_event_id) {
                    $success = $this->googleCalendarService->updateEventFromOrder($order);
                    Log::info('Google Calendar update result from observer', [
                        'order_id' => $order->id,
                        'success' => $success
                    ]);
                } else {
                    Log::info('Order belum memiliki Google Calendar event ID, skip update', [
                        'order_id' => $order->id
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error saat update Google Calendar event di observer', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        Log::info('OrderObserver::deleted triggered', [
            'order_id' => $order->id,
            'google_calendar_event_id' => $order->google_calendar_event_id
        ]);

        // Hapus event Google Calendar saat order dihapus
        try {
            $success = $this->googleCalendarService->deleteEventFromOrder($order);
            Log::info('Google Calendar delete result from observer', [
                'order_id' => $order->id,
                'success' => $success
            ]);
        } catch (\Exception $e) {
            Log::error('Error saat hapus Google Calendar event di observer', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        // Buat ulang event Google Calendar saat order di-restore
        if ($order->due_date) {
            try {
                $this->googleCalendarService->createEventFromOrder($order);
            } catch (\Exception $e) {
                Log::error('Error saat restore Google Calendar event di observer', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        // Hapus event Google Calendar saat order di-force delete
        try {
            $this->googleCalendarService->deleteEventFromOrder($order);
        } catch (\Exception $e) {
            Log::error('Error saat force delete Google Calendar event di observer', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
