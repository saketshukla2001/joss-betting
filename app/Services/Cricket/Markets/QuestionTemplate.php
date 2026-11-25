<?php

namespace App\Services\Cricket\Markets;

/**
 * Approved question shapes. The model may only suggest these.
 * Ball-level templates stay unavailable until the live feed actually contains ball events.
 */
final class QuestionTemplate
{
    public const RUNS_BY_OVER = 'runs_by_over';

    public const OVER_RUNS = 'over_runs';

    public const WICKET_IN_OVER = 'wicket_in_over';

    public const SIX_IN_OVER = 'six_in_over';

    public const NEXT_BALLS = 'next_balls';

    /**
     * @return array<string, array{label: string, available: bool, reason: string|null}>
     */
    public static function catalog(): array
    {
        return [
            self::RUNS_BY_OVER => [
                'label' => 'Runs by the end of the current over',
                'available' => true,
                'reason' => null,
            ],
            self::OVER_RUNS => [
                'label' => 'Runs in the next over',
                'available' => true,
                'reason' => null,
            ],
            self::WICKET_IN_OVER => [
                'label' => 'Wicket in the next over',
                'available' => true,
                'reason' => null,
            ],
            self::SIX_IN_OVER => [
                'label' => 'Six in the next over',
                'available' => false,
                'reason' => 'Needs a complete ball list from the provider. Innings totals cannot prove a six.',
            ],
            self::NEXT_BALLS => [
                'label' => 'Runs in the next 6 legal balls',
                'available' => false,
                'reason' => 'Needs a sequential ball list. It is not settled from the innings total.',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public static function question(string $template, array $parameters): string
    {
        $over = OverClock::ordinal((int) $parameters['target_over']);
        $team = (string) $parameters['batting_team'];
        $threshold = (int) ($parameters['threshold'] ?? 0);

        return match ($template) {
            self::RUNS_BY_OVER => "Will {$team} score {$threshold} or more runs by the end of the {$over} over?",
            self::OVER_RUNS => "Will the {$over} over contain {$threshold} or more runs?",
            self::WICKET_IN_OVER => "Will a wicket fall in the {$over} over?",
            self::SIX_IN_OVER => "Will the {$over} over contain a six?",
            self::NEXT_BALLS => "Will the next 6 legal balls produce {$threshold} or more runs?",
            default => '',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function enabled(): array
    {
        $raw = (string) setting('cricket_ai_templates', 'runs_by_over,over_runs,wicket_in_over');
        $keys = array_values(array_filter(array_map('trim', explode(',', $raw))));
        $available = array_keys(array_filter(self::catalog(), fn (array $row) => $row['available']));

        return array_values(array_intersect($keys, $available));
    }
}
