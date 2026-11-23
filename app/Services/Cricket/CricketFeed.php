<?php

namespace App\Services\Cricket;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Read-only bridge between OpenCricketAPI and the dashboard.
 *
 * The public dashboard must never block or break because a third-party API is
 * slow or down, so this feed:
 *  - fetches with a short timeout and no retries,
 *  - caches successful payloads for a few minutes,
 *  - caches failures briefly so a dead API is not hammered,
 *  - normalises matches into a view-friendly shape,
 *  - never writes to the database (no duplicate fixtures, no betting impact)
 *    and never logs or exposes the API key.
 */
class CricketFeed
{
    private const CACHE_PREFIX = 'cricket:feed:recent';

    private const SUCCESS_TTL_MINUTES = 10;

    private const FAILURE_TTL_MINUTES = 1;

    public function __construct(private ?OpenCricketApiService $api = null)
    {
        $this->api ??= new OpenCricketApiService(timeout: 5, retries: 0, connectTimeout: 3);
    }

    /**
     * Latest cricket matches with scores, ready for the Blade view.
     *
     * @return array{matches: array<int, array<string, mixed>>, error: string|null, source: string|null, plan: string|null}
     */
    public function recent(int $limit = 4): array
    {
        $limit = max(1, min(10, $limit));
        $cacheKey = self::CACHE_PREFIX.':'.$limit;

        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        try {
            $result = $this->api->getMatches(['limit' => $limit]);
        } catch (Throwable $e) {
            return $this->failed($cacheKey, 'Cricket feed could not be reached.');
        }

        if (! ($result['success'] ?? false) || ! is_array($result['data'] ?? null)) {
            return $this->failed($cacheKey, $result['error'] ?? 'Cricket feed is unavailable.');
        }

        $seen = [];
        $matches = [];

        foreach ($result['data'] as $row) {
            if (! is_array($row)) {
                continue;
            }

            $id = (string) ($row['id'] ?? '');

            if ($id !== '' && isset($seen[$id])) {
                continue;
            }

            if ($id !== '') {
                $seen[$id] = true;
            }

            $matches[] = $this->normalize($row);

            if (count($matches) >= $limit) {
                break;
            }
        }

        $payload = [
            'matches' => $matches,
            'error' => null,
            'source' => $result['meta']['source'] ?? 'OpenCricketAPI',
            'plan' => $result['meta']['plan'] ?? null,
        ];

        Cache::put(
            $cacheKey,
            $payload,
            now()->addMinutes($matches === [] ? self::FAILURE_TTL_MINUTES : self::SUCCESS_TTL_MINUTES)
        );

        if ($matches !== []) {
            Log::debug('Cricket feed loaded from OpenCricketAPI', [
                'matches' => count($matches),
                'source' => $payload['source'],
                'plan' => $payload['plan'],
            ]);
        }

        return $payload;
    }

    /**
     * @return array{matches: array<int, array<string, mixed>>, error: string|null, source: string|null, plan: string|null}
     */
    private function failed(string $cacheKey, string $message): array
    {
        $payload = [
            'matches' => [],
            'error' => $message,
            'source' => null,
            'plan' => null,
        ];

        Cache::put($cacheKey, $payload, now()->addMinutes(self::FAILURE_TTL_MINUTES));

        Log::warning('Cricket feed unavailable on dashboard', ['reason' => $message]);

        return $payload;
    }

    /**
     * Map one API match into the fields the dashboard needs.
     *
     * @param array<string, mixed> $match
     * @return array<string, mixed>
     */
    private function normalize(array $match): array
    {
        $teams = [];

        foreach (($match['teams'] ?? []) as $team) {
            $name = is_array($team) ? ($team['name'] ?? null) : $team;

            if (is_string($name) && trim($name) !== '') {
                $teams[] = trim($name);
            }
        }

        $home = $teams[0] ?? 'Home';
        $away = $teams[1] ?? 'Away';

        $teamNamesById = [];

        foreach (($match['teams'] ?? []) as $team) {
            if (is_array($team) && isset($team['id'], $team['name'])) {
                $teamNamesById[(string) $team['id']] = $team['name'];
            }
        }

        $scores = [];

        foreach (($match['innings'] ?? []) as $innings) {
            if (! is_array($innings)) {
                continue;
            }

            $runs = $innings['runs'] ?? ($innings['total']['runs'] ?? null);

            if ($runs === null) {
                continue;
            }

            $wickets = $innings['wickets'] ?? ($innings['total']['wickets'] ?? null);
            $overs = $innings['overs'] ?? ($innings['total']['overs'] ?? null);

            $team = $innings['team']
                ?? ($teamNamesById[(string) ($innings['team_id'] ?? '')] ?? null);

            $scores[] = [
                'team' => $team,
                'score' => $runs
                    .($wickets !== null ? '/'.$wickets : '')
                    .($overs !== null ? ' ('.$overs.' ov)' : ''),
            ];
        }

        $venueName = $match['venue']['name'] ?? null;
        $venueCity = $match['venue']['city'] ?? null;

        return [
            'id' => (string) ($match['id'] ?? ''),
            'home' => $home,
            'away' => $away,
            'label' => $home.' vs '.$away,
            'status' => ! empty($match['result']) ? 'Completed' : 'No result',
            'result' => $match['result'] ?? null,
            'scores' => $scores,
            'competition' => $match['competition']['name'] ?? ($match['event_name'] ?? null),
            'stage' => $match['stage'] ?? null,
            'format' => $match['format'] ?? null,
            'venue' => $venueName ? trim($venueName.($venueCity ? ', '.$venueCity : '')) : null,
            'date' => $match['date'] ?? null,
        ];
    }
}
