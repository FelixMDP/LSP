@extends('layouts.app')

@section('title', 'Pembayaran Vaksinasi')

@section('content')
    <div class="section-heading">
        <div>
            <span class="eyebrow">Pembayaran peserta</span>
            <h1>Pembayaran vaksinasi</h1>
            <p>Unggah bukti setelah pendaftaran jadwal Anda diterima.</p>
        </div>
    </div>

    @if (!is_int($biaya) || $biaya < 1)
        <div class="alert alert--error">
            Nominal pembayaran belum dikonfigurasi. Admin perlu mengatur <code>VAKSINASI_BIAYA</code>.
        </div>
    @else
        <section class="content-card">
            <span class="eyebrow">Nominal pembayaran</span>
            <h2>Rp {{ number_format($biaya, 0, ',', '.') }}</h2>
            <p class="muted">Nominal ini ditetapkan sistem. Unggah bukti berformat JPG, PNG, atau PDF dengan ukuran maksimal 5 MB.</p>
        </section>
    @endif

    @forelse ($pendaftaran as $item)
        <section class="content-card">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">Jadwal vaksinasi</span>
                    <h2>{{ $item->jadwal->tanggal->format('d-m-Y') }}</h2>
                </div>
                <span class="badge">Pendaftaran {{ $item->status_pendaftaran }}</span>
            </div>

            @if ($item->pembayaran)
                <p>Status pembayaran: <span class="badge">{{ $item->pembayaran->status_pembayaran }}</span></p>
                @if ($item->pembayaran->alasan_penolakan)
                    <div class="alert alert--error">Alasan penolakan: {{ $item->pembayaran->alasan_penolakan }}</div>
                @endif
                @if (
                    $item->status_pendaftaran === \App\Models\PendaftaranVaksinasi::STATUS_DITERIMA
                    && $item->pembayaran->status_pembayaran === \App\Models\Pembayaran::STATUS_DITOLAK
                    && $item->pembayaran->verified_at
                )
                    <p class="muted">
                        Unggah ulang sebelum
                        <time
                            datetime="{{ $item->pembayaran->verified_at->copy()->addHour()->toIso8601String() }}"
                            data-local-time
                        >{{ $item->pembayaran->verified_at->copy()->addHour()->format('d-m-Y H:i') }}</time>.
                        Jika melewati batas waktu, Anda perlu mendaftar vaksin kembali.
                    </p>
                @endif
            @else
                <p class="muted">Bukti pembayaran belum dikirim.</p>
            @endif

            @if ($item->status_pendaftaran === \App\Models\PendaftaranVaksinasi::STATUS_KEDALUWARSA)
                <div class="alert alert--error">
                    Batas waktu unggah ulang telah lewat. Kuota sudah dilepaskan; silakan mendaftar vaksin kembali.
                </div>
                <a class="button" href="{{ route('peserta.jadwal.index') }}">Daftar ulang dan pilih jadwal</a>
            @elseif ($item->status_pendaftaran === \App\Models\PendaftaranVaksinasi::STATUS_DITERIMA)
                <section class="transfer-card" aria-label="Informasi rekening simulasi">
                    <div class="transfer-card__heading">
                        <span class="eyebrow">Pembayaran simulasi</span>
                        <span class="badge">Rekening demo</span>
                    </div>
                    <p class="muted">Gunakan informasi ini sebagai panduan simulasi unggah bukti pembayaran.</p>
                    <dl class="transfer-details">
                        <div>
                            <dt>Bank</dt>
                            <dd>{{ $rekeningDemo['bank'] }}</dd>
                        </div>
                        <div>
                            <dt>Nomor rekening</dt>
                            <dd class="transfer-details__number">{{ $rekeningDemo['nomor'] }}</dd>
                        </div>
                        <div>
                            <dt>Atas nama</dt>
                            <dd>{{ $rekeningDemo['atas_nama'] }}</dd>
                        </div>
                        <div>
                            <dt>Nominal</dt>
                            <dd>Rp {{ is_int($biaya) ? number_format($biaya, 0, ',', '.') : '-' }}</dd>
                        </div>
                    </dl>
                    <p class="transfer-card__notice">Ini rekening fiktif untuk demonstrasi. Jangan melakukan transfer uang sungguhan.</p>
                </section>

                @if (!$item->pembayaran || $item->pembayaran->status_pembayaran === \App\Models\Pembayaran::STATUS_DITOLAK)
                    @if (is_int($biaya) && $biaya > 0)
                        <form class="upload-form" action="{{ route('peserta.pembayaran.store', $item) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="field">
                                <label for="bukti-{{ $item->id }}">Bukti pembayaran</label>
                                <input id="bukti-{{ $item->id }}" type="file" name="bukti_pembayaran" accept=".jpg,.jpeg,.png,.pdf" required>
                            </div>
                            <button type="submit">Kirim bukti pembayaran</button>
                        </form>
                    @endif
                @endif
            @else
                <p class="muted">Form pembayaran akan tersedia setelah pendaftaran diterima admin.</p>
            @endif
        </section>
    @empty
        <div class="empty-state">Belum ada pendaftaran vaksinasi. Pilih jadwal terlebih dahulu.</div>
    @endforelse
@endsection
