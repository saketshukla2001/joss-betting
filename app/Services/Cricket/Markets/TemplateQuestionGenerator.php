<?php

namespace App\Services\Cricket\Markets;

/**
 * Deterministic suggestions from the live score. This is the fallback when no
 * model is configured, and the shape every model suggestion must match.
 */
final class TemplateQuestionGenerator
{
    /**
     * @param  array<string, mixed>  $state
     * @return array<int, array<string, mixed>>
     */
    public function candidates(array $state): array
    {
        $enabled = QuestionTemplate::enabled();
        $clock = $state['clock'];
        $questions = [];

        if (in_array(QuestionTemplate::RUNS_BY_OVER, $enabled, true) && $clock['in_progress'] !== null) {
            $threshold = (int) (ceil(($state['runs'] + 1) / 10) * 10);

            if ($threshold > $state['runs'] && $threshold - $state['runs'] <= 30) {
                $questions[] = $this->make(QuestionTemplate::RUNS_BY_OVER, $state, $clock['in_progress'], $threshold, 'over_end', 'end_of_over_or_threshold', 'runs_at_boundary_gte_threshold');
            }
        }

        if (in_array(QuestionTemplate::OVER_RUNS, $enabled, true)) {
            $questions[] = $this->make(QuestionTemplate::OVER_RUNS, $state, $clock['next'], 10, 'over_end', 'end_of_target_over', 'over_runs_gte_threshold');
        }

        if (in_array(QuestionTemplate::WICKET_IN_OVER, $enabled, true)) {
            $questions[] = $this->make(QuestionTemplate::WICKET_IN_OVER, $state, $clock['next'], null, 'over_end', 'end_of_target_over', 'wicket_in_over');
        }

        if (! empty($state['ball_feed']) && is_array($state['balls'] ?? null)) {
            $questions[] = $this->make(QuestionTemplate::SIX_IN_OVER, $state, $clock['next'], null, 'over_end', 'end_of_target_over', 'six_in_ball_list');
            $questions[] = $this->make(QuestionTemplate::NEXT_BALLS, $state, $clock['next'], 8, 'ball_count', 'six_legal_balls', 'legal_ball_runs_gte_threshold');
        }

        return array_values(array_filter($questions));
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>|null
     */
    private function make(string $template, array $state, int $over, ?int $threshold, string $trigger, string $close, string $resolve): ?array
    {
        $parameters = [
            'innings' => (int) $state['innings'],
            'target_over' => $over,
            'target_ball' => null,
            'threshold' => $threshold,
            'batting_team' => $state['batting_team'],
            'trigger' => $trigger,
            'close_rule' => $close,
            'resolve_rule' => $resolve,
        ];

        $question = QuestionTemplate::question($template, $parameters);

        if ($question === '' || strlen($question) > 120) {
            return null;
        }

        return [
            'template' => $template,
            'question' => $question,
            'parameters' => $parameters,
        ];
    }
}
