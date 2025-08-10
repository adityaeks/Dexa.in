<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleCalendarService;

class SyncGoogleCalendar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-calendar:sync {--force : Force sync semua order}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync semua order ke Google Calendar';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai sync Google Calendar...');

        $googleCalendarService = new GoogleCalendarService();
        
        if ($this->option('force')) {
            $this->info('Mode force sync - akan sync semua order yang memiliki due_date');
            $orders = \App\Models\Order::whereNotNull('due_date')->get();
            
            $success = 0;
            $failed = 0;
            
            $progressBar = $this->output->createProgressBar($orders->count());
            $progressBar->start();
            
            foreach ($orders as $order) {
                try {
                    // Hapus event lama jika ada
                    if ($order->google_calendar_event_id) {
                        $googleCalendarService->deleteEventFromOrder($order);
                    }
                    
                    // Buat event baru
                    $googleEvent = $googleCalendarService->createEventFromOrder($order);
                    if ($googleEvent) {
                        $success++;
                    } else {
                        $failed++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $this->error("Error pada order ID {$order->id}: " . $e->getMessage());
                }
                
                $progressBar->advance();
            }
            
            $progressBar->finish();
            $this->newLine();
            
        } else {
            $result = $googleCalendarService->syncAllOrders();
            $success = $result['success'];
            $failed = $result['failed'];
        }

        $this->info("Sync selesai!");
        $this->info("Berhasil: {$success}");
        $this->info("Gagal: {$failed}");
        
        if ($failed > 0) {
            $this->warn("Ada {$failed} order yang gagal di-sync. Cek log untuk detail.");
        }
    }
}
