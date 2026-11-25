<?php

namespace App\Console\Commands;

use App\Models\PendaftaranVaksinasi;
use Illuminate\Console\Command;

class ExpireRejectedPaymentRegistrations extends Command
{
    protected $signature = 'vaksinasi:expire-rejected-payment-registrations';

    protected $description = 'Expire registrations whose rejected payments were not re-uploaded within one hour.';

    public function handle(): int
    {
        $expiredCount = PendaftaranVaksinasi::expireRejectedPaymentRegistrations();

        $this->info("{$expiredCount} registration(s) expired.");

        return self::SUCCESS;
    }
}
