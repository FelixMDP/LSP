@extends('layouts.app')

@section('title', 'Pilih Jadwal Vaksinasi')

@section('content')
    <div class="section-heading">
        <div>
            <span class="eyebrow">Pendaftaran peserta</span>
            <h1>Pilih jadwal vaksinasi</h1>
            <p>Pilih satu jadwal yang masih memiliki kuota. Pendaftaran Anda akan ditinjau admin.</p>
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
                        <th>Sisa kuota</th>
                        <th>Pendaftaran</th>
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
                            <td>{{ $sisaKuota }} dari {{ $item->kuota }}</td>
                            <td>
                                @if ($item->sudah_mendaftar)
                                    <span class="badge">Sudah mendaftar</span>
                                @elseif ($sisaKuota === 0)
                                    <span class="badge badge--muted">Kuota penuh</span>
                                @else
                                    <form action="{{ route('peserta.jadwal.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="jadwal_id" value="{{ $item->id }}">
                                        <button class="button--small" type="submit">Pilih jadwal</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
