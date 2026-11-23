<?php

namespace App\Services\Cricket;

use Illuminate\Support\Facades\Cache;
use App\Support\CricketStatus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Read-only Cricbuzz (RapidAPI) client for manual testing.
 *
 * - Sends x-rapidapi-host / x-rapidapi-key headers (never exposed to the browser)
 * - Caches per match+endpoint and supports a controlled refresh
 * - Throttles outbound calls to respect the provider's per-second rate limit
 * - Retries once after a 429 back-off
 *
 * Does not touch matches, markets, odds, bets, wallets or settlements.
 */
class CricbuzzCricketService
{
    private const BASE_PATH = '/mcenter/v1';

    private static float $lastRequestAt = 0.0;

    private ?string $host;

    private ?string $key;

    private string $baseUrl;

    private int $timeout;

    public function __construct(?string $host = null, ?string $key = null, ?string $baseUrl = null, ?int $timeout = null)
    {
        $this->host = $host ?? config('services.cricbuzz.host');
        $this->key = $key ?? config('services.cricbuzz.key');
        $this->baseUrl = rtrim((string) ($baseUrl ?? config('services.cricbuzz.base_url', 'https://cricbuzz-cricket.p.rapidapi.com')), '/');
        $this->timeout = max(3, $timeout ?? (int) config('services.cricbuzz.timeout', 15));
    }

    public function isConfigured(): bool
    {
        return is_string($this->host) && trim($this->host) !== ''
            && is_string($this->key) && trim($this->key) !== '';
    }

    public function getHost(): string
    {
        return (string) $this->host;
    }

    /**
     * @return array{success: bool, status: int|null, data: mixed, error: string|null, error_code: string|null, cached: bool, fetched_at: string|null}
     */
    public function livescore(int $matchId, bool $refresh = false, int $ttl = 20): array
    {
        return $this->cached('livescore', $matchId, max(5, $ttl), $refresh);
    }

    /**
     * @return array{success: bool, status: int|null, data: mixed, error: string|null, error_code: string|null, cached: bool, fetched_at: string|null}
     */
    public function miniscore(int $matchId, bool $refresh = false): array
    {
        return $this->cached('miniscorecard', $matchId, 20, $refresh);
    }

    /**
     * @return array{success: bool, status: int|null, data: mixed, error: string|null, error_code: string|null, cached: bool, fetched_at: string|null}
     */
    public function scorecard(int $matchId, bool $refresh = false): array
    {
        return $this->cached('scard', $matchId, 60, $refresh);
    }

    /**
     * Discovery feed: live / upcoming / recent matches from the provider.
     *
     * @return array{success: bool, status: int|null, data: mixed, error: string|null, error_code: string|null, cached: bool, fetched_at: string|null}
     */
    public function discovery(string $type, bool $refresh = false): array
    {
        $type = in_array($type, ['live', 'upcoming', 'recent'], true) ? $type : 'live';
        $cacheKey = 'cricbuzz:discovery:'.$type;

        if ($refresh) {
            Cache::forget($cacheKey);
        }

        $cached = Cache::get($cacheKey);

        if (is_array($cached) && ! $refresh) {
            $cached['cached'] = true;

            return $cached;
        }

        $result = $this->requestPath('/matches/v1/'.$type);
        $result['cached'] = false;
        $result['fetched_at'] = now()->toIso8601String();

        if ($result['success'] && is_array($result['data'])) {
            $result['data'] = $this->normalizeDiscovery($result['data']);
            $ttl = max(30, (int) setting('cricket_discovery_ttl', config('services.cricbuzz.discovery_ttl', 120)));
            Cache::put($cacheKey, $result, $ttl);
        } else {
            Cache::put($cacheKey, $result, 30);
        }

        return $result;
    }

    /**
     * @return array{success: bool, status: int|null, data: mixed, error: string|null, error_code: string|null, cached: bool, fetched_at: string|null}
     */
    private function cached(string $endpoint, int $matchId, int $ttl, bool $refresh): array
    {
        $cacheKey = 'cricbuzz:'.$endpoint.':'.$matchId;

        if ($refresh) {
            Cache::forget($cacheKey);
        }

        $cached = Cache::get($cacheKey);

        if (is_array($cached) && ! $refresh) {
            $cached['cached'] = true;

            return $cached;
        }

        $result = $this->request($matchId, $endpoint);
        $result['cached'] = false;
        $result['fetched_at'] = now()->toIso8601String();

        Cache::put($cacheKey, $result, $result['success'] ? $ttl : 30);

        return $result;
    }

    /**
     * @return array{success: bool, status: int|null, data: mixed, error: string|null, error_code: string|null}
     */
    public function request(int $matchId, string $endpoint): array
    {
        return $this->requestPath(self::BASE_PATH.'/'.$matchId.'/'.$endpoint);
    }

    /**
     * @return array{success: bool, status: int|null, data: mixed, error: string|null, error_code: string|null}
     */
    public function requestPath(string $path): array
    {
        if (! $this->isConfigured()) {
            return $this->error('RapidAPI credentials missing. Set RAPIDAPI_HOST and RAPIDAPI_KEY in .env.', 'missing_credentials', null);
        }

        $url = $this->baseUrl.'/'.ltrim($path, '/');
        $endpoint = parse_url($url, PHP_URL_PATH) ?: $path;

        $response = $this->send($url);

        if ($response['status'] === 429) {
            // Basic plans allow only a few requests per second — back off and retry once.
            usleep(max(0, (int) config('services.cricbuzz.retry_backoff_ms', 1500)) * 1000);
            $response = $this->send($url);
        }

        if ($response['status'] === null) {
            return $this->error($response['error'] ?? 'Could not reach Cricbuzz.', 'connection_failed', null);
        }

        $status = $response['status'];
        $body = $response['body'];

        if ($status >= 200 && $status < 300) {
            return [
                'success' => true,
                'status' => $status,
                'data' => $body,
                'error' => null,
                'error_code' => null,
            ];
        }

        Log::warning('Cricbuzz API error', ['endpoint' => $endpoint, 'status' => $status]);

        return $this->error($this->messageFor($status, $body), $this->codeFor($status), $status);
    }

    /**
     * @return array{status: int|null, body: mixed, error: string|null}
     */
    private function send(string $url): array
    {
        $this->throttle();

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'x-rapidapi-host' => (string) $this->host,
                    'x-rapidapi-key' => (string) $this->key,
                ])
                ->acceptJson()
                ->get($url);
        } catch (Throwable $e) {
            Log::warning('Cricbuzz request failed', ['url_path' => parse_url($url, PHP_URL_PATH), 'type' => get_class($e)]);

            Cache::forever('cricbuzz:health:last_failure', [
                'at' => now()->toIso8601String(),
                'status' => null,
                'path' => parse_url($url, PHP_URL_PATH) ?: '',
            ]);
            $this->bumpHealth('failures');

            return ['status' => null, 'body' => null, 'error' => 'Could not reach Cricbuzz (timeout/connection).'];
        }

        $status = $response->status();
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $body = trim((string) $response->body());
        $json = $response->json();
        $parsed = is_array($json) || $body === '';

        if (! $parsed) {
            $this->bumpHealth('parse_errors');
        }

        if ($response->failed() && ! is_array($json)) {
            $json = ['message' => mb_substr($body, 0, 200)];
        }

        $meta = ['at' => now()->toIso8601String(), 'status' => $status, 'path' => $path];

        if ($response->successful()) {
            Cache::forever('cricbuzz:health:last_success', $meta);
        } else {
            Cache::forever('cricbuzz:health:last_failure', $meta);
            $this->bumpHealth('failures');

            if ($status === 429) {
                $this->bumpHealth('rate_limits');
            }
        }

        return ['status' => $status, 'body' => $json, 'error' => null];
    }

    private function throttle(): void
    {
        $throttleMs = max(0, (int) config('services.cricbuzz.throttle_ms', 1050));

        if ($throttleMs > 0) {
            $elapsedMs = (microtime(true) - self::$lastRequestAt) * 1000;

            if ($elapsedMs < $throttleMs) {
                usleep((int) (($throttleMs - $elapsedMs) * 1000));
            }
        }

        self::$lastRequestAt = microtime(true);
    }

    private function bumpHealth(string $key): void
    {
        $cacheKey = 'cricbuzz:health:'.$key;
        Cache::forever($cacheKey, ((int) Cache::get($cacheKey, 0)) + 1);
    }

    /**
     * @return array<string, mixed>
     */
    public function health(): array
    {
        return [
            'configured' => $this->isConfigured(),
            'host' => $this->host,
            'last_success' => Cache::get('cricbuzz:health:last_success'),
            'last_failure' => Cache::get('cricbuzz:health:last_failure'),
            'failures' => (int) Cache::get('cricbuzz:health:failures', 0),
            'rate_limits' => (int) Cache::get('cricbuzz:health:rate_limits', 0),
            'parse_errors' => (int) Cache::get('cricbuzz:health:parse_errors', 0),
        ];
    }

    private function messageFor(int $status, mixed $body): string
    {
        $apiMessage = is_array($body) ? ($body['message'] ?? null) : null;

        return match ($status) {
            401, 403 => 'Cricbuzz rejected the request ('.$status.'). Check your RapidAPI subscription/key.',
            404 => 'Match not found on Cricbuzz (404).',
            429 => 'Cricbuzz rate limit reached (429). Wait a moment and refresh.',
            500, 502, 503, 504 => 'Cricbuzz is temporarily unavailable ('.$status.').',
            default => is_string($apiMessage) && $apiMessage !== ''
                ? $apiMessage
                : 'Cricbuzz returned HTTP '.$status.'.',
        };
    }

    private function codeFor(int $status): string
    {
        return match ($status) {
            401, 403 => 'unauthorized',
            404 => 'not_found',
            429 => 'rate_limited',
            default => 'http_'.$status,
        };
    }

    /**
     * @return array{success: false, status: int|null, data: null, error: string, error_code: string}
     */
    private function error(string $message, string $code, ?int $status): array
    {
        return [
            'success' => false,
            'status' => $status,
            'data' => null,
            'error' => $message,
            'error_code' => $code,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, array<string, mixed>>
     */
    private function normalizeDiscovery(array $payload): array
    {
        $rows = [];
        $seen = [];

        foreach (($payload['typeMatches'] ?? []) as $typeGroup) {
            if (! is_array($typeGroup)) {
                continue;
            }

            $typeName = $typeGroup['matchType'] ?? null;

            foreach (($typeGroup['seriesMatches'] ?? []) as $series) {
                $wrapper = is_array($series) ? ($series['seriesAdWrapper'] ?? null) : null;

                if (! is_array($wrapper)) {
                    continue;
                }

                $seriesName = $wrapper['seriesName'] ?? ($series['seriesName'] ?? null);

                foreach (($wrapper['matches'] ?? []) as $match) {
                    $info = is_array($match) ? ($match['matchInfo'] ?? null) : null;

                    if (! is_array($info)) {
                        continue;
                    }

                    $id = (int) ($info['matchId'] ?? 0);

                    if ($id <= 0 || isset($seen[$id])) {
                        continue;
                    }

                    $seen[$id] = true;
                    $status = (string) ($info['status'] ?? '');
                    $normalized = CricketStatus::normalize($status);
                    $team1 = is_array($info['team1'] ?? null) ? $info['team1'] : [];
                    $team2 = is_array($info['team2'] ?? null) ? $info['team2'] : [];
                    $venue = is_array($info['venueInfo'] ?? null) ? $info['venueInfo'] : [];

                    $rows[] = [
                        'id' => $id,
                        'label' => ($team1['teamName'] ?? 'Team 1').' vs '.($team2['teamName'] ?? 'Team 2'),
                        'team1' => $team1['teamName'] ?? null,
                        'team2' => $team2['teamName'] ?? null,
                        'team1_short' => $team1['teamSName'] ?? null,
                        'team2_short' => $team2['teamSName'] ?? null,
                        'series' => $info['seriesName'] ?? $seriesName,
                        'format' => $info['matchFormat'] ?? null,
                        'desc' => $info['matchDesc'] ?? null,
                        'state' => $info['state'] ?? null,
                        'state_title' => $info['stateTitle'] ?? null,
                        'status_raw' => $status,
                        'status_label' => $normalized['label'],
                        'status_group' => $normalized['group'],
                        'start_date' => $info['startDate'] ?? null,
                        'venue' => $venue['ground'] ?? null,
                        'city' => $venue['city'] ?? null,
                        'type' => $typeName,
                    ];
                }
            }
        }

        return $rows;
    }

    /**
     * Normalise a livescore/miniscorecard payload for the list/detail views.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function presentLive(array $payload): array
    {
        $ms = $payload['miniscore'] ?? $payload;

        $innings = [];
        $rawInnings = $ms['inningsscores']['inningsscore'] ?? ($ms['inningsscores'] ?? []);

        if (is_array($rawInnings)) {
            foreach ($rawInnings as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $innings[] = [
                    'team' => $row['batteamshortname'] ?? ('Team #'.($row['batteamid'] ?? '?')),
                    'team_id' => $row['batteamid'] ?? null,
                    'runs' => $row['runs'] ?? null,
                    'wickets' => $row['wickets'] ?? null,
                    'overs' => $row['overs'] ?? null,
                    'target' => $row['target'] ?? null,
                ];
            }
        }

        $rawStatus = (string) ($ms['custstatus'] ?? '');
        $normalized = CricketStatus::normalize($rawStatus);

        return [
            'status' => $rawStatus,
            'status_raw' => $rawStatus,
            'status_label' => $normalized['label'],
            'status_group' => $normalized['group'],
            'innings' => $innings,
            'crr' => $ms['crr'] ?? null,
            'rrr' => $ms['rrr'] ?? null,
            'target' => $ms['target'] ?? null,
            'partnership' => $ms['partnership'] ?? null,
            'last_wkt' => $ms['lastwkt'] ?? null,
            'overs_remaining' => $ms['oversrem'] ?? null,
            'batting_team_score' => $ms['batteamscore'] ?? null,
            'striker' => $this->batsman($ms['batsmanstriker'] ?? null),
            'non_striker' => $this->batsman($ms['batsmannonstriker'] ?? null),
            'bowler' => $this->bowler($ms['bowlerstriker'] ?? null),
            'raw' => $payload,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function presentScorecard(array $payload): array
    {
        $innings = [];
        $rows = $payload['scorecard'] ?? [];

        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $batsmen = [];

                foreach (($row['batsman'] ?? []) as $bat) {
                    if (! is_array($bat)) {
                        continue;
                    }

                    $batsmen[] = [
                        'name' => $bat['name'] ?? '—',
                        'runs' => $bat['runs'] ?? null,
                        'balls' => $bat['balls'] ?? null,
                        'fours' => $bat['fours'] ?? null,
                        'sixes' => $bat['sixes'] ?? null,
                        'sr' => $bat['strkrate'] ?? null,
                        'out' => $bat['outdec'] ?? '',
                    ];
                }

                $bowlers = [];

                foreach (($row['bowler'] ?? []) as $bwl) {
                    if (! is_array($bwl)) {
                        continue;
                    }

                    $bowlers[] = [
                        'name' => $bwl['name'] ?? '—',
                        'overs' => $bwl['overs'] ?? null,
                        'maidens' => $bwl['maidens'] ?? null,
                        'runs' => $bwl['runs'] ?? null,
                        'wickets' => $bwl['wickets'] ?? null,
                        'economy' => $bwl['economy'] ?? null,
                    ];
                }

                $innings[] = [
                    'innings_id' => $row['inningsid'] ?? null,
                    'team' => $row['batteamname'] ?? ($row['batteamsname'] ?? '—'),
                    'score' => $row['score'] ?? null,
                    'wickets' => $row['wickets'] ?? null,
                    'overs' => $row['overs'] ?? null,
                    'runrate' => $row['runrate'] ?? null,
                    'extras' => $row['extras'] ?? null,
                    'batsmen' => $batsmen,
                    'bowlers' => $bowlers,
                ];
            }
        }

        return [
            'status' => $payload['status'] ?? '',
            'complete' => (bool) ($payload['ismatchcomplete'] ?? false),
            'innings' => $innings,
            'raw' => $payload,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $bat
     * @return array<string, mixed>|null
     */
    private function batsman(?array $bat): ?array
    {
        if (! $bat) {
            return null;
        }

        return [
            'name' => $bat['name'] ?? '—',
            'runs' => $bat['runs'] ?? null,
            'balls' => $bat['balls'] ?? null,
            'fours' => $bat['fours'] ?? null,
            'sixes' => $bat['sixes'] ?? null,
            'sr' => $bat['strkrate'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $bowler
     * @return array<string, mixed>|null
     */
    private function bowler(?array $bowler): ?array
    {
        if (! $bowler) {
            return null;
        }

        return [
            'name' => $bowler['name'] ?? '—',
            'overs' => $bowler['overs'] ?? null,
            'maidens' => $bowler['maidens'] ?? null,
            'runs' => $bowler['runs'] ?? null,
            'wickets' => $bowler['wickets'] ?? null,
            'economy' => $bowler['economy'] ?? null,
        ];
    }
}
