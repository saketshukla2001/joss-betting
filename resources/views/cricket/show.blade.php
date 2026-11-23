@extends('layouts.app')

@section('title', 'Cricket · '.$label)

@section('content')
    @php
        $status = $presentedLive['status_raw'] ?? ($presentedLive['status'] ?? ($presentedCard['status'] ?? ''));
        $normalized = \App\Support\CricketStatus::normalize($status);
        $statusGroup = $presentedLive['status_group'] ?? $normalized['group'];
        $statusLabel = $presentedLive['status_label'] ?? $normalized['label'];
        $fetchedAt = $live['fetched_at'] ?? null;
        $statusStale = \App\Support\CricketStatus::isStale($fetchedAt);
        $statusBadge = match ($statusGroup) {
            'completed' => 'badge--muted',
            'upcoming' => 'badge--info',
            default => 'badge--live',
        };
    @endphp

    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span class="sep">/</span>
            <a href="{{ route('cricket.index') }}">Cricket</a>
            <span class="sep">/</span>
            <span>{{ $label }}</span>
        </div>

        <div class="page-head">
            <div>
                <h1>
                    {{ $label }}
                    <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                    @if($statusStale)<span class="badge badge--warning">stale</span>@endif
                </h1>
                <p>
                    {{ $status ?: 'Match status unavailable' }}
                    @if($fetchedAt)
                        · <span class="text-muted">updated {{ \Illuminate\Support\Carbon::parse($fetchedAt)->timezone(config('app.timezone'))->format('h:i:s A') }}</span>
                    @endif
                </p>
            </div>
            <div class="chip-row">
                <a class="btn btn--ghost btn--sm" href="{{ route('cricket.show', ['match' => $matchId, 'refresh' => 1]) }}">Refresh</a>
                <a class="btn btn--ghost btn--sm" href="{{ route('cricket.index') }}">← All cricket</a>
            </div>
        </div>

        @if(! $live['success'])
            <div class="alert alert--error">
                <strong>Live score:</strong> {{ $live['error'] }}
                @if($live['status']) <span class="text-muted">(HTTP {{ $live['status'] }})</span>@endif
            </div>
        @endif

        @if(! $card['success'])
            <div class="alert alert--error">
                <strong>Scorecard:</strong> {{ $card['error'] }}
                @if($card['status']) <span class="text-muted">(HTTP {{ $card['status'] }})</span>@endif
            </div>
        @endif

        <div class="card mb-20">
            <div class="card__head">
                <div>
                    <h3>Match score</h3>
                    <p>{{ $status ?: 'Waiting for data' }}</p>
                </div>
            </div>

            @if(! $presentedLive)
                <div class="card__body">
                    <div class="empty">
                        <div class="empty__ico">🏏</div>
                        <strong>Score unavailable</strong>
                        <p>The live score feed did not return data. Try refreshing in a moment.</p>
                    </div>
                </div>
            @else
                <div class="card__body">
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px">
                        <div>
                            <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;font-weight:700">Innings</div>
                            @forelse($presentedLive['innings'] as $inning)
                                <div class="flex-between" style="margin-top:8px">
                                    <span class="fw-800">{{ $inning['team'] }}</span>
                                    <span class="mono fw-800">
                                        {{ $inning['runs'] ?? '—' }}@if($inning['wickets'] !== null)/{{ $inning['wickets'] }}@endif
                                        @if($inning['overs'] !== null)<span class="sub">({{ $inning['overs'] }} ov)</span>@endif
                                    </span>
                                </div>
                            @empty
                                <p class="text-muted" style="font-size:.8rem;margin:8px 0 0">Score not started yet.</p>
                            @endforelse

                            <div class="chip-row mt-14">
                                @if($presentedLive['crr'] !== null)<span class="chip">CRR {{ $presentedLive['crr'] }}</span>@endif
                                @if($presentedLive['rrr'] !== null)<span class="chip">RRR {{ $presentedLive['rrr'] }}</span>@endif
                                @if($presentedLive['target'] !== null)<span class="chip">Target {{ $presentedLive['target'] }}</span>@endif
                                @if($presentedLive['partnership'])<span class="chip">P'ship {{ $presentedLive['partnership'] }}</span>@endif
                            </div>
                        </div>

                        <div>
                            <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;font-weight:700">At the crease</div>
                            @foreach([$presentedLive['striker'], $presentedLive['non_striker']] as $bat)
                                @if($bat)
                                    <div class="flex-between" style="margin-top:8px">
                                        <span>{{ $bat['name'] }}</span>
                                        <span class="mono">{{ $bat['runs'] }} <span class="sub">({{ $bat['balls'] }})</span></span>
                                    </div>
                                @endif
                            @endforeach

                            @if($presentedLive['bowler'])
                                <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;font-weight:700;margin-top:16px">Bowling</div>
                                <div class="flex-between" style="margin-top:8px">
                                    <span>{{ $presentedLive['bowler']['name'] }}</span>
                                    <span class="mono">
                                        {{ $presentedLive['bowler']['overs'] }}-{{ $presentedLive['bowler']['maidens'] }}-{{ $presentedLive['bowler']['runs'] }}-{{ $presentedLive['bowler']['wickets'] }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="card">
            <div class="card__head">
                <div>
                    <h3>Scorecard</h3>
                    <p>Batting and bowling by innings</p>
                </div>
            </div>

            @if(! $presentedCard || empty($presentedCard['innings']))
                <div class="card__body">
                    <div class="empty">
                        <div class="empty__ico">📋</div>
                        <strong>Scorecard not available yet</strong>
                        <p>{{ $card['success'] ? 'The API has no innings data for this match.' : 'The scorecard request failed — try refreshing.' }}</p>
                    </div>
                </div>
            @else
                <div class="card__body">
                    @foreach($presentedCard['innings'] as $innings)
                        <div class="{{ $loop->last ? '' : 'mb-20' }}">
                            <div class="flex-between mb-14" style="align-items:baseline;gap:10px">
                                <h4 style="margin:0">{{ $innings['team'] }}</h4>
                                <span class="mono fw-800">
                                    {{ $innings['score'] }}/{{ $innings['wickets'] }}
                                    <span class="sub">({{ $innings['overs'] }} ov · RR {{ $innings['runrate'] }})</span>
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
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
