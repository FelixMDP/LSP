@extends('layouts.app')

@section('title', 'Jadwal Vaksinasi')

@section('content')
    <div class="section-heading">
        <div>
            <span class="eyebrow">Jadwal terbuka untuk dilihat</span>
            <h1>Jadwal vaksinasi</h1>
            <p>Lihat waktu pelaksanaan dan sisa kuota. Pendaftaran jadwal hanya tersedia setelah masuk sebagai peserta.</p>
        </div>
    </div>

    @if ($jadwal->isEmpty())
        <div class="empty-state">Belum ada jadwal vaksinasi mendatang.</div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Kuota tersedia</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($jadwal as $item)
                        @php
                            $sisaKuota = max($item->kuota - $item->pendaftaran_aktif_count, 0);
                        @endphp
                        <tr>
                            <td><strong>{{ $item->tanggal->format('d-m-Y') }}</strong></td>
                            <td>{{ substr($item->jam_mulai, 0, 5) }}-{{ substr($item->jam_selesai, 0, 5) }}</td>
                            <td>{{ $sisaKuota }} dari {{ $item->kuota }} tempat</td>
                            <td>
                                @if ($sisaKuota > 0)
                                    <span class="badge">Tersedia</span>
                                @else
                                    <span class="badge badge--muted">Penuh</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $jadwal->links() }}</div>
    @endif

    <section class="content-card public-schedule-cta">
        <div>
            @guest
                <h2>Ingin mendaftar?</h2>
                <p class="muted">Masuk atau buat akun peserta untuk memilih jadwal yang masih tersedia.</p>
            @elseif (auth()->user()->role === 'peserta' && auth()->user()->status_akun === 'diterima')
                <h2>Siap mendaftar?</h2>
                <p class="muted">Pilih jadwal yang masih tersedia dari halaman pendaftaran Anda.</p>
            @elseif (auth()->user()->role === 'peserta')
                <h2>Akun sedang diverifikasi</h2>
                <p class="muted">Anda dapat mendaftar setelah akun disetujui admin.</p>
            @else
                <h2>Jadwal vaksinasi</h2>
                <p class="muted">Anda sedang melihat informasi jadwal yang tersedia untuk publik.</p>
            @endguest
        </div>
        <div class="actions">
            @guest
                <a class="button" href="{{ route('login.form') }}">Masuk</a>
                <a class="button button--outline" href="{{ route('register.form') }}">Buat akun</a>
            @elseif (auth()->user()->role === 'peserta' && auth()->user()->status_akun === 'diterima')
                <a class="button" href="{{ route('peserta.jadwal.index') }}">Pilih jadwal</a>
            @elseif (auth()->user()->role === 'peserta')
                <a class="button" href="{{ route('peserta.status') }}">Lihat status akun</a>
            @endguest
        </div>
    </section>
@endsection
