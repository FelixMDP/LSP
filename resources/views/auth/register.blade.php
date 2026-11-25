@extends('layouts.app')

@section('title', 'Registrasi Peserta')

@section('content')
    <section class="form-card">
        <span class="eyebrow">Mulai perjalanan sehat</span>
        <h1>Buat akun peserta</h1>
        <p class="form-intro">Lengkapi data dengan benar. Akun Anda akan diperiksa oleh admin.</p>

        <form action="{{ route('register') }}" method="POST">
            @csrf

            <div class="form-grid">
                <div class="field field--full">
                    <label for="name">Nama lengkap</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" autocomplete="name" required>
                    @error('name')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field field--full">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                    @error('email')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field">
                    <label for="nik">NIK</label>
                    <input id="nik" type="text" name="nik" value="{{ old('nik') }}" inputmode="numeric" minlength="16" maxlength="16" required>
                    @error('nik')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field">
                    <label for="no_telepon">Nomor telepon</label>
                    <input id="no_telepon" type="tel" name="no_telepon" value="{{ old('no_telepon') }}" autocomplete="tel" required>
                    @error('no_telepon')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">Kata sandi</label>
                    <input id="password" type="password" name="password" autocomplete="new-password" required>
                    @error('password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field">
                    <label for="password_confirmation">Konfirmasi kata sandi</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                </div>

                <div class="field field--full">
                    <label for="alamat">Alamat</label>
                    <textarea id="alamat" name="alamat" autocomplete="street-address" required>{{ old('alamat') }}</textarea>
                    @error('alamat')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <button class="button" type="submit">Kirim pendaftaran akun</button>
        </form>

        <p class="muted" style="margin-top: 20px;">
            Sudah punya akun? <a href="{{ route('login.peserta') }}">Masuk sebagai peserta</a>
        </p>
    </section>
@endsection
