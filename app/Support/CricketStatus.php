<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Single place that turns a provider status string into a stable state.
 *
 * Cricbuzz uses short labels (NS, Aban., Postp., Cancl., Int., Stumps ...) and
 * long result sentences ("Odisha won by 13 runs"), so every surface — board,
 * public detail page, admin screens — must use this normaliser instead of
 * re-parsing strings locally.
 */
final class CricketStatus
{
    public const LIVE = 'live';

    public const UPCOMING = 'upcoming';

    public const COMPLETED = 'completed';

    public const OTHER = 'other';

    /**
     * @return array{raw: string, group: string, label: string}
     */
    public static function normalize(?string $raw): array
    {
        $raw = trim((string) $raw);
        $key = strtolower(preg_replace('/\s+/', ' ', $raw) ?? '');

        return [
            'raw' => $raw,
            'group' => self::group($key, $raw),
            'label' => self::label($key, $raw),
        ];
    }

    private static function group(string $key, string $raw): string
    {
        if ($key === '' || in_array($key, ['ns', 'not started', 'scheduled', 'starts soon'], true)) {
            return self::UPCOMING;
        }

        if (in_array($key, ['postp.', 'postponed', 'delayed'], true)
            || str_starts_with($key, 'match starts at')) {
            return self::UPCOMING;
        }

        if (in_array($key, ['finished', 'complete', 'completed', 'drawn', 'tied', 'tie', 'no result', 'aban.', 'abandoned', 'cancl.', 'cancelled'], true)
            || str_contains($key, 'won by')
            || str_contains($key, 'won the')
            || str_contains($key, 'draw')
            || str_contains($key, 'tied')) {
            return self::COMPLETED;
        }

        if (str_contains($key, 'innings')
            || str_contains($key, 'break')
            || str_contains($key, 'stump')
            || in_array($key, ['int.', 'interrupted', 'tea', 'lunch', 'dinner'], true)
            || str_contains($key, 'need')
            || str_contains($key, 'trail')
            || str_contains($key, 'lead')
            || str_contains($key, 'opt to bat')
            || str_contains($key, 'elected')
            || str_contains($key, 'in progress')) {
            return self::LIVE;
        }

        // A result sentence we do not recognise but that clearly describes an outcome.
        if (preg_match('/\b(won|draw|abandon|no result|cancel|postpon)/i', $raw)) {
            return self::COMPLETED;
        }

        return self::OTHER;
    }

    private static function label(string $key, string $raw): string
    {
        return match (true) {
            $key === '' => 'Unknown',
            $key === 'ns', $key === 'not started' => 'Not started',
            $key === 'postp.', $key === 'postponed' => 'Postponed',
            $key === 'cancl.', $key === 'cancelled' => 'Cancelled',
            $key === 'aban.', $key === 'abandoned' => 'Abandoned',
            $key === 'int.', $key === 'interrupted' => 'Interrupted',
            $key === 'delayed' => 'Delayed',
            $key === 'finished', $key === 'complete', $key === 'completed' => 'Finished',
            $key === 'drawn' => 'Drawn',
            $key === 'tied', $key === 'tie' => 'Tied',
            $key === 'no result' => 'No result',
            str_starts_with($key, 'stump') => 'Stumps',
            str_starts_with($key, 'match starts at') => 'Not started',
            str_contains($key, 'postpon') => 'Postponed',
            str_contains($key, 'cancel') => 'Cancelled',
            str_contains($key, 'abandon') => 'Abandoned',
            str_contains($key, 'no result') => 'No result',
            str_contains($key, 'draw') => 'Drawn',
            str_contains($key, 'tied'), str_contains($key, 'tie') => 'Tied',
            str_contains($key, 'won by'), str_contains($key, 'won the') => 'Completed',
            str_contains($key, 'need'), str_contains($key, 'trail'), str_contains($key, 'lead'), str_contains($key, 'opt to bat') => 'Live',
            default => $raw !== '' ? ucfirst($raw) : 'Unknown',
        };
    }

    public static function isStale(?string $isoTime, ?int $minutes = null): bool
    {
        if (! $isoTime) {
            return true;
        }

        try {
            $at = Carbon::parse($isoTime);
        } catch (\Throwable) {
            return true;
        }

        $limit = max(1, $minutes ?? (int) setting('cricket_stale_minutes', 15));

        return $at->lt(now()->subMinutes($limit));
    }
}
