@extends('layouts.admin')

@section('title', 'AI cricket markets')
@section('page-title', 'AI cricket markets')
@section('page-subtitle', 'Suggestions are validated here. Settlement still uses the existing bet service.')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>Live cricket markets</h1>
            <p>Questions come from the cached Cricbuzz board. Yes and No are normal selections. This server has no WebSocket process, so the sportsbook polls <code>/api/cricket-markets</code> the same way it already polls odds.</p>
        </div>
        <div class="actions-row">
            <form method="POST" action="{{ route('admin.cricket.markets.run') }}">
                @csrf
                <button class="btn btn--primary btn--sm" type="submit">Run one pass</button>
            </form>
            <a class="btn btn--ghost btn--sm" href="{{ route('admin.cricket.demo') }}">Live demo</a>
            <a class="btn btn--ghost btn--sm" href="{{ route('admin.cricket.index') }}">Cricket feed</a>
        </div>
    </div>

    <form class="admin-card mb-20" method="POST" action="{{ route('admin.cricket.markets.settings') }}">
        @csrf
        <div class="admin-card__head"><h3>Generation</h3></div>
        <div class="admin-card__body">
            <div class="form-grid">
                <label class="check"><input type="checkbox" name="enabled" value="1" @checked(setting_enabled('cricket_ai_markets_enabled', false))> <span>Generate markets for live matches</span></label>
                <label class="check"><input type="checkbox" name="approval_required" value="1" @checked(setting_enabled('cricket_ai_approval_required', true))> <span>Hold questions until an admin approves them</span></label>
                <label class="check"><input type="checkbox" name="use_model" value="1" @checked(setting_enabled('cricket_ai_use_model', false)) @disabled(! $configured)> <span>Ask the configured model for suggestions{{ $configured ? '' : ' (no API key set)' }}</span></label>
            </div>
            <div class="form-grid mt-14">
                <div class="field">
                    <label for="generation_seconds">Minimum seconds between new questions</label>
                    <input class="input" id="generation_seconds" name="generation_seconds" type="number" min="30" max="3600" value="{{ setting('cricket_ai_generation_seconds', 60) }}">
                </div>
                <div class="field">
                    <label for="yes_odds">Yes odds</label>
                    <input class="input" id="yes_odds" name="yes_odds" type="number" min="1.01" max="20" step="0.01" value="{{ setting('cricket_ai_yes_odds', '1.85') }}">
                </div>
                <div class="field">
                    <label for="no_odds">No odds</label>
                    <input class="input" id="no_odds" name="no_odds" type="number" min="1.01" max="20" step="0.01" value="{{ setting('cricket_ai_no_odds', '1.85') }}">
                </div>
            </div>
            <div class="mt-14">
                @foreach($catalog as $key => $template)
                    <label class="check" style="display:flex;margin-bottom:8px">
                        <input type="checkbox" name="templates[]" value="{{ $key }}" @checked(in_array($key, $enabledTemplates, true)) @disabled(! $template['available'])>
                        <span>{{ $template['label'] }}@if(! $template['available']) — {{ $template['reason'] }}@endif</span>
                    </label>
                @endforeach
            </div>
            <button class="btn btn--primary btn--sm mt-14" type="submit">Save settings</button>
        </div>
    </form>

    <div class="admin-card mb-20">
        <div class="admin-card__head"><h3>Providers</h3></div>
        <div class="admin-card__body">
            <p>Cricbuzz {{ ($providers['cricbuzz']['configured'] ?? false) ? 'is configured' : 'is not configured' }}. Rate-limit responses recorded: {{ $providers['cricbuzz']['rate_limits'] ?? 0 }}. Last failure: {{ $providers['cricbuzz']['last_failure']['status'] ?? 'none' }}.</p>
            <p>Sportmonks {{ ($providers['sportmonks']['configured'] ?? false) ? 'is configured' : 'is not configured' }}. Its current client supplies innings totals, not a ball-by-ball feed, unless a fixture payload includes a ball list.</p>
            <p>Six and next-ball markets stay off until a real ball list is present. Demo matches are labelled LIVE DEMO and cannot take real-money bets.</p>
        </div>
    </div>

    <div class="admin-card mb-20">
        <div class="admin-card__head">
            <h3>Live matches</h3>
            <span>{{ count($live) }}@if($generatedAt) · cached {{ $generatedAt }}@endif</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Match</th><th>Score</th><th>Status</th><th>Generation</th></tr></thead>
                <tbody>
                    @forelse($live as $row)
                        <tr>
                            <td>
                                <strong>{{ $row['label'] }}</strong>
                                <div class="text-muted">{{ $row['team1'] }} vs {{ $row['team2'] }}</div>
                                <div class="text-muted">{{ $row['provider'] === 'demo' ? 'LIVE DEMO' : 'REAL' }} · {{ $row['provider'] }} · {{ $row['play'] }} · {{ $row['ball_feed'] ? 'ball feed' : 'no ball feed' }}</div>
                                <div class="text-muted">Provider {{ $row['id'] }} · innings {{ $row['innings'] ?: '—' }}</div>
                                @if($row['error'])
                                    <div class="text-muted">{{ $row['error'] }}</div>
                                @endif
                                @forelse($row['questions'] as $question)
                                    <div class="text-muted">{{ $question['status'] }}: {{ $question['question'] }}@if($question['target_over']) · over {{ $question['target_over'] }}@endif</div>
                                @empty
                                    <div class="text-muted">No questions for this match</div>
                                @endforelse
                                @foreach($row['markets'] as $market)
                                    <div class="text-muted">Market {{ $market['status'] ?? '—' }} / {{ $market['phase'] }}: {{ $market['name'] }}</div>
                                @endforeach
                            </td>
                            <td>
                                @if($row['runs'] !== null && $row['wickets'] !== null && $row['overs'])
                                    {{ $row['batting_team'] }} {{ $row['runs'] }}/{{ $row['wickets'] }} ({{ $row['overs'] }})
                                    @if($row['stale'])<div class="text-muted">Cached score is stale</div>@endif
                                @else
                                    <span class="text-muted">Score not in the cached detail yet</span>
                                    @if($row['error'])<div class="text-muted">{{ $row['error'] }}</div>@endif
                                @endif
                            </td>
                            <td>{{ $row['status'] !== '' ? $row['status'] : 'Live' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.cricket.markets.toggle', $row['id']) }}">
                                    @csrf
                                    <button class="btn btn--ghost btn--sm" type="submit">{{ $row['enabled'] ? 'On for this match' : 'Off for this match' }}</button>
                                </form>
                                @unless($generationEnabled)
                                    <div class="text-muted">Global generation is off</div>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty">
                                    <strong>No live Cricbuzz matches in the board cache</strong>
                                    @foreach($boardErrors as $error)
                                        @if(!empty($error['message']))
                                            <div class="text-muted">{{ $error['message'] }}</div>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card mb-20">
        <div class="admin-card__head"><h3>Waiting questions</h3></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Question</th><th>Source</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($candidates as $candidate)
                        <tr>
                            <td>
                                <strong>{{ $candidate->question }}</strong>
                                <div class="text-muted">{{ $candidate->template }} · over {{ $candidate->parameters['target_over'] ?? '—' }} · match {{ $candidate->provider_match_id }}</div>
                            </td>
                            <td>{{ $candidate->source }}</td>
                            <td>{{ $candidate->status }}@if($candidate->rejection_reason)<div class="text-muted">{{ $candidate->rejection_reason }}</div>@endif</td>
                            <td>
                                @if($candidate->status === 'pending')
                                    <form method="POST" action="{{ route('admin.cricket.markets.approve', $candidate) }}" style="display:inline">@csrf<button class="btn btn--primary btn--sm" type="submit">Approve</button></form>
                                    <form method="POST" action="{{ route('admin.cricket.markets.reject', $candidate) }}" style="display:inline">@csrf<button class="btn btn--ghost btn--sm" type="submit">Reject</button></form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty"><strong>No questions yet</strong></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card mb-20">
        <div class="admin-card__head"><h3>Markets</h3></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Question</th><th>Yes / No</th><th>Market</th><th>Phase</th><th></th></tr></thead>
                <tbody>
                    @forelse($rules as $rule)
                        @php
                            $yes = $rule->market?->selections->firstWhere('result_key', 'yes');
                            $no = $rule->market?->selections->firstWhere('result_key', 'no');
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $rule->market?->name }}</strong>
                                <div class="text-muted">{{ $rule->source }} · {{ $rule->template }} · innings {{ $rule->innings }} · over {{ $rule->target_over }}</div>
                            </td>
                            <td class="mono">{{ $yes ? number_format($yes->odds, 2) : '—' }} / {{ $no ? number_format($no->odds, 2) : '—' }}</td>
                            <td>{{ $rule->market?->status }}</td>
                            <td>{{ $rule->phase }}</td>
                            <td>
                                @if($rule->market?->status === 'open')
                                    <form method="POST" action="{{ route('admin.cricket.markets.suspend', $rule) }}" style="display:inline">@csrf<button class="btn btn--ghost btn--sm" type="submit">Suspend</button></form>
                                @endif
                                @if($rule->phase === 'open' && $rule->market?->status === 'suspended')
                                    <form method="POST" action="{{ route('admin.cricket.markets.reopen', $rule) }}" style="display:inline">@csrf<button class="btn btn--ghost btn--sm" type="submit">Reopen</button></form>
                                @endif
                                @if(! in_array($rule->phase, ['settled', 'void'], true))
                                    <form method="POST" action="{{ route('admin.cricket.markets.void', $rule) }}" style="display:inline" data-confirm="Void this market and refund open bets?">@csrf<button class="btn btn--danger btn--sm" type="submit">Void</button></form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty"><strong>No generated markets</strong></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card__head"><h3>Lifecycle log</h3></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>When</th><th>Event</th><th>Message</th></tr></thead>
                <tbody>
                    @forelse($events as $event)
                        <tr>
                            <td>{{ optional($event->created_at)->format('d M H:i:s') }}</td>
                            <td>{{ $event->event }}</td>
                            <td>{{ $event->message }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="empty"><strong>No events yet</strong></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
