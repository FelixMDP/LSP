@extends('layouts.app')

@section('title', 'Detail Pendaftaran')

@section('content')
    <p><a href="{{ route('admin.pendaftaran.index') }}">← Kembali ke daftar pendaftaran</a></p>

    <div class="section-heading">
        <div>
            <span class="eyebrow">Pendaftaran #{{ $pendaftaran->id }}</span>
            <h1>Detail pengajuan jadwal</h1>
            <p>Periksa kecocokan data peserta dan jadwal sebelum memberi keputusan.</p>
        </div>
        <span class="badge">{{ $pendaftaran->status_pendaftaran }}</span>
    </div>

    <div class="grid">
        <section class="content-card">
            <span class="eyebrow">Data peserta</span>
            <h2>{{ $pendaftaran->user->name }}</h2>
            <p><strong>Email:</strong> {{ $pendaftaran->user->email }}</p>
            <p><strong>NIK:</strong> {{ $pendaftaran->user->profilPeserta?->nik ?? '-' }}</p>
            <p><strong>Telepon:</strong> {{ $pendaftaran->user->profilPeserta?->no_telepon ?? '-' }}</p>
            <p><strong>Alamat:</strong> {{ $pendaftaran->user->profilPeserta?->alamat ?? '-' }}</p>
        </section>

        <section class="content-card">
            <span class="eyebrow">Jadwal pilihan</span>
            <h2>{{ $pendaftaran->jadwal->tanggal->format('d-m-Y') }}</h2>
            <p><strong>Waktu:</strong> {{ substr($pendaftaran->jadwal->jam_mulai, 0, 5) }}-{{ substr($pendaftaran->jadwal->jam_selesai, 0, 5) }}</p>
            <p><strong>Status:</strong> {{ $pendaftaran->status_pendaftaran }}</p>
            @if ($pendaftaran->alasan_penolakan)
                <div class="alert alert--error">Alasan penolakan: {{ $pendaftaran->alasan_penolakan }}</div>
            @endif
        </section>
    </div>

    @if ($pendaftaran->status_pendaftaran === \App\Models\PendaftaranVaksinasi::STATUS_PENDING)
        <section class="content-card">
            <h2>Keputusan verifikasi</h2>
            <div class="actions">
                <form action="{{ route('admin.pendaftaran.terima', $pendaftaran) }}" method="POST">
                    @csrf
                    <button type="submit">Terima pendaftaran</button>
                </form>
            </div>
            <form action="{{ route('admin.pendaftaran.tolak', $pendaftaran) }}" method="POST">
                @csrf
                <div class="field">
                    <label for="alasan_penolakan">Alasan penolakan</label>
                    <textarea id="alasan_penolakan" name="alasan_penolakan" maxlength="1000" required>{{ old('alasan_penolakan') }}</textarea>
                    @error('alasan_penolakan')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
                <button class="button button--outline" type="submit">Tolak pendaftaran</button>
            </form>
        </section>
    @endif
@endsection
