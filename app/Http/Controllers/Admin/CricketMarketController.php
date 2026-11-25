<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CricketMarketEvent;
use App\Models\CricketMarketRule;
use App\Models\CricketMatchControl;
use App\Models\CricketQuestionCandidate;
use App\Models\Setting;
use App\Services\Cricket\CricbuzzCricketService;
use App\Services\Cricket\CricketBoard;
use App\Services\Cricket\Demo\DemoMatchSimulator;
use App\Services\Cricket\Markets\CricketMarketCycle;
use App\Services\Cricket\Markets\MarketLifecycle;
use App\Services\Cricket\Markets\MarketPublisher;
use App\Services\Cricket\Markets\MatchStateFactory;
use App\Services\Cricket\Markets\QuestionTemplate;
use App\Services\Cricket\Markets\QuestionValidator;
use App\Services\Cricket\SportmonksCricketService;
use App\Support\PlayState;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CricketMarketController extends Controller
{
    public function index(CricketBoard $board, MatchStateFactory $states)
    {
        $payload = $this->boardPayload($board);
        $rows = is_array($payload['live'] ?? null) ? $payload['live'] : [];

        foreach (app(DemoMatchSimulator::class)->boardRows() as $demoRow) {
            if (($demoRow['kind'] ?? '') === 'live') {
                $rows[] = $demoRow;
            }
        }
        $ids = [];

        foreach ($rows as $row) {
            if (is_array($row) && isset($row['id']) && (string) $row['id'] !== '') {
                $ids[] = (string) $row['id'];
            }
        }

        $ids = array_values(array_unique($ids));

        $controls = CricketMatchControl::query()
            ->whereIn('provider_match_id', $ids)
            ->pluck('generation_enabled', 'provider_match_id');

        $questions = CricketQuestionCandidate::query()
            ->whereIn('provider_match_id', $ids)
            ->latest('id')
            ->get()
            ->groupBy('provider_match_id');

        $matchRules = CricketMarketRule::with(['market.selections'])
            ->whereIn('provider_match_id', $ids)
            ->latest('id')
            ->get()
            ->groupBy('provider_match_id');

        $live = [];
        $seen = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $id = (string) ($row['id'] ?? '');

            if ($id === '' || isset($seen[$id])) {
                continue;
            }

            $seen[$id] = true;
            $state = $states->fromBoardRow($row);
            $innings = is_array($row['innings'] ?? null) ? $row['innings'] : [];
            $current = $innings === [] ? null : $innings[array_key_last($innings)];
            $current = is_array($current) ? $current : null;
            $enabled = ! $controls->has($id) || (bool) $controls->get($id);
            [$team1, $team2] = $this->teams($row, $state);
            $status = (string) ($row['status_raw'] ?? $row['status'] ?? $row['status_label'] ?? $row['provider_state'] ?? '');

            $live[] = [
                'id' => $id,
                'provider' => (string) ($row['provider'] ?? 'cricbuzz'),
                'label' => (string) ($row['label'] ?? ($team1.' vs '.$team2)),
                'team1' => $team1,
                'team2' => $team2,
                'status' => $status,
                'play' => (string) ($row['play_state'] ?? ($state['play_state'] ?? PlayState::classify($status))),
                'error' => $row['error'] ?? null,
                'ball_feed' => (bool) ($state['ball_feed'] ?? false),
                'innings' => $state['innings'] ?? count($innings),
                'batting_team' => $state['batting_team'] ?? ($current['team'] ?? null),
                'runs' => $state['runs'] ?? (isset($current['runs']) ? (int) $current['runs'] : null),
                'wickets' => $state['wickets'] ?? (isset($current['wickets']) ? (int) $current['wickets'] : null),
                'overs' => $state['overs'] ?? (isset($current['overs']) ? (string) $current['overs'] : null),
                'stale' => (bool) ($row['stale'] ?? false),
                'enabled' => $enabled,
                'questions' => ($questions[$id] ?? collect())->map(fn (CricketQuestionCandidate $candidate) => [
                    'question' => $candidate->question,
                    'status' => $candidate->status,
                    'template' => $candidate->template,
                    'target_over' => $candidate->parameters['target_over'] ?? null,
                ])->all(),
                'markets' => ($matchRules[$id] ?? collect())->map(fn (CricketMarketRule $rule) => [
                    'name' => $rule->market?->name,
                    'status' => $rule->market?->status,
                    'phase' => $rule->phase,
                ])->all(),
            ];
        }

        return view('admin.cricket.markets', [
            'live' => $live,
            'generatedAt' => $payload['generated_at'] ?? null,
            'boardMissing' => (bool) ($payload['missing'] ?? false),
            'boardErrors' => is_array($payload['errors'] ?? null) ? $payload['errors'] : [],
            'rules' => CricketMarketRule::with(['market.selections', 'market.match'])->latest('id')->limit(30)->get(),
            'candidates' => CricketQuestionCandidate::latest('id')->limit(30)->get(),
            'events' => CricketMarketEvent::latest('id')->limit(40)->get(),
            'catalog' => QuestionTemplate::catalog(),
            'enabledTemplates' => QuestionTemplate::enabled(),
            'configured' => is_string(config('services.cricket_ai.key')) && config('services.cricket_ai.key') !== '',
            'generationEnabled' => setting_enabled('cricket_ai_markets_enabled', false),
            'providers' => [
                'cricbuzz' => app(CricbuzzCricketService::class)->health(),
                'sportmonks' => [
                    'configured' => app(SportmonksCricketService::class)->isConfigured(),
                    'ball_by_ball' => false,
                ],
            ],
        ]);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'generation_seconds' => ['required', 'integer', 'min:30', 'max:3600'],
            'yes_odds' => ['required', 'numeric', 'min:1.01', 'max:20'],
            'no_odds' => ['required', 'numeric', 'min:1.01', 'max:20'],
            'templates' => ['nullable', 'array'],
            'templates.*' => ['string'],
        ]);

        $available = array_keys(array_filter(QuestionTemplate::catalog(), fn (array $row) => $row['available']));
        $templates = array_values(array_intersect($data['templates'] ?? [], $available));

        Setting::set('cricket_ai_markets_enabled', $request->boolean('enabled') ? '1' : '0');
        Setting::set('cricket_ai_approval_required', $request->boolean('approval_required') ? '1' : '0');
        Setting::set('cricket_ai_use_model', $request->boolean('use_model') ? '1' : '0');
        Setting::set('cricket_ai_generation_seconds', (string) $data['generation_seconds']);
        Setting::set('cricket_ai_yes_odds', number_format((float) $data['yes_odds'], 2, '.', ''));
        Setting::set('cricket_ai_no_odds', number_format((float) $data['no_odds'], 2, '.', ''));
        Setting::set('cricket_ai_templates', implode(',', $templates));

        return back()->with('success', 'Cricket market settings saved.');
    }

    public function toggleMatch(Request $request, string $match)
    {
        $control = CricketMatchControl::firstOrNew(['provider_match_id' => $match]);
        $control->generation_enabled = ! $control->exists ? false : ! $control->generation_enabled;
        $control->save();

        return back()->with('success', $control->generation_enabled
            ? 'Question generation is on for this match.'
            : 'Question generation is off for this match.');
    }

    public function run(CricketMarketCycle $cycle)
    {
        $summary = $cycle->run();

        return back()->with('success', 'Market pass finished. Opened '.$summary['generated'].', held '.$summary['held'].', settled '.$summary['settled'].', voided '.$summary['voided'].'.');
    }

    public function approve(Request $request, CricketQuestionCandidate $candidate, CricketBoard $board, MatchStateFactory $states, QuestionValidator $validator, MarketPublisher $publisher)
    {
        if ($candidate->status !== 'pending') {
            throw ValidationException::withMessages(['candidate' => 'Only a waiting question can be approved.']);
        }

        $state = $this->stateFor($board, $states, $candidate->provider_match_id);

        if ($state === null) {
            throw ValidationException::withMessages(['candidate' => 'This match has no live score to validate against.']);
        }

        $suggestion = [
            'template' => $candidate->template,
            'question' => $candidate->question,
            'parameters' => $candidate->parameters,
            'fingerprint' => $candidate->fingerprint,
        ];

        $reason = $validator->check($state, $suggestion, true);

        if ($reason !== null) {
            $candidate->update(['status' => 'rejected', 'rejection_reason' => $reason]);
            $publisher->event(null, 'rejected', $reason, ['question' => $candidate->question], $candidate->provider_match_id);

            return back()->withErrors(['candidate' => 'Not published: '.$reason]);
        }

        $rule = $publisher->publish($state, $suggestion, $candidate->source);
        $candidate->update(['status' => 'published', 'market_id' => $rule->market_id]);

        return back()->with('success', 'Question approved and opened on the existing sportsbook.');
    }

    public function reject(Request $request, CricketQuestionCandidate $candidate, MarketPublisher $publisher)
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:180']]);

        if ($candidate->status !== 'pending') {
            return back()->withErrors(['candidate' => 'This question is no longer waiting.']);
        }

        $reason = $data['reason'] ?? 'Rejected by an administrator.';
        $candidate->update(['status' => 'rejected', 'rejection_reason' => $reason]);
        $publisher->event(null, 'rejected', $reason, ['question' => $candidate->question], $candidate->provider_match_id);

        return back()->with('success', 'Question rejected. No market was created.');
    }

    public function suspend(CricketMarketRule $rule, MarketPublisher $publisher)
    {
        $market = $rule->market;

        if (! $market || ! in_array($market->status, ['open'], true)) {
            return back()->withErrors(['market' => 'Only an open market can be suspended.']);
        }

        $market->update(['status' => 'suspended']);
        $publisher->event($rule, 'suspended', 'Suspended by an administrator.');

        return back()->with('success', 'Market suspended. Open bets stay pending.');
    }

    public function reopen(CricketMarketRule $rule, MarketPublisher $publisher)
    {
        $market = $rule->market;

        if (! $market || $rule->phase !== 'open' || $market->status !== 'suspended') {
            return back()->withErrors(['market' => 'This market is closed for a result and cannot be reopened.']);
        }

        $market->update(['status' => 'open']);
        $publisher->event($rule, 'reopened', 'Reopened by an administrator.');

        return back()->with('success', 'Market reopened.');
    }

    public function void(CricketMarketRule $rule, MarketLifecycle $lifecycle)
    {
        $market = $rule->market;

        if (! $market || in_array($rule->phase, ['settled', 'void'], true)) {
            return back()->withErrors(['market' => 'This market is already finished.']);
        }

        $lifecycle->forceVoid($rule, 'Voided by an administrator. Open stakes were refunded.');

        return back()->with('success', 'Market voided and open stakes refunded through the existing settlement service.');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function stateFor(CricketBoard $board, MatchStateFactory $states, string $providerMatchId): ?array
    {
        $payload = $this->boardPayload($board);

        foreach (($payload['live'] ?? []) as $row) {
            if (! is_array($row) || (string) ($row['id'] ?? '') !== $providerMatchId) {
                continue;
            }

            return $states->fromBoardRow($row);
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function boardPayload(CricketBoard $board): array
    {
        return $board->rememberedOrLoad();
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>|null  $state
     * @return array{0: string, 1: string}
     */
    private function teams(array $row, ?array $state): array
    {
        $team1 = trim((string) ($row['team1'] ?? (is_array($state) ? ($state['home_team'] ?? '') : '')));
        $team2 = trim((string) ($row['team2'] ?? (is_array($state) ? ($state['away_team'] ?? '') : '')));

        if ($team1 !== '' && $team2 !== '') {
            return [$team1, $team2];
        }

        $parts = preg_split('/\s+vs\s+/i', (string) ($row['label'] ?? ''), 2) ?: [];
        $team1 = trim((string) ($parts[0] ?? ''));
        $team2 = trim((string) ($parts[1] ?? ''));

        return [
            $team1 !== '' ? $team1 : 'Team 1',
            $team2 !== '' ? $team2 : 'Team 2',
        ];
    }
}
