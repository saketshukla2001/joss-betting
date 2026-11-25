<?php

namespace App\Services\Cricket\Markets;

/**
 * Cricket overs notation: "3.4" is 3 completed overs and 4 balls of over 4.
 * "4" and "4.0" mean over 4 has just finished. Cricbuzz writes the sixth
 * ball as ".6", so "16.6" is the same boundary as "17.0".
 */
final class OverClock
{
    /**
     * @return array{completed: int, balls: int, in_progress: int|null, next: int}|null
     */
    public static function parse(mixed $overs): ?array
    {
        if ($overs === null || $overs === '') {
            return null;
        }

        if (is_int($overs) || (is_float($overs) && floor($overs) === $overs)) {
            $raw = ((int) $overs).'.0';
        } elseif (is_float($overs)) {
            $raw = number_format($overs, 1, '.', '');
        } else {
            $raw = trim((string) $overs);
        }

        if (! preg_match('/^(\d+)(?:\.(\d))?$/', $raw, $match)) {
            return null;
        }

        $completed = (int) $match[1];
        $balls = isset($match[2]) ? (int) $match[2] : 0;

        if ($balls === 6) {
            $completed++;
            $balls = 0;
        }

        if ($balls > 6) {
            return null;
        }

        return [
            'completed' => $completed,
            'balls' => $balls,
            'in_progress' => $balls > 0 ? $completed + 1 : null,
            'next' => $balls > 0 ? $completed + 2 : $completed + 1,
        ];
    }

    public static function ordinal(int $over): string
    {
        $mod = $over % 100;

        if ($mod >= 11 && $mod <= 13) {
            return $over.'th';
        }

        return $over.match ($over % 10) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };
    }
}
