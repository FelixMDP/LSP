<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\PendaftaranVaksinasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminPembayaranController extends Controller
{
    public function index(): View
    {
        PendaftaranVaksinasi::expireRejectedPaymentRegistrations();

        $pembayaran = Pembayaran::query()
            ->with([
                'pendaftaran.user.profilPeserta',
                'pendaftaran.jadwal',
                'verifier',
            ])
            ->latest()
            ->paginate(15);

        return view('admin.pembayaran.index', compact('pembayaran'));
    }

    public function bukti(Pembayaran $pembayaran): StreamedResponse
    {
        abort_unless(
            Storage::disk('local')->exists($pembayaran->bukti_pembayaran),
            404,
            'Bukti pembayaran tidak ditemukan.'
        );

        return Storage::disk('local')->download(
            $pembayaran->bukti_pembayaran,
            'bukti-pembayaran-'.$pembayaran->id.'.'.pathinfo($pembayaran->bukti_pembayaran, PATHINFO_EXTENSION)
        );
    }

    public function terima(Pembayaran $pembayaran): RedirectResponse
    {
        DB::transaction(function () use ($pembayaran): void {
            $pembayaran = Pembayaran::query()
                ->lockForUpdate()
                ->findOrFail($pembayaran->id);

            abort_unless(
                $pembayaran->status_pembayaran === Pembayaran::STATUS_PENDING,
                404,
                'Pembayaran tidak ditemukan atau sudah diverifikasi.'
            );

            $pembayaran->update([
                'status_pembayaran' => Pembayaran::STATUS_DITERIMA,
                'alasan_penolakan' => null,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.pembayaran.index')
            ->with('success', 'Pembayaran berhasil diterima.');
    }

    public function tolak(Request $request, Pembayaran $pembayaran): RedirectResponse
    {
        $validated = $request->validate([
            'alasan_penolakan' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($pembayaran, $validated): void {
            $pembayaran = Pembayaran::query()
                ->lockForUpdate()
                ->findOrFail($pembayaran->id);

            abort_unless(
                $pembayaran->status_pembayaran === Pembayaran::STATUS_PENDING,
                404,
                'Pembayaran tidak ditemukan atau sudah diverifikasi.'
            );

            $pembayaran->update([
                'status_pembayaran' => Pembayaran::STATUS_DITOLAK,
                'alasan_penolakan' => $validated['alasan_penolakan'],
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.pembayaran.index')
            ->with('success', 'Pembayaran berhasil ditolak.');
    }
}
