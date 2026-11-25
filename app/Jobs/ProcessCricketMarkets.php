<?php

namespace App\Jobs;

use App\Services\Cricket\Markets\CricketMarketCycle;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessCricketMarkets implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function handle(CricketMarketCycle $cycle): void
    {
        $cycle->run();
    }
}
