<section class="dashboard-announcements health-news" aria-labelledby="health-news-title">
    <div class="section-heading">
        <div>
            <span class="eyebrow">Edukasi kesehatan</span>
            <h2 id="health-news-title">Berita seputar imunisasi dan penyakit</h2>
            <p>Ringkasan berita terbaru dari sumber tepercaya, dengan tanggal dan tautan sumber.</p>
        </div>
    </div>

    @if (count($beritaKesehatan) > 0)
        <div class="announcement-carousel" data-slide-carousel>
            <div class="announcement-carousel__track" data-slide-track id="health-news-track">
                @foreach ($beritaKesehatan as $berita)
                    <article class="announcement-preview health-news-card" data-news-slide>
                        <div class="health-news-card__meta">
                            <span class="badge">{{ $berita['kategori'] }}</span>
                            <time datetime="{{ $berita['tanggal'] }}">{{ \Illuminate\Support\Carbon::parse($berita['tanggal'])->format('d-m-Y') }}</time>
                        </div>
                        <h3>{{ $berita['judul'] }}</h3>
                        <p>{{ $berita['ringkasan'] }}</p>
                        <p class="health-news-card__context">{{ $berita['konteks'] }}</p>
                        <a href="{{ $berita['url'] }}" target="_blank" rel="noopener noreferrer">
                            Baca sumber: {{ $berita['sumber'] }} <span aria-hidden="true">↗</span>
                        </a>
                        @if (! empty($berita['searchSuggestions']))
                            <div class="health-news-search-attribution" aria-label="Saran Penelusuran Google">
                                {!! $berita['searchSuggestions'] !!}
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
            @if (count($beritaKesehatan) > 1)
                <div class="announcement-carousel__controls" aria-label="Navigasi berita kesehatan">
                    <button class="carousel-button" type="button" data-slide-prev aria-label="Berita sebelumnya" aria-controls="health-news-track">
                        <span aria-hidden="true">←</span>
                    </button>
                    <button class="carousel-button" type="button" data-slide-next aria-label="Berita berikutnya" aria-controls="health-news-track">
                        <span aria-hidden="true">→</span>
                    </button>
                </div>
            @endif
        </div>

        <p class="health-news-disclaimer">
            Ringkasan ini bersifat edukatif, bukan diagnosis atau pengganti konsultasi medis. Status imunisasi bukan satu-satunya faktor dalam menilai penyebab penyakit pada seseorang.
        </p>
    @else
        <div class="announcement-empty">
            <span class="announcement-empty__icon" aria-hidden="true">i</span>
            <div>
                <strong>Belum ada berita terbaru</strong>
                <p>Berita kesehatan akan ditampilkan di sini saat tersedia.</p>
            </div>
        </div>
    @endif
</section>
