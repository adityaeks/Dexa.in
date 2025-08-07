<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Akademisi;
use App\Models\Customer;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Jobs\UpdateOrderStatisticsJob;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['Not started', 'Inprogress', 'Done'];
        $faker = \Faker\Factory::create();
        $start = Carbon::now()->subMonths(6)->startOfMonth();
        $end = Carbon::now()->endOfMonth();
        $period = Carbon::parse($start)->monthsUntil($end);

        // Ensure Akademisi and Customer records exist
        $akademisiCount = 10;
        $customerCount = 20;

        $akademisis = Akademisi::query()->pluck('id')->all();
        if (count($akademisis) < $akademisiCount) {
            for ($i = count($akademisis); $i < $akademisiCount; $i++) {
                $akademisis[] = Akademisi::create([
                    'name' => $faker->name,
                    'nomor' => $faker->unique()->numerify('AKD###'),
                    'jurusan' => $faker->word,
                    'asal_kampus' => $faker->company,
                    // Add other required Akademisi fields here
                ])->id;
            }
        }

        $customers = Customer::query()->pluck('id')->all();
        if (count($customers) < $customerCount) {
            for ($i = count($customers); $i < $customerCount; $i++) {
                $customers[] = Customer::create([
                    'name' => $faker->name,
                    'nomor' => $faker->unique()->numerify('CUST###'),
                    // Add other required Customer fields here
                ])->id;
            }
        }

        foreach ($period as $month) {
            $ordersThisMonth = rand(100, 300); // menghasilkan ribuan order
            for ($i = 0; $i < $ordersThisMonth; $i++) {
                $status = $statuses[array_rand($statuses)];
                Order::create([
                    'nomer_nota' => strtoupper(Str::random(8)),
                    'customer_id' => $faker->randomElement($customers),
                    'nama' => $faker->name,
                    'status' => $status,
                    'prioritas' => $faker->randomElement(['low', 'medium', 'urgent']),
                    'status_payment' => $faker->randomElement(['belum', 'DP', 'Lunas']),
                    'price' => $faker->numberBetween(50000, 500000),
                    'price_dexain' => $faker->numberBetween(5000, 100000),
                    'price_akademisi' => $faker->numberBetween(10000, 400000),
                    'start_date' => $month->copy()->addDays(rand(0, 27)),
                    'due_date' => $month->copy()->addDays(rand(0, 27))->addDays(rand(1, 30)),
                    'contact' => $faker->phoneNumber,
                    'akademisi_id' => $faker->randomElement($akademisis),
                    'note' => $faker->sentence,
                    'created_at' => $month->copy()->addDays(rand(0, 27)),
                    'updated_at' => $month->copy()->addDays(rand(0, 27)),
                ]);
            }
        }

        // Jalankan job statistik setelah seeding order
        dispatch(new UpdateOrderStatisticsJob());
    }
}
