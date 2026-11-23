@extends('layouts.app')

@section('title', 'Cricket fixture #'.$fixtureId)

@section('content')
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('cricket-test.index') }}">Cricket API test</a>
            <span class="sep">/</span>
            <span>Fixture #{{ $fixtureId }}</span>
        </div>

        <div class="page-head">
            <div>
                <h1>{{ $match['name'] ?? 'Fixture #'.$fixtureId }} <span class="badge badge--muted">read-only</span></h1>
                <p>GET /fixtures/{{ $fixtureId }} · Sportmonks Cricket API 2.0</p>
            </div>
            <div class="chip-row">
                <a class="btn btn--ghost btn--sm" href="{{ route('cricket-test.show', $fixtureId) }}">Refresh</a>
                <a class="btn btn--ghost btn--sm" href="{{ route('cricket-test.index') }}">← All fixtures</a>
            </div>
        </div>

        @if(! $result['success'])
            <div class="alert alert--error">
                {{ $result['error'] }}
                @if($result['status'])
                    <span class="text-muted">(HTTP {{ $result['status'] }}{{ $result['error_code'] ? ' · '.$result['error_code'] : '' }})</span>
                @endif
            </div>
        @endif

        @if($match)
            @php
                $statusLower = strtolower((string) $match['status']);
                $badge = $match['live'] ? 'badge--live' : (str_contains($statusLower, 'finish') ? 'badge--success' : 'badge--info');

                try {
                    $startsAt = $match['starting_at']
                        ? \Illuminate\Support\Carbon::parse($match['starting_at'])->timezone(config('app.timezone'))
                        : null;
                } catch (\Throwable) {
                    $startsAt = null;
                }
            @endphp

            <div class="card mb-20">
                <div class="card__head">
                    <div>
                        <h3>{{ $match['name'] }}</h3>
                        <p>
                            <span class="badge {{ $badge }}">{{ $match['status'] }}</span>
                            @if($match['note']) · {{ $match['note'] }} @endif
                        </p>
                    </div>
                </div>
                <div class="card__body">
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;font-size:.85rem">
                        <div><span class="text-muted">Tournament</span><br><strong>{{ $match['league'] ?? '—' }}</strong></div>
                        <div><span class="text-muted">Season</span><br><strong>{{ $match['season'] ?? '—' }}</strong></div>
                        <div><span class="text-muted">Stage / round</span><br><strong>{{ $match['stage'] ?? '—' }}@if($match['round']) · {{ $match['round'] }}@endif</strong></div>
                        <div><span class="text-muted">Format</span><br><strong>{{ $match['type'] ?? '—' }}</strong></div>
                        <div><span class="text-muted">Venue</span><br><strong>{{ $match['venue'] ?? '—' }}@if($match['city']) · {{ $match['city'] }}@endif</strong></div>
                        <div><span class="text-muted">Starts</span><br><strong>{{ $startsAt?->format('d M Y, h:i A') ?? '—' }}</strong></div>
                        <div><span class="text-muted">Toss won by team ID</span><br><strong>{{ $match['toss_won_team_id'] ?? '—' }}</strong></div>
                        <div><span class="text-muted">Winner team ID</span><br><strong>{{ $match['winner_team_id'] ?? '—' }}</strong></div>
                        <div><span class="text-muted">Overs played</span><br><strong>{{ $match['total_overs_played'] ?? '—' }}</strong></div>
                    </div>
                </div>
            </div>

            <div class="card mb-20">
                <div class="card__head">
                    <div>
                        <h3>Scores</h3>
                        <p>From the <code>runs</code> include</p>
                    </div>
                </div>
                @if(empty($match['scores']))
                    <div class="card__body">
                        <p class="text-muted" style="font-size:.8rem;margin:0">
                            No score rows in this response. Add/enable the <code>runs</code> include, or check the plan restriction above.
                        </p>
                    </div>
                @else
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr><th>Team</th><th>Runs</th><th>Wickets</th><th>Overs</th><th>Innings</th></tr>
                            </thead>
                            <tbody>
                                @foreach($match['scores'] as $score)
                                    <tr>
                                        <td><strong>{{ $score['team'] }}</strong></td>
                                        <td class="mono">{{ $score['runs'] }}</td>
                                        <td class="mono">{{ $score['wickets'] ?? '—' }}</td>
                                        <td class="mono">{{ $score['overs'] ?? '—' }}</td>
                                        <td>{{ $score['inning'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="card">
                <div class="card__head">
                    <div>
                        <h3>Raw payload</h3>
                        <p>What {{ $result['degraded'] ? 'the fallback request' : 'the API' }} actually returned — use this to confirm available fields/plan limits</p>
                    </div>
                </div>
                <div class="card__body">
                    @if($result['degraded'])
                        <div class="alert alert--warning" style="margin-bottom:12px">
                            Rich includes were refused by the API, so this page retried with core includes only.
                        </div>
                    @endif
                    <details>
                        <summary style="cursor:pointer;font-size:.82rem">Show raw JSON</summary>
                        <pre style="max-height:520px;overflow:auto;font-size:.72rem;line-height:1.5;white-space:pre-wrap">{{ json_encode($match['raw'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                    </details>
                </div>
            </div>
        @endif
    </div>
@endsection
