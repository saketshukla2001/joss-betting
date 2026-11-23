<?php

namespace App\Http\Controllers;

use App\Services\Cricket\SportmonksCricketService;
use Illuminate\Http\Request;

/**
 * Isolated, read-only Sportmonks Cricket test page.
 *
 * Deliberately separate from the sportsbook: it never reads or writes matches,
 * markets, odds, bets, wallets, commissions or settlements.
 */
class CricketTestController extends Controller
{
    public function index(Request $request, SportmonksCricketService $sportmonks)
    {
        $page = max(1, (int) $request->integer('page', 1));

        // Upcoming: documented status filter (NS = Not Started) inside a 30-day window.
        $upcoming = $sportmonks->upcomingFixtures(days: 30);

        // Live: dedicated endpoint (0 rows when nothing is in progress).
        $live = $sportmonks->livescores();

        // Recent: last 14 days, so the Upcoming section stays clean and separate.
        $from = now()->subDays(14)->toDateString();
        $to = now()->toDateString();

        $recentFilter = [
            'filter[starts_between]' => $from.','.$to,
            'sort' => 'starting_at',
            'page' => $page,
        ];

        if ($request->filled('league') && ctype_digit((string) $request->input('league'))) {
            $recentFilter['filter[league_id]'] = (int) $request->input('league');
        }

        $recent = $sportmonks->fixtures($recentFilter);

        $liveFixtures = $this->rows($sportmonks, $live['data'] ?? null);
        $upcomingFixtures = $this->rows($sportmonks, $upcoming['data'] ?? null);
        $recentRows = $this->rows($sportmonks, $recent['data'] ?? null);

        return view('cricket-test.index', [
            'configured' => $sportmonks->isConfigured(),
            'baseUrl' => $sportmonks->getBaseUrl(),
            'live' => $live,
            'upcoming' => $upcoming,
            'recent' => $recent,
            'liveFixtures' => $liveFixtures,
            'upcomingFixtures' => $upcomingFixtures,
            'recentRows' => $recentRows,
            'page' => $page,
            'from' => $from,
            'to' => $to,
            'league' => $request->input('league'),
        ]);
    }

    public function show(int $fixture, SportmonksCricketService $sportmonks)
    {
        $result = $sportmonks->fixture($fixture);

        $match = null;
        $raw = $result['data'] ?? null;

        if ($result['success'] && is_array($raw)) {
            if (isset($raw['data']) && is_array($raw['data'])) {
                $raw = $raw['data'];
            }

            if (array_is_list($raw) && isset($raw[0]) && is_array($raw[0])) {
                $raw = $raw[0];
            }

            $match = $sportmonks->presentFixture($raw);
        }

        return view('cricket-test.show', [
            'fixtureId' => $fixture,
            'result' => $result,
            'match' => $match,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rows(SportmonksCricketService $sportmonks, mixed $data): array
    {
        if (! is_array($data)) {
            return [];
        }

        if (isset($data['data']) && is_array($data['data'])) {
            $data = $data['data'];
        }

        $rows = [];

        foreach ($data as $item) {
            if (is_array($item) && isset($item['id'])) {
                $rows[] = $sportmonks->presentFixture($item);
            }
        }

        return $rows;
    }
}
