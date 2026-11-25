@extends('layouts.app')

@section('title', 'Pengumuman')

@section('content')
    <div class="section-heading">
        <div>
            <span class="eyebrow">Informasi resmi</span>
            <h1>Pengumuman vaksinasi</h1>
            <p>Informasi terbaru dari admin untuk peserta.</p>
        </div>
    </div>

    @forelse ($pengumuman as $item)
        <article class="content-card">
            <span class="eyebrow">{{ $item->created_at->format('d-m-Y H:i') }} · {{ $item->admin->name }}</span>
            <h2>{{ $item->judul }}</h2>
            <p>{{ $item->isi }}</p>
        </article>
    @empty
        <div class="empty-state">Belum ada pengumuman saat ini.</div>
    @endforelse

    <div class="pagination-wrap">{{ $pengumuman->links() }}</div>
@endsection
