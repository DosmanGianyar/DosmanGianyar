<?php

namespace App\Console\Commands;

use App\Models\VotingSession;
use Illuminate\Console\Command;

class CloseExpiredVotingSessions extends Command
{
    protected $signature   = 'voting:close-expired';
    protected $description = 'Close voting sessions that have passed their end time';

    public function handle(): int
    {
        $count = VotingSession::where('status', 'active')
            ->where('end_time', '<=', now())
            ->update(['status' => 'closed']);

        $this->info("Closed {$count} expired voting session(s).");

        return self::SUCCESS;
    }
}
