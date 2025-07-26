<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixAkademisiIdArray extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-akademisi-id-array';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = 0;
        foreach (\App\Models\Order::all() as $order) {
            if (is_string($order->akademisi_id)) {
                $decoded = json_decode($order->akademisi_id, true);
                if (is_array($decoded)) {
                    $order->akademisi_id = $decoded;
                    $order->save();
                    $count++;
                }
            }
        }
        $this->info("Selesai. {$count} data order berhasil diperbaiki.");
    }
}
