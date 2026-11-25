<?php

namespace App\Http\Controllers;

use App\Models\CricketMarketRule;
use App\Models\MatchGame;
use App\Services\Cricket\CricketBoard;
use App\Services\Cricket\Demo\DemoMatchSimulator;
use App\Services\Cricket\Markets\MatchStateFactory;
use Illuminate\Http\JsonResponse;

/**
 * Score and market status for the pages that already poll this app.
 * Built from the database and the cached board. It does not call Cricbuzz.
 */
class CricketMarketFeedController extends Controller
{
    public function __invoke(MatchStateFactory $states): JsonResponse
    {
        $cached = app(CricketBoard::class)->remembered();
        $scores = [];
        $groups = [
            'live' => $cached['live'] ?? [],
            'upcoming' => $cached['upcoming'] ?? [],
            'results' => $cached['results'] ?? [],
        ];

        foreach (app(DemoMatchSimulator::class)->boardRows() as $row) {
            $groups[$row['kind'] ?? 'live'][] = $row;
        }

        foreach ($groups as $rows) {
            foreach ($rows as $row) {
                if (! is_array($row) || ! isset($row['id'])) {
                    continue;
                }

                $state = $states->fromBoardRow($row);
                $scores[(string) $row['id']] = $state ?? [
                    'label' => $row['label'] ?? null,
                    'status_raw' => $row['status_raw'] ?? ($row['status'] ?? null),
                    'runs' => null,
                    'wickets' => null,
                    'overs' => null,
                    'innings' => null,
                    'batting_team' => null,
                    'stale' => (bool) ($row['stale'] ?? true),
                ];
            }
        }

        $rules = CricketMarketRule::with(['market.selections', 'market.match'])
            ->whereHas('market')
            ->where(function ($query) {
                $query->whereIn('phase', ['open', 'awaiting'])
                    ->orWhere('updated_at', '>=', now()->subHours(6));
            })
            ->orderByDesc('id')
            ->limit(80)
            ->get();

        $matches = [];

        foreach ($rules as $rule) {
            $match = MatchGame::withoutGlobalScope('real_money')->find($rule->market->match_id);

            if (! $match) {
                continue;
            }

            $key = (string) $match->id;
            $score = $scores[$rule->provider_match_id] ?? ($rule->snapshot['last'] ?? []);

            if (! isset($matches[$key])) {
                $matches[$key] = [
                    'match_id' => $match->id,
                    'provider_match_id' => $rule->provider_match_id,
                    'label' => $match->label,
                    'batting_team' => $score['batting_team'] ?? $rule->batting_team,
                    'runs' => $score['runs'] ?? null,
                    'wickets' => $score['wickets'] ?? null,
                    'overs' => $score['overs'] ?? null,
                    'innings' => $score['innings'] ?? $rule->innings,
                    'status' => $match->provider === 'demo'
                        ? 'LIVE DEMO'
                        : ($score['status_raw'] ?? $score['status'] ?? $match->status_label),
                    'demo' => $match->provider === 'demo',
                    'stale' => $match->provider === 'demo' ? false : (bool) ($score['stale'] ?? ($cached['missing'] ?? false)),
                    'markets' => [],
                ];
            }

            $yes = $rule->market->selections->firstWhere('result_key', 'yes');
            $no = $rule->market->selections->firstWhere('result_key', 'no');

            $matches[$key]['markets'][] = [
                'id' => $rule->market->id,
                'name' => $rule->market->name,
                'status' => $rule->market->status,
                'phase' => $rule->phase,
                'yes_odds' => $yes ? number_format((float) $yes->odds, 2, '.', '') : null,
                'no_odds' => $no ? number_format((float) $no->odds, 2, '.', '') : null,
            ];
        }

        return response()->json([
            'server_time' => now()->toIso8601String(),
            'matches' => array_values($matches),
        ])->header('Cache-Control', 'no-store');
    }
}
