@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
    <section class="form-card">
        <span class="eyebrow">Akses akun</span>
        <h1>Masuk ke akun</h1>
        <p class="form-intro">Gunakan email dan kata sandi yang terdaftar.</p>

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="field">
                <label for="login_as">Masuk sebagai</label>
                <select id="login_as" name="login_as" required>
                    <option value="">Pilih peran</option>
                    <option value="admin" @selected(old('login_as') === 'admin')>Admin</option>
                    <option value="peserta" @selected(old('login_as') === 'peserta')>Peserta</option>
                </select>
                @error('login_as')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
                @error('email')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="field">
                <label for="password">Kata sandi</label>
                <input id="password" type="password" name="password" autocomplete="current-password" required>
                @error('password')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <button class="button" type="submit">Masuk</button>
        </form>

        <p class="muted" style="margin-top: 20px;">
            Belum punya akun peserta?
            <a href="{{ route('register.form') }}">Daftar sekarang</a>
        </p>
    </section>
@endsection
