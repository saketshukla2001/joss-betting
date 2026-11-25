<?php

namespace App\Services\Cricket\Markets;

use App\Models\CricketMarketRule;
use App\Models\CricketQuestionCandidate;
use App\Support\PlayState;

/**
 * Rejects a suggestion before any market row is written.
 * Returns an error string, or null when the suggestion may be published.
 */
final class QuestionValidator
{
    /**
     * @param  array<string, mixed>  $state
     * @param  array<string, mixed>  $candidate
     */
    public function check(array $state, array $candidate, bool $ignorePending = false): ?string
    {
        $template = (string) ($candidate['template'] ?? '');
        $catalog = QuestionTemplate::catalog();

        if (! isset($catalog[$template])) {
            return 'Unknown question template.';
        }

        $ballTemplate = in_array($template, [QuestionTemplate::SIX_IN_OVER, QuestionTemplate::NEXT_BALLS], true);

        if ($ballTemplate && empty($state['ball_feed'])) {
            return $catalog[$template]['reason'] ?? 'This template needs a real ball list.';
        }

        if (! $catalog[$template]['available'] && ! $ballTemplate) {
            return $catalog[$template]['reason'] ?? 'This template cannot be settled from the current feed.';
        }

        if (! $ballTemplate && ! in_array($template, QuestionTemplate::enabled(), true)) {
            return 'This template is turned off.';
        }

        if (($state['kind'] ?? '') !== 'live' || ($state['status_group'] ?? '') !== 'live') {
            return 'The match is not live.';
        }

        if (! PlayState::allowsBetting((string) ($state['play_state'] ?? PlayState::classify((string) ($state['status_raw'] ?? ''))))) {
            return 'The match is not in active play.';
        }

        if (! empty($state['stale'])) {
            return 'The live score is stale.';
        }

        if ($this->interrupted($state)) {
            return 'The match is interrupted.';
        }

        $parameters = is_array($candidate['parameters'] ?? null) ? $candidate['parameters'] : [];
        $clock = $state['clock'] ?? null;

        if (! is_array($clock)) {
            return 'Overs are missing.';
        }

        if ((int) ($parameters['innings'] ?? 0) !== (int) $state['innings']) {
            return 'The innings does not match the live score.';
        }

        if ((string) ($parameters['batting_team'] ?? '') !== (string) $state['batting_team']) {
            return 'The batting side does not match the live score.';
        }

        if ((string) ($state['batting_team'] ?? '') === '') {
            return 'The batting side is missing.';
        }

        $over = (int) ($parameters['target_over'] ?? 0);

        if ($over < 1 || $over > 500) {
            return 'The target over is not valid.';
        }

        $error = match ($template) {
            QuestionTemplate::RUNS_BY_OVER => $this->runsByOver($state, $clock, $parameters, $over),
            QuestionTemplate::OVER_RUNS => $this->nextOver($clock, $parameters, $over, true),
            QuestionTemplate::WICKET_IN_OVER, QuestionTemplate::SIX_IN_OVER => $this->nextOver($clock, $parameters, $over, false),
            QuestionTemplate::NEXT_BALLS => $this->nextOver($clock, $parameters, $over, true),
            default => 'This template cannot be settled from the current feed.',
        };

        if ($error !== null) {
            return $error;
        }

        $fingerprint = $this->fingerprint($state, $candidate);

        if (CricketMarketRule::where('fingerprint', $fingerprint)->exists()) {
            return 'This market already exists.';
        }

        if (! $ignorePending && CricketQuestionCandidate::where('fingerprint', $fingerprint)->exists()) {
            return 'This question was already decided.';
        }

        $rendered = QuestionTemplate::question($template, $parameters);

        if ($rendered === '' || $rendered !== ($candidate['question'] ?? $rendered)) {
            $candidate['question'] = $rendered;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $state
     * @param  array<string, mixed>  $candidate
     */
    public function fingerprint(array $state, array $candidate): string
    {
        $parameters = $candidate['parameters'];
        $threshold = $parameters['threshold'] === null ? 'event' : (string) $parameters['threshold'];

        return implode('|', [
            $state['provider_match_id'],
            $candidate['template'],
            $parameters['innings'],
            $parameters['target_over'],
            $threshold,
            $parameters['batting_team'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public function interrupted(array $state): bool
    {
        $play = (string) ($state['play_state'] ?? PlayState::classify((string) ($state['status_raw'] ?? '')));

        return ! in_array($play, [PlayState::IN_PLAY, PlayState::COMPLETED], true);
    }

    /**
     * @param  array<string, mixed>  $state
     * @param  array<string, mixed>  $clock
     * @param  array<string, mixed>  $parameters
     */
    private function runsByOver(array $state, array $clock, array $parameters, int $over): ?string
    {
        if ($clock['in_progress'] === null || (int) $clock['in_progress'] !== $over) {
            return 'That over is not the over currently being bowled.';
        }

        $threshold = (int) ($parameters['threshold'] ?? 0);

        if ($threshold <= (int) $state['runs']) {
            return 'The score has already reached this target.';
        }

        if ($threshold - (int) $state['runs'] > 30) {
            return 'The target is too far from the current score to be a live over market.';
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $clock
     * @param  array<string, mixed>  $parameters
     */
    private function nextOver(array $clock, array $parameters, int $over, bool $needsThreshold): ?string
    {
        if ((int) $clock['next'] !== $over) {
            return 'That over is not the next over.';
        }

        if ($clock['completed'] > $over - 1 || ($clock['completed'] === $over - 1 && $clock['balls'] > 0)) {
            return 'That over has already started.';
        }

        if (! $needsThreshold) {
            return null;
        }

        $threshold = (int) ($parameters['threshold'] ?? 0);

        if ($threshold < 6 || $threshold > 18) {
            return 'The over-run target must be between 6 and 18.';
        }

        return null;
    }
}
