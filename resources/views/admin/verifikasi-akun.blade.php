@extends('layouts.app')

@section('title', 'Verifikasi Akun Peserta')

@section('content')
    <div class="section-heading">
        <div>
            <span class="eyebrow">Administrasi peserta</span>
            <h1>Verifikasi akun</h1>
            <p>Periksa data peserta sebelum memberikan akses ke layanan.</p>
        </div>
    </div>

    @if ($peserta->isEmpty())
        <div class="empty-state">Tidak ada akun yang menunggu verifikasi.</div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Peserta</th>
                        <th>Identitas</th>
                        <th>Alamat</th>
                        <th>Keputusan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($peserta as $user)
                        <tr>
                            <td><strong>{{ $user->name }}</strong><br><span class="muted">{{ $user->email }}</span></td>
                            <td>NIK: {{ $user->profilPeserta?->nik ?? '-' }}<br>Telepon: {{ $user->profilPeserta?->no_telepon ?? '-' }}</td>
                            <td>{{ $user->profilPeserta?->alamat ?? '-' }}</td>
                            <td>
                                <div class="stack">
                                    <form action="{{ route('admin.verifikasi-akun.terima', $user) }}" method="POST">
                                        @csrf
                                        <button type="submit">Terima akun</button>
                                    </form>
                                    <form action="{{ route('admin.verifikasi-akun.tolak', $user) }}" method="POST">
                                        @csrf
                                        <label class="sr-only" for="alasan-{{ $user->id }}">Alasan penolakan</label>
                                        <input id="alasan-{{ $user->id }}" name="alasan_penolakan" placeholder="Alasan penolakan" required>
                                        <button class="button--outline" type="submit">Tolak akun</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
