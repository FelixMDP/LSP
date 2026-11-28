@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    <section class="hero home-hero">
        <div class="hero__content">
            <span class="eyebrow">Informasi vaksinasi yang lebih mudah</span>
            <h1>Jaga kesehatan, mulai dengan langkah yang terencana.</h1>
            <p>
                Temukan jadwal vaksinasi, pahami tahapannya, dan ikuti informasi
                terbaru dari tim VaksinSehat.
            </p>
            <div class="hero__actions">
                <a class="button" href="{{ route('jadwal.publik') }}">Jelajahi jadwal</a>
                <a class="button button--outline" href="{{ route('login.form') }}">Masuk ke akun</a>
            </div>
            <div class="home-hero__assurance">
                <span aria-hidden="true">✓</span>
                Informasi jadwal dapat dilihat tanpa membuat akun
            </div>
        </div>
        <div class="hero__visual">
            <img src="{{ asset('images/vaksinasi-anak.jpg') }}" alt="Petugas kesehatan memberikan vaksin kepada anak dengan didampingi orang tua">
            <span class="hero__image-caption">Perlindungan kesehatan untuk keluarga</span>
        </div>
    </section>

    <section class="home-highlights" aria-label="Informasi layanan">
        <article>
            <span class="home-highlight__icon" aria-hidden="true">01</span>
            <div>
                <strong>Jadwal transparan</strong>
                <p>Lihat waktu pelaksanaan dan kuota yang tersedia.</p>
            </div>
        </article>
        <article>
            <span class="home-highlight__icon" aria-hidden="true">02</span>
            <div>
                <strong>Alur terarah</strong>
                <p>Pantau pendaftaran dan pembayaran dari akun Anda.</p>
            </div>
        </article>
        <article>
            <span class="home-highlight__icon" aria-hidden="true">03</span>
            <div>
                <strong>Informasi terbaru</strong>
                <p>Baca pengumuman resmi langsung dari beranda.</p>
            </div>
        </article>
    </section>

    <section class="home-section" aria-labelledby="health-guide-title">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Panduan singkat</span>
                <h2 id="health-guide-title">Persiapkan vaksinasi dengan nyaman</h2>
                <p>Ikuti arahan tenaga kesehatan untuk kondisi dan jenis vaksin Anda.</p>
            </div>
        </div>
        <div class="health-guide-grid">
            <article class="health-guide-card">
                <span class="health-guide-card__number">01</span>
                <h3>Sebelum jadwal</h3>
                <p>Pastikan data pendaftaran benar, periksa jadwal, dan siapkan identitas yang diminta petugas.</p>
            </article>
            <article class="health-guide-card">
                <span class="health-guide-card__number">02</span>
                <h3>Saat pemeriksaan</h3>
                <p>Sampaikan kondisi kesehatan dan riwayat alergi kepada tenaga kesehatan sebelum vaksinasi.</p>
            </article>
            <article class="health-guide-card">
                <span class="health-guide-card__number">03</span>
                <h3>Setelah vaksin</h3>
                <p>Ikuti arahan observasi dan petunjuk perawatan dari tenaga kesehatan. Cari bantuan medis bila merasa khawatir.</p>
            </article>
        </div>
        <p class="health-guide-note">Panduan umum ini bukan pengganti konsultasi atau arahan tenaga kesehatan.</p>
    </section>

    <section class="dashboard-announcements home-announcements" aria-labelledby="public-announcement-title">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Kabar VaksinSehat</span>
                <h2 id="public-announcement-title">Pengumuman terbaru</h2>
                <p>Informasi yang dibagikan admin untuk membantu Anda mempersiapkan vaksinasi.</p>
            </div>
            <a class="text-link" href="{{ route('jadwal.publik') }}">Lihat jadwal vaksinasi <span aria-hidden="true">→</span></a>
        </div>

        @if ($pengumuman->isEmpty())
            <div class="announcement-empty">
                <span class="announcement-empty__icon" aria-hidden="true">i</span>
                <div>
                    <strong>Belum ada pengumuman</strong>
                    <p>Informasi terbaru akan muncul di bagian ini.</p>
                </div>
            </div>
        @else
            <div class="announcement-grid">
                @foreach ($pengumuman as $item)
                    <article class="announcement-preview">
                        <span class="announcement-date">{{ $item->created_at->format('d M Y') }}</span>
                        <h3>{{ $item->judul }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit($item->isi, 150) }}</p>
                        <span class="announcement-author">Dari {{ $item->admin?->name ?? 'Tim vaksinasi' }}</span>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    @include('partials.health-news-carousel', ['beritaKesehatan' => $beritaKesehatan])

    <section class="home-promo" aria-labelledby="home-promo-title">
        <div>
            <span class="eyebrow">Langkah berikutnya</span>
            <h2 id="home-promo-title">Temukan jadwal yang sesuai untuk Anda</h2>
            <p>Lihat ketersediaan jadwal terlebih dahulu. Pendaftaran dapat dilanjutkan setelah masuk atau membuat akun peserta.</p>
        </div>
        <div class="home-promo__actions">
            <a class="button home-promo__button" href="{{ route('jadwal.publik') }}">Lihat jadwal</a>
            <a href="{{ route('register.form') }}">Buat akun peserta</a>
        </div>
    </section>
@endsection
