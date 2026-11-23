<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperationLog;
use App\Models\Setting;
use App\Services\Cricket\CricbuzzCricketService;
use App\Services\Cricket\CricketBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Admin monitoring for the read-only Cricbuzz integration.
 *
 * This screen never writes to matches, markets, selections, bets or wallets.
 */
class CricketController extends Controller
{
    public function index(CricketBoard $board, CricbuzzCricketService $service)
    {
        $payload = $board->get();
        $health = $service->health();

        $stale = 0;

        foreach (['live', 'upcoming', 'results'] as $group) {
            foreach (($payload[$group] ?? []) as $row) {
                if (! empty($row['stale'])) {
                    $stale++;
                }
            }
        }

        return view('admin.cricket.index', [
            'board' => $payload,
            'health' => $health,
            'configured' => count($board->matchIds()),
            'hidden' => $this->hiddenIds(),
            'stale' => $stale,
            'errorCount' => count($payload['errors'] ?? []),
            'feedEnabled' => setting_enabled('cricket_feed_enabled', true),
            'boardEnabled' => setting_enabled('cricket_board_enabled', true),
            'discovery' => setting_enabled('cricket_discovery_mode', true),
        ]);
    }

    public function matches(Request $request, CricbuzzCricketService $service)
    {
        $bucket = $request->string('bucket')->toString();
        $query = trim($request->string('q')->toString());

        $result = $this->discoveryRows($service);
        $rows = $result['rows'];
        $hidden = $this->hiddenIds();

        if ($rows === []) {
            $rows = $this->fallbackRows(app(CricketBoard::class)->get());
        }

        if (in_array($bucket, ['live', 'upcoming', 'completed'], true)) {
            $rows = array_values(array_filter($rows, fn ($row) => ($row['bucket'] ?? '') === $bucket));
        }

        if ($query !== '') {
            $rows = array_values(array_filter($rows, function ($row) use ($query) {
                $haystack = strtolower(implode(' ', [
                    (string) ($row['label'] ?? ''),
                    (string) ($row['series'] ?? ''),
                    (string) ($row['id'] ?? ''),
                ]));

                return str_contains($haystack, strtolower($query));
            }));
        }

        return view('admin.cricket.matches', [
            'rows' => $rows,
            'bucket' => $bucket,
            'query' => $query,
            'hidden' => $hidden,
            'feedErrors' => $result['errors'],
            'health' => $service->health(),
        ]);
    }

    public function toggleVisibility(int $match)
    {
        $ids = $this->hiddenIds();

        if (in_array($match, $ids, true)) {
            $ids = array_values(array_filter($ids, fn ($id) => $id !== $match));
            $message = 'Match '.$match.' is now visible in the public cricket feed.';
        } else {
            $ids[] = $match;
            $ids = array_values(array_unique($ids));
            $message = 'Match '.$match.' is now hidden from the public cricket feed.';
        }

        Setting::set('cricket_hidden_match_ids', json_encode($ids));
        Setting::flushCache();
        Cache::forget('cricbuzz:board');

        OperationLog::record('CRICKET VISIBILITY', strtoupper($message), auth()->user(), ['match_id' => $match]);

        return back()->with('success', $message);
    }

    public function refresh(int $match, CricbuzzCricketService $service)
    {
        if (! Cache::add('cricbuzz:admin-refresh:'.$match, 1, 15)) {
            return back()->with('error', 'Refresh is rate-limited. Try again in a few seconds.');
        }

        Cache::forget('cricbuzz:livescore:'.$match);
        Cache::forget('cricbuzz:miniscorecard:'.$match);
        Cache::forget('cricbuzz:scard:'.$match);
        Cache::forget('cricbuzz:row:'.$match);
        Cache::forget('cricbuzz:board');

        $result = $service->livescore($match, true);

        if (! $result['success']) {
            return back()->with('error', 'Refresh failed: '.($result['error'] ?? 'unknown error'));
        }

        OperationLog::record('CRICKET REFRESH', 'PROVIDER MATCH '.$match.' REFRESHED BY ADMIN', auth()->user(), ['match_id' => $match]);

        return back()->with('success', 'Match '.$match.' refreshed from Cricbuzz.');
    }

    public function health(CricbuzzCricketService $service)
    {
        $health = $service->health();
        $board = Cache::get('cricbuzz:board');

        return view('admin.cricket.health', [
            'health' => $health,
            'boardCached' => Cache::has('cricbuzz:board'),
            'boardGeneratedAt' => is_array($board) ? ($board['generated_at'] ?? null) : null,
            'discoveryCached' => [
                'live' => Cache::has('cricbuzz:discovery:live'),
                'upcoming' => Cache::has('cricbuzz:discovery:upcoming'),
                'recent' => Cache::has('cricbuzz:discovery:recent'),
            ],
            'boardTtl' => (int) setting('cricket_board_ttl', config('services.cricbuzz.board_ttl', 60)),
            'discoveryTtl' => (int) setting('cricket_discovery_ttl', 120),
            'throttle' => (int) config('services.cricbuzz.throttle_ms', 1600),
            'timeout' => (int) config('services.cricbuzz.timeout', 15),
        ]);
    }

    public function settings()
    {
        return view('admin.cricket.settings', [
            'values' => [
                'cricket_feed_enabled' => setting('cricket_feed_enabled', '1'),
                'cricket_board_enabled' => setting('cricket_board_enabled', '1'),
                'live_only_sport' => setting('live_only_sport', 'cricket'),
                'cricket_discovery_mode' => setting('cricket_discovery_mode', '1'),
                'cricket_fallback_ids' => setting('cricket_fallback_ids', ''),
                'cricket_stale_minutes' => setting('cricket_stale_minutes', '15'),
                'cricket_detail_limit' => setting('cricket_detail_limit', '6'),
                'cricket_discovery_ttl' => setting('cricket_discovery_ttl', '120'),
                'live_stale_hours' => setting('live_stale_hours', '6'),
                'legacy_opencricket_enabled' => setting('legacy_opencricket_enabled', '0'),
            ],
            'configured' => count(app(CricketBoard::class)->matchIds()),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'live_only_sport' => ['required', 'in:all,cricket'],
            'cricket_fallback_ids' => ['nullable', 'string', 'max:1000'],
            'cricket_stale_minutes' => ['required', 'integer', 'min:1', 'max:240'],
            'cricket_detail_limit' => ['required', 'integer', 'min:0', 'max:20'],
            'cricket_discovery_ttl' => ['required', 'integer', 'min:30', 'max:3600'],
            'live_stale_hours' => ['required', 'integer', 'min:0', 'max:168'],
        ]);

        foreach (['cricket_feed_enabled', 'cricket_board_enabled', 'cricket_discovery_mode', 'legacy_opencricket_enabled'] as $flag) {
            Setting::set($flag, $request->boolean($flag) ? '1' : '0');
        }

        Setting::set('live_only_sport', $data['live_only_sport']);
        Setting::set('cricket_fallback_ids', (string) ($data['cricket_fallback_ids'] ?? ''));
        Setting::set('cricket_stale_minutes', (string) (int) $data['cricket_stale_minutes']);
        Setting::set('cricket_detail_limit', (string) (int) $data['cricket_detail_limit']);
        Setting::set('cricket_discovery_ttl', (string) (int) $data['cricket_discovery_ttl']);
        Setting::set('live_stale_hours', (string) (int) $data['live_stale_hours']);
        Setting::flushCache();

        Cache::forget('cricbuzz:board');

        OperationLog::record('CRICKET SETTINGS', 'CRICKET FEED SETTINGS UPDATED BY ADMIN', auth()->user());

        return back()->with('success', 'Cricket settings saved.');
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, errors: array<int, array<string, mixed>>}
     */
    private function discoveryRows(CricbuzzCricketService $service): array
    {
        $rows = [];
        $errors = [];

        foreach (['live' => 'live', 'upcoming' => 'upcoming', 'recent' => 'completed'] as $type => $bucket) {
            $result = $service->discovery($type);

            if (! ($result['success'] ?? false) || ! is_array($result['data'] ?? null)) {
                if (! empty($result['error'])) {
                    $errors[] = ['type' => $type, 'message' => $result['error']];
                }

                continue;
            }

            foreach ($result['data'] as $match) {
                if (! is_array($match) || ! isset($match['id'])) {
                    continue;
                }

                $match['bucket'] = $bucket;
                $match['fetched_at'] = $result['fetched_at'] ?? null;
                $rows[(int) $match['id']] = $match;
            }
        }

        return ['rows' => array_values($rows), 'errors' => $errors];
    }

    /**
     * @param  array<string, mixed>  $board
     * @return array<int, array<string, mixed>>
     */
    private function fallbackRows(array $board): array
    {
        $rows = [];

        foreach (['live' => 'live', 'upcoming' => 'upcoming', 'results' => 'completed'] as $group => $bucket) {
            foreach (($board[$group] ?? []) as $row) {
                if (! is_array($row) || ! isset($row['id'])) {
                    continue;
                }

                $rows[] = [
                    'id' => (int) $row['id'],
                    'label' => $row['label'] ?? 'Match #'.$row['id'],
                    'series' => $row['series'] ?? null,
                    'format' => $row['format'] ?? null,
                    'status_raw' => $row['status_raw'] ?? ($row['status'] ?? ''),
                    'status_label' => $row['status_label'] ?? null,
                    'status_group' => $row['status_group'] ?? null,
                    'start_date' => $row['start_date'] ?? null,
                    'venue' => $row['venue'] ?? null,
                    'city' => null,
                    'state' => null,
                    'bucket' => $bucket,
                    'fetched_at' => $row['fetched_at'] ?? null,
                ];
            }
        }

        return $rows;
    }

    /**
     * @return array<int, int>
     */
    private function hiddenIds(): array
    {
        $decoded = json_decode((string) setting('cricket_hidden_match_ids', ''), true);

        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', $decoded)));
    }
}
