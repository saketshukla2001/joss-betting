<?php

namespace App\Console\Commands;

use App\Models\Market;
use App\Models\Selection;
use App\Models\Setting;
use Illuminate\Console\Command;

class TickOdds extends Command
{
    protected $signature = 'odds:tick {--loop : Keep running} {--sleep=12 : Seconds between ticks} {--force : Ignore the odds_auto_move setting}';

    protected $description = 'Nudge open market odds to simulate a live sportsbook feed';

    public function handle(): int
    {
        do {
            if (! $this->option('force') && Setting::get('odds_auto_move', '1') !== '1') {
                $this->warn('Automatic odds movement is disabled in settings.');

                if (! $this->option('loop')) {
                    return self::SUCCESS;
                }

                sleep((int) $this->option('sleep'));

                continue;
            }

            $changed = $this->tick();

            $this->info(now()->format('H:i:s').' · updated '.$changed.' selection(s)');

            if ($this->option('loop')) {
                sleep(max(3, (int) $this->option('sleep')));
            }
        } while ($this->option('loop'));

        return self::SUCCESS;
    }

    private function tick(): int
    {
        $marketIds = Market::query()
            ->where('status', 'open')
            ->whereDoesntHave('cricketRule')
            ->whereHas('match', fn ($query) => $query->whereIn('status', ['upcoming', 'live']))
            ->pluck('id');

        if ($marketIds->isEmpty()) {
            return 0;
        }

        $selections = Selection::query()
            ->whereIn('market_id', $marketIds)
            ->where('status', 'active')
            ->get();

        $changed = 0;

        foreach ($selections as $selection) {
            if (random_int(1, 100) > 38) {
                continue;
            }

            $drift = random_int(-10, 10) / 100;

            if ($drift === 0.0) {
                continue;
            }

            $next = round((float) $selection->odds + $drift, 2);
            $next = max(1.05, min(12.00, $next));

            if (abs($next - (float) $selection->odds) < 0.01) {
                continue;
            }

            $selection->update(['odds' => $next]);
            $changed++;
        }

        return $changed;
    }
}
