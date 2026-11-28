@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <section class="hero dashboard-hero dashboard-hero--admin">
        <div class="hero__content">
            <div class="dashboard-hero__greeting">
                <span class="dashboard-hero__greeting-mark" aria-hidden="true">✦</span>
                <span data-dashboard-greeting>Selamat datang kembali</span>
                <time data-dashboard-date></time>
            </div>
            <h1>Halo, <span class="dashboard-hero__name">{{ auth()->user()->name }}</span>.</h1>
            <p>Ringkasan kerja dan informasi penting untuk membantu Anda mengelola layanan vaksinasi.</p>
        </div>
    </section>

    <div class="section-heading">
        <div>
            <h2>Menu pengelolaan</h2>
            <p>Pilih alur yang ingin Anda tinjau.</p>
        </div>
    </div>

    <section class="grid">
        <article class="card">
            <span class="card__icon" aria-hidden="true">01</span>
            <h2>Verifikasi akun</h2>
            <p>Tinjau profil peserta baru sebelum mereka memilih jadwal.</p>
            <a class="button button--outline" href="{{ route('admin.verifikasi-akun.index') }}">Buka verifikasi akun</a>
        </article>

        <article class="card">
            <span class="card__icon" aria-hidden="true">02</span>
            <h2>Verifikasi pendaftaran</h2>
            <p>Periksa detail peserta dan jadwal yang dipilih.</p>
            <a class="button button--outline" href="{{ route('admin.pendaftaran.index') }}">Buka pendaftaran</a>
        </article>

        <article class="card">
            <span class="card__icon" aria-hidden="true">03</span>
            <h2>Verifikasi pembayaran</h2>
            <p>Tinjau bukti dan catat keputusan pembayaran.</p>
            <a class="button button--outline" href="{{ route('admin.pembayaran.index') }}">Buka pembayaran</a>
        </article>

        <article class="card">
            <span class="card__icon" aria-hidden="true">04</span>
            <h2>Pengumuman</h2>
            <p>Bagikan informasi terbaru kepada peserta.</p>
            <a class="button button--outline" href="{{ route('admin.pengumuman.index') }}">Kelola pengumuman</a>
        </article>
    </section>

    <section class="dashboard-announcements dashboard-announcements--admin" aria-labelledby="admin-announcement-title">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Kabar terbaru</span>
                <h2 id="admin-announcement-title">Pengumuman yang sudah dibagikan</h2>
                <p>Pastikan informasi untuk peserta selalu terbaru.</p>
            </div>
            <a class="button button--outline button--small" href="{{ route('admin.pengumuman.index') }}">Kelola pengumuman</a>
        </div>

        @if ($pengumuman->isEmpty())
            <div class="announcement-empty">
                <span class="announcement-empty__icon" aria-hidden="true">+</span>
                <div>
                    <strong>Belum ada pengumuman</strong>
                    <p>Buat pengumuman pertama agar peserta mendapat informasi resmi.</p>
                    <a class="text-link" href="{{ route('admin.pengumuman.create') }}">Buat pengumuman <span aria-hidden="true">→</span></a>
                </div>
            </div>
        @else
            <div class="announcement-grid">
                @foreach ($pengumuman as $item)
                    <article class="announcement-preview">
                        <span class="announcement-date">{{ $item->created_at->format('d M Y') }}</span>
                        <h3>{{ $item->judul }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit($item->isi, 130) }}</p>
                        <a class="text-link" href="{{ route('admin.pengumuman.edit', $item) }}">Perbarui informasi <span aria-hidden="true">→</span></a>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
