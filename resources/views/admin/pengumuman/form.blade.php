@extends('layouts.app')

@section('title', $pengumuman->exists ? 'Edit Pengumuman' : 'Buat Pengumuman')

@section('content')
    <section class="form-card">
        <span class="eyebrow">Informasi peserta</span>
        <h1>{{ $pengumuman->exists ? 'Perbarui pengumuman' : 'Buat pengumuman' }}</h1>
        <p class="form-intro">Isi judul dan informasi yang ingin disampaikan.</p>

        <form
            action="{{ $pengumuman->exists ? route('admin.pengumuman.update', $pengumuman) : route('admin.pengumuman.store') }}"
            method="POST"
        >
            @csrf
            @if ($pengumuman->exists)
                @method('PUT')
            @endif

            <div class="field">
                <label for="judul">Judul</label>
                <input id="judul" name="judul" maxlength="255" value="{{ old('judul', $pengumuman->judul) }}" required autofocus>
                @error('judul')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="field">
                <label for="isi">Isi pengumuman</label>
                <textarea id="isi" name="isi" maxlength="20000" required>{{ old('isi', $pengumuman->isi) }}</textarea>
                @error('isi')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="actions">
                <button type="submit">Simpan pengumuman</button>
                <a class="button button--outline" href="{{ route('admin.pengumuman.index') }}">Batal</a>
            </div>
        </form>
    </section>
@endsection
