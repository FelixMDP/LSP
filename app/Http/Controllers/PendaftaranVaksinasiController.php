<?php

namespace App\Http\Controllers;

use App\Models\JadwalVaksinasi;
use App\Models\PendaftaranVaksinasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PendaftaranVaksinasiController extends Controller
{
    public function index(Request $request): View
    {
        PendaftaranVaksinasi::expireRejectedPaymentRegistrations();

        $userId = $request->user()->getAuthIdentifier();

        $jadwal = JadwalVaksinasi::query()
            ->withCount('pendaftaranAktif')
            ->withExists([
                'pendaftaran as sudah_mendaftar' => fn (Builder $query) => $query
                    ->where('user_id', $userId)
                    ->where('status_pendaftaran', '!=', PendaftaranVaksinasi::STATUS_KEDALUWARSA),
            ])
            ->whereDate('tanggal', '>=', today())
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        return view('peserta.jadwal.index', compact('jadwal'));
    }

    public function store(Request $request): RedirectResponse
    {
        PendaftaranVaksinasi::expireRejectedPaymentRegistrations();

        $validated = $request->validate([
            'jadwal_id' => ['required', 'integer', 'exists:jadwal_vaksinasi,id'],
        ]);

        $userId = $request->user()->getAuthIdentifier();

        try {
            DB::transaction(function () use ($validated, $userId): void {
                $jadwal = JadwalVaksinasi::query()
                    ->lockForUpdate()
                    ->findOrFail($validated['jadwal_id']);

                if ($jadwal->pendaftaran()
                    ->where('user_id', $userId)
                    ->where('status_pendaftaran', '!=', PendaftaranVaksinasi::STATUS_KEDALUWARSA)
                    ->exists()) {
                    throw ValidationException::withMessages([
                        'jadwal_id' => 'Anda sudah terdaftar pada jadwal ini.',
                    ]);
                }

                if ($jadwal->tanggal->lt(today())) {
                    throw ValidationException::withMessages([
                        'jadwal_id' => 'Jadwal vaksinasi tersebut sudah lewat.',
                    ]);
                }

                if ($jadwal->pendaftaranAktif()->count() >= $jadwal->kuota) {
                    throw ValidationException::withMessages([
                        'jadwal_id' => 'Kuota jadwal vaksinasi sudah penuh.',
                    ]);
                }

                $jadwal->pendaftaran()->create([
                    'user_id' => $userId,
                    'status_pendaftaran' => PendaftaranVaksinasi::STATUS_PENDING,
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'jadwal_id' => 'Anda sudah terdaftar pada jadwal ini.',
            ]);
        }

        return redirect()
            ->route('peserta.jadwal.index')
            ->with('success', 'Pendaftaran jadwal berhasil disimpan.');
    }
}
