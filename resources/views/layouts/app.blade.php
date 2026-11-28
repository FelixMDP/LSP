<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Vaksinasi'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header">
        <a class="brand" href="{{ route('home') }}">
            <span class="brand-mark">V</span>
            <span>Vaksin<span class="brand-accent">Sehat</span></span>
        </a>

        <button
            class="site-nav-toggle"
            type="button"
            aria-label="Buka navigasi utama"
            aria-expanded="false"
            aria-controls="site-navigation"
            data-nav-toggle
        >
            <span class="site-nav-toggle__dots" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </button>

        <nav class="site-nav" id="site-navigation" aria-label="Navigasi utama">
            @auth
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    <a href="{{ route('admin.verifikasi-akun.index') }}">Akun</a>
                    <a href="{{ route('admin.pendaftaran.index') }}">Pendaftaran</a>
                    <a href="{{ route('admin.pembayaran.index') }}">Pembayaran</a>
                    <a href="{{ route('admin.pengumuman.index') }}">Pengumuman</a>
                @elseif (auth()->user()->status_akun === 'diterima')
                    <a href="{{ route('peserta.dashboard') }}">Dashboard</a>
                    <a href="{{ route('peserta.jadwal.index') }}">Jadwal</a>
                    <a href="{{ route('peserta.pembayaran.index') }}">Pembayaran</a>
                    <a href="{{ route('peserta.pengumuman.index') }}">Pengumuman</a>
                @else
                    <a href="{{ route('peserta.status') }}">Status akun</a>
                @endif
                <form class="nav-logout" action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="button button--quiet" type="submit">Keluar</button>
                </form>
            @else
                <a href="{{ route('jadwal.publik') }}">Jadwal vaksinasi</a>
                <a href="{{ route('login.form') }}">Masuk</a>
                <a class="button button--small" href="{{ route('register.form') }}">Buat akun</a>
            @endauth
        </nav>
    </header>

    <main class="page-shell">
        @if (!request()->routeIs('home', 'admin.dashboard', 'peserta.dashboard'))
            <a class="back-link" href="{{ route('home') }}" data-history-back>
                <span aria-hidden="true">←</span>
                <span>Kembali</span>
            </a>
        @endif

        @if (session('success'))
            <div class="alert alert--success" role="status">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert--error" role="alert">
                <strong>Periksa kembali data Anda.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="site-footer">
        <span>VaksinSehat</span>
        <span>Informasi dan layanan vaksinasi dalam satu tempat.</span>
    </footer>
</body>
</html>
