<?php

use App\Models\JadwalVaksinasi;
use App\Models\PendaftaranVaksinasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->peserta = User::factory()->create([
        'role' => 'peserta',
        'status_akun' => 'diterima',
    ]);

    $this->jadwal = JadwalVaksinasi::query()->create([
        'tanggal' => today()->addDays(2),
        'jam_mulai' => '08:00:00',
        'jam_selesai' => '12:00:00',
        'kuota' => 2,
    ]);
});

test('peserta dapat melihat jadwal mendatang dan sisa kuotanya', function () {
    $this->actingAs($this->peserta)
        ->get(route('peserta.jadwal.index'))
        ->assertOk()
        ->assertSee($this->jadwal->tanggal->format('d-m-Y'))
        ->assertSee('Pilih jadwal');
});

test('peserta dapat mendaftar dan status pendaftaran menjadi pending', function () {
    $this->actingAs($this->peserta)
        ->post(route('peserta.jadwal.store'), [
            'jadwal_id' => $this->jadwal->id,
        ])
        ->assertRedirect(route('peserta.jadwal.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('pendaftaran_vaksinasi', [
        'user_id' => $this->peserta->id,
        'jadwal_id' => $this->jadwal->id,
        'status_pendaftaran' => PendaftaranVaksinasi::STATUS_PENDING,
    ]);
});

test('peserta tidak dapat mendaftar dua kali pada jadwal yang sama', function () {
    $this->actingAs($this->peserta)
        ->post(route('peserta.jadwal.store'), [
            'jadwal_id' => $this->jadwal->id,
        ])
        ->assertRedirect();

    $this->post(route('peserta.jadwal.store'), [
        'jadwal_id' => $this->jadwal->id,
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('jadwal_id');

    $this->assertDatabaseCount('pendaftaran_vaksinasi', 1);
});

test('kuota penuh menolak pendaftaran tambahan', function () {
    $this->jadwal->update(['kuota' => 1]);

    $pesertaLain = User::factory()->create([
        'role' => 'peserta',
        'status_akun' => 'diterima',
    ]);

    $this->jadwal->pendaftaran()->create([
        'user_id' => $pesertaLain->id,
        'status_pendaftaran' => PendaftaranVaksinasi::STATUS_PENDING,
    ]);

    $this->actingAs($this->peserta)
        ->post(route('peserta.jadwal.store'), [
            'jadwal_id' => $this->jadwal->id,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('jadwal_id');

    $this->assertDatabaseCount('pendaftaran_vaksinasi', 1);
});

test('pendaftaran yang ditolak tidak menggunakan kuota', function () {
    $this->jadwal->update(['kuota' => 1]);

    $pesertaLain = User::factory()->create([
        'role' => 'peserta',
        'status_akun' => 'diterima',
    ]);

    $this->jadwal->pendaftaran()->create([
        'user_id' => $pesertaLain->id,
        'status_pendaftaran' => PendaftaranVaksinasi::STATUS_DITOLAK,
    ]);

    $this->actingAs($this->peserta)
        ->post(route('peserta.jadwal.store'), [
            'jadwal_id' => $this->jadwal->id,
        ])
        ->assertRedirect(route('peserta.jadwal.index'))
        ->assertSessionHas('success');

    expect($this->jadwal->pendaftaranAktif()->count())->toBe(1);
});

test('jadwal yang sudah lewat tidak ditampilkan dan tidak bisa dipilih', function () {
    $jadwalLampau = JadwalVaksinasi::query()->create([
        'tanggal' => today()->subDay(),
        'jam_mulai' => '08:00:00',
        'jam_selesai' => '12:00:00',
        'kuota' => 2,
    ]);

    $this->actingAs($this->peserta)
        ->get(route('peserta.jadwal.index'))
        ->assertOk()
        ->assertDontSee($jadwalLampau->tanggal->format('d-m-Y'));

    $this->post(route('peserta.jadwal.store'), [
        'jadwal_id' => $jadwalLampau->id,
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('jadwal_id');

    $this->assertDatabaseCount('pendaftaran_vaksinasi', 0);
});

test('akun peserta yang belum diterima tidak dapat membuka fitur jadwal', function () {
    $this->peserta->update(['status_akun' => 'pending']);

    $this->actingAs($this->peserta)
        ->get(route('peserta.jadwal.index'))
        ->assertRedirect(route('peserta.status'));

    $this->post(route('peserta.jadwal.store'), [
        'jadwal_id' => $this->jadwal->id,
    ])
        ->assertRedirect(route('peserta.status'));

    $this->assertDatabaseCount('pendaftaran_vaksinasi', 0);
});

test('admin tidak dapat menggunakan route pendaftaran peserta', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status_akun' => 'diterima',
    ]);

    $this->actingAs($admin)
        ->get(route('peserta.jadwal.index'))
        ->assertForbidden();

    $this->post(route('peserta.jadwal.store'), [
        'jadwal_id' => $this->jadwal->id,
    ])
        ->assertForbidden();
});
