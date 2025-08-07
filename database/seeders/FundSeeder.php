<?php

namespace Database\Seeders;

use App\Models\Fund;
use Illuminate\Database\Seeder;

class FundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create initial fund data
        Fund::create([
            'in' => 10000000, // 10 juta rupiah
            'out' => 2500000, // 2.5 juta rupiah
        ]);
    }
}
