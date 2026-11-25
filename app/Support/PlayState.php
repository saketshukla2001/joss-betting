<?php

namespace App\Support;

/**
 * Turns a provider status sentence into whether betting may run.
 * Stumps, breaks and interruptions are listed, but they are not active play.
 */
final class PlayState
{
    public const IN_PLAY = 'in_play';

    public const BREAK = 'break';

    public const STUMPS = 'stumps';

    public const INTERRUPTED = 'interrupted';

    public const COMPLETED = 'completed';

    public const UPCOMING = 'upcoming';

    public const UNKNOWN = 'unknown';

    public static function classify(?string $status): string
    {
        $key = strtolower(trim(preg_replace('/\s+/', ' ', (string) $status) ?? ''));

        if ($key === '') {
            return self::UNKNOWN;
        }

        if (str_contains($key, 'stump')) {
            return self::STUMPS;
        }

        if (str_contains($key, 'lunch')
            || str_contains($key, 'tea')
            || str_contains($key, 'dinner')
            || str_contains($key, 'drinks')
            || str_contains($key, 'innings break')
            || str_contains($key, 'session break')
            || $key === 'innings break') {
            return self::BREAK;
        }

        if (str_contains($key, 'abandon')
            || str_contains($key, 'interrupt')
            || str_contains($key, 'rain')
            || str_contains($key, 'bad light')
            || str_contains($key, 'delay')
            || in_array($key, ['int.', 'postp.', 'postponed', 'cancl.', 'cancelled'], true)) {
            return self::INTERRUPTED;
        }

        if (str_contains($key, 'won by')
            || str_contains($key, 'won the')
            || str_contains($key, 'match drawn')
            || str_contains($key, 'no result')
            || in_array($key, ['finished', 'complete', 'completed', 'drawn', 'tied', 'tie'], true)) {
            return self::COMPLETED;
        }

        if (in_array($key, ['ns', 'not started', 'scheduled', 'preview'], true)
            || str_starts_with($key, 'match starts')) {
            return self::UPCOMING;
        }

        if (str_contains($key, 'need')
            || str_contains($key, 'trail')
            || str_contains($key, 'lead')
            || str_contains($key, 'opt to')
            || str_contains($key, 'elected')
            || str_contains($key, 'in progress')
            || str_contains($key, 'session')
            || str_contains($key, 'innings')
            || $key === 'live') {
            return self::IN_PLAY;
        }

        return self::UNKNOWN;
    }

    public static function allowsBetting(string $play): bool
    {
        return $play === self::IN_PLAY;
    }
}
