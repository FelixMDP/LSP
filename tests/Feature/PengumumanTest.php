<?php

use App\Models\BeritaKesehatan;
use App\Models\Pengumuman;
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
});

test('admin dapat membuat pengumuman dan tercatat sebagai pembuat', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.pengumuman.store'), [
            'judul' => 'Jadwal vaksinasi',
            'isi' => 'Peserta diminta datang tepat waktu.',
        ])
        ->assertRedirect(route('admin.pengumuman.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('pengumuman', [
        'admin_id' => $this->admin->id,
        'judul' => 'Jadwal vaksinasi',
        'isi' => 'Peserta diminta datang tepat waktu.',
    ]);
});

test('admin dapat mengubah pengumuman', function () {
    $pengumuman = Pengumuman::query()->create([
        'admin_id' => $this->admin->id,
        'judul' => 'Judul awal',
        'isi' => 'Isi awal',
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.pengumuman.update', $pengumuman), [
            'judul' => 'Judul diperbarui',
            'isi' => 'Isi diperbarui',
        ])
        ->assertRedirect(route('admin.pengumuman.index'));

    $this->assertDatabaseHas('pengumuman', [
        'id' => $pengumuman->id,
        'judul' => 'Judul diperbarui',
        'isi' => 'Isi diperbarui',
    ]);
});

test('admin dapat menghapus pengumuman', function () {
    $pengumuman = Pengumuman::query()->create([
        'admin_id' => $this->admin->id,
        'judul' => 'Pengumuman lama',
        'isi' => 'Isi lama',
    ]);

    $this->actingAs($this->admin)
        ->delete(route('admin.pengumuman.destroy', $pengumuman))
        ->assertRedirect(route('admin.pengumuman.index'));

    $this->assertDatabaseMissing('pengumuman', [
        'id' => $pengumuman->id,
    ]);
});

test('peserta dapat melihat daftar pengumuman dan kontennya di-escape', function () {
    Pengumuman::query()->create([
        'admin_id' => $this->admin->id,
        'judul' => 'Informasi penting',
        'isi' => '<script>alert(1)</script>',
    ]);

    $this->actingAs($this->peserta)
        ->get(route('peserta.pengumuman.index'))
        ->assertOk()
        ->assertSee('Informasi penting')
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
        ->assertDontSee('<script>alert(1)</script>', false);
});

test('dashboard peserta menampilkan pengumuman terbaru dan tautan ke daftar lengkap', function () {
    $pengumumanLama = Pengumuman::query()->create([
        'admin_id' => $this->admin->id,
        'judul' => 'Pengumuman lama',
        'isi' => 'Informasi lama.',
    ]);
    $pengumumanLama->created_at = now()->subDays(2);
    $pengumumanLama->save();

    Pengumuman::query()->create([
        'admin_id' => $this->admin->id,
        'judul' => 'Pengumuman terbaru',
        'isi' => 'Informasi yang perlu diketahui peserta.',
    ]);

    foreach ([2, 3] as $nomor) {
        Pengumuman::query()->create([
            'admin_id' => $this->admin->id,
            'judul' => 'Info dashboard '.$nomor,
            'isi' => 'Informasi tambahan.',
        ]);
    }

    $this->actingAs($this->peserta)
        ->get(route('peserta.dashboard'))
        ->assertOk()
        ->assertSee('Pengumuman untuk Anda')
        ->assertSee('Pengumuman terbaru')
        ->assertSee('data-dashboard-greeting')
        ->assertSee('data-dashboard-date')
        ->assertDontSee('dashboard-hero__art')
        ->assertDontSee('Pengumuman lama')
        ->assertSee(route('peserta.pengumuman.index'))
        ->assertSee('data-slide-carousel')
        ->assertSee('Berita seputar imunisasi dan penyakit')
        ->assertSee('Kemenkes soroti risiko KLB campak')
        ->assertSee('KLB polio tipe 2 berakhir')
        ->assertSee('data-slide-next')
        ->assertSee('rel="noopener noreferrer"', false)
        ->assertSee('data-announcement-slide')
        ->assertDontSee('data-history-back');
});

test('beranda publik menampilkan pengumuman terbaru tanpa login', function () {
    $pengumumanLama = Pengumuman::query()->create([
        'admin_id' => $this->admin->id,
        'judul' => 'Berita publik lama',
        'isi' => 'Informasi terdahulu.',
    ]);
    $pengumumanLama->created_at = now()->subDays(3);
    $pengumumanLama->save();

    Pengumuman::query()->create([
        'admin_id' => $this->admin->id,
        'judul' => 'Info vaksinasi terbaru',
        'isi' => 'Periksa jadwal sebelum datang ke lokasi.',
    ]);

    foreach ([2, 3] as $nomor) {
        Pengumuman::query()->create([
            'admin_id' => $this->admin->id,
            'judul' => 'Kabar publik '.$nomor,
            'isi' => 'Informasi vaksinasi terkini.',
        ]);
    }

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Pengumuman terbaru')
        ->assertSee('Info vaksinasi terbaru')
        ->assertSee('Berita seputar imunisasi dan penyakit')
        ->assertSee('Kemenkes soroti risiko KLB campak')
        ->assertDontSee('Berita publik lama')
        ->assertSee('Persiapkan vaksinasi dengan nyaman');
});

test('dashboard admin menampilkan ringkasan pengumuman terbaru', function () {
    Pengumuman::query()->create([
        'admin_id' => $this->admin->id,
        'judul' => 'Pengumuman untuk dashboard admin',
        'isi' => 'Ringkasan informasi terbaru.',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Pengumuman yang sudah dibagikan')
        ->assertSee('data-dashboard-greeting')
        ->assertDontSee('dashboard-hero__art')
        ->assertSee('Pengumuman untuk dashboard admin')
        ->assertSee(route('admin.pengumuman.index'))
        ->assertDontSee('data-history-back');
});

test('dashboard peserta menampilkan berita harian yang sudah tersimpan', function () {
    $tanggal = now('Asia/Jakarta')->toDateString();

    BeritaKesehatan::query()->create([
        'published_on' => $tanggal,
        'category' => 'Imunisasi',
        'title' => 'Berita imunisasi hari ini',
        'summary' => 'Ringkasan berita terbaru tentang imunisasi.',
        'context' => 'Informasi ini bersifat edukatif.',
        'source_name' => 'Kementerian Kesehatan RI',
        'source_url' => 'https://www.kemkes.go.id/berita-imunisasi-hari-ini',
        'search_suggestions' => '<div class="search-suggestions">Google Search</div>',
        'source_published_at' => $tanggal,
    ]);

    $this->actingAs($this->peserta)
        ->get(route('peserta.dashboard'))
        ->assertOk()
        ->assertSee('Berita imunisasi hari ini')
        ->assertSee('https://www.kemkes.go.id/berita-imunisasi-hari-ini')
        ->assertSee('Google Search')
        ->assertDontSee('KLB polio tipe 2 berakhir');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Berita imunisasi hari ini')
        ->assertSee('https://www.kemkes.go.id/berita-imunisasi-hari-ini')
        ->assertSee('Google Search');
});

test('judul dan isi pengumuman wajib diisi', function () {
    $this->actingAs($this->admin)
        ->from(route('admin.pengumuman.create'))
        ->post(route('admin.pengumuman.store'), [
            'judul' => '',
            'isi' => '',
        ])
        ->assertRedirect(route('admin.pengumuman.create'))
        ->assertSessionHasErrors(['judul', 'isi']);

    $this->assertDatabaseCount('pengumuman', 0);
});

test('peserta tidak dapat mengakses route pengelolaan pengumuman admin', function () {
    $this->actingAs($this->peserta)
        ->get(route('admin.pengumuman.index'))
        ->assertForbidden();

    $this->post(route('admin.pengumuman.store'), [
        'judul' => 'Tidak berhak',
        'isi' => 'Konten',
    ])
        ->assertForbidden();
});

test('admin tidak menggunakan halaman pengumuman peserta', function () {
    $this->actingAs($this->admin)
        ->get(route('peserta.pengumuman.index'))
        ->assertForbidden();
});
