<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status_akun',
        'alasan_penolakan',
        'verified_by',
        'verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'verified_at' => 'datetime',
        ];
    }

    // Relasi dengan profil peserta
    public function profilPeserta()
    {
        return $this->hasOne(ProfilPeserta::class, 'user_id');
    }

    // Relasi dengan pendaftaran vaksinasi
    public function pendaftaranVaksinasi()
    {
        return $this->hasMany(PendaftaranVaksinasi::class, 'user_id');
    }

    // Relasi dengan pengumuman yang dibuat admin
    public function pengumuman()
    {
        return $this->hasMany(Pengumuman::class, 'admin_id');
    }

    // Admin yang memverifikasi akun
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Akun peserta yang diverifikasi oleh admin
    public function akunDiverifikasi()
    {
        return $this->hasMany(User::class, 'verified_by');
    }
}
