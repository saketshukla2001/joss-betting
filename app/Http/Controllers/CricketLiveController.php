<?php

namespace App\Http\Controllers;

use App\Services\Cricket\CricbuzzCricketService;
use App\Services\Cricket\CricketBoard;
use App\Services\Cricket\Demo\DemoMatchSimulator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Public, read-only cricket section.
 *
 * Reads Cricbuzz through the cached service and never touches betting,
 * wallets, odds or settlement code.
 */
class CricketLiveController extends Controller
{
    public function index(CricketBoard $board)
    {
        return view('cricket.index', [
            'configured' => $board->isConfigured(),
            'feedEnabled' => setting_enabled('cricket_feed_enabled', true),
            'matchCount' => count($board->matchIds()),
        ]);
    }

    /**
     * Shared JSON board consumed by the public pages (auto-refresh friendly).
     */
    public function board(Request $request, CricketBoard $board)
    {
        $refresh = false;
        $limited = false;

        if ($request->boolean('refresh')) {
            // Only one forced rebuild at a time, so a refresh button cannot burn the API quota.
            if (Cache::add('cricbuzz:force-refresh', 1, 20)) {
                $refresh = true;
            } else {
                $limited = true;
            }
        }

        try {
            $payload = $board->get($refresh);
        } catch (\Throwable $e) {
            Log::warning('Cricket board build failed', ['error' => $e->getMessage()]);

            $payload = [
                'generated_at' => now()->toIso8601String(),
                'live' => [],
                'upcoming' => [],
                'results' => [],
                'errors' => [['id' => null, 'message' => 'Live cricket is temporarily unavailable.']],
                'cached' => false,
                'stale' => false,
            ];
        }

        return response()
            ->json($this->withDemoCards($payload) + ['refresh_limited' => $limited])
            ->header('Cache-Control', 'no-store');
    }

    /**
     * Adds simulated matches beside the provider board for this response only.
     * The cached Cricbuzz payload is left unchanged.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function withDemoCards(array $payload): array
    {
        $live = is_array($payload['live'] ?? null) ? $payload['live'] : [];
        $results = is_array($payload['results'] ?? null) ? $payload['results'] : [];
        $demoLive = [];
        $demoResults = [];

        foreach (app(DemoMatchSimulator::class)->listingRows() as $row) {
            if (($row['kind'] ?? '') === 'result') {
                $demoResults[] = $row;
            } else {
                $demoLive[] = $row;
            }
        }

        $payload['live'] = array_merge($demoLive, $live);
        $payload['results'] = array_merge($demoResults, $results);

        return $payload;
    }

    public function show(Request $request, int $match, CricketBoard $board, CricbuzzCricketService $cricbuzz)
    {
        $ids = $board->knownIds();

        abort_unless(in_array($match, $ids, true), 404);

        $label = (string) ($board->matchIds()[$match] ?? 'Match #'.$match);
        $lastBoard = Cache::get('cricbuzz:board:last');

        if (is_array($lastBoard)) {
            foreach (['live', 'upcoming', 'results'] as $group) {
                foreach (($lastBoard[$group] ?? []) as $row) {
                    if ((int) ($row['id'] ?? 0) === $match && ! empty($row['label'])) {
                        $label = (string) $row['label'];
                        break 2;
                    }
                }
            }
        }

        $refresh = false;

        if ($request->boolean('refresh')) {
            $refresh = Cache::add('cricbuzz:detail-refresh:'.$match, 1, 15);
        }

        $live = $cricbuzz->miniscore($match, $refresh);
        $card = $cricbuzz->scorecard($match, $refresh);

        return view('cricket.show', [
            'matchId' => $match,
            'label' => $label,
            'live' => $live,
            'card' => $card,
            'presentedLive' => ($live['success'] && is_array($live['data'])) ? $cricbuzz->presentLive($live['data']) : null,
            'presentedCard' => ($card['success'] && is_array($card['data'])) ? $cricbuzz->presentScorecard($card['data']) : null,
        ]);
    }
}
