<?php

use App\Models\JadwalVaksinasi;
use App\Models\PendaftaranVaksinasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->jadwal = JadwalVaksinasi::query()->create([
        'tanggal' => today()->addDays(3),
        'jam_mulai' => '08:00:00',
        'jam_selesai' => '12:00:00',
        'kuota' => 3,
    ]);
});

test('guest dapat melihat jadwal mendatang dan sisa kuota tanpa aksi pendaftaran', function () {
    $peserta = User::factory()->create([
        'role' => 'peserta',
        'status_akun' => 'diterima',
    ]);

    $this->jadwal->pendaftaran()->create([
        'user_id' => $peserta->id,
        'status_pendaftaran' => PendaftaranVaksinasi::STATUS_PENDING,
    ]);

    $this->get(route('jadwal.publik'))
        ->assertOk()
        ->assertSee($this->jadwal->tanggal->format('d-m-Y'))
        ->assertSee('2 dari 3 tempat')
        ->assertSee('Tersedia')
        ->assertSee('Kembali')
        ->assertDontSee('Pilih jadwal')
        ->assertDontSee(route('peserta.jadwal.store'))
        ->assertDontSee('<form', false);
});

test('guest dapat melihat jadwal yang kuotanya sudah penuh', function () {
    $this->jadwal->update(['kuota' => 1]);

    $peserta = User::factory()->create([
        'role' => 'peserta',
        'status_akun' => 'diterima',
    ]);

    $this->jadwal->pendaftaran()->create([
        'user_id' => $peserta->id,
        'status_pendaftaran' => PendaftaranVaksinasi::STATUS_DITERIMA,
    ]);

    $this->get(route('jadwal.publik'))
        ->assertOk()
        ->assertSee('0 dari 1 tempat')
        ->assertSee('Penuh')
        ->assertDontSee('Pilih jadwal');
});

test('guest tidak melihat jadwal yang sudah lewat', function () {
    $jadwalLampau = JadwalVaksinasi::query()->create([
        'tanggal' => today()->subDay(),
        'jam_mulai' => '08:00:00',
        'jam_selesai' => '12:00:00',
        'kuota' => 3,
    ]);

    $this->get(route('jadwal.publik'))
        ->assertOk()
        ->assertSee($this->jadwal->tanggal->format('d-m-Y'))
        ->assertDontSee($jadwalLampau->tanggal->format('d-m-Y'));
});

test('guest tidak dapat mengirim pendaftaran jadwal', function () {
    $this->post(route('peserta.jadwal.store'), [
        'jadwal_id' => $this->jadwal->id,
    ])
        ->assertRedirect(route('login.form'));

    $this->assertDatabaseCount('pendaftaran_vaksinasi', 0);
});

test('halaman awal menyediakan tautan untuk melihat jadwal publik', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('jadwal.publik'))
        ->assertSee('Lihat jadwal vaksinasi')
        ->assertDontSee('data-history-back')
        ->assertDontSee('Kembali');
});
