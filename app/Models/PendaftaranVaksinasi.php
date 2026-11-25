<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class PendaftaranVaksinasi extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_DITERIMA = 'diterima';

    public const STATUS_DITOLAK = 'ditolak';

    public const STATUS_KEDALUWARSA = 'kedaluwarsa';

    protected $table = 'pendaftaran_vaksinasi';

    protected $fillable = [
        'user_id',
        'jadwal_id',
        'status_pendaftaran',
        'alasan_penolakan',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    public static function expireRejectedPaymentRegistrations(): int
    {
        $cutoff = now()->subHour();
        $expiredCount = 0;

        DB::transaction(function () use ($cutoff, &$expiredCount): void {
            $registrationIds = static::query()
                ->where('status_pendaftaran', self::STATUS_DITERIMA)
                ->whereHas('pembayaran', function (Builder $query) use ($cutoff): void {
                    $query
                        ->where('status_pembayaran', Pembayaran::STATUS_DITOLAK)
                        ->whereNotNull('verified_at')
                        ->where('verified_at', '<=', $cutoff);
                })
                ->lockForUpdate()
                ->pluck('id');

            foreach ($registrationIds as $registrationId) {
                $registration = static::query()
                    ->lockForUpdate()
                    ->findOrFail($registrationId);

                $payment = $registration->pembayaran()
                    ->lockForUpdate()
                    ->first();

                if (
                    $payment === null
                    || $payment->status_pembayaran !== Pembayaran::STATUS_DITOLAK
                    || $payment->verified_at === null
                    || $payment->verified_at->gt($cutoff)
                ) {
                    continue;
                }

                $registration->update([
                    'status_pendaftaran' => self::STATUS_KEDALUWARSA,
                ]);
                $expiredCount++;
            }
        });

        return $expiredCount;
    }

    public function scopeMengisiKuota(Builder $query): void
    {
        $query->whereIn('status_pendaftaran', [
            self::STATUS_PENDING,
            self::STATUS_DITERIMA,
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(
            JadwalVaksinasi::class,
            'jadwal_id'
        );
    }

    public function pembayaran(): HasOne
    {
        return $this->hasOne(
            Pembayaran::class,
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
