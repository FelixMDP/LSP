<?php

use App\Models\JadwalVaksinasi;
use App\Models\Pembayaran;
use App\Models\PendaftaranVaksinasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('local');
    Config::set('vaksinasi.biaya', 500000);

    $this->admin = User::factory()->create([
        'role' => 'admin',
        'status_akun' => 'diterima',
    ]);

    $this->peserta = User::factory()->create([
        'role' => 'peserta',
        'status_akun' => 'diterima',
    ]);

    $this->jadwal = JadwalVaksinasi::query()->create([
        'tanggal' => today()->addDays(3),
        'jam_mulai' => '08:00:00',
        'jam_selesai' => '12:00:00',
        'kuota' => 10,
    ]);

    $this->pendaftaran = $this->jadwal->pendaftaran()->create([
        'user_id' => $this->peserta->id,
        'status_pendaftaran' => PendaftaranVaksinasi::STATUS_DITERIMA,
    ]);
});

test('peserta melihat nominal pembayaran yang dikonfigurasi dan statusnya', function () {
    $this->actingAs($this->peserta)
        ->get(route('peserta.pembayaran.index'))
        ->assertOk()
        ->assertSee('500.000')
        ->assertSee('belum dikirim');
});

test('peserta dapat mengunggah bukti dan nominal berasal dari konfigurasi server', function () {
    $this->actingAs($this->peserta)
        ->post(route('peserta.pembayaran.store', $this->pendaftaran), [
            'bukti_pembayaran' => UploadedFile::fake()->create(
                'bukti.pdf',
                100,
                'application/pdf'
            ),
            'jumlah_pembayaran' => 1,
        ])
        ->assertRedirect(route('peserta.pembayaran.index'))
        ->assertSessionHas('success');

    $pembayaran = Pembayaran::query()->sole();

    expect((float) $pembayaran->jumlah_pembayaran)->toBe(500000.0)
        ->and($pembayaran->status_pembayaran)->toBe(Pembayaran::STATUS_PENDING);

    Storage::disk('local')->assertExists($pembayaran->bukti_pembayaran);
});

test('peserta belum dapat membayar sebelum pendaftaran diterima', function () {
    $this->pendaftaran->update([
        'status_pendaftaran' => PendaftaranVaksinasi::STATUS_PENDING,
    ]);

    $this->actingAs($this->peserta)
        ->post(route('peserta.pembayaran.store', $this->pendaftaran), [
            'bukti_pembayaran' => UploadedFile::fake()->create(
                'bukti.pdf',
                100,
                'application/pdf'
            ),
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('pendaftaran');

    $this->assertDatabaseCount('pembayaran', 0);
});

test('peserta tidak dapat membayar pendaftaran milik peserta lain', function () {
    $pesertaLain = User::factory()->create([
        'role' => 'peserta',
        'status_akun' => 'diterima',
    ]);

    $pendaftaranLain = $this->jadwal->pendaftaran()->create([
        'user_id' => $pesertaLain->id,
        'status_pendaftaran' => PendaftaranVaksinasi::STATUS_DITERIMA,
    ]);

    $this->actingAs($this->peserta)
        ->post(route('peserta.pembayaran.store', $pendaftaranLain), [
            'bukti_pembayaran' => UploadedFile::fake()->create(
                'bukti.pdf',
                100,
                'application/pdf'
            ),
        ])
        ->assertNotFound();

    $this->assertDatabaseCount('pembayaran', 0);
});

test('unggahan ditolak jika jenis file tidak didukung atau ukuran melebihi 5 MB', function () {
    $this->actingAs($this->peserta)
        ->from(route('peserta.pembayaran.index'))
        ->post(route('peserta.pembayaran.store', $this->pendaftaran), [
            'bukti_pembayaran' => UploadedFile::fake()->create(
                'bukti.txt',
                100,
                'text/plain'
            ),
        ])
        ->assertRedirect(route('peserta.pembayaran.index'))
        ->assertSessionHasErrors('bukti_pembayaran');

    $this->post(route('peserta.pembayaran.store', $this->pendaftaran), [
        'bukti_pembayaran' => UploadedFile::fake()->create(
            'besar.pdf',
            5121,
            'application/pdf'
        ),
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('bukti_pembayaran');

    $this->assertDatabaseCount('pembayaran', 0);
});

test('unggahan diblokir jika nominal belum dikonfigurasi', function () {
    Config::set('vaksinasi.biaya', null);

    $this->actingAs($this->peserta)
        ->post(route('peserta.pembayaran.store', $this->pendaftaran), [
            'bukti_pembayaran' => UploadedFile::fake()->create(
                'bukti.pdf',
                100,
                'application/pdf'
            ),
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('bukti_pembayaran');

    $this->assertDatabaseCount('pembayaran', 0);
});

test('peserta dapat mengirim ulang bukti setelah pembayaran ditolak', function () {
    Storage::disk('local')->put('bukti-pembayaran/lama.pdf', 'bukti lama');

    $pembayaranLama = $this->pendaftaran->pembayaran()->create([
        'jumlah_pembayaran' => 500000,
        'bukti_pembayaran' => 'bukti-pembayaran/lama.pdf',
        'status_pembayaran' => Pembayaran::STATUS_DITOLAK,
        'alasan_penolakan' => 'Bukti tidak terbaca.',
        'verified_by' => $this->admin->id,
        'verified_at' => now(),
    ]);

    $this->actingAs($this->peserta)
        ->post(route('peserta.pembayaran.store', $this->pendaftaran), [
            'bukti_pembayaran' => UploadedFile::fake()->create(
                'bukti-baru.pdf',
                100,
                'application/pdf'
            ),
        ])
        ->assertRedirect(route('peserta.pembayaran.index'));

    $pembayaranLama->refresh();

    expect($pembayaranLama->status_pembayaran)->toBe(Pembayaran::STATUS_PENDING)
        ->and($pembayaranLama->alasan_penolakan)->toBeNull()
        ->and($pembayaranLama->verified_by)->toBeNull()
        ->and($pembayaranLama->bukti_pembayaran)->not->toBe('bukti-pembayaran/lama.pdf');

    Storage::disk('local')->assertMissing('bukti-pembayaran/lama.pdf');
    Storage::disk('local')->assertExists($pembayaranLama->bukti_pembayaran);
});

test('pendaftaran kedaluwarsa setelah satu jam dan peserta dapat mendaftar ulang dengan kuota yang dilepaskan', function () {
    $this->jadwal->update(['kuota' => 1]);

    $pembayaran = $this->pendaftaran->pembayaran()->create([
        'jumlah_pembayaran' => 500000,
        'bukti_pembayaran' => 'bukti-pembayaran/ditolak.pdf',
        'status_pembayaran' => Pembayaran::STATUS_DITOLAK,
        'alasan_penolakan' => 'Bukti tidak terbaca.',
        'verified_by' => $this->admin->id,
        'verified_at' => now()->subHour()->subSecond(),
    ]);

    $this->actingAs($this->peserta)
        ->get(route('peserta.pembayaran.index'))
        ->assertOk()
        ->assertSee('Batas waktu unggah ulang telah lewat')
        ->assertSee('Daftar ulang dan pilih jadwal');

    $this->assertDatabaseHas('pendaftaran_vaksinasi', [
        'id' => $this->pendaftaran->id,
        'status_pendaftaran' => PendaftaranVaksinasi::STATUS_KEDALUWARSA,
    ]);
    $this->assertDatabaseHas('pembayaran', [
        'id' => $pembayaran->id,
        'status_pembayaran' => Pembayaran::STATUS_DITOLAK,
    ]);

    $this->post(route('peserta.pembayaran.store', $this->pendaftaran), [
        'bukti_pembayaran' => UploadedFile::fake()->create(
            'terlambat.pdf',
            100,
            'application/pdf'
        ),
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('pendaftaran');

    $this->get(route('peserta.jadwal.index'))
        ->assertOk()
        ->assertSee('1 dari 1')
        ->assertSee('Pilih jadwal')
        ->assertDontSee('Sudah mendaftar');

    $this->post(route('peserta.jadwal.store'), [
        'jadwal_id' => $this->jadwal->id,
    ])
        ->assertRedirect(route('peserta.jadwal.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseCount('pendaftaran_vaksinasi', 2);
    $this->assertDatabaseHas('pendaftaran_vaksinasi', [
        'id' => $this->pendaftaran->id,
        'status_pendaftaran' => PendaftaranVaksinasi::STATUS_KEDALUWARSA,
    ]);
    $this->assertDatabaseHas('pendaftaran_vaksinasi', [
        'user_id' => $this->peserta->id,
        'jadwal_id' => $this->jadwal->id,
        'status_pendaftaran' => PendaftaranVaksinasi::STATUS_PENDING,
    ]);
});

test('unggah ulang masih diizinkan sebelum satu jam sejak penolakan', function () {
    $pembayaran = $this->pendaftaran->pembayaran()->create([
        'jumlah_pembayaran' => 500000,
        'bukti_pembayaran' => 'bukti-pembayaran/ditolak.pdf',
        'status_pembayaran' => Pembayaran::STATUS_DITOLAK,
        'verified_by' => $this->admin->id,
        'verified_at' => now()->subMinutes(59),
    ]);

    $this->actingAs($this->peserta)
        ->get(route('peserta.pembayaran.index'))
        ->assertOk()
        ->assertSee('Unggah ulang sebelum')
        ->assertSee('data-local-time')
        ->assertSee('Kirim bukti pembayaran');

    $this->post(route('peserta.pembayaran.store', $this->pendaftaran), [
        'bukti_pembayaran' => UploadedFile::fake()->create(
            'bukti-ulang.pdf',
            100,
            'application/pdf'
        ),
    ])
        ->assertRedirect(route('peserta.pembayaran.index'))
        ->assertSessionHas('success');

    expect($this->pendaftaran->fresh()->status_pendaftaran)
        ->toBe(PendaftaranVaksinasi::STATUS_DITERIMA)
        ->and($pembayaran->fresh()->status_pembayaran)
        ->toBe(Pembayaran::STATUS_PENDING)
        ->and($pembayaran->fresh()->verified_at)
        ->toBeNull();
});

test('perintah terjadwal menandai pendaftaran kedaluwarsa setelah batas waktu', function () {
    $this->pendaftaran->pembayaran()->create([
        'jumlah_pembayaran' => 500000,
        'bukti_pembayaran' => 'bukti-pembayaran/ditolak.pdf',
        'status_pembayaran' => Pembayaran::STATUS_DITOLAK,
        'verified_by' => $this->admin->id,
        'verified_at' => now()->subHour()->subSecond(),
    ]);

    $this->artisan('vaksinasi:expire-rejected-payment-registrations')
        ->assertExitCode(0);

    $this->assertDatabaseHas('pendaftaran_vaksinasi', [
        'id' => $this->pendaftaran->id,
        'status_pendaftaran' => PendaftaranVaksinasi::STATUS_KEDALUWARSA,
    ]);
});

test('admin dapat mengunduh bukti secara terproteksi dan menerima pembayaran', function () {
    Storage::disk('local')->put('bukti-pembayaran/receipt.pdf', 'bukti pengujian');

    $pembayaran = $this->pendaftaran->pembayaran()->create([
        'jumlah_pembayaran' => 500000,
        'bukti_pembayaran' => 'bukti-pembayaran/receipt.pdf',
        'status_pembayaran' => Pembayaran::STATUS_PENDING,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.pembayaran.bukti', $pembayaran))
        ->assertDownload('bukti-pembayaran-'.$pembayaran->id.'.pdf');

    $this->post(route('admin.pembayaran.terima', $pembayaran))
        ->assertRedirect(route('admin.pembayaran.index'));

    $this->assertDatabaseHas('pembayaran', [
        'id' => $pembayaran->id,
        'status_pembayaran' => Pembayaran::STATUS_DITERIMA,
        'verified_by' => $this->admin->id,
        'alasan_penolakan' => null,
    ]);

    expect($pembayaran->fresh()->verified_at)->not->toBeNull();
});

test('admin dapat menolak pembayaran dengan alasan', function () {
    $pembayaran = $this->pendaftaran->pembayaran()->create([
        'jumlah_pembayaran' => 500000,
        'bukti_pembayaran' => 'bukti-pembayaran/receipt.pdf',
        'status_pembayaran' => Pembayaran::STATUS_PENDING,
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.pembayaran.tolak', $pembayaran), [
            'alasan_penolakan' => 'Nominal pada bukti tidak sesuai.',
        ])
        ->assertRedirect(route('admin.pembayaran.index'));

    $this->assertDatabaseHas('pembayaran', [
        'id' => $pembayaran->id,
        'status_pembayaran' => Pembayaran::STATUS_DITOLAK,
        'alasan_penolakan' => 'Nominal pada bukti tidak sesuai.',
        'verified_by' => $this->admin->id,
    ]);
});

test('alasan penolakan pembayaran wajib diisi', function () {
    $pembayaran = $this->pendaftaran->pembayaran()->create([
        'jumlah_pembayaran' => 500000,
        'bukti_pembayaran' => 'bukti-pembayaran/receipt.pdf',
        'status_pembayaran' => Pembayaran::STATUS_PENDING,
    ]);

    $this->actingAs($this->admin)
        ->from(route('admin.pembayaran.index'))
        ->post(route('admin.pembayaran.tolak', $pembayaran), [
            'alasan_penolakan' => '',
        ])
        ->assertRedirect(route('admin.pembayaran.index'))
        ->assertSessionHasErrors('alasan_penolakan');
});

test('peserta tidak dapat mengakses daftar atau bukti pembayaran admin', function () {
    $pembayaran = $this->pendaftaran->pembayaran()->create([
        'jumlah_pembayaran' => 500000,
        'bukti_pembayaran' => 'bukti-pembayaran/receipt.pdf',
        'status_pembayaran' => Pembayaran::STATUS_PENDING,
    ]);

    $this->actingAs($this->peserta)
        ->get(route('admin.pembayaran.index'))
        ->assertForbidden();

    $this->get(route('admin.pembayaran.bukti', $pembayaran))
        ->assertForbidden();
});

test('admin dapat melihat daftar pembayaran yang masuk', function () {
    $this->pendaftaran->pembayaran()->create([
        'jumlah_pembayaran' => 500000,
        'bukti_pembayaran' => 'bukti-pembayaran/receipt.pdf',
        'status_pembayaran' => Pembayaran::STATUS_PENDING,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.pembayaran.index'))
        ->assertOk()
        ->assertSee($this->peserta->name)
        ->assertSee('500.000')
        ->assertSee('Unduh bukti');
});
