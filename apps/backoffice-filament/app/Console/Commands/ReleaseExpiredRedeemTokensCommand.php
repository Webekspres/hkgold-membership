<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Redeem\ReleaseExpiredRedeemTokenService;
use Illuminate\Console\Command;

class ReleaseExpiredRedeemTokensCommand extends Command
{
    protected $signature = 'redeem:release-expired-tokens';

    protected $description = 'Release held points and stock for expired unused redeem tokens';

    public function handle(ReleaseExpiredRedeemTokenService $service): int
    {
        $count = $service->releaseExpired();

        if ($count === 0) {
            $this->info('Tidak ada token redeem kedaluwarsa yang perlu di-release.');

            return self::SUCCESS;
        }

        $this->info("Released {$count} expired redeem tokens.");

        return self::SUCCESS;
    }
}
