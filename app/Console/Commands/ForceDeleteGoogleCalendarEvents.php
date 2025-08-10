<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Spatie\GoogleCalendar\Event;

class ForceDeleteGoogleCalendarEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-calendar:force-delete {--all : Delete all events} {--order-id= : Delete specific order event}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Force delete Google Calendar events';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🗑️ Force Delete Google Calendar Events');
        $this->newLine();

        if ($this->option('all')) {
            $this->deleteAllEvents();
        } elseif ($orderId = $this->option('order-id')) {
            $this->deleteSpecificOrderEvent($orderId);
        } else {
            $this->error('Please specify --all or --order-id option');
            return 1;
        }

        return 0;
    }

    private function deleteAllEvents()
    {
        $this->info('Deleting all Google Calendar events...');

        try {
            // Get all events from Google Calendar
            $events = Event::get();
            $this->info("Found {$events->count()} events in Google Calendar");

            $deleted = 0;
            $failed = 0;

            foreach ($events as $event) {
                try {
                    $event->delete();
                    $deleted++;
                    $this->info("✅ Deleted event: {$event->name}");
                } catch (\Exception $e) {
                    $failed++;
                    $this->error("❌ Failed to delete event: {$event->name} - {$e->getMessage()}");
                }
            }

            $this->newLine();
            $this->info("Results:");
            $this->info("- Successfully deleted: {$deleted}");
            $this->info("- Failed to delete: {$failed}");

            // Clear all Google Calendar event IDs from database
            Order::whereNotNull('google_calendar_event_id')->update(['google_calendar_event_id' => null]);
            $this->info("✅ Cleared all Google Calendar event IDs from database");

        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            return 1;
        }
    }

    private function deleteSpecificOrderEvent($orderId)
    {
        $this->info("Deleting Google Calendar event for order ID: {$orderId}");

        $order = Order::find($orderId);

        if (!$order) {
            $this->error("Order with ID {$orderId} not found!");
            return 1;
        }

        $this->info("Order found: {$order->nomer_nota}");
        $this->info("Google Calendar Event ID: " . ($order->google_calendar_event_id ?? 'None'));

        if (!$order->google_calendar_event_id) {
            $this->warn("Order doesn't have Google Calendar event ID");
            return 0;
        }

        try {
            $event = Event::find($order->google_calendar_event_id);

            if ($event) {
                $event->delete();
                $this->info("✅ Successfully deleted Google Calendar event");
            } else {
                $this->warn("⚠️ Event not found in Google Calendar");
            }

            // Clear the event ID from database
            $order->update(['google_calendar_event_id' => null]);
            $this->info("✅ Cleared Google Calendar event ID from database");

        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            return 1;
        }
    }
}
