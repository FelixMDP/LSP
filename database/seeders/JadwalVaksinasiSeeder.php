<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JadwalVaksinasi;

class JadwalVaksinasiSeeder extends Seeder
{
    public function run(): void
    {
        $jadwal = [
            [
                'tanggal' => '2026-11-25',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '12:00:00',
                'kuota' => 50,
            ],
            [
                'tanggal' => '2026-11-26',
                'jam_mulai' => '09:00:00',
                'jam_selesai' => '13:00:00',
                'kuota' => 50,
            ],
            [
                'tanggal' => '2026-11-27',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '12:00:00',
                'kuota' => 50,
            ],
            [
                'tanggal' => '2026-11-28',
                'jam_mulai' => '09:00:00',
                'jam_selesai' => '13:00:00',
                'kuota' => 50,
            ],
            [
                'tanggal' => '2026-11-29',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '12:00:00',
                'kuota' => 50,
            ],
            [
                'tanggal' => '2026-11-30',
                'jam_mulai' => '09:00:00',
                'jam_selesai' => '13:00:00',
                'kuota' => 50,
            ],
            [
                'tanggal' => '2026-12-01',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '12:00:00',
                'kuota' => 50,
            ],
            [
                'tanggal' => '2026-12-02',
                'jam_mulai' => '09:00:00',
                'jam_selesai' => '13:00:00',
                'kuota' => 50,
            ],
        ];

        foreach ($jadwal as $data) {
            JadwalVaksinasi::updateOrCreate(
                [
                    'tanggal' => $data['tanggal'],
                    'jam_mulai' => $data['jam_mulai'],
                    'jam_selesai' => $data['jam_selesai'],
                ],
                [
                    'kuota' => $data['kuota'],
                ]
            );
        }
    }
}
