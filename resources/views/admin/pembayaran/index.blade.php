@extends('layouts.app')

@section('title', 'Verifikasi Pembayaran')

@section('content')
    <div class="section-heading">
        <div>
            <span class="eyebrow">Persetujuan admin</span>
            <h1>Verifikasi pembayaran</h1>
            <p>Tinjau bukti transfer dan status pembayaran peserta.</p>
        </div>
    </div>

    @if ($pembayaran->isEmpty())
        <div class="empty-state">Belum ada bukti pembayaran yang masuk.</div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Peserta dan jadwal</th>
                        <th>Nominal</th>
                        <th>Status</th>
                        <th>Bukti dan keputusan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pembayaran as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->pendaftaran->user->name }}</strong><br>
                                <span class="muted">{{ $item->pendaftaran->user->email }}</span><br>
                                {{ $item->pendaftaran->jadwal->tanggal->format('d-m-Y') }}
                            </td>
                            <td>Rp {{ number_format((float) $item->jumlah_pembayaran, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge">{{ $item->status_pembayaran }}</span>
                                @if ($item->pendaftaran->status_pendaftaran === \App\Models\PendaftaranVaksinasi::STATUS_KEDALUWARSA)
                                    <p class="form-error">Pendaftaran kedaluwarsa; kuota telah dilepaskan.</p>
                                @endif
                                @if ($item->alasan_penolakan)
                                    <p class="form-error">{{ $item->alasan_penolakan }}</p>
                                @endif
                            </td>
                            <td>
                                <div class="stack">
                                    <a href="{{ route('admin.pembayaran.bukti', $item) }}">Unduh bukti</a>
                                    @if ($item->status_pembayaran === \App\Models\Pembayaran::STATUS_PENDING)
                                        <form action="{{ route('admin.pembayaran.terima', $item) }}" method="POST">
                                            @csrf
                                            <button type="submit">Terima</button>
                                        </form>
                                        <form action="{{ route('admin.pembayaran.tolak', $item) }}" method="POST">
                                            @csrf
                                            <div class="field">
                                                <label for="alasan-{{ $item->id }}">Alasan penolakan</label>
                                                <input id="alasan-{{ $item->id }}" name="alasan_penolakan" maxlength="1000" required>
                                            </div>
                                            <button class="button button--outline" type="submit">Tolak</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $pembayaran->links() }}</div>
    @endif
@endsection
