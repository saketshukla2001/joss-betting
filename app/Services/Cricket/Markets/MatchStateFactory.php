<?php

namespace App\Services\Cricket\Markets;

use App\Support\PlayState;

/**
 * Builds a match state from a cached CricketBoard row. It does not call Cricbuzz.
 */
final class MatchStateFactory
{
    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>|null
     */
    public function fromBoardRow(array $row): ?array
    {
        $innings = is_array($row['innings'] ?? null) ? $row['innings'] : [];

        if ($innings === []) {
            return null;
        }

        $current = $innings[array_key_last($innings)];

        if (! is_array($current) || ! isset($current['runs'], $current['wickets'], $current['overs'])) {
            return null;
        }

        $clock = OverClock::parse($current['overs']);

        if ($clock === null) {
            return null;
        }

        $home = trim((string) ($row['team1'] ?? ''));
        $away = trim((string) ($row['team2'] ?? ''));

        if ($home === '' || $away === '') {
            [$home, $away] = $this->teams((string) ($row['label'] ?? ''));
        }
        $balls = $this->recentBalls($row, $current);
        $feed = is_array($row['balls'] ?? null) ? array_values($row['balls']) : null;
        $ballFeed = ($row['ball_feed'] ?? false) === true && is_array($feed);
        $status = (string) ($row['status_raw'] ?? $row['status'] ?? '');

        return [
            'provider' => (string) ($row['provider'] ?? 'cricbuzz'),
            'provider_match_id' => (string) ($row['id'] ?? ''),
            'label' => (string) ($row['label'] ?? 'Cricket match'),
            'home_team' => $home,
            'away_team' => $away,
            'league' => (string) ($row['series'] ?? '') ?: 'Cricket',
            'kind' => (string) ($row['kind'] ?? 'live'),
            'status_group' => (string) ($row['status_group'] ?? 'live'),
            'status_raw' => $status,
            'play_state' => (string) ($row['play_state'] ?? PlayState::classify($status)),
            'stale' => (bool) ($row['stale'] ?? false),
            'innings' => count($innings),
            'batting_team' => (string) ($current['team'] ?? $home),
            'runs' => (int) $current['runs'],
            'wickets' => (int) $current['wickets'],
            'overs' => (string) $current['overs'],
            'clock' => $clock,
            'recent_balls' => $balls,
            'ball_feed' => $ballFeed,
            'balls' => $ballFeed ? $feed : null,
            'fetched_at' => $row['fetched_at'] ?? null,
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function teams(string $label): array
    {
        $parts = preg_split('/\s+vs\s+/i', $label, 2) ?: [];
        $home = trim((string) ($parts[0] ?? ''));
        $away = trim((string) ($parts[1] ?? ''));

        return [
            $home !== '' ? $home : 'Home',
            $away !== '' ? $away : 'Away',
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $inning
     * @return array<int, string>|null
     */
    private function recentBalls(array $row, array $inning): ?array
    {
        foreach (['recent_balls', 'recentballs', 'last_balls'] as $key) {
            $value = $inning[$key] ?? $row[$key] ?? null;

            if (is_array($value) && $value !== []) {
                return array_values(array_map('strval', $value));
            }
        }

        return null;
    }
}
