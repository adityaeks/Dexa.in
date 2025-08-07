<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Guava\Calendar\Widgets\CalendarWidget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;


class OrderCalendarWidget extends CalendarWidget
{
    protected static ?int $sort = 2;

    protected string $calendarView = 'dayGridMonth';

    protected bool $eventClickEnabled = true;
    protected bool $dateClickEnabled = true;
    protected bool $dateSelectEnabled = true;

    public function getEvents(array $fetchInfo = []): Collection|array
    {
        return Order::whereNotNull('due_date')
            ->with(['customer'])
            ->get()
            ->filter(function ($order) {
                return $order->due_date && $order->nomer_nota;
            })
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'title' => $order->nomer_nota . ' - ' . $order->due_date->format('H:i'),
                    'start' => $order->due_date->format('Y-m-d H:i:s'),
                    'end' => $order->due_date->format('Y-m-d H:i:s'),
                    'allDay' => true,
                    'backgroundColor' => $this->getEventColor($order),
                    'borderColor' => $this->getEventColor($order),
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'order_id' => $order->id,
                        'customer_name' => $order->customer?->name ?? 'Tidak ada customer',
                        'total_amount' => $order->total_harga,
                        'payment_status' => $order->status_payment,
                        'due_time' => $order->due_date->format('H:i'),
                    ],
                ];
            })
            ->sortBy('due_date') // Urutkan berdasarkan jam
            ->values();
    }

    protected function getEventColor($order): string
    {
        // Tentukan warna berdasarkan status pembayaran
        switch ($order->status_payment) {
            case 'paid':
                return '#10b981'; // Green
            case 'partial':
                return '#f59e0b'; // Amber
            case 'unpaid':
                return '#ef4444'; // Red
            default:
                return '#6b7280'; // Gray
        }
    }

    public function getEventClickContextMenuActions(): array
    {
        // Return array kosong agar tidak ada context menu
        // Event click akan langsung dihandle oleh onEventClick method
        return [];
    }

    public function onEventClick(array $info = [], string|null $action = null): void
    {
        // Debug: Log info untuk melihat struktur data yang diterima
        Log::info('Event click info:', $info);

        // Coba berbagai cara untuk mendapatkan order ID
        $orderId = null;

        // Coba dari berbagai kemungkinan lokasi
        if (isset($info['id'])) {
            $orderId = $info['id'];
        } elseif (isset($info['extendedProps']['order_id'])) {
            $orderId = $info['extendedProps']['order_id'];
        } elseif (isset($info['event']['id'])) {
            $orderId = $info['event']['id'];
        } elseif (isset($info['event']['extendedProps']['order_id'])) {
            $orderId = $info['event']['extendedProps']['order_id'];
        }

        if ($orderId) {
            $this->js("window.location.href = '" . route('filament.admin.resources.orders.edit', $orderId) . "'");
        } else {
            Notification::make()
                ->title('Tidak dapat menemukan ID order')
                ->body('Data event: ' . json_encode($info))
                ->danger()
                ->send();
        }
    }

    public function getOptions(): array
    {
        return [
            'nowIndicator' => true,
            'height' => 'auto',
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
            ],
            'locale' => 'id',
            'firstDay' => 1, // Senin sebagai hari pertama
            'displayEventTime' => true, // Tampilkan waktu event
            'eventDisplay' => 'block', // Tampilkan event sebagai block
            'eventTimeFormat' => [
                'hour' => '2-digit',
                'minute' => '2-digit',
                'hour12' => false,
            ],
        ];
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }
}
