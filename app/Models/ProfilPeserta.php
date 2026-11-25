<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfilPeserta extends Model
{
    protected $table = 'profil_peserta';

    protected $fillable = [
        'user_id',
        'nik',
        'no_telepon',
        'alamat',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
