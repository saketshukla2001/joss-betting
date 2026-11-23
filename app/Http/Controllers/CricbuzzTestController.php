<?php

namespace App\Http\Controllers;

use App\Services\Cricket\CricbuzzCricketService;
use Illuminate\Http\Request;

/**
 * Isolated read-only Cricbuzz (RapidAPI) test pages.
 *
 * Fixed match IDs, manual refresh, no betting/wallet/odds/DB involvement.
 */
class CricbuzzTestController extends Controller
{
    private const MATCHES = [
        165061 => 'UP vs Jharkhand',
        165080 => 'Odisha vs Goa',
        177178 => 'Pakistan A vs India A',
        175840 => 'Ireland A vs Zimbabwe A',
        175240 => 'Abu Dhabi Knight Riders vs Dubai Capitals',
        161285 => 'New Zealand vs India',
    ];

    public function index(Request $request, CricbuzzCricketService $cricbuzz)
    {
        $refresh = $request->boolean('refresh');
        $matches = [];

        foreach (self::MATCHES as $id => $label) {
            $result = $cricbuzz->livescore($id, $refresh);

            $matches[] = [
                'id' => $id,
                'label' => $label,
                'result' => $result,
                'live' => ($result['success'] && is_array($result['data']))
                    ? $cricbuzz->presentLive($result['data'])
                    : null,
            ];
        }

        return view('cricbuzz-test.index', [
            'configured' => $cricbuzz->isConfigured(),
            'host' => $cricbuzz->getHost(),
            'matches' => $matches,
            'refresh' => $refresh,
        ]);
    }

    public function results(Request $request, CricbuzzCricketService $cricbuzz)
    {
        $refresh = $request->boolean('refresh');
        $matches = [];
        $liveCount = 0;
        $finishedCount = 0;

        foreach (self::MATCHES as $id => $label) {
            // The standalone screen keeps a slightly longer cache to spare the API quota.
            $result = $cricbuzz->livescore($id, $refresh, 60);
            $live = ($result['success'] && is_array($result['data']))
                ? $cricbuzz->presentLive($result['data'])
                : null;

            $finished = $live ? $this->isFinished((string) ($live['status'] ?? '')) : false;

            if ($live) {
                $finished ? $finishedCount++ : $liveCount++;
            }

            $matches[] = [
                'id' => $id,
                'label' => $label,
                'result' => $result,
                'live' => $live,
                'finished' => $finished,
            ];
        }

        return view('cricbuzz-test.results', [
            'host' => $cricbuzz->getHost(),
            'configured' => $cricbuzz->isConfigured(),
            'matches' => $matches,
            'liveCount' => $liveCount,
            'finishedCount' => $finishedCount,
            'refresh' => $refresh,
            'generatedAt' => now()->timezone(config('app.timezone')),
        ]);
    }

    private function isFinished(string $status): bool
    {
        return (bool) preg_match('/\bwon\b|drawn|abandoned|no result/i', $status);
    }

    public function show(Request $request, int $match, CricbuzzCricketService $cricbuzz)
    {
        abort_unless(array_key_exists($match, self::MATCHES), 404);

        $refresh = $request->boolean('refresh');

        $live = $cricbuzz->miniscore($match, $refresh);
        $card = $cricbuzz->scorecard($match, $refresh);

        return view('cricbuzz-test.show', [
            'matchId' => $match,
            'label' => self::MATCHES[$match],
            'live' => $live,
            'card' => $card,
            'presentedLive' => ($live['success'] && is_array($live['data'])) ? $cricbuzz->presentLive($live['data']) : null,
            'presentedCard' => ($card['success'] && is_array($card['data'])) ? $cricbuzz->presentScorecard($card['data']) : null,
            'refresh' => $refresh,
        ]);
    }
}
