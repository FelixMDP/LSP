<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\View\View;

class PengumumanPesertaController extends Controller
{
    public function __invoke(): View
    {
        $pengumuman = Pengumuman::query()
            ->with('admin:id,name')
            ->latest()
            ->paginate(15);

        return view('peserta.pengumuman.index', compact('pengumuman'));
    }
}
