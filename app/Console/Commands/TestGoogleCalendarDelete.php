<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\GoogleCalendarService;

class TestGoogleCalendarDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-calendar:test-delete {order_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test delete Google Calendar event untuk order tertentu';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderId = $this->argument('order_id');

        $this->info("Testing delete Google Calendar event untuk order ID: {$orderId}");

        $order = Order::find($orderId);

        if (!$order) {
            $this->error("Order dengan ID {$orderId} tidak ditemukan!");
            return 1;
        }

        $this->info("Order ditemukan:");
        $this->info("- Nomer Nota: {$order->nomer_nota}");
        $this->info("- Customer: " . ($order->customer?->name ?? 'Tidak ada customer'));
        $this->info("- Google Calendar Event ID: " . ($order->google_calendar_event_id ?? 'Tidak ada'));

        if (!$order->google_calendar_event_id) {
            $this->warn("Order ini tidak memiliki Google Calendar event ID!");
            return 0;
        }

        $this->info("Memulai proses delete Google Calendar event...");

        try {
            $googleCalendarService = new GoogleCalendarService();
            $success = $googleCalendarService->deleteEventFromOrder($order);

            if ($success) {
                $this->info("✅ Google Calendar event berhasil dihapus!");
            } else {
                $this->error("❌ Gagal menghapus Google Calendar event!");
            }

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
