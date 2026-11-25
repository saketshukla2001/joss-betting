<?php

namespace App\Services\Cricket\Markets;

final class CricketQuestionEngine
{
    public function __construct(
        private TemplateQuestionGenerator $templates,
        private AiQuestionSuggester $ai,
        private QuestionValidator $validator,
    ) {}

    /**
     * @param  array<string, mixed>  $state
     * @return array{source: string, questions: array<int, array<string, mixed>>, rejected: array<int, array{question: string, reason: string}>}
     */
    public function propose(array $state): array
    {
        $templateQuestions = $this->templates->candidates($state);
        $source = 'template';
        $pool = $templateQuestions;

        if (setting_enabled('cricket_ai_use_model', false)) {
            $suggested = $this->ai->suggest($state, $templateQuestions);
            $accepted = $this->keepValid($state, $suggested);

            if ($accepted !== []) {
                $source = 'ai';
                $pool = $accepted;
            }
        }

        $questions = [];
        $rejected = [];

        foreach ($pool as $candidate) {
            $reason = $this->validator->check($state, $candidate);

            if ($reason !== null) {
                $rejected[] = [
                    'question' => (string) ($candidate['question'] ?? $candidate['template'] ?? 'Question'),
                    'reason' => $reason,
                ];

                continue;
            }

            $candidate['question'] = QuestionTemplate::question($candidate['template'], $candidate['parameters']);
            $candidate['fingerprint'] = $this->validator->fingerprint($state, $candidate);
            $questions[] = $candidate;
        }

        return [
            'source' => $source,
            'questions' => $questions,
            'rejected' => $rejected,
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     * @param  array<int, array<string, mixed>>  $suggested
     * @return array<int, array<string, mixed>>
     */
    private function keepValid(array $state, array $suggested): array
    {
        $kept = [];

        foreach ($suggested as $candidate) {
            if ($this->validator->check($state, $candidate) === null) {
                $kept[] = $candidate;
            }
        }

        return $kept;
    }
}
