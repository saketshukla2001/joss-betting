<?php

namespace App\Services\Cricket\Providers;

use App\Services\Cricket\SportmonksCricketService;

/**
 * Maps Sportmonks livescores into the same row shape as the Cricbuzz board.
 * Ball-by-ball is reported only when the payload actually contains a ball list.
 */
final class SportmonksCricketProvider
{
    public function __construct(private SportmonksCricketService $sportmonks) {}

    public function configured(): bool
    {
        return $this->sportmonks->isConfigured();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function liveRows(): array
    {
        if (! $this->configured() || ! setting_enabled('cricket_sportmonks_enabled', false)) {
            return [];
        }

        $result = $this->sportmonks->livescores(30);

        if (! ($result['success'] ?? false) || ! is_array($result['data'] ?? null)) {
            return [];
        }

        $rows = [];

        foreach ($result['data'] as $fixture) {
            if (! is_array($fixture)) {
                continue;
            }

            $presented = $this->sportmonks->presentFixture($fixture);

            if (($presented['id'] ?? 0) <= 0 || ! ($presented['live'] ?? false)) {
                continue;
            }

            $scores = is_array($presented['scores'] ?? null) ? $presented['scores'] : [];
            $innings = [];

            foreach ($scores as $score) {
                if (! is_array($score) || ! isset($score['runs'], $score['wickets'], $score['overs'])) {
                    continue;
                }

                $innings[] = [
                    'team' => $score['team'],
                    'runs' => $score['runs'],
                    'wickets' => $score['wickets'],
                    'overs' => $score['overs'],
                ];
            }

            $balls = is_array($fixture['balls'] ?? null) ? $fixture['balls'] : null;

            $rows[] = [
                'id' => (string) $presented['id'],
                'provider' => 'sportmonks',
                'label' => $presented['name'],
                'team1' => $presented['local_name'],
                'team2' => $presented['visitor_name'],
                'kind' => 'live',
                'status_group' => 'live',
                'status' => $presented['status'],
                'status_raw' => (string) $presented['status'],
                'status_label' => $presented['status_label'],
                'series' => $presented['league'] ?: 'Cricket',
                'stale' => false,
                'innings' => $innings,
                'balls' => $balls,
                'ball_feed' => is_array($balls) && $balls !== [],
                'fetched_at' => now()->toIso8601String(),
                'error' => $innings === [] ? 'Sportmonks did not include an overs score for this match.' : null,
            ];
        }

        return $rows;
    }
}
