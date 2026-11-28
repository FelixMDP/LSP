@extends('layouts.app')

@section('title', 'Dashboard Peserta')

@section('content')
    <section class="hero dashboard-hero dashboard-hero--peserta">
        <div class="hero__content">
            <div class="dashboard-hero__greeting">
                <span class="dashboard-hero__greeting-mark" aria-hidden="true">✦</span>
                <span data-dashboard-greeting>Senang bertemu Anda kembali</span>
                <time data-dashboard-date></time>
            </div>
            <h1>Halo, <span class="dashboard-hero__name">{{ Auth::user()->name }}</span>.</h1>
            <p>Semua informasi dan langkah vaksinasi Anda ada di satu tempat.</p>
            <span class="badge">Akun {{ Auth::user()->status_akun }}</span>
        </div>
    </section>

    <section class="dashboard-announcements" aria-labelledby="peserta-announcement-title">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Informasi terbaru</span>
                <h2 id="peserta-announcement-title">Pengumuman untuk Anda</h2>
                <p>Informasi penting dari tim vaksinasi, langsung di dashboard.</p>
            </div>
            <a class="text-link" href="{{ route('peserta.pengumuman.index') }}">Lihat semua pengumuman <span aria-hidden="true">→</span></a>
        </div>

        @if ($pengumuman->isEmpty())
            <div class="announcement-empty">
                <span class="announcement-empty__icon" aria-hidden="true">i</span>
                <div>
                    <strong>Belum ada pengumuman baru</strong>
                    <p>Pengumuman terbaru akan muncul di sini saat tersedia.</p>
                </div>
            </div>
        @else
            <div class="announcement-carousel" data-slide-carousel>
                <div class="announcement-carousel__track" data-slide-track id="announcement-track">
                    @foreach ($pengumuman as $item)
                        <article class="announcement-preview" data-announcement-slide>
                            <span class="announcement-date">{{ $item->created_at->format('d M Y') }}</span>
                            <h3>{{ $item->judul }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit($item->isi, 180) }}</p>
                            <span class="announcement-author">Dari {{ $item->admin?->name ?? 'Tim vaksinasi' }}</span>
                        </article>
                    @endforeach
                </div>
                @if ($pengumuman->count() > 1)
                    <div class="announcement-carousel__controls" aria-label="Navigasi pengumuman">
                        <button class="carousel-button" type="button" data-slide-prev aria-label="Pengumuman sebelumnya" aria-controls="announcement-track">
                            <span aria-hidden="true">←</span>
                        </button>
                        <button class="carousel-button" type="button" data-slide-next aria-label="Pengumuman berikutnya" aria-controls="announcement-track">
                            <span aria-hidden="true">→</span>
                        </button>
                    </div>
                @endif
            </div>
        @endif
    </section>

    @include('partials.health-news-carousel', ['beritaKesehatan' => $beritaKesehatan])

    <div class="section-heading">
        <div>
            <span class="eyebrow">Langkah berikutnya</span>
            <h2>Layanan peserta</h2>
            <p>Pilih layanan yang ingin Anda buka.</p>
        </div>
    </div>

    <section class="grid">
        <article class="card">
            <span class="card__icon" aria-hidden="true">01</span>
            <h2>Jadwal vaksinasi</h2>
            <p>Lihat jadwal yang tersedia dan pilih waktu yang sesuai.</p>
            <a class="button button--outline" href="{{ route('peserta.jadwal.index') }}">Lihat jadwal</a>
        </article>

        <article class="card">
            <span class="card__icon" aria-hidden="true">02</span>
            <h2>Pembayaran</h2>
            <p>Pantau status pembayaran dan unggah bukti setelah pendaftaran diterima.</p>
            <a class="button button--outline" href="{{ route('peserta.pembayaran.index') }}">Lihat pembayaran</a>
        </article>

        <article class="card">
            <span class="card__icon" aria-hidden="true">03</span>
            <h2>Pengumuman</h2>
            <p>Baca informasi terbaru yang disampaikan oleh admin.</p>
            <a class="button button--outline" href="{{ route('peserta.pengumuman.index') }}">Baca pengumuman</a>
        </article>
    </section>

    <section class="content-card">
        <span class="eyebrow">Profil Anda</span>
        <h2>Informasi akun</h2>
        <div class="form-grid">
            <p><strong>Email</strong><br>{{ Auth::user()->email }}</p>
            <p><strong>NIK</strong><br>{{ Auth::user()->profilPeserta?->nik ?? '-' }}</p>
            <p><strong>Nomor telepon</strong><br>{{ Auth::user()->profilPeserta?->no_telepon ?? '-' }}</p>
            <p class="field--full"><strong>Alamat</strong><br>{{ Auth::user()->profilPeserta?->alamat ?? '-' }}</p>
        </div>
    </section>
@endsection
