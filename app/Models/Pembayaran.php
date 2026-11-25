<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_DITERIMA = 'diterima';

    public const STATUS_DITOLAK = 'ditolak';

    protected $table = 'pembayaran';

    protected $fillable = [
        'pendaftaran_id',
        'jumlah_pembayaran',
        'bukti_pembayaran',
        'status_pembayaran',
        'alasan_penolakan',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_pembayaran' => 'decimal:2',
            'verified_at' => 'datetime',
        ];
    }

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(
            PendaftaranVaksinasi::class,
            'pendaftaran_id'
        );
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'verified_by'
        );
    }
}
