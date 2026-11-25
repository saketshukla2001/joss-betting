<?php

namespace App\Services\Cricket\Markets;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Asks a configured model for template-shaped suggestions.
 * A missing key, a bad response, or any exception returns an empty list.
 * This class never writes markets, bets or wallet rows.
 */
final class AiQuestionSuggester
{
    /**
     * @param  array<string, mixed>  $state
     * @param  array<int, array<string, mixed>>  $allowed
     * @return array<int, array<string, mixed>>
     */
    public function suggest(array $state, array $allowed): array
    {
        $key = config('services.cricket_ai.key');

        if (! is_string($key) || trim($key) === '' || ! setting_enabled('cricket_ai_use_model', false)) {
            return [];
        }

        if ($allowed === []) {
            return [];
        }

        try {
            $response = Http::timeout((int) config('services.cricket_ai.timeout', 12))
                ->withToken($key)
                ->acceptJson()
                ->post((string) config('services.cricket_ai.url'), [
                    'model' => config('services.cricket_ai.model', 'gpt-4o-mini'),
                    'temperature' => 0,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => $this->systemPrompt()],
                        ['role' => 'user', 'content' => json_encode([
                            'match' => [
                                'label' => $state['label'],
                                'innings' => $state['innings'],
                                'batting_team' => $state['batting_team'],
                                'runs' => $state['runs'],
                                'wickets' => $state['wickets'],
                                'overs' => $state['overs'],
                                'recent_balls' => $state['recent_balls'],
                            ],
                            'allowed' => $allowed,
                        ], JSON_THROW_ON_ERROR)],
                    ],
                ]);
        } catch (Throwable $e) {
            Log::warning('Cricket question model request failed', ['error' => $e->getMessage()]);

            return [];
        }

        if (! $response->successful()) {
            Log::warning('Cricket question model returned an error', ['status' => $response->status()]);

            return [];
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            return [];
        }

        $decoded = json_decode($this->stripFence($content), true);

        if (! is_array($decoded)) {
            return [];
        }

        $rows = $decoded['questions'] ?? $decoded;

        if (! is_array($rows)) {
            return [];
        }

        $suggestions = [];

        foreach ($rows as $row) {
            if (! is_array($row) || ! isset($row['template'], $row['parameters']) || ! is_array($row['parameters'])) {
                continue;
            }

            $template = (string) $row['template'];
            $parameters = $row['parameters'];
            $parameters['target_ball'] = $parameters['target_ball'] ?? null;
            $question = QuestionTemplate::question($template, $parameters);

            if ($question === '') {
                continue;
            }

            $suggestions[] = [
                'template' => $template,
                'question' => $question,
                'parameters' => $parameters,
            ];
        }

        return $suggestions;
    }

    private function systemPrompt(): string
    {
        return 'You suggest live cricket Yes/No markets. Reply with JSON only: {"questions":[{"template":"","parameters":{}}]}. '
            .'Choose only from the allowed list. Copy innings, target_over, threshold, batting_team, trigger, close_rule and resolve_rule from an allowed item. '
            .'Do not invent templates. Do not settle markets. Do not include odds, winners or wallet instructions. '
            .'Omit a question when the allowed list has no safe version of it.';
    }

    private function stripFence(string $content): string
    {
        $content = trim($content);

        if (str_starts_with($content, '```')) {
            $content = preg_replace('/^```(?:json)?\s*|\s*```$/', '', $content) ?? $content;
        }

        return trim($content);
    }
}
