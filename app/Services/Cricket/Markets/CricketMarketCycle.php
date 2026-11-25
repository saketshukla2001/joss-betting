<?php

namespace App\Services\Cricket\Markets;

use App\Models\CricketMarketRule;
use App\Models\CricketMatchControl;
use App\Services\Cricket\CricketBoard;
use App\Services\Cricket\Demo\DemoMatchSimulator;
use App\Services\Cricket\Providers\SportmonksCricketProvider;
use App\Support\PlayState;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * One pass over the cached cricket board: suggest questions, then advance open markets.
 * Reads CricketBoard::get(false), so a warm cache does not place a new provider call.
 */
final class CricketMarketCycle
{
    public function __construct(
        private CricketBoard $board,
        private MatchStateFactory $states,
        private CricketQuestionEngine $engine,
        private MarketPublisher $publisher,
        private MarketLifecycle $lifecycle,
    ) {}

    /**
     * @return array{generated: int, held: int, settled: int, voided: int, skipped: string|null}
     */
    public function run(): array
    {
        $summary = ['generated' => 0, 'held' => 0, 'settled' => 0, 'voided' => 0, 'skipped' => null];

        if (! setting_enabled('cricket_ai_markets_enabled', false)) {
            $summary['skipped'] = 'Automatic cricket markets are turned off. Demo matches still run.';
        }

        if (! Cache::add('cricket:markets:lock', 1, 55)) {
            $summary['skipped'] = 'Another market pass is already running.';

            return $summary;
        }

        try {
            app(DemoMatchSimulator::class)->tick();
            $enabled = setting_enabled('cricket_ai_markets_enabled', false);
            $payload = $enabled ? $this->board->get(false) : $this->board->remembered();
            $rows = is_array($payload['live'] ?? null) ? $payload['live'] : [];

            if ($enabled) {
                foreach (app(SportmonksCricketProvider::class)->liveRows() as $row) {
                    $rows[] = $row;
                }
            }

            foreach (app(DemoMatchSimulator::class)->boardRows() as $row) {
                if (($row['kind'] ?? '') === 'live') {
                    $rows[] = $row;
                }
            }

            $states = [];

            $this->index($rows, $states, true);
            $this->index(is_array($payload['results'] ?? null) ? $payload['results'] : [], $states, false);

            if ($this->due()) {
                foreach ($states as $state) {
                    if (! PlayState::allowsBetting((string) ($state['play_state'] ?? '')) || ! empty($state['stale']) || ! is_array($state['clock'] ?? null)) {
                        continue;
                    }

                    if (! $enabled && ($state['provider'] ?? '') !== 'demo') {
                        continue;
                    }

                    $created = $this->generate($state);
                    $summary['generated'] += $created['generated'];
                    $summary['held'] += $created['held'];
                }

                Cache::put('cricket:markets:last-generation', now()->timestamp, 3600);
            }

            $rules = CricketMarketRule::with('market')
                ->whereIn('phase', ['open', 'awaiting'])
                ->get();

            foreach ($rules as $rule) {
                $before = $rule->phase;
                $this->lifecycle->evaluate($rule, $states[$rule->provider_match_id] ?? null);
                $rule->refresh();

                if ($before !== 'settled' && $rule->phase === 'settled') {
                    $summary['settled']++;
                }

                if ($before !== 'void' && $rule->phase === 'void') {
                    $summary['voided']++;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Cricket market cycle failed', ['error' => $e->getMessage()]);
            $summary['skipped'] = $e->getMessage();
        } finally {
            Cache::forget('cricket:markets:lock');
        }

        return $summary;
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array{generated: int, held: int}
     */
    private function generate(array $state): array
    {
        $counts = ['generated' => 0, 'held' => 0];
        $control = CricketMatchControl::where('provider_match_id', $state['provider_match_id'])->first();

        if ($control && ! $control->generation_enabled) {
            return $counts;
        }

        $proposal = $this->engine->propose($state);

        foreach ($proposal['rejected'] as $rejected) {
            if (str_contains($rejected['reason'], 'already')
                || str_contains($rejected['reason'], 'not in active play')
                || str_contains($rejected['reason'], 'stale')) {
                continue;
            }

            $this->publisher->event(null, 'rejected', $rejected['reason'], ['question' => $rejected['question']], $state['provider_match_id']);
        }

        $approval = setting_enabled('cricket_ai_approval_required', true);

        foreach ($proposal['questions'] as $question) {
            try {
                if ($approval) {
                    $this->publisher->hold($state, $question, $proposal['source']);
                    $this->publisher->event(null, 'generated', 'Question is waiting for admin approval.', ['question' => $question['question']], $state['provider_match_id']);
                    $counts['held']++;

                    continue;
                }

                $rule = $this->publisher->publish($state, $question, $proposal['source']);
                $counts['generated'] += $rule->wasRecentlyCreated ? 1 : 0;
            } catch (\Throwable $e) {
                Log::warning('Cricket question was not published', ['error' => $e->getMessage(), 'match' => $state['provider_match_id']]);
                $this->publisher->event(null, 'error', 'Question was not published: '.$e->getMessage(), ['question' => $question['question'] ?? null], $state['provider_match_id']);
            }
        }

        return $counts;
    }

    /**
     * @param  array<int, mixed>  $rows
     * @param  array<string, array<string, mixed>>  $states
     */
    private function index(array $rows, array &$states, bool $logMissing): void
    {
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $state = $this->states->fromBoardRow($row);

            if ($state !== null && $state['provider_match_id'] !== '') {
                $states[$state['provider_match_id']] = $state;

                continue;
            }

            $id = isset($row['id']) ? (string) $row['id'] : '';

            if ($id === '') {
                continue;
            }

            if (($row['kind'] ?? '') === 'result') {
                $states[$id] = [
                    'provider_match_id' => $id,
                    'kind' => 'result',
                    'status_group' => 'completed',
                    'status_raw' => (string) ($row['status_raw'] ?? $row['status'] ?? ''),
                    'stale' => (bool) ($row['stale'] ?? false),
                    'innings' => null,
                    'clock' => null,
                    'runs' => null,
                    'wickets' => null,
                    'overs' => null,
                ];

                continue;
            }

            if ($logMissing && Cache::add('cricket:markets:missing:'.$id, 1, 3600)) {
                $this->publisher->event(null, 'error', 'Live row has no usable score yet.', ['id' => $id], $id);
            }
        }
    }

    private function due(): bool
    {
        $interval = max(30, (int) setting('cricket_ai_generation_seconds', 60));
        $last = (int) Cache::get('cricket:markets:last-generation', 0);

        return $last === 0 || (now()->timestamp - $last) >= $interval;
    }
}
