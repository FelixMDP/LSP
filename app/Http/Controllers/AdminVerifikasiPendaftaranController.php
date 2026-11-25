<?php

namespace App\Http\Controllers;

use App\Models\PendaftaranVaksinasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminVerifikasiPendaftaranController extends Controller
{
    public function index(): View
    {
        $pendaftaran = PendaftaranVaksinasi::query()
            ->with(['user.profilPeserta', 'jadwal'])
            ->where('status_pendaftaran', PendaftaranVaksinasi::STATUS_PENDING)
            ->latest()
            ->paginate(15);

        return view('admin.pendaftaran.index', compact('pendaftaran'));
    }

    public function show(PendaftaranVaksinasi $pendaftaran): View
    {
        $pendaftaran->load(['user.profilPeserta', 'jadwal', 'verifier']);

        return view('admin.pendaftaran.show', compact('pendaftaran'));
    }

    public function terima(PendaftaranVaksinasi $pendaftaran): RedirectResponse
    {
        DB::transaction(function () use ($pendaftaran): void {
            $pendaftaran = PendaftaranVaksinasi::query()
                ->lockForUpdate()
                ->findOrFail($pendaftaran->id);

            abort_unless(
                $pendaftaran->status_pendaftaran === PendaftaranVaksinasi::STATUS_PENDING,
                404,
                'Pendaftaran tidak ditemukan atau sudah diverifikasi.'
            );

            $pendaftaran->update([
                'status_pendaftaran' => PendaftaranVaksinasi::STATUS_DITERIMA,
                'alasan_penolakan' => null,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.pendaftaran.index')
            ->with('success', 'Pendaftaran berhasil diterima.');
    }

    public function tolak(Request $request, PendaftaranVaksinasi $pendaftaran): RedirectResponse
    {
        $validated = $request->validate([
            'alasan_penolakan' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($pendaftaran, $validated): void {
            $pendaftaran = PendaftaranVaksinasi::query()
                ->lockForUpdate()
                ->findOrFail($pendaftaran->id);

            abort_unless(
                $pendaftaran->status_pendaftaran === PendaftaranVaksinasi::STATUS_PENDING,
                404,
                'Pendaftaran tidak ditemukan atau sudah diverifikasi.'
            );

            $pendaftaran->update([
                'status_pendaftaran' => PendaftaranVaksinasi::STATUS_DITOLAK,
                'alasan_penolakan' => $validated['alasan_penolakan'],
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.pendaftaran.index')
            ->with('success', 'Pendaftaran berhasil ditolak.');
    }
}
