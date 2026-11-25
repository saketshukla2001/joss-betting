<?php

namespace App\Services\Cricket\Markets;

use App\Models\CricketMarketRule;
use App\Models\Market;
use App\Services\BettingService;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Opens, suspends, settles and voids cricket markets from verified score states.
 * Settlement always goes through BettingService. The model is not consulted here.
 */
final class MarketLifecycle
{
    public function __construct(
        private BettingService $betting,
        private MarketPublisher $publisher,
        private QuestionValidator $validator,
    ) {}

    /**
     * @param  array<string, mixed>|null  $state
     */
    public function forceVoid(CricketMarketRule $rule, string $message): void
    {
        $rule->loadMissing('market.selections');

        if ($rule->market && ! in_array($rule->phase, ['settled', 'void'], true)) {
            $this->void($rule, $rule->market, $message);
        }
    }

    /**
     * @param  array<string, mixed>|null  $state
     */
    public function evaluate(CricketMarketRule $rule, ?array $state): void
    {
        $rule->loadMissing('market.selections');
        $market = $rule->market;

        if (! $market || in_array($rule->phase, ['settled', 'void'], true)) {
            return;
        }

        if ($state === null) {
            $this->suspend($rule, $market, 'This match is not on the current scoreboard.');

            return;
        }

        $finished = ($state['status_group'] ?? '') === 'completed' || ($state['kind'] ?? '') === 'result';

        if ($finished && (! empty($state['stale']) || ! is_array($state['clock'] ?? null))) {
            $this->void($rule, $market, 'The match ended without an overs reading that can settle this market.');

            return;
        }

        if ((int) ($state['innings'] ?? 0) !== (int) $rule->innings) {
            $this->void($rule, $market, 'The innings changed before this market reached its boundary.');

            return;
        }

        $snapshot = $rule->snapshot ?? [];
        $snapshot['last'] = [
            'runs' => $state['runs'],
            'wickets' => $state['wickets'],
            'overs' => $state['overs'],
            'status' => $state['status_raw'],
        ];
        $rule->snapshot = $snapshot;
        $rule->save();

        if (! $finished && (! empty($state['stale']) || $this->validator->interrupted($state) || ! is_array($state['clock'] ?? null))) {
            $this->suspend($rule, $market, 'Live data is stale, interrupted, or missing overs.');

            return;
        }

        if ($rule->template === QuestionTemplate::RUNS_BY_OVER) {
            $this->cumulative($rule, $market, $state);
        } elseif (in_array($rule->template, [QuestionTemplate::SIX_IN_OVER, QuestionTemplate::NEXT_BALLS], true)) {
            $this->ballMarket($rule, $market, $state);
        } else {
            $this->overWindow($rule, $market, $state);
        }

        if ($finished) {
            $rule->refresh();

            if (in_array($rule->phase, ['open', 'awaiting'], true)) {
                $this->void($rule, $market, 'The match ended before this market reached a verifiable over boundary.');
            }
        }
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function cumulative(CricketMarketRule $rule, Market $market, array $state): void
    {
        $clock = $state['clock'];
        $over = (int) $rule->target_over;
        $threshold = (int) $rule->threshold;
        $during = $clock['completed'] === $over - 1 && $clock['balls'] > 0;
        $boundary = $clock['completed'] === $over && $clock['balls'] === 0;

        if ($during && $state['runs'] >= $threshold) {
            $this->settle($rule, $market, true, 'The score reached the target during the over.');

            return;
        }

        if ($boundary) {
            $this->rememberBoundary($rule, $state);
            $this->settle($rule, $market, $state['runs'] >= $threshold, 'Observed the end of the target over.');

            return;
        }

        if ($clock['completed'] > $over || ($clock['completed'] === $over && $clock['balls'] > 0)) {
            $this->void($rule, $market, 'The score feed skipped the end of the target over, so the result is not known.');

            return;
        }

        $this->reopen($rule, $market);
    }

    /**
     * Six and next-ball markets settle only from a gap-free provider ball list.
     *
     * @param  array<string, mixed>  $state
     */
    private function ballMarket(CricketMarketRule $rule, Market $market, array $state): void
    {
        $balls = is_array($state['balls'] ?? null) ? array_values($state['balls']) : null;

        if (! is_array($balls)) {
            $this->suspend($rule, $market, 'The ball list is missing, so this market cannot be settled.');

            return;
        }

        if ($balls === []) {
            $this->reopen($rule, $market);

            return;
        }

        $sequences = array_map(fn (array $ball) => (int) ($ball['seq'] ?? 0), $balls);
        sort($sequences);
        $expected = range((int) min($sequences), (int) max($sequences));

        if ($sequences === [] || $sequences !== $expected) {
            $this->void($rule, $market, 'The ball list has a gap, so the result is not known.');

            return;
        }

        if ($rule->template === QuestionTemplate::NEXT_BALLS) {
            $this->nextBalls($rule, $market, $balls);

            return;
        }

        $this->sixInOver($rule, $market, $state, $balls);
    }

    /**
     * @param  array<string, mixed>  $state
     * @param  array<int, array<string, mixed>>  $balls
     */
    private function sixInOver(CricketMarketRule $rule, Market $market, array $state, array $balls): void
    {
        $clock = $state['clock'];
        $over = (int) $rule->target_over;
        $legal = array_values(array_filter($balls, fn (array $ball) => ! empty($ball['legal'])));
        $slice = array_slice($legal, ($over - 1) * 6, 6);
        $boundary = $clock['completed'] === $over && $clock['balls'] === 0;
        $past = $clock['completed'] > $over || ($clock['completed'] === $over && $clock['balls'] > 0);

        if ($clock['completed'] < $over - 1 || ($clock['completed'] === $over - 1 && $clock['balls'] === 0)) {
            $this->reopen($rule, $market);

            return;
        }

        if ($clock['completed'] === $over - 1 && $clock['balls'] > 0) {
            $rule->update(['phase' => 'awaiting', 'closed_at' => $rule->closed_at ?? now()]);
            $this->suspend($rule, $market, 'The target over has started. Betting is closed while the over is bowled.');

            return;
        }

        if (($boundary || $past) && count($slice) === 6) {
            $six = false;

            foreach ($slice as $ball) {
                if (($ball['kind'] ?? '') === '6') {
                    $six = true;
                }
            }

            $this->settle($rule, $market, $six, 'Observed the six legal balls of the target over.');

            return;
        }

        $this->void($rule, $market, 'The ball list does not cover the target over, so a six cannot be verified.');
    }

    /**
     * @param  array<int, array<string, mixed>>  $balls
     */
    private function nextBalls(CricketMarketRule $rule, Market $market, array $balls): void
    {
        $legal = array_values(array_filter($balls, fn (array $ball) => ! empty($ball['legal'])));
        $cursor = (int) (($rule->snapshot['ball_cursor'] ?? null) ?? count($legal));
        $slice = array_slice($legal, $cursor, 6);

        if (count($slice) < 6) {
            $this->reopen($rule, $market);

            return;
        }

        $runs = 0;

        foreach ($slice as $ball) {
            $runs += (int) ($ball['runs'] ?? 0);
        }

        $this->settle($rule, $market, $runs >= (int) $rule->threshold, 'Observed the next 6 legal balls.');
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function overWindow(CricketMarketRule $rule, Market $market, array $state): void
    {
        $clock = $state['clock'];
        $over = (int) $rule->target_over;
        $before = $clock['completed'] === $over - 1 && $clock['balls'] === 0;
        $inside = $clock['completed'] === $over - 1 && $clock['balls'] > 0;
        $boundary = $clock['completed'] === $over && $clock['balls'] === 0;
        $past = $clock['completed'] > $over || ($clock['completed'] === $over && $clock['balls'] > 0);
        $snapshot = $rule->snapshot ?? [];

        // The baseline is the score before the target over. Ball 1 is already part of that over.
        if ($rule->phase === 'open' && $before && empty($snapshot['start_captured'])) {
            $snapshot['start_runs'] = $state['runs'];
            $snapshot['start_wickets'] = $state['wickets'];
            $snapshot['start_captured'] = true;
            $rule->snapshot = $snapshot;
            $rule->save();
            $this->reopen($rule, $market);

            return;
        }

        if ($rule->phase === 'open' && $inside && ! empty($snapshot['start_captured'])) {
            $rule->phase = 'awaiting';
            $rule->closed_at = now();
            $rule->save();
            $this->suspend($rule, $market, 'The target over has started. Betting is closed while the over is bowled.');

            return;
        }

        if ($rule->phase === 'open' && ($inside || $boundary || $past) && empty($snapshot['start_captured'])) {
            $this->void($rule, $market, 'The feed did not show the score before the target over, so the over cannot be measured.');

            return;
        }

        if ($rule->phase === 'awaiting' && $boundary && ! empty($snapshot['start_captured'])) {
            $this->rememberBoundary($rule, $state);
            $startRuns = (int) $snapshot['start_runs'];
            $startWickets = (int) $snapshot['start_wickets'];

            if ($rule->template === QuestionTemplate::WICKET_IN_OVER) {
                $this->settle($rule, $market, $state['wickets'] > $startWickets, 'Observed wickets across the target over.');

                return;
            }

            $this->settle($rule, $market, ($state['runs'] - $startRuns) >= (int) $rule->threshold, 'Observed runs scored in the target over.');

            return;
        }

        if ($rule->phase === 'awaiting' && $past) {
            $this->void($rule, $market, 'The feed skipped the end of the target over, so the over result is not known.');

            return;
        }

        if ($rule->phase === 'open') {
            $this->reopen($rule, $market);
        }
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function rememberBoundary(CricketMarketRule $rule, array $state): void
    {
        $snapshot = $rule->snapshot ?? [];
        $snapshot['boundary_runs'] = $state['runs'];
        $snapshot['boundary_wickets'] = $state['wickets'];
        $snapshot['boundary_captured'] = true;
        $rule->snapshot = $snapshot;
        $rule->save();
    }

    private function settle(CricketMarketRule $rule, Market $market, bool $yes, string $message): void
    {
        $market->load('selections');
        $winner = $market->selections->firstWhere('result_key', $yes ? 'yes' : 'no');

        if (! $winner) {
            $this->void($rule, $market, 'The Yes/No selections are missing.');

            return;
        }

        $market->refresh();

        if (in_array($market->status, ['settled', 'void'], true)) {
            $rule->update([
                'phase' => $market->status === 'void' ? 'void' : 'settled',
                'settled_at' => $rule->settled_at ?? now(),
                'closed_at' => $rule->closed_at ?? now(),
            ]);

            return;
        }

        try {
            $this->betting->settleMarket($market, $winner->id);
        } catch (Throwable $e) {
            $market->refresh();

            if (in_array($market->status, ['settled', 'void'], true)) {
                $rule->update([
                    'phase' => $market->status === 'void' ? 'void' : 'settled',
                    'settled_at' => now(),
                    'closed_at' => $rule->closed_at ?? now(),
                ]);

                return;
            }

            Log::warning('Cricket market settlement failed', ['market_id' => $market->id, 'error' => $e->getMessage()]);
            $this->publisher->event($rule, 'error', 'Settlement failed: '.$e->getMessage());

            return;
        }

        $rule->update(['phase' => 'settled', 'settled_at' => now(), 'closed_at' => $rule->closed_at ?? now()]);
        $this->publisher->event($rule, 'settled', $message, ['winner' => $yes ? 'yes' : 'no']);
    }

    private function void(CricketMarketRule $rule, Market $market, string $message): void
    {
        if (in_array($market->status, ['settled', 'void'], true)) {
            $rule->update(['phase' => $market->status === 'void' ? 'void' : 'settled', 'settled_at' => now()]);

            return;
        }

        try {
            $this->betting->settleMarket($market, null, true);
        } catch (Throwable $e) {
            Log::warning('Cricket market void failed', ['market_id' => $market->id, 'error' => $e->getMessage()]);
            $this->publisher->event($rule, 'error', 'Void failed: '.$e->getMessage());

            return;
        }

        $rule->update(['phase' => 'void', 'settled_at' => now(), 'closed_at' => $rule->closed_at ?? now()]);
        $this->publisher->event($rule, 'voided', $message);
    }

    private function suspend(CricketMarketRule $rule, Market $market, string $message): void
    {
        if ($market->status === 'open') {
            $market->update(['status' => 'suspended']);
            $this->publisher->event($rule, 'suspended', $message);
        }
    }

    private function reopen(CricketMarketRule $rule, Market $market): void
    {
        if ($rule->phase === 'open' && $market->status === 'suspended') {
            $market->update(['status' => 'open']);
            $this->publisher->event($rule, 'reopened', 'Live data is available again and the market is still inside its window.');
        }
    }
}
