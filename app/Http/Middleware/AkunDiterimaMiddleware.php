<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AkunDiterimaMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Memastikan pengguna sudah login
        if (!$request->user()) {
            return redirect()->route('login.form');
        }

        // Memeriksa status akun peserta
        if ($request->user()->status_akun !== 'diterima') {
            return redirect()->route('peserta.status');
        }

        return $next($request);
    }
}
