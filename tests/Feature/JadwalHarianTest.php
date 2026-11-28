<?php

use App\Models\JadwalVaksinasi;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

afterEach(function (): void {
    Carbon::setTestNow();
});

test('perintah membuat jadwal hari ini dengan pola yang bergantian dan kuota 50', function () {
    Carbon::setTestNow(Carbon::parse('2026-12-03 00:05:00', 'Asia/Jakarta'));

    JadwalVaksinasi::query()->create([
        'tanggal' => '2026-12-02',
        'jam_mulai' => '09:00:00',
        'jam_selesai' => '13:00:00',
        'kuota' => 50,
    ]);

    $this->artisan('vaksinasi:generate-daily-schedule')
        ->assertExitCode(0);

    $jadwal = JadwalVaksinasi::query()
        ->whereDate('tanggal', '2026-12-03')
        ->sole();

    expect(substr($jadwal->jam_mulai, 0, 5))->toBe('08:00')
        ->and(substr($jadwal->jam_selesai, 0, 5))->toBe('12:00')
        ->and($jadwal->kuota)->toBe(50);
});

test('perintah tidak menggandakan jadwal jika dijalankan lebih dari sekali dalam sehari', function () {
    Carbon::setTestNow(Carbon::parse('2026-12-04 00:05:00', 'Asia/Jakarta'));

    $this->artisan('vaksinasi:generate-daily-schedule')
        ->assertExitCode(0);
    $this->artisan('vaksinasi:generate-daily-schedule')
        ->assertExitCode(0);

    expect(JadwalVaksinasi::query()->whereDate('tanggal', '2026-12-04')->count())
        ->toBe(1);
});

test('pola slot berganti kembali untuk hari berikutnya', function () {
    Carbon::setTestNow(Carbon::parse('2026-12-04 00:05:00', 'Asia/Jakarta'));

    JadwalVaksinasi::query()->create([
        'tanggal' => '2026-12-03',
        'jam_mulai' => '08:00:00',
        'jam_selesai' => '12:00:00',
        'kuota' => 50,
    ]);

    $this->artisan('vaksinasi:generate-daily-schedule')
        ->assertExitCode(0);

    $jadwal = JadwalVaksinasi::query()
        ->whereDate('tanggal', '2026-12-04')
        ->sole();

    expect(substr($jadwal->jam_mulai, 0, 5))->toBe('09:00')
        ->and(substr($jadwal->jam_selesai, 0, 5))->toBe('13:00');
});

test('perintah tidak mengubah jadwal yang sudah tersedia untuk hari ini', function () {
    Carbon::setTestNow(Carbon::parse('2026-12-05 00:05:00', 'Asia/Jakarta'));

    JadwalVaksinasi::query()->create([
        'tanggal' => '2026-12-05',
        'jam_mulai' => '10:00:00',
        'jam_selesai' => '14:00:00',
        'kuota' => 20,
    ]);

    $this->artisan('vaksinasi:generate-daily-schedule')
        ->assertExitCode(0);

    $jadwal = JadwalVaksinasi::query()->whereDate('tanggal', '2026-12-05')->sole();

    expect(substr($jadwal->jam_mulai, 0, 5))->toBe('10:00')
        ->and($jadwal->kuota)->toBe(20);
});
