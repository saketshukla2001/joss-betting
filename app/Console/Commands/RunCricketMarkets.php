<?php

namespace App\Console\Commands;

use App\Jobs\ProcessCricketMarkets;
use App\Services\Cricket\Markets\CricketMarketCycle;
use Illuminate\Console\Command;

class RunCricketMarkets extends Command
{
    protected $signature = 'cricket:markets';

    protected $description = 'Generate and settle live cricket Yes/No markets from the cached scoreboard';

    public function handle(): int
    {
        if (config('queue.default') === 'sync') {
            $summary = app(CricketMarketCycle::class)->run();
            $this->line(json_encode($summary));

            return self::SUCCESS;
        }

        ProcessCricketMarkets::dispatch();
        $this->info('Cricket market pass queued.');

        return self::SUCCESS;
    }
}
