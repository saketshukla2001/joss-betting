<?php

namespace App\Services\Cricket\Markets;

use App\Models\CricketMarketEvent;
use App\Models\CricketMarketRule;
use App\Models\CricketProviderLink;
use App\Models\CricketQuestionCandidate;
use App\Models\MatchGame;
use App\Models\Sport;
use Illuminate\Support\Facades\DB;

/**
 * Writes one validated question onto the existing match, market and selection tables.
 */
final class MarketPublisher
{
    public function __construct(private QuestionValidator $validator) {}

    /**
     * @param  array<string, mixed>  $state
     * @param  array<string, mixed>  $candidate
     */
    public function publish(array $state, array $candidate, string $source): CricketMarketRule
    {
        $fingerprint = $candidate['fingerprint'] ?? $this->validator->fingerprint($state, $candidate);
        $parameters = $candidate['parameters'];

        return DB::transaction(function () use ($state, $candidate, $source, $fingerprint, $parameters) {
            $existing = CricketMarketRule::where('fingerprint', $fingerprint)->lockForUpdate()->first();

            if ($existing) {
                return $existing;
            }

            $provider = (string) ($state['provider'] ?? 'cricbuzz');
            $match = $this->resolveMatch($state, $provider);

            $duplicateQuery = CricketMarketRule::where('template', $candidate['template'])
                ->where('innings', $parameters['innings'])
                ->where('target_over', $parameters['target_over'])
                ->where('batting_team', $parameters['batting_team'])
                ->whereHas('market', fn ($query) => $query->where('match_id', $match->id));

            if ($parameters['threshold'] === null) {
                $duplicateQuery->whereNull('threshold');
            } else {
                $duplicateQuery->where('threshold', $parameters['threshold']);
            }

            $duplicate = $duplicateQuery->first();

            if ($duplicate) {
                return $duplicate;
            }

            $market = $match->markets()->create([
                'name' => $candidate['question'],
                'auto_settle' => false,
                'status' => 'open',
                'sort_order' => (int) $match->markets()->max('sort_order') + 1,
            ]);

            $yesOdds = round((float) setting('cricket_ai_yes_odds', 1.85), 2);
            $noOdds = round((float) setting('cricket_ai_no_odds', 1.85), 2);

            $market->selections()->createMany([
                ['name' => 'Yes', 'result_key' => 'yes', 'odds' => max(1.01, $yesOdds), 'status' => 'active', 'sort_order' => 1],
                ['name' => 'No', 'result_key' => 'no', 'odds' => max(1.01, $noOdds), 'status' => 'active', 'sort_order' => 2],
            ]);

            $rule = CricketMarketRule::create([
                'market_id' => $market->id,
                'provider' => $provider,
                'provider_match_id' => $state['provider_match_id'],
                'fingerprint' => $fingerprint,
                'template' => $candidate['template'],
                'batting_team' => $parameters['batting_team'],
                'innings' => $parameters['innings'],
                'target_over' => $parameters['target_over'],
                'target_ball' => $parameters['target_ball'],
                'threshold' => $parameters['threshold'],
                'trigger_event' => $parameters['trigger'],
                'close_rule' => $parameters['close_rule'],
                'resolve_rule' => $parameters['resolve_rule'],
                'source' => $source,
                'phase' => 'open',
                'parameters' => $parameters,
                'snapshot' => $this->snapshot($state),
                'generation_meta' => [
                    'source' => $source,
                    'overs' => $state['overs'],
                    'runs' => $state['runs'],
                    'wickets' => $state['wickets'],
                    'recent_balls' => $state['recent_balls'],
                ],
                'opened_at' => now(),
            ]);

            $this->event($rule, 'opened', 'Market opened from '.$source.' suggestion.', [
                'question' => $candidate['question'],
                'template' => $candidate['template'],
            ]);

            CricketQuestionCandidate::where('fingerprint', $fingerprint)->update([
                'status' => 'published',
                'market_id' => $market->id,
            ]);

            return $rule;
        });
    }

    /**
     * @param  array<string, mixed>  $state
     * @param  array<string, mixed>  $candidate
     */
    public function hold(array $state, array $candidate, string $source): CricketQuestionCandidate
    {
        return CricketQuestionCandidate::create([
            'provider' => (string) ($state['provider'] ?? 'cricbuzz'),
            'provider_match_id' => $state['provider_match_id'],
            'fingerprint' => $candidate['fingerprint'],
            'template' => $candidate['template'],
            'question' => $candidate['question'],
            'parameters' => $candidate['parameters'],
            'source' => $source,
            'status' => 'pending',
        ]);
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    private function snapshot(array $state): array
    {
        return [
            'start_runs' => null,
            'start_wickets' => null,
            'start_captured' => false,
            'boundary_runs' => null,
            'boundary_wickets' => null,
            'boundary_captured' => false,
            'ball_cursor' => $this->legalBallCount($state),
            'last' => [
                'runs' => $state['runs'],
                'wickets' => $state['wickets'],
                'overs' => $state['overs'],
                'status' => $state['status_raw'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function resolveMatch(array $state, string $provider): MatchGame
    {
        $query = MatchGame::query();

        if ($provider === 'demo') {
            $query->withoutGlobalScope('real_money');
        }

        $match = (clone $query)->where('provider', $provider)
            ->where('provider_match_id', $state['provider_match_id'])
            ->lockForUpdate()
            ->first();

        if (! $match && $provider !== 'demo') {
            $link = CricketProviderLink::where('provider', $provider)
                ->where('provider_match_id', $state['provider_match_id'])
                ->first();
            $match = $link?->match;
        }

        if (! $match && $provider !== 'demo') {
            $match = MatchGame::query()
                ->whereIn('provider', ['cricbuzz', 'sportmonks'])
                ->where('provider', '!=', $provider)
                ->where('start_time', '>=', now()->subDays(6))
                ->lockForUpdate()
                ->get()
                ->first(fn (MatchGame $row) => $this->sameTeams($row->home_team, $row->away_team, $state['home_team'], $state['away_team']));
        }

        if (! $match) {
            $sport = Sport::firstOrCreate(
                ['slug' => 'cricket'],
                ['name' => 'Cricket', 'icon' => '🏏', 'active' => true, 'sort_order' => 2]
            );

            $match = MatchGame::create([
                'sport_id' => $sport->id,
                'league' => mb_substr((string) $state['league'], 0, 120),
                'home_team' => mb_substr((string) $state['home_team'], 0, 120),
                'away_team' => mb_substr((string) $state['away_team'], 0, 120),
                'start_time' => now(),
                'status' => 'live',
                'featured' => false,
                'provider' => $provider,
                'provider_match_id' => $state['provider_match_id'],
            ]);
        } elseif ($match->status === 'upcoming' || ($provider === 'demo' && ($state['play_state'] ?? '') === 'in_play' && $match->status !== 'live')) {
            $match->update(['status' => 'live']);
        }

        CricketProviderLink::firstOrCreate(
            ['provider' => $provider, 'provider_match_id' => $state['provider_match_id']],
            ['match_id' => $match->id]
        );

        return $match;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function legalBallCount(array $state): int
    {
        $balls = is_array($state['balls'] ?? null) ? $state['balls'] : [];

        return count(array_filter($balls, fn ($ball) => is_array($ball) && ! empty($ball['legal'])));
    }

    private function sameTeams(string $leftHome, string $leftAway, string $rightHome, string $rightAway): bool
    {
        $left = [strtolower(trim($leftHome)), strtolower(trim($leftAway))];
        $right = [strtolower(trim($rightHome)), strtolower(trim($rightAway))];
        sort($left);
        sort($right);

        return $left === $right && $left[0] !== '';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function event(?CricketMarketRule $rule, string $event, string $message, array $payload = [], ?string $providerMatchId = null): void
    {
        CricketMarketEvent::create([
            'cricket_market_rule_id' => $rule?->id,
            'provider_match_id' => $providerMatchId ?? $rule?->provider_match_id,
            'event' => $event,
            'message' => $message,
            'payload' => $payload === [] ? null : $payload,
            'created_at' => now(),
        ]);
    }
}
