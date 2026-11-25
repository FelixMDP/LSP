@extends('layouts.app')

@section('title', 'Kelola Pengumuman')

@section('content')
    <div class="section-heading">
        <div>
            <span class="eyebrow">Informasi resmi</span>
            <h1>Kelola pengumuman</h1>
            <p>Buat, perbarui, atau hapus informasi yang ditampilkan kepada peserta.</p>
        </div>
        <a class="button" href="{{ route('admin.pengumuman.create') }}">Buat pengumuman</a>
    </div>

    @forelse ($pengumuman as $item)
        <article class="content-card">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">{{ $item->created_at->format('d-m-Y H:i') }}</span>
                    <h2>{{ $item->judul }}</h2>
                </div>
                <span class="badge">Oleh {{ $item->admin->name }}</span>
            </div>
            <p>{{ $item->isi }}</p>
            <div class="actions">
                <a class="button button--outline" href="{{ route('admin.pengumuman.edit', $item) }}">Edit</a>
                <form action="{{ route('admin.pengumuman.destroy', $item) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button class="button--danger" type="submit">Hapus</button>
                </form>
            </div>
        </article>
    @empty
        <div class="empty-state">Belum ada pengumuman. Buat informasi pertama untuk peserta.</div>
    @endforelse

    <div class="pagination-wrap">{{ $pengumuman->links() }}</div>
@endsection
