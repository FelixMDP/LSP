@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    <section class="hero">
        <div class="hero__content">
            <span class="eyebrow">Layanan vaksinasi terpadu</span>
            <h1>Langkah sehat dimulai dari jadwal yang tepat.</h1>
            <p>
                Daftar vaksinasi, pantau status pendaftaran dan pembayaran,
                serta dapatkan informasi terbaru dari satu portal.
            </p>
            <div class="hero__actions">
                <a class="button" href="{{ route('login.peserta') }}">Masuk sebagai peserta</a>
                <a class="button" href="{{ route('jadwal.publik') }}">Lihat jadwal vaksinasi</a>
            </div>
        </div>
        <div class="hero__visual">
            <img src="{{ asset('images/vaksinasi-anak.jpg') }}" alt="Petugas kesehatan memberikan vaksin kepada anak dengan didampingi orang tua">
        </div>
    </section>

    <div class="section-heading">
        <div>
            <span class="eyebrow">Pilih akses Anda</span>
            <h2>Selamat datang di VaksinSehat</h2>
            <p>Gunakan portal sesuai peran untuk melanjutkan.</p>
        </div>
    </div>

    <section class="grid" aria-label="Pilihan akses">
        <article class="card">
            <span class="card__icon" aria-hidden="true">A</span>
            <h2>Portal admin</h2>
            <p>Kelola verifikasi akun, pendaftaran, pembayaran, dan pengumuman.</p>
            <a class="button" href="{{ route('login.admin') }}">Masuk sebagai admin</a>
        </article>

        <article class="card">
            <span class="card__icon" aria-hidden="true">P</span>
            <h2>Portal peserta</h2>
            <p>Pilih jadwal, kirim bukti pembayaran, dan pantau status Anda.</p>
            <a class="button" href="{{ route('login.peserta') }}">Masuk sebagai peserta</a>
        </article>

        <article class="card">
            <span class="card__icon" aria-hidden="true">+</span>
            <h2>Belum punya akun?</h2>
            <p>Buat akun peserta terlebih dahulu. Akun baru akan menunggu verifikasi admin.</p>
            <a class="button" href="{{ route('register.form') }}">Daftar sebagai peserta</a>
        </article>
    </section>
@endsection
