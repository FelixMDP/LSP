<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalVaksinasi extends Model
{
    protected $table = 'jadwal_vaksinasi';

    protected $fillable = [
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'kuota',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'kuota' => 'integer',
        ];
    }

    public function pendaftaran(): HasMany
    {
        return $this->hasMany(
            PendaftaranVaksinasi::class,
            'jadwal_id'
        );
    }

    public function pendaftaranAktif(): HasMany
    {
        return $this->pendaftaran()->mengisiKuota();
    }
}
