<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\PendaftaranVaksinasi;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class PembayaranController extends Controller
{
    public function index(Request $request): View
    {
        PendaftaranVaksinasi::expireRejectedPaymentRegistrations();

        $pendaftaran = $request->user()
            ->pendaftaranVaksinasi()
            ->with(['jadwal', 'pembayaran'])
            ->latest()
            ->get();

        return view('peserta.pembayaran.index', [
            'pendaftaran' => $pendaftaran,
            'biaya' => config('vaksinasi.biaya'),
            'rekeningDemo' => config('vaksinasi.rekening_demo'),
        ]);
    }

    public function store(Request $request, PendaftaranVaksinasi $pendaftaran): RedirectResponse
    {
        PendaftaranVaksinasi::expireRejectedPaymentRegistrations();

        $biaya = config('vaksinasi.biaya');

        if (! is_int($biaya) || $biaya < 1) {
            throw ValidationException::withMessages([
                'bukti_pembayaran' => 'Nominal pembayaran belum dikonfigurasi. Hubungi admin.',
            ]);
        }

        $pendaftaran = $request->user()
            ->pendaftaranVaksinasi()
            ->whereKey($pendaftaran->getKey())
            ->firstOrFail();

        if ($pendaftaran->status_pendaftaran === PendaftaranVaksinasi::STATUS_KEDALUWARSA) {
            throw ValidationException::withMessages([
                'pendaftaran' => 'Batas waktu unggah ulang telah lewat. Silakan mendaftar vaksin kembali.',
            ]);
        }

        if ($pendaftaran->status_pendaftaran !== PendaftaranVaksinasi::STATUS_DITERIMA) {
            throw ValidationException::withMessages([
                'pendaftaran' => 'Pembayaran hanya dapat dilakukan setelah pendaftaran diterima.',
            ]);
        }

        $pembayaranSaatIni = $pendaftaran->pembayaran;

        if ($pembayaranSaatIni && $pembayaranSaatIni->status_pembayaran !== Pembayaran::STATUS_DITOLAK) {
            throw ValidationException::withMessages([
                'bukti_pembayaran' => 'Bukti pembayaran sedang menunggu atau sudah selesai diverifikasi.',
            ]);
        }

        $validated = $request->validate([
            'bukti_pembayaran' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $filePath = $validated['bukti_pembayaran']->store('bukti-pembayaran', 'local');

        if (! is_string($filePath)) {
            throw new RuntimeException('Bukti pembayaran gagal disimpan.');
        }

        $pathLama = null;
        $kedaluwarsaSaatUnggah = false;

        try {
            DB::transaction(function () use ($pendaftaran, $filePath, $biaya, &$pathLama, &$kedaluwarsaSaatUnggah): void {
                $pendaftaranTerkunci = PendaftaranVaksinasi::query()
                    ->lockForUpdate()
                    ->findOrFail($pendaftaran->id);

                if ($pendaftaranTerkunci->status_pendaftaran !== PendaftaranVaksinasi::STATUS_DITERIMA) {
                    throw ValidationException::withMessages([
                        'pendaftaran' => 'Pembayaran hanya dapat dilakukan setelah pendaftaran diterima.',
                    ]);
                }

                $pembayaran = $pendaftaranTerkunci->pembayaran()
                    ->lockForUpdate()
                    ->first();

                if (
                    $pembayaran
                    && $pembayaran->status_pembayaran === Pembayaran::STATUS_DITOLAK
                    && $pembayaran->verified_at !== null
                    && $pembayaran->verified_at->lte(now()->subHour())
                ) {
                    $pendaftaranTerkunci->update([
                        'status_pendaftaran' => PendaftaranVaksinasi::STATUS_KEDALUWARSA,
                    ]);
                    $kedaluwarsaSaatUnggah = true;

                    return;
                }

                if ($pembayaran && $pembayaran->status_pembayaran !== Pembayaran::STATUS_DITOLAK) {
                    throw ValidationException::withMessages([
                        'bukti_pembayaran' => 'Bukti pembayaran sedang menunggu atau sudah selesai diverifikasi.',
                    ]);
                }

                $data = [
                    'jumlah_pembayaran' => $biaya,
                    'bukti_pembayaran' => $filePath,
                    'status_pembayaran' => Pembayaran::STATUS_PENDING,
                    'alasan_penolakan' => null,
                    'verified_by' => null,
                    'verified_at' => null,
                ];

                if ($pembayaran) {
                    $pathLama = $pembayaran->bukti_pembayaran;
                    $pembayaran->update($data);
                } else {
                    $pendaftaranTerkunci->pembayaran()->create($data);
                }
            });
        } catch (ValidationException $exception) {
            Storage::disk('local')->delete($filePath);

            throw $exception;
        } catch (UniqueConstraintViolationException) {
            Storage::disk('local')->delete($filePath);

            throw ValidationException::withMessages([
                'bukti_pembayaran' => 'Bukti pembayaran untuk pendaftaran ini sudah dikirim.',
            ]);
        }

        if ($kedaluwarsaSaatUnggah) {
            Storage::disk('local')->delete($filePath);

            throw ValidationException::withMessages([
                'pendaftaran' => 'Batas waktu unggah ulang telah lewat. Silakan mendaftar vaksin kembali.',
            ]);
        }

        if ($pathLama !== null && $pathLama !== $filePath) {
            Storage::disk('local')->delete($pathLama);
        }

        return redirect()
            ->route('peserta.pembayaran.index')
            ->with('success', 'Bukti pembayaran berhasil dikirim.');
    }
}
