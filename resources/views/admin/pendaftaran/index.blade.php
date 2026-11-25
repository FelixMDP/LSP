@extends('layouts.app')

@section('title', 'Verifikasi Pendaftaran')

@section('content')
    <div class="section-heading">
        <div>
            <span class="eyebrow">Persetujuan admin</span>
            <h1>Verifikasi pendaftaran</h1>
            <p>Tinjau peserta dan jadwal pilihan sebelum memberi keputusan.</p>
        </div>
    </div>

    @if ($pendaftaran->isEmpty())
        <div class="empty-state">Tidak ada pendaftaran yang menunggu verifikasi.</div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Peserta</th>
                        <th>Identitas</th>
                        <th>Jadwal</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pendaftaran as $item)
                        <tr>
                            <td><strong>{{ $item->user->name }}</strong><br><span class="muted">{{ $item->user->email }}</span></td>
                            <td>{{ $item->user->profilPeserta?->nik ?? '-' }}</td>
                            <td>{{ $item->jadwal->tanggal->format('d-m-Y') }}<br>{{ substr($item->jadwal->jam_mulai, 0, 5) }}-{{ substr($item->jadwal->jam_selesai, 0, 5) }}</td>
                            <td><a class="button button--outline button--small" href="{{ route('admin.pendaftaran.show', $item) }}">Periksa detail</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $pendaftaran->links() }}</div>
    @endif
@endsection
