<?php

use App\Models\JadwalVaksinasi;
use App\Models\PendaftaranVaksinasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = User::factory()->create([
        'role' => 'admin',
        'status_akun' => 'diterima',
    ]);

    $this->peserta = User::factory()->create([
        'role' => 'peserta',
        'status_akun' => 'diterima',
    ]);

    $this->peserta->profilPeserta()->create([
        'nik' => '3333333333333333',
        'no_telepon' => '081234567890',
        'alamat' => 'Alamat Peserta',
    ]);

    $this->jadwal = JadwalVaksinasi::query()->create([
        'tanggal' => today()->addDays(3),
        'jam_mulai' => '08:00:00',
        'jam_selesai' => '12:00:00',
        'kuota' => 10,
    ]);

    $this->pendaftaran = $this->jadwal->pendaftaran()->create([
        'user_id' => $this->peserta->id,
        'status_pendaftaran' => PendaftaranVaksinasi::STATUS_PENDING,
    ]);
});

test('admin melihat daftar pendaftaran pending dengan peserta dan jadwal', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.pendaftaran.index'))
        ->assertOk()
        ->assertSee($this->peserta->name)
        ->assertSee('3333333333333333')
        ->assertSee($this->jadwal->tanggal->format('d-m-Y'));
});

test('admin dapat melihat detail peserta dan jadwal yang dipilih', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.pendaftaran.show', $this->pendaftaran))
        ->assertOk()
        ->assertSee($this->peserta->email)
        ->assertSee('Alamat Peserta')
        ->assertSee('Terima pendaftaran')
        ->assertSee('Tolak pendaftaran');
});

test('admin dapat menerima pendaftaran dan menyimpan identitas verifikator', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.pendaftaran.terima', $this->pendaftaran))
        ->assertRedirect(route('admin.pendaftaran.index'));

    $this->assertDatabaseHas('pendaftaran_vaksinasi', [
        'id' => $this->pendaftaran->id,
        'status_pendaftaran' => PendaftaranVaksinasi::STATUS_DITERIMA,
        'verified_by' => $this->admin->id,
        'alasan_penolakan' => null,
    ]);

    expect($this->pendaftaran->fresh()->verified_at)->not->toBeNull();
});

test('admin dapat menolak pendaftaran dengan alasan dan identitas verifikator', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.pendaftaran.tolak', $this->pendaftaran), [
            'alasan_penolakan' => 'Data jadwal tidak sesuai.',
        ])
        ->assertRedirect(route('admin.pendaftaran.index'));

    $this->assertDatabaseHas('pendaftaran_vaksinasi', [
        'id' => $this->pendaftaran->id,
        'status_pendaftaran' => PendaftaranVaksinasi::STATUS_DITOLAK,
        'alasan_penolakan' => 'Data jadwal tidak sesuai.',
        'verified_by' => $this->admin->id,
    ]);

    expect($this->pendaftaran->fresh()->verified_at)->not->toBeNull();
});

test('alasan penolakan pendaftaran wajib diisi', function () {
    $this->actingAs($this->admin)
        ->from(route('admin.pendaftaran.show', $this->pendaftaran))
        ->post(route('admin.pendaftaran.tolak', $this->pendaftaran), [
            'alasan_penolakan' => '',
        ])
        ->assertRedirect(route('admin.pendaftaran.show', $this->pendaftaran))
        ->assertSessionHasErrors('alasan_penolakan');
});

test('pendaftaran yang sudah diproses tidak dapat diverifikasi kembali', function () {
    $this->pendaftaran->update([
        'status_pendaftaran' => PendaftaranVaksinasi::STATUS_DITERIMA,
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.pendaftaran.tolak', $this->pendaftaran), [
            'alasan_penolakan' => 'Tidak berlaku.',
        ])
        ->assertNotFound();
});

test('peserta tidak dapat mengakses daftar atau aksi verifikasi admin', function () {
    $this->actingAs($this->peserta)
        ->get(route('admin.pendaftaran.index'))
        ->assertForbidden();

    $this->post(route('admin.pendaftaran.terima', $this->pendaftaran))
        ->assertForbidden();
});
