<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminVerifikasiAkunController extends Controller
{
    // Menampilkan daftar akun peserta yang menunggu verifikasi
    public function index()
    {
        $peserta = User::with('profilPeserta')
            ->where('role', 'peserta')
            ->where('status_akun', 'pending')
            ->latest()
            ->get();

        return view('admin.verifikasi-akun', compact('peserta'));
    }

    // Menerima akun peserta
    public function terima(User $user)
    {
        if ($user->role !== 'peserta' || $user->status_akun !== 'pending') {
            abort(404, 'Data peserta tidak ditemukan atau sudah diverifikasi.');
        }

        $user->update([
            'status_akun' => 'diterima',
            'alasan_penolakan' => null,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.verifikasi-akun.index')
            ->with('success', 'Akun peserta berhasil diterima.');
    }

    // Menolak akun peserta
    public function tolak(Request $request, User $user)
    {
        if ($user->role !== 'peserta' || $user->status_akun !== 'pending') {
            abort(404, 'Data peserta tidak ditemukan atau sudah diverifikasi.');
        }

        $validated = $request->validate([
            'alasan_penolakan' => 'required|string|max:1000',
        ]);

        $user->update([
            'status_akun' => 'ditolak',
            'alasan_penolakan' => $validated['alasan_penolakan'],
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.verifikasi-akun.index')
            ->with('success', 'Akun peserta berhasil ditolak.');
    }
}
