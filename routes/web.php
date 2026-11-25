
<?php

use App\Http\Controllers\AdminPembayaranController;
use App\Http\Controllers\AdminPengumumanController;
use App\Http\Controllers\AdminVerifikasiAkunController;
use App\Http\Controllers\AdminVerifikasiPendaftaranController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JadwalPublikController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PendaftaranVaksinasiController;
use App\Http\Controllers\PengumumanPesertaController;
use App\Models\Pengumuman;
use Illuminate\Support\Facades\Route;

// ======================================
// HALAMAN UTAMA
// ======================================

Route::view('/', 'welcome')->name('home');
Route::get('/jadwal', [JadwalPublikController::class, 'index'])
    ->name('jadwal.publik');

// ======================================
// AUTENTIKASI
// ======================================

Route::get('/login/admin', function () {
    return view('auth.login', ['loginRole' => 'admin']);
})->name('login.admin');

Route::get('/login/peserta', function () {
    return view('auth.login', ['loginRole' => 'peserta']);
})->name('login.peserta');

// Menampilkan halaman login
Route::get('/login', function () {
    return view('auth.login', ['loginRole' => null]);
})->name('login.form');

// Memproses login
Route::post('/login', [AuthController::class, 'login'])
    ->name('login');

// Menampilkan halaman registrasi
Route::get('/register', function () {
    return view('auth.register');
})->name('register.form');

// Memproses registrasi
Route::post('/register', [AuthController::class, 'register'])
    ->name('register');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ======================================
// DASHBOARD DAN FITUR ADMIN
// ======================================

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard admin
        Route::get('/dashboard', function () {
            $pengumuman = Pengumuman::query()
                ->latest()
                ->limit(3)
                ->get();

            return view('admin.dashboard', compact('pengumuman'));
        })->name('dashboard');

        // ==================================
        // VERIFIKASI AKUN PESERTA
        // ==================================

        // Menampilkan daftar peserta pending
        Route::get('/verifikasi-akun', [
            AdminVerifikasiAkunController::class,
            'index',
        ])->name('verifikasi-akun.index');

        // Menerima akun peserta
        Route::post('/verifikasi-akun/{user}/terima', [
            AdminVerifikasiAkunController::class,
            'terima',
        ])->name('verifikasi-akun.terima');

        // Menolak akun peserta
        Route::post('/verifikasi-akun/{user}/tolak', [
            AdminVerifikasiAkunController::class,
            'tolak',
        ])->name('verifikasi-akun.tolak');

        Route::get('/pendaftaran', [
            AdminVerifikasiPendaftaranController::class,
            'index',
        ])->name('pendaftaran.index');
        Route::get('/pendaftaran/{pendaftaran}', [
            AdminVerifikasiPendaftaranController::class,
            'show',
        ])->name('pendaftaran.show');
        Route::post('/pendaftaran/{pendaftaran}/terima', [
            AdminVerifikasiPendaftaranController::class,
            'terima',
        ])->name('pendaftaran.terima');
        Route::post('/pendaftaran/{pendaftaran}/tolak', [
            AdminVerifikasiPendaftaranController::class,
            'tolak',
        ])->name('pendaftaran.tolak');

        Route::get('/pembayaran', [
            AdminPembayaranController::class,
            'index',
        ])->name('pembayaran.index');
        Route::get('/pembayaran/{pembayaran}/bukti', [
            AdminPembayaranController::class,
            'bukti',
        ])->name('pembayaran.bukti');
        Route::post('/pembayaran/{pembayaran}/terima', [
            AdminPembayaranController::class,
            'terima',
        ])->name('pembayaran.terima');
        Route::post('/pembayaran/{pembayaran}/tolak', [
            AdminPembayaranController::class,
            'tolak',
        ])->name('pembayaran.tolak');

        Route::resource('pengumuman', AdminPengumumanController::class)
            ->except(['show']);

    });

// ======================================
// DASHBOARD PESERTA
// ======================================

Route::middleware(['auth', 'role:peserta'])
    ->prefix('peserta')
    ->name('peserta.')
    ->group(function () {

        // Halaman status akun
        Route::get('/status', function () {
            return view('peserta.status');
        })->name('status');

        // Dashboard peserta yang akunnya diterima
        Route::get('/dashboard', function () {
            $pengumuman = Pengumuman::query()
                ->latest()
                ->limit(3)
                ->get();

            return view('peserta.dashboard', compact('pengumuman'));
        })
            ->middleware('akun.diterima')
            ->name('dashboard');

        Route::middleware('akun.diterima')->group(function () {
            Route::get('/jadwal', [
                PendaftaranVaksinasiController::class,
                'index',
            ])->name('jadwal.index');

            Route::post('/jadwal/daftar', [
                PendaftaranVaksinasiController::class,
                'store',
            ])->name('jadwal.store');

            Route::get('/pembayaran', [
                PembayaranController::class,
                'index',
            ])->name('pembayaran.index');
            Route::post('/pendaftaran/{pendaftaran}/pembayaran', [
                PembayaranController::class,
                'store',
            ])->name('pembayaran.store');

            Route::get('/pengumuman', PengumumanPesertaController::class)
                ->name('pengumuman.index');
        });

    });
