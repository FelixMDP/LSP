<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPengumumanController extends Controller
{
    public function index(): View
    {
        $pengumuman = Pengumuman::query()
            ->with('admin')
            ->latest()
            ->paginate(15);

        return view('admin.pengumuman.index', compact('pengumuman'));
    }

    public function create(): View
    {
        return view('admin.pengumuman.form', [
            'pengumuman' => new Pengumuman,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string', 'max:20000'],
        ]);

        $request->user()->pengumuman()->create($validated);

        return redirect()
            ->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function edit(Pengumuman $pengumuman): View
    {
        return view('admin.pengumuman.form', compact('pengumuman'));
    }

    public function update(Request $request, Pengumuman $pengumuman): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string', 'max:20000'],
        ]);

        $pengumuman->update($validated);

        return redirect()
            ->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman): RedirectResponse
    {
        $pengumuman->delete();

        return redirect()
            ->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }
}
