<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = User::factory()->create([
        'name' => 'Admin Penguji',
        'role' => 'admin',
        'status_akun' => 'diterima',
    ]);

    $this->peserta = User::factory()->create([
        'name' => 'Peserta Penguji',
        'role' => 'peserta',
        'status_akun' => 'pending',
    ]);

    $this->peserta->profilPeserta()->create([
        'nik' => '1111111111111111',
        'no_telepon' => '081234567890',
        'alamat' => 'Alamat Pengujian',
    ]);
});

test('peserta dapat registrasi dan profilnya tersimpan dengan status pending', function () {
    $response = $this->post(route('register'), [
        'name' => 'Peserta Baru',
        'email' => 'baru@example.test',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
        'nik' => '2222222222222222',
        'no_telepon' => '081234567891',
        'alamat' => 'Alamat Baru',
    ]);

    $response->assertRedirect(route('register.form'));

    $this->assertDatabaseHas('users', [
        'name' => 'Peserta Baru',
        'email' => 'baru@example.test',
        'role' => 'peserta',
        'status_akun' => 'pending',
    ]);

    $this->assertDatabaseHas('profil_peserta', [
        'nik' => '2222222222222222',
        'no_telepon' => '081234567891',
        'alamat' => 'Alamat Baru',
    ]);

    expect(Hash::check(
        'Password123',
        User::query()->where('email', 'baru@example.test')->value('password')
    ))->toBeTrue();
});

test('registrasi menolak email yang sudah digunakan', function () {
    $this->from(route('register.form'))
        ->post(route('register'), [
            'name' => 'Peserta Duplikat',
            'email' => $this->peserta->email,
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'nik' => '2222222222222222',
            'no_telepon' => '081234567891',
            'alamat' => 'Alamat Baru',
        ])
        ->assertRedirect(route('register.form'))
        ->assertSessionHasErrors('email');
});

test('registrasi menolak NIK yang sudah digunakan', function () {
    $this->from(route('register.form'))
        ->post(route('register'), [
            'name' => 'Peserta Duplikat',
            'email' => 'nik-duplikat@example.test',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'nik' => '1111111111111111',
            'no_telepon' => '081234567891',
            'alamat' => 'Alamat Baru',
        ])
        ->assertRedirect(route('register.form'))
        ->assertSessionHasErrors('nik');
});

test('registrasi menolak konfirmasi kata sandi yang tidak cocok', function () {
    $this->from(route('register.form'))
        ->post(route('register'), [
            'name' => 'Peserta Baru',
            'email' => 'baru@example.test',
            'password' => 'Password123',
            'password_confirmation' => 'Password456',
            'nik' => '2222222222222222',
            'no_telepon' => '081234567891',
            'alamat' => 'Alamat Baru',
        ])
        ->assertRedirect(route('register.form'))
        ->assertSessionHasErrors('password');
});

test('peserta dengan akun diterima dapat login ke dashboard', function () {
    $pesertaDiterima = User::factory()->create([
        'email' => 'diterima@example.test',
        'password' => 'password',
        'role' => 'peserta',
        'status_akun' => 'diterima',
    ]);

    $this->get(route('login.peserta'))
        ->assertRedirect(route('login.form'));

    $this->get(route('login.form'))
        ->assertOk()
        ->assertSee('Masuk ke akun')
        ->assertSee('Masuk sebagai')
        ->assertSee('Admin')
        ->assertSee('Peserta')
        ->assertSee('data-history-back');

    $this->post(route('login'), [
        'email' => $pesertaDiterima->email,
        'password' => 'password',
        'login_as' => 'peserta',
    ])
        ->assertRedirect(route('peserta.dashboard'));

    $this->assertAuthenticatedAs($pesertaDiterima);
});

test('login sebagai peserta menolak kredensial admin', function () {
    $admin = User::factory()->create([
        'email' => 'admin@example.test',
        'password' => 'password',
        'role' => 'admin',
        'status_akun' => 'diterima',
    ]);

    $this->from(route('login.form'))
        ->post(route('login'), [
            'email' => $admin->email,
            'password' => 'password',
            'login_as' => 'peserta',
        ])
        ->assertRedirect(route('login.form'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('login admin pada portal admin diarahkan ke dashboard admin', function () {
    $admin = User::factory()->create([
        'email' => 'admin@example.test',
        'password' => 'password',
        'role' => 'admin',
        'status_akun' => 'diterima',
    ]);

    $this->get(route('login.admin'))
        ->assertRedirect(route('login.form'));

    $this->get(route('login.form'))
        ->assertOk()
        ->assertSee('Masuk sebagai')
        ->assertSee('Admin')
        ->assertSee('Peserta');

    $this->post(route('login'), [
        'email' => $admin->email,
        'password' => 'password',
        'login_as' => 'admin',
    ])
        ->assertRedirect(route('admin.dashboard'));
});

test('peserta yang menunggu verifikasi diarahkan ke status akun saat login', function () {
    $this->post(route('login'), [
        'email' => $this->peserta->email,
        'password' => 'password',
    ])
        ->assertRedirect(route('peserta.status'));

    $this->assertAuthenticatedAs($this->peserta);
});

test('login menolak kata sandi yang salah', function () {
    $this->from(route('login.form'))
        ->post(route('login'), [
            'email' => $this->peserta->email,
            'password' => 'bukan-password-benar',
        ])
        ->assertRedirect(route('login.form'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('peserta yang login dapat logout', function () {
    $this->actingAs($this->peserta)
        ->post(route('logout'))
        ->assertRedirect(route('home'))
        ->assertSessionHas('success');

    $this->assertGuest();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Masuk')
        ->assertDontSee('Keluar');
});

test('admin yang login dapat logout dan kembali ke beranda', function () {
    $this->actingAs($this->admin)
        ->post(route('logout'))
        ->assertRedirect(route('home'));

    $this->assertGuest();
});

test('admin dapat melihat peserta pending beserta profilnya', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.verifikasi-akun.index'))
        ->assertOk()
        ->assertSee($this->peserta->name)
        ->assertSee('1111111111111111');
});

test('admin dapat menerima pendaftaran akun dan sistem mencatat verifikator', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.verifikasi-akun.terima', $this->peserta))
        ->assertRedirect(route('admin.verifikasi-akun.index'));

    $this->assertDatabaseHas('users', [
        'id' => $this->peserta->id,
        'status_akun' => 'diterima',
        'verified_by' => $this->admin->id,
    ]);

    expect($this->peserta->fresh()->verified_at)->not->toBeNull();
});

test('admin dapat menolak akun dengan alasan dan identitas verifikator', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.verifikasi-akun.tolak', $this->peserta), [
            'alasan_penolakan' => 'Data identitas belum sesuai.',
        ])
        ->assertRedirect(route('admin.verifikasi-akun.index'));

    $this->assertDatabaseHas('users', [
        'id' => $this->peserta->id,
        'status_akun' => 'ditolak',
        'alasan_penolakan' => 'Data identitas belum sesuai.',
        'verified_by' => $this->admin->id,
    ]);

    expect($this->peserta->fresh()->verified_at)->not->toBeNull();
});

test('alasan penolakan akun wajib diisi', function () {
    $this->actingAs($this->admin)
        ->from(route('admin.verifikasi-akun.index'))
        ->post(route('admin.verifikasi-akun.tolak', $this->peserta), [
            'alasan_penolakan' => '',
        ])
        ->assertRedirect(route('admin.verifikasi-akun.index'))
        ->assertSessionHasErrors('alasan_penolakan');
});

test('peserta tidak dapat mengakses route verifikasi akun admin', function () {
    $this->actingAs($this->peserta)
        ->get(route('admin.verifikasi-akun.index'))
        ->assertForbidden();

    $this->post(route('admin.verifikasi-akun.terima', $this->peserta))
        ->assertForbidden();
});

test('akun yang sudah diverifikasi tidak dapat diproses kembali', function () {
    $this->peserta->update(['status_akun' => 'diterima']);

    $this->actingAs($this->admin)
        ->post(route('admin.verifikasi-akun.terima', $this->peserta))
        ->assertNotFound();
});
