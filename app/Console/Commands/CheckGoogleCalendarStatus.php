<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;

class CheckGoogleCalendarStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-calendar:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek status Google Calendar integration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🔍 Checking Google Calendar Integration Status...");
        $this->newLine();

        // Check environment variables
        $this->info("📋 Environment Variables:");
        $calendarId = config('google-calendar.calendar_id');
        $authProfile = config('google-calendar.auth_profile');

        $this->info("- GOOGLE_CALENDAR_ID: " . ($calendarId ?: '❌ Not set'));
        $this->info("- GOOGLE_CALENDAR_AUTH_PROFILE: " . ($authProfile ?: '❌ Not set'));

        // Check credentials file
        $this->newLine();
        $this->info("🔐 Credentials File:");
        $credentialsPath = config('google-calendar.service_account_credentials_json');
        if (file_exists($credentialsPath)) {
            $this->info("- ✅ Credentials file exists: {$credentialsPath}");
        } else {
            $this->error("- ❌ Credentials file not found: {$credentialsPath}");
        }

        // Check orders with Google Calendar events
        $this->newLine();
        $this->info("📊 Orders with Google Calendar Events:");

        $totalOrders = Order::count();
        $ordersWithGoogleEvent = Order::whereNotNull('google_calendar_event_id')->count();
        $ordersWithoutGoogleEvent = Order::whereNull('google_calendar_event_id')->whereNotNull('due_date')->count();

        $this->info("- Total Orders: {$totalOrders}");
        $this->info("- Orders with Google Calendar Event: {$ordersWithGoogleEvent}");
        $this->info("- Orders without Google Calendar Event (but have due_date): {$ordersWithoutGoogleEvent}");

        // Show recent orders with Google Calendar events
        if ($ordersWithGoogleEvent > 0) {
            $this->newLine();
            $this->info("📅 Recent Orders with Google Calendar Events:");

            $recentOrders = Order::whereNotNull('google_calendar_event_id')
                ->with('customer')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            foreach ($recentOrders as $order) {
                $this->info("- ID: {$order->id} | Nota: {$order->nomer_nota} | Customer: " . ($order->customer?->name ?? 'N/A') . " | Google Event ID: {$order->google_calendar_event_id}");
            }
        }

        // Test Google Calendar connection
        $this->newLine();
        $this->info("🔗 Testing Google Calendar Connection:");

        try {
            $event = new \Spatie\GoogleCalendar\Event();
            $this->info("- ✅ Google Calendar Event class loaded successfully");
        } catch (\Exception $e) {
            $this->error("- ❌ Failed to load Google Calendar Event class: " . $e->getMessage());
        }

        $this->newLine();
        $this->info("📝 Next Steps:");
        $this->info("1. If credentials are missing, setup Google Cloud Project and download credentials");
        $this->info("2. If calendar ID is missing, add GOOGLE_CALENDAR_ID to .env file");
        $this->info("3. Test sync with: php artisan google-calendar:sync");
        $this->info("4. Check logs with: tail -f storage/logs/laravel.log");

        return 0;
    }
}
