<?php

namespace App\Console\Commands;

use App\Models\JadwalVaksinasi;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class GenerateDailyVaksinasiSchedule extends Command
{
    protected $signature = 'vaksinasi:generate-daily-schedule';

    protected $description = 'Create one vaccination schedule for the current Jakarta date if none exists.';

    public function handle(): int
    {
        $date = now('Asia/Jakarta')->startOfDay();

        if (JadwalVaksinasi::query()->whereDate('tanggal', $date)->exists()) {
            $this->info("A schedule already exists for {$date->toDateString()}.");

            return self::SUCCESS;
        }

        $previousSchedule = JadwalVaksinasi::query()
            ->whereDate('tanggal', '<', $date)
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_mulai')
            ->first();

        $useMorningSlot = true;

        if ($previousSchedule !== null) {
            $previousDate = CarbonImmutable::parse(
                $previousSchedule->tanggal->toDateString(),
                'Asia/Jakarta'
            );
            $daysSincePreviousSchedule = $previousDate->diffInDays($date);
            $samePattern = $daysSincePreviousSchedule % 2 === 0;
            $previousWasMorningSlot = substr($previousSchedule->jam_mulai, 0, 5) === '08:00';
            $useMorningSlot = $samePattern
                ? $previousWasMorningSlot
                : ! $previousWasMorningSlot;
        }

        JadwalVaksinasi::query()->create([
            'tanggal' => $date->toDateString(),
            'jam_mulai' => $useMorningSlot ? '08:00:00' : '09:00:00',
            'jam_selesai' => $useMorningSlot ? '12:00:00' : '13:00:00',
            'kuota' => 50,
        ]);

        $this->info("A schedule was created for {$date->toDateString()}.");

        return self::SUCCESS;
    }
}
