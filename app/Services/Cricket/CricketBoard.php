<?php

namespace App\Services\Cricket;

use App\Support\CricketStatus;
use Illuminate\Support\Facades\Cache;

/**
 * Shared, read-only cricket board for the public UI.
 *
 * Preferred source: Cricbuzz discovery (live / upcoming / recent). Detailed
 * score payloads are fetched only for a small number of live matches. If
 * discovery is disabled or unavailable, the configured match IDs are used as a
 * fallback. Results are cached and a mutex prevents duplicate API bursts.
 */
class CricketBoard
{
    public function __construct(private CricbuzzCricketService $cricbuzz) {}

    /**
     * Configured fallback IDs (config, optionally overridden in settings).
     *
     * @return array<int, string>
     */
    public function matchIds(): array
    {
        $configured = config('services.cricbuzz.match_ids', []);
        $configured = is_array($configured) ? $configured : [];

        $raw = trim((string) setting('cricket_fallback_ids', ''));

        if ($raw === '') {
            return $configured;
        }

        $parsed = [];

        foreach (preg_split('/[\r\n,]+/', $raw) ?: [] as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $parts = preg_split('/[:|]/', $line, 2);
            $id = (int) trim((string) ($parts[0] ?? ''));

            if ($id <= 0) {
                continue;
            }

            $label = trim((string) ($parts[1] ?? ''));
            $parsed[$id] = $label !== '' ? $label : ($configured[$id] ?? 'Match #'.$id);
        }

        return $parsed !== [] ? $parsed : $configured;
    }

    /**
     * IDs the public detail page may open (configured + last discovered).
     *
     * @return array<int, int>
     */
    public function knownIds(): array
    {
        $ids = array_map('intval', array_keys($this->matchIds()));

        $board = Cache::get('cricbuzz:board:last');

        if (is_array($board)) {
            foreach (['live', 'upcoming', 'results'] as $group) {
                foreach (($board[$group] ?? []) as $row) {
                    if (isset($row['id'])) {
                        $ids[] = (int) $row['id'];
                    }
                }
            }
        }

        $ids = array_values(array_unique(array_filter($ids)));

        return array_values(array_diff($ids, $this->hiddenIds()));
    }

    public function isConfigured(): bool
    {
        if (! $this->cricbuzz->isConfigured()) {
            return false;
        }

        return setting_enabled('cricket_discovery_mode', true) || $this->matchIds() !== [];
    }

    /**
     * Last board already in cache. Never calls the provider.
     *
     * @return array<string, mixed>
     */
    public function remembered(): array
    {
        $cached = Cache::get('cricbuzz:board');

        if (! is_array($cached)) {
            $cached = Cache::get('cricbuzz:board:last');
        }

        if (! is_array($cached)) {
            return [
                'generated_at' => null,
                'live' => [],
                'upcoming' => [],
                'results' => [],
                'errors' => [],
                'cached' => false,
                'stale' => true,
                'missing' => true,
            ];
        }

        return $cached + ['cached' => true];
    }

    /**
     * Cached board when one exists. Loads through the existing board builder
     * only when both cache keys are empty, so a warm cache makes no provider call.
     *
     * @return array<string, mixed>
     */
    public function rememberedOrLoad(): array
    {
        $cached = $this->remembered();

        if (! empty($cached['missing'])) {
            return $this->get(false);
        }

        return $cached;
    }

    /**
     * @return array<string, mixed>
     */
    public function get(bool $refresh = false): array
    {
        if (! setting_enabled('cricket_feed_enabled', true) || ! setting_enabled('cricket_board_enabled', true)) {
            return [
                'generated_at' => now()->toIso8601String(),
                'live' => [],
                'upcoming' => [],
                'results' => [],
                'errors' => [],
                'disabled' => true,
                'cached' => false,
                'stale' => false,
            ];
        }

        $cacheKey = 'cricbuzz:board';
        $ttl = max(10, (int) setting('cricket_board_ttl', config('services.cricbuzz.board_ttl', 60)));

        if ($refresh) {
            Cache::forget($cacheKey);
        }

        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached + ['cached' => true, 'stale' => false];
        }

        $lastKnown = Cache::get('cricbuzz:board:last');
        $lockKey = 'cricbuzz:board:lock';

        if (! Cache::add($lockKey, 1, 20)) {
            if (is_array($lastKnown)) {
                return $lastKnown + ['cached' => true, 'stale' => true];
            }

            return [
                'generated_at' => now()->toIso8601String(),
                'live' => [],
                'upcoming' => [],
                'results' => [],
                'errors' => [['id' => null, 'message' => 'Live cricket scores are warming up — retry in a few seconds.']],
                'cached' => false,
                'stale' => false,
                'warming' => true,
            ];
        }

        try {
            $board = $this->build();

            Cache::put($cacheKey, $board, $ttl);
            Cache::put('cricbuzz:board:last', $board, 600);

            return $board + ['cached' => false, 'stale' => false];
        } finally {
            Cache::forget($lockKey);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function build(): array
    {
        $discovery = setting_enabled('cricket_discovery_mode', true)
            ? $this->buildFromDiscovery()
            : null;

        return $discovery ?? $this->buildFromFallback();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildFromDiscovery(): ?array
    {
        $liveResult = $this->cricbuzz->discovery('live');
        $upcomingResult = $this->cricbuzz->discovery('upcoming');
        $recentResult = $this->cricbuzz->discovery('recent');

        $hidden = $this->hiddenIds();
        $errors = [];
        $seen = [];
        $entries = [];

        $collect = function (array $result, string $kind, int $limit) use (&$errors, &$seen, &$entries, $hidden): void {
            if (! ($result['success'] ?? false) || ! is_array($result['data'] ?? null)) {
                if (! empty($result['error'])) {
                    $errors[] = ['id' => null, 'message' => $result['error']];
                }

                return;
            }

            $count = 0;

            foreach ($result['data'] as $match) {
                if ($count >= $limit) {
                    break;
                }

                if (! is_array($match) || ! isset($match['id'])) {
                    continue;
                }

                $id = (int) $match['id'];

                if (in_array($id, $hidden, true) || isset($seen[$id])) {
                    continue;
                }

                $seen[$id] = true;
                $count++;
                $entries[] = [
                    'match' => $match,
                    'fetched_at' => $result['fetched_at'] ?? null,
                ];
            }
        };

        $collect($liveResult, 'live', max(1, (int) setting('cricket_live_limit', 10)));
        $collect($upcomingResult, 'upcoming', max(1, (int) setting('cricket_upcoming_limit', 8)));
        $collect($recentResult, 'recent', max(1, (int) setting('cricket_recent_limit', 12)));

        if ($entries === []) {
            return null;
        }

        $detailLimit = max(0, (int) setting('cricket_detail_limit', 6));
        $fetched = 0;
        $stopDetails = false;
        $rows = [];

        foreach ($entries as $entry) {
            $match = $entry['match'];
            $row = $this->rowFromDiscovery($match);

            // Fetch detailed score payloads only for genuinely live matches, and only up to the limit.
            if (! $stopDetails && $row['kind'] === 'live' && ($detailLimit === 0 || $fetched < $detailLimit)) {
                $detail = $this->cricbuzz->livescore((int) $match['id'], false, 90);

                if ($detail['success'] && is_array($detail['data'])) {
                    $detailed = $this->rowFromPresentation($row['label'], (int) $match['id'], $this->cricbuzz->presentLive($detail['data']), $detail['fetched_at'] ?? null);
                    $detailed['team1'] = $row['team1'];
                    $detailed['team2'] = $row['team2'];
                    $detailed['provider_state'] = $row['provider_state'];
                    $row = $detailed;
                } else {
                    $row['error'] = $detail['error'] ?? 'Live detail unavailable.';
                    $previous = $this->previousDetailedRow((int) $match['id']);

                    if (is_array($previous) && is_array($previous['innings'] ?? null) && $previous['innings'] !== []) {
                        $row['innings'] = $previous['innings'];
                        $row['stale'] = true;
                        $row['detail_reused'] = true;
                    }

                    $error = strtolower((string) $row['error']);

                    if (str_contains($error, '429') || str_contains($error, 'rate limit')) {
                        $stopDetails = true;
                    }
                }

                $fetched++;
            }

            $rows[] = $this->markStale($row);
        }

        $liveRows = [];
        $upcomingRows = [];
        $resultRows = [];

        foreach ($rows as $row) {
            match ($row['kind']) {
                'upcoming' => $upcomingRows[] = $row,
                'result' => $resultRows[] = $row,
                default => $liveRows[] = $row,
            };
        }

        return [
            'generated_at' => now()->toIso8601String(),
            'live' => array_slice($liveRows, 0, max(1, (int) setting('cricket_live_limit', 10))),
            'upcoming' => array_slice($upcomingRows, 0, max(1, (int) setting('cricket_upcoming_limit', 8))),
            'results' => array_slice($resultRows, 0, max(1, (int) setting('cricket_recent_limit', 12))),
            'errors' => $errors,
            'source' => 'discovery',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFromFallback(): array
    {
        $live = [];
        $upcoming = [];
        $results = [];
        $errors = [];
        $hidden = $this->hiddenIds();

        foreach ($this->matchIds() as $id => $label) {
            $id = (int) $id;

            if (in_array($id, $hidden, true)) {
                continue;
            }

            $rowKey = 'cricbuzz:row:'.$id;
            $lastRow = Cache::get($rowKey);

            if (is_array($lastRow) && ($lastRow['kind'] ?? '') === 'result') {
                $results[] = $lastRow;

                continue;
            }

            $result = $this->cricbuzz->livescore($id, false, 90);

            if (! $result['success'] || ! is_array($result['data'])) {
                $errors[] = ['id' => $id, 'message' => $result['error'] ?? 'Unable to load this match.'];

                continue;
            }

            $row = $this->markStale(
                $this->rowFromPresentation((string) $label, $id, $this->cricbuzz->presentLive($result['data']), $result['fetched_at'] ?? null)
            );

            Cache::put($rowKey, $row, $row['kind'] === 'result' ? 1800 : 120);

            match ($row['kind']) {
                'upcoming' => $upcoming[] = $row,
                'result' => $results[] = $row,
                default => $live[] = $row,
            };
        }

        return [
            'generated_at' => now()->toIso8601String(),
            'live' => $live,
            'upcoming' => $upcoming,
            'results' => $results,
            'errors' => $errors,
            'source' => 'fallback',
        ];
    }

    /**
     * @param  array<string, mixed>  $match
     * @return array<string, mixed>
     */
    private function rowFromDiscovery(array $match): array
    {
        $status = (string) ($match['status_raw'] ?? '');
        $normalized = CricketStatus::normalize($status);

        return [
            'id' => (int) $match['id'],
            'label' => (string) ($match['label'] ?? 'Match #'.$match['id']),
            'url' => route('cricket.show', (int) $match['id']),
            'status' => $status,
            'status_raw' => $status,
            'status_label' => $match['status_label'] ?? $normalized['label'],
            'status_group' => $match['status_group'] ?? $normalized['group'],
            'kind' => $this->kindFromGroup($match['status_group'] ?? $normalized['group']),
            'innings' => [],
            'crr' => null,
            'rrr' => null,
            'target' => null,
            'partnership' => null,
            'striker' => null,
            'non_striker' => null,
            'bowler' => null,
            'team1' => $match['team1'] ?? null,
            'team2' => $match['team2'] ?? null,
            'provider_state' => $match['state'] ?? null,
            'series' => $match['series'] ?? null,
            'format' => $match['format'] ?? null,
            'venue' => $match['venue'] ?? null,
            'start_date' => $match['start_date'] ?? null,
            'fetched_at' => $match['_fetched_at'] ?? now()->toIso8601String(),
            'cached' => true,
            'source' => 'discovery',
        ];
    }

    /**
     * @param  array<string, mixed>  $presented
     * @return array<string, mixed>
     */
    private function rowFromPresentation(string $label, int $id, array $presented, ?string $fetchedAt): array
    {
        $status = (string) ($presented['status_raw'] ?? $presented['status'] ?? '');
        $normalized = CricketStatus::normalize($status);

        return [
            'id' => $id,
            'label' => $label,
            'url' => route('cricket.show', $id),
            'status' => $status,
            'status_raw' => $status,
            'status_label' => $presented['status_label'] ?? $normalized['label'],
            'status_group' => $presented['status_group'] ?? $normalized['group'],
            'kind' => $this->kindFromGroup($presented['status_group'] ?? $normalized['group']),
            'innings' => $presented['innings'] ?? [],
            'crr' => $presented['crr'] ?? null,
            'rrr' => $presented['rrr'] ?? null,
            'target' => $presented['target'] ?? null,
            'partnership' => $presented['partnership'] ?? null,
            'striker' => $presented['striker'] ?? null,
            'non_striker' => $presented['non_striker'] ?? null,
            'bowler' => $presented['bowler'] ?? null,
            'team1' => null,
            'team2' => null,
            'provider_state' => null,
            'series' => null,
            'format' => null,
            'venue' => null,
            'start_date' => null,
            'fetched_at' => $fetchedAt ?? now()->toIso8601String(),
            'cached' => false,
            'source' => 'detail',
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function markStale(array $row): array
    {
        if (! empty($row['detail_reused'])) {
            $row['stale'] = true;

            return $row;
        }

        $row['stale'] = CricketStatus::isStale($row['fetched_at'] ?? null);

        return $row;
    }

    private function kindFromGroup(string $group): string
    {
        return match ($group) {
            CricketStatus::UPCOMING => 'upcoming',
            CricketStatus::COMPLETED => 'result',
            CricketStatus::OTHER => 'result',
            default => 'live',
        };
    }

    /**
     * @return array<int, int>
     */
    private function hiddenIds(): array
    {
        $raw = trim((string) setting('cricket_hidden_match_ids', ''));

        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        $ids = is_array($decoded) ? $decoded : preg_split('/[\s,]+/', $raw);

        return array_values(array_unique(array_filter(array_map('intval', $ids ?: []))));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function previousDetailedRow(int $id): ?array
    {
        foreach (['cricbuzz:board', 'cricbuzz:board:last'] as $key) {
            $cached = Cache::get($key);

            if (! is_array($cached)) {
                continue;
            }

            foreach (['live', 'results'] as $group) {
                foreach (($cached[$group] ?? []) as $row) {
                    if (! is_array($row) || (int) ($row['id'] ?? 0) !== $id) {
                        continue;
                    }

                    if (is_array($row['innings'] ?? null) && $row['innings'] !== []) {
                        return $row;
                    }
                }
            }
        }

        return null;
    }
}
