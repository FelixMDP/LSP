@extends('layouts.app')

@section('title', 'Status Akun')

@section('content')
    <section class="status-layout">
        <div class="content-card">
            <span class="eyebrow">Status verifikasi</span>
            <h1>Halo, {{ Auth::user()->name }}.</h1>
            <p class="lead">Email akun: {{ Auth::user()->email }}</p>

            @if (Auth::user()->status_akun === 'pending')
                <span class="badge">Menunggu verifikasi</span>
                <p style="margin-top: 18px;">
                    Akun Anda sedang diperiksa oleh admin. Anda dapat masuk ke fitur peserta setelah akun diterima.
                </p>
            @elseif (Auth::user()->status_akun === 'ditolak')
                <span class="badge" style="background: #fff0ee; color: #a42b24;">Akun ditolak</span>
                <p style="margin-top: 18px;">Silakan tinjau alasan penolakan berikut.</p>
                <div class="alert alert--error">{{ Auth::user()->alasan_penolakan ?? 'Tidak ada keterangan dari admin.' }}</div>
            @endif
        </div>
    </section>
@endsection
