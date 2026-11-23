<?php

namespace App\Services\Cricket;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Read-only Sportmonks Cricket API 2.0 client for the isolated test page.
 *
 * - Auth: ?api_token=... (never sent to the frontend, never logged)
 * - Caching: successful payloads cached per endpoint+query, failures cached briefly
 * - Plan safety: if a rich include set is refused (403), retries once with the
 *   core includes so the page still shows fixtures.
 *
 * This service does not touch matches, markets, odds, bets or wallets.
 */
class SportmonksCricketService
{
    private const CORE_INCLUDES = 'localteam,visitorteam,venue,league,season,stage';

    private const EXTRA_INCLUDES = 'runs';

    private const DETAIL_EXTRA_INCLUDES = 'runs,scoreboards,batting,bowling';

    private const LIST_INCLUDES = self::CORE_INCLUDES.','.self::EXTRA_INCLUDES;

    private const DETAIL_INCLUDES = self::CORE_INCLUDES.','.self::DETAIL_EXTRA_INCLUDES;

    private const LIVE_STATUSES = [
        '1st Innings', '2nd Innings', '3rd Innings', '4th Innings',
        'Innings Break', 'Stump Day 1', 'Stump Day 2', 'Stump Day 3', 'Stump Day 4',
        'Tea Break', 'Lunch', 'Dinner', 'Int.', 'Delayed',
    ];

    private ?string $apiKey;

    private string $baseUrl;

    private int $timeout;

    public function __construct(?string $apiKey = null, ?string $baseUrl = null, ?int $timeout = null)
    {
        $this->apiKey = $apiKey ?? config('services.sportmonks.cricket_key');
        $this->baseUrl = rtrim((string) ($baseUrl ?? config('services.sportmonks.cricket_base_url', 'https://cricket.sportmonks.com/api/v2.0')), '/');
        $this->timeout = max(1, $timeout ?? (int) config('services.sportmonks.cricket_timeout', 10));
    }

    public function isConfigured(): bool
    {
        return is_string($this->apiKey) && trim($this->apiKey) !== '';
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @return array{success: bool, status: int|null, data: mixed, meta: array|null, error: string|null, error_code: string|null, degraded: bool, cached: bool}
     */
    public function livescores(int $ttl = 30): array
    {
        return $this->fetch(
            'livescores',
            ['sort' => 'starting_at'],
            self::LIST_INCLUDES,
            self::CORE_INCLUDES,
            $ttl
        );
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array{success: bool, status: int|null, data: mixed, meta: array|null, error: string|null, error_code: string|null, degraded: bool, cached: bool}
     */
    public function fixtures(array $query = [], int $ttl = 120): array
    {
        return $this->fetch(
            'fixtures',
            array_merge(['sort' => 'starting_at'], $query),
            self::LIST_INCLUDES,
            self::CORE_INCLUDES,
            $ttl
        );
    }

    /**
     * Upcoming fixtures only (`status = NS`), soonest first.
     *
     * @return array{success: bool, status: int|null, data: mixed, meta: array|null, error: string|null, error_code: string|null, degraded: bool, cached: bool}
     */
    public function upcomingFixtures(int $days = 30, int $page = 1, int $ttl = 120): array
    {
        return $this->fetch(
            'fixtures',
            [
                'filter[status]' => 'NS',
                'filter[starts_between]' => now()->toDateString().','.now()->addDays(max(1, $days))->toDateString(),
                'sort' => 'starting_at',
                'page' => max(1, $page),
            ],
            self::LIST_INCLUDES,
            self::CORE_INCLUDES,
            $ttl
        );
    }

    /**
     * @return array{success: bool, status: int|null, data: mixed, meta: array|null, error: string|null, error_code: string|null, degraded: bool, cached: bool}
     */
    public function fixture(int $id, int $ttl = 60): array
    {
        return $this->fetch(
            'fixtures/'.$id,
            [],
            self::DETAIL_INCLUDES,
            self::CORE_INCLUDES,
            $ttl
        );
    }

    /**
     * @return array{success: bool, status: int|null, data: mixed, meta: array|null, error: string|null, error_code: string|null, degraded: bool, cached: bool}
     */
    public function leagues(int $ttl = 21600): array
    {
        return $this->fetch('leagues', [], '', '', $ttl);
    }

    /**
     * Fetch with an include set; fall back to core includes when the plan rejects extras.
     *
     * @param  array<string, mixed>  $query
     * @return array{success: bool, status: int|null, data: mixed, meta: array|null, error: string|null, error_code: string|null, degraded: bool, cached: bool}
     */
    private function fetch(string $endpoint, array $query, string $includes, string $fallbackIncludes, int $ttl): array
    {
        $cacheKey = 'sportmonks:'.md5($endpoint.'|'.json_encode($query).'|'.$includes);

        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            $cached['cached'] = true;

            return $cached;
        }

        $result = $this->request($endpoint, $query + ['include' => $includes]);

        if (! $result['success'] && $result['status'] === 403 && $includes !== self::CORE_INCLUDES) {
            $core = $this->request($endpoint, $query + ['include' => self::CORE_INCLUDES]);

            if ($core['success']) {
                $core['degraded'] = true;
                $core['error'] = null;
                $result = $core;
            }
        }

        $payload = $result + ['degraded' => $result['degraded'] ?? false, 'cached' => false];

        Cache::put($cacheKey, $payload, $payload['success'] ? $ttl : 30);

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array{success: bool, status: int|null, data: mixed, meta: array|null, error: string|null, error_code: string|null, degraded: bool}
     */
    public function request(string $endpoint, array $query = []): array
    {
        if (! $this->isConfigured()) {
            return $this->error('Sportmonks API token is missing. Set SPORTMONKS_CRICKET_KEY or CRICKET_API_KEY in .env.', 'missing_token', null);
        }

        $url = $this->baseUrl.'/'.ltrim($endpoint, '/');
        $query['api_token'] = $this->apiKey;

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->retry(1, 200, fn (Throwable $e) => $e instanceof ConnectionException, throw: false)
                ->get($url, $query);
        } catch (ConnectionException $e) {
            Log::warning('Sportmonks cricket request failed to connect', ['endpoint' => $endpoint]);

            return $this->error('Could not reach Sportmonks (connection/timeout).', 'connection_failed', null);
        } catch (Throwable $e) {
            Log::warning('Sportmonks cricket request error', ['endpoint' => $endpoint, 'type' => get_class($e)]);

            return $this->error('Unexpected error while calling Sportmonks.', 'request_failed', null);
        }

        $status = $response->status();
        $json = $response->json();
        $body = is_array($json) ? $json : [];

        if ($response->successful()) {
            return [
                'success' => true,
                'status' => $status,
                'data' => $body['data'] ?? $body,
                'meta' => $body['meta'] ?? null,
                'error' => null,
                'error_code' => null,
                'degraded' => false,
            ];
        }

        $message = $this->errorMessage($status, $body);

        Log::warning('Sportmonks cricket API error', [
            'endpoint' => $endpoint,
            'status' => $status,
            'code' => $this->errorCode($status),
        ]);

        return $this->error($message, $this->errorCode($status), $status);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function errorMessage(int $status, array $body): string
    {
        $apiMessage = $body['message'] ?? null;

        if (is_array($apiMessage)) {
            $apiMessage = $apiMessage['message'] ?? null;
        }

        return match ($status) {
            401 => 'Sportmonks rejected the API token (401 Unauthenticated). Check the token in .env and try again.',
            403 => 'This data needs a higher Sportmonks plan (403). Live scores/scoreboards may be restricted on your subscription.',
            404 => 'Fixture not found in Sportmonks (404).',
            429 => 'Sportmonks rate limit reached (429). Try again shortly.',
            500, 502, 503, 504 => 'Sportmonks is temporarily unavailable ('.$status.').',
            default => is_string($apiMessage) && $apiMessage !== ''
                ? $apiMessage
                : 'Sportmonks returned HTTP '.$status.'.',
        };
    }

    private function errorCode(int $status): string
    {
        return match ($status) {
            401 => 'unauthenticated',
            403 => 'plan_restriction',
            404 => 'not_found',
            429 => 'rate_limited',
            default => 'http_'.$status,
        };
    }

    /**
     * @return array{success: false, status: int|null, data: null, meta: null, error: string, error_code: string, degraded: bool}
     */
    private function error(string $message, string $code, ?int $status): array
    {
        return [
            'success' => false,
            'status' => $status,
            'data' => null,
            'meta' => null,
            'error' => $message,
            'error_code' => $code,
            'degraded' => false,
        ];
    }

    /**
     * Turn one raw Sportmonks fixture into a predictable array for Blade.
     *
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    public function presentFixture(array $raw): array
    {
        $local = $this->relation($raw, 'localteam');
        $visitor = $this->relation($raw, 'visitorteam');
        $venue = $this->relation($raw, 'venue');
        $league = $this->relation($raw, 'league');
        $season = $this->relation($raw, 'season');
        $stage = $this->relation($raw, 'stage');

        $localName = $this->nameOf($local, 'Team #'.($raw['localteam_id'] ?? '?'));
        $visitorName = $this->nameOf($visitor, 'Team #'.($raw['visitorteam_id'] ?? '?'));

        return [
            'id' => (int) ($raw['id'] ?? 0),
            'name' => $localName.' vs '.$visitorName,
            'local_name' => $localName,
            'visitor_name' => $visitorName,
            'status' => (string) ($raw['status'] ?? 'Unknown'),
            'status_label' => $this->statusLabel((string) ($raw['status'] ?? 'Unknown')),
            'live' => $this->isLiveStatus((string) ($raw['status'] ?? '')),
            'is_upcoming' => ((string) ($raw['status'] ?? '')) === 'NS',
            'note' => $raw['note'] ?? null,
            'type' => $raw['type'] ?? null,
            'round' => $raw['round'] ?? null,
            'starting_at' => $raw['starting_at'] ?? null,
            'league' => $this->nameOf($league),
            'league_code' => is_array($league) ? ($league['code'] ?? null) : null,
            'season' => $this->nameOf($season),
            'stage' => $this->nameOf($stage),
            'venue' => $this->nameOf($venue),
            'city' => is_array($venue) ? ($venue['city'] ?? null) : null,
            'local_logo' => is_array($local) ? ($local['image_path'] ?? null) : null,
            'visitor_logo' => is_array($visitor) ? ($visitor['image_path'] ?? null) : null,
            'league_logo' => is_array($league) ? ($league['image_path'] ?? null) : null,
            'venue_logo' => is_array($venue) ? ($venue['image_path'] ?? null) : null,
            'toss_won_team_id' => $raw['toss_won_team_id'] ?? null,
            'winner_team_id' => $raw['winner_team_id'] ?? null,
            'total_overs_played' => $raw['total_overs_played'] ?? null,
            'scores' => $this->scores($raw, $localName, $visitorName),
            'raw' => $raw,
        ];
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<int, array<string, mixed>>
     */
    private function scores(array $raw, string $localName, string $visitorName): array
    {
        $rows = $this->relation($raw, 'runs');
        $rows = is_array($rows) ? $rows : [];

        $teamNames = [];

        if (isset($raw['localteam_id'])) {
            $teamNames[(string) $raw['localteam_id']] = $localName;
        }

        if (isset($raw['visitorteam_id'])) {
            $teamNames[(string) $raw['visitorteam_id']] = $visitorName;
        }

        $scores = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $runs = $row['score'] ?? $row['runs'] ?? null;

            if ($runs === null) {
                continue;
            }

            $wickets = $row['wickets'] ?? $row['wickets_out'] ?? null;
            $overs = $row['overs'] ?? null;
            $teamId = (string) ($row['team_id'] ?? '');
            $team = $teamNames[$teamId] ?? ('Team '.$teamId);

            $scores[] = [
                'team' => $team,
                'runs' => $runs,
                'wickets' => $wickets,
                'overs' => $overs,
                'inning' => $row['inning'] ?? null,
                'text' => $runs.($wickets !== null ? '/'.$wickets : '').($overs !== null ? ' ('.$overs.' ov)' : ''),
            ];
        }

        return $scores;
    }

    /**
     * Unwrap Sportmonks' { "data": ... } include envelope.
     */
    private function relation(array $raw, string $key): mixed
    {
        $value = $raw[$key] ?? null;

        if (is_array($value) && array_key_exists('data', $value)) {
            return $value['data'];
        }

        return $value;
    }

    private function isLiveStatus(string $status): bool
    {
        return in_array($status, self::LIVE_STATUSES, true);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'NS' => 'Upcoming',
            'Finished' => 'Finished',
            '1st Innings', '2nd Innings', '3rd Innings', '4th Innings',
            'Stump Day 1', 'Stump Day 2', 'Stump Day 3', 'Stump Day 4', 'Innings Break' => 'Live',
            'Tea Break', 'Lunch', 'Dinner' => 'Break',
            'Postp.' => 'Postponed',
            'Int.' => 'Interrupted',
            'Aban.' => 'Abandoned',
            'Delayed' => 'Delayed',
            'Cancl.' => 'Cancelled',
            default => $status === '' ? 'Unknown' : $status,
        };
    }

    private function nameOf(mixed $relation, ?string $fallback = null): ?string
    {
        if (is_array($relation)) {
            if (isset($relation['name']) && is_string($relation['name'])) {
                return $relation['name'];
            }

            if (isset($relation[0]['name']) && is_string($relation[0]['name'])) {
                return $relation[0]['name'];
            }
        }

        return $fallback;
    }
}
