<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Registrasi peserta
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'nik' => 'required|string|size:16|unique:profil_peserta,nik',
            'no_telepon' => 'required|string|max:20',
            'alamat' => 'required|string',
        ]);

        DB::transaction(function () use ($validated) {

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'peserta',
                'status_akun' => 'pending',
            ]);

            $user->profilPeserta()->create([
                'nik' => $validated['nik'],
                'no_telepon' => $validated['no_telepon'],
                'alamat' => $validated['alamat'],
            ]);
        });

        return redirect()
            ->route('register.form')
            ->with(
                'success',
                'Registrasi berhasil. Akun Anda menunggu verifikasi admin.'
            );
    }

    // Login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'login_as' => ['nullable', Rule::in(['admin', 'peserta'])],
        ]);

        $loginAs = $credentials['login_as'] ?? null;
        unset($credentials['login_as']);

        if ($loginAs !== null) {
            $credentials['role'] = $loginAs;
        }

        // Memeriksa email dan password
        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password tidak sesuai.'],
            ]);
        }

        // Memperbarui session setelah login
        $request->session()->regenerate();

        $user = Auth::user();

        // Jika pengguna adalah admin
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // Jika pengguna adalah peserta
        if ($user->role === 'peserta') {

            // Jika akun belum diterima atau ditolak
            if ($user->status_akun !== 'diterima') {
                return redirect()->route('peserta.status');
            }

            // Jika akun sudah diterima
            return redirect()->route('peserta.dashboard');
        }

        // Jika role tidak dikenali
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login.form')
            ->withErrors([
                'email' => 'Role akun tidak dikenali.',
            ]);
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with('success', 'Anda berhasil logout.');
    }
}
