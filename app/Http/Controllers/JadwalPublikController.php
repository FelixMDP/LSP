<?php

namespace App\Http\Controllers;

use App\Models\JadwalVaksinasi;
use App\Models\PendaftaranVaksinasi;
use Illuminate\View\View;

class JadwalPublikController extends Controller
{
    public function index(): View
    {
        PendaftaranVaksinasi::expireRejectedPaymentRegistrations();

        $jadwal = JadwalVaksinasi::query()
            ->withCount('pendaftaranAktif')
            ->whereDate('tanggal', '>=', today())
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->paginate(15);

        return view('jadwal.publik', compact('jadwal'));
    }
}
