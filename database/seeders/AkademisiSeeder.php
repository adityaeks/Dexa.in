<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Akademisi;

class AkademisiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $akademisis = [
            [
                'name' => 'Eko',
                'nomor' => '+6281234567890',
                'jurusan' => 'Teknik Informatika',
                'asal_kampus' => 'Universitas Indonesia',
                'rekening' => '1234567890',
                'minat' => ['Web Development', 'Mobile Development', 'AI/ML'],
            ],
            [
                'name' => 'Cece',
                'nomor' => '+6282345678901',
                'jurusan' => 'Sistem Informasi',
                'asal_kampus' => 'Institut Teknologi Bandung',
                'rekening' => '2345678901',
                'minat' => ['Database Management', 'UI/UX Design', 'Cloud Computing'],
            ],
            [
                'name' => 'Amar',
                'nomor' => '+6283456789012',
                'jurusan' => 'Teknik Komputer',
                'asal_kampus' => 'Universitas Gadjah Mada',
                'rekening' => '3456789012',
                'minat' => ['Network Security', 'DevOps', 'Data Science'],
            ],
        ];

        foreach ($akademisis as $akademisi) {
            Akademisi::create($akademisi);
        }
    }
}
