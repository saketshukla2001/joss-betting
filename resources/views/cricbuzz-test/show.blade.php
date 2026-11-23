@extends('layouts.app')

@section('title', 'Cricbuzz · '.$label)

@section('content')
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('cricbuzz-test.index') }}">Cricbuzz API test</a>
            <span class="sep">/</span>
            <span>{{ $label }}</span>
        </div>

        @php
            $status = $presentedLive['status'] ?? ($presentedCard['status'] ?? '');
            $isFinished = (bool) preg_match('/\bwon\b|drawn|abandoned|no result/i', $status);
        @endphp

        <div class="page-head">
            <div>
                <h1>{{ $label }} <span class="badge {{ $isFinished ? 'badge--muted' : 'badge--live' }}">{{ $isFinished ? 'Result' : 'Live' }}</span></h1>
                <p>
                    Cricbuzz match <code>{{ $matchId }}</code> ·
                    <code>/miniscorecard</code> + <code>/scard</code>
                </p>
            </div>
            <div class="chip-row">
                <a class="btn btn--primary btn--sm" href="{{ route('cricbuzz-test.show', ['match' => $matchId, 'refresh' => 1]) }}">Refresh</a>
                <a class="btn btn--ghost btn--sm" href="{{ route('cricbuzz-test.index') }}">← All matches</a>
            </div>
        </div>

        @foreach(['live' => 'Miniscorecard', 'card' => 'Scorecard'] as $key => $section)
            @php $result = $$key; @endphp
            @if(! $result['success'])
                <div class="alert alert--error">
                    <strong>{{ $section }}:</strong> {{ $result['error'] }}
                    @if($result['status']) <span class="text-muted">(HTTP {{ $result['status'] }}{{ $result['error_code'] ? ' · '.$result['error_code'] : '' }})</span>@endif
                </div>
            @endif
        @endforeach

        {{-- Live panel --}}
        <div class="card mb-20">
            <div class="card__head">
                <div>
                    <h3>Live now</h3>
                    <p>{{ $status ?: 'No status returned' }}</p>
                </div>
                @if($presentedLive)
                    <span class="badge {{ $isFinished ? 'badge--muted' : 'badge--live' }}">{{ $isFinished ? 'Result' : 'In play' }}</span>
                @endif
            </div>

            @if(! $presentedLive)
                <div class="card__body">
                    <p class="text-muted" style="font-size:.8rem;margin:0">Miniscorecard unavailable — see the error above.</p>
                </div>
            @else
                <div class="card__body">
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px">
                        <div>
                            <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.07em;font-weight:700">Innings</div>
                            @forelse($presentedLive['innings'] as $inning)
                                <div style="display:flex;justify-content:space-between;gap:10px;margin-top:6px">
                                    <span style="font-weight:700">{{ $inning['team'] }}</span>
                                    <span class="mono">
                                        {{ $inning['runs'] ?? '—' }}@if($inning['wickets'] !== null)/{{ $inning['wickets'] }}@endif
                                        @if($inning['overs'] !== null)<span class="sub">({{ $inning['overs'] }})</span>@endif
                                    </span>
                                </div>
                            @empty
                                <p class="text-muted" style="font-size:.78rem;margin:6px 0 0">Not started.</p>
                            @endforelse

                            <div style="display:flex;gap:14px;flex-wrap:wrap;font-size:.75rem;margin-top:12px" class="text-muted">
                                @if($presentedLive['crr'] !== null)<span>CRR <strong>{{ $presentedLive['crr'] }}</strong></span>@endif
                                @if($presentedLive['rrr'] !== null)<span>RRR <strong>{{ $presentedLive['rrr'] }}</strong></span>@endif
                                @if($presentedLive['target'] !== null)<span>Target <strong>{{ $presentedLive['target'] }}</strong></span>@endif
                                @if($presentedLive['partnership'])<span>P'ship <strong>{{ $presentedLive['partnership'] }}</strong></span>@endif
                            </div>
                            @if($presentedLive['last_wkt'])
                                <p class="text-muted" style="font-size:.75rem;margin:8px 0 0">Last wicket: {{ $presentedLive['last_wkt'] }}</p>
                            @endif
                        </div>

                        <div>
                            <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.07em;font-weight:700">At the crease</div>
                            @foreach([$presentedLive['striker'], $presentedLive['non_striker']] as $bat)
                                @if($bat)
                                    <div style="display:flex;justify-content:space-between;gap:10px;margin-top:6px">
                                        <span>{{ $bat['name'] }}</span>
                                        <span class="mono">{{ $bat['runs'] }} <span class="sub">({{ $bat['balls'] }})</span></span>
                                    </div>
                                @endif
                            @endforeach

                            @if($presentedLive['bowler'])
                                <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.07em;font-weight:700;margin-top:14px">Bowling</div>
                                <div style="display:flex;justify-content:space-between;gap:10px;margin-top:6px">
                                    <span>{{ $presentedLive['bowler']['name'] }}</span>
                                    <span class="mono">{{ $presentedLive['bowler']['overs'] }}-{{ $presentedLive['bowler']['maidens'] }}-{{ $presentedLive['bowler']['runs'] }}-{{ $presentedLive['bowler']['wickets'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Scorecard --}}
        <div class="card mb-20">
            <div class="card__head">
                <div>
                    <h3>Scorecard</h3>
                    <p><code>/scard</code></p>
                </div>
            </div>

            @if(! $presentedCard || empty($presentedCard['innings']))
                <div class="card__body">
                    <p class="text-muted" style="font-size:.8rem;margin:0">
                        {{ $card['success'] ? 'No innings returned yet.' : 'Scorecard unavailable — see the error above.' }}
                    </p>
                </div>
            @else
                <div class="card__body">
                    @foreach($presentedCard['innings'] as $innings)
                        <div class="mb-20">
                            <div style="display:flex;align-items:baseline;justify-content:space-between;gap:10px;margin-bottom:8px">
                                <h4 style="margin:0">{{ $innings['team'] }}</h4>
                                <span class="mono">
                                    {{ $innings['score'] }}/{{ $innings['wickets'] }} <span class="sub">({{ $innings['overs'] }} ov, RR {{ $innings['runrate'] }})</span>
                                </span>
                            </div>

                            <div class="table-wrap">
                                <table class="data-table">
                                    <thead>
                                        <tr><th>Batter</th><th>R</th><th>B</th><th>4s</th><th>6s</th><th>SR</th><th>Dismissal</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($innings['batsmen'] as $bat)
                                            <tr>
                                                <td><strong>{{ $bat['name'] }}</strong></td>
                                                <td class="mono">{{ $bat['runs'] }}</td>
                                                <td class="mono">{{ $bat['balls'] }}</td>
                                                <td class="mono">{{ $bat['fours'] }}</td>
                                                <td class="mono">{{ $bat['sixes'] }}</td>
                                                <td class="mono">{{ $bat['sr'] }}</td>
                                                <td class="text-muted">{{ $bat['out'] ?: 'not out' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="table-wrap mt-14">
                                <table class="data-table">
                                    <thead>
                                        <tr><th>Bowler</th><th>O</th><th>M</th><th>R</th><th>W</th><th>Econ</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($innings['bowlers'] as $bwl)
                                            <tr>
                                                <td><strong>{{ $bwl['name'] }}</strong></td>
                                                <td class="mono">{{ $bwl['overs'] }}</td>
                                                <td class="mono">{{ $bwl['maidens'] }}</td>
                                                <td class="mono">{{ $bwl['runs'] }}</td>
                                                <td class="mono">{{ $bwl['wickets'] }}</td>
                                                <td class="mono">{{ $bwl['economy'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if(is_array($innings['extras']))
                                <p class="text-muted" style="font-size:.75rem;margin-top:8px">
                                    Extras:
                                    @foreach($innings['extras'] as $key => $value)
                                        @if(is_scalar($value) && $value !== null && $value !== 0)
                                            {{ ucfirst(str_replace('_', ' ', $key)) }} {{ $value }}@if(! $loop->last) · @endif
                                        @endif
                                    @endforeach
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="card">
            <div class="card__head">
                <div>
                    <h3>Raw payloads</h3>
                    <p>Use this to confirm what the current RapidAPI plan actually returns</p>
                </div>
            </div>
            <div class="card__body">
                <details>
                    <summary style="cursor:pointer;font-size:.82rem">Miniscorecard JSON</summary>
                    <pre style="max-height:320px;overflow:auto;font-size:.7rem;line-height:1.45;white-space:pre-wrap">{{ json_encode($live['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                </details>
                <details class="mt-14">
                    <summary style="cursor:pointer;font-size:.82rem">Scorecard JSON</summary>
                    <pre style="max-height:420px;overflow:auto;font-size:.7rem;line-height:1.45;white-space:pre-wrap">{{ json_encode($card['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                </details>
            </div>
        </div>
    </div>
@endsection
