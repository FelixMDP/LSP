<?php

namespace Database\Seeders;

use App\Models\BeritaKesehatan;
use Illuminate\Database\Seeder;

class BeritaKesehatanSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('berita-kesehatan', []) as $berita) {
            BeritaKesehatan::query()->updateOrCreate(
                ['source_url' => $berita['url']],
                [
                    'published_on' => $berita['tanggal'],
                    'category' => $berita['kategori'],
                    'title' => $berita['judul'],
                    'summary' => $berita['ringkasan'],
                    'context' => $berita['konteks'],
                    'source_name' => $berita['sumber'],
                    'source_published_at' => $berita['tanggal'],
                ]
            );
        }
    }
}
