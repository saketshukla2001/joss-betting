@extends('layouts.app')

@section('title', 'Cricket API test')

@section('content')
    <div class="container">
        <div class="page-head">
            <div>
                <h1>Cricket API test <span class="badge badge--muted">read-only</span></h1>
                <p>Sportmonks Cricket API 2.0 · isolated test page · does not touch betting, wallets or odds</p>
            </div>
            <div class="chip-row">
                <a class="btn btn--ghost btn--sm" href="{{ route('cricket-test.index') }}">Refresh</a>
            </div>
        </div>

        @if(! $configured)
            <div class="alert alert--error">
                No Sportmonks token found. Add <strong>SPORTMONKS_CRICKET_KEY</strong> (or <strong>CRICKET_API_KEY</strong>) to <code>.env</code>.
            </div>
        @endif

        <div class="card mb-20">
            <div class="card__body" style="display:flex;gap:24px;flex-wrap:wrap;font-size:.8rem">
                <span><strong>Base URL</strong><br>{{ $baseUrl }}</span>
                <span><strong>Upcoming window</strong><br>today → +30 days</span>
                <span><strong>Recent window</strong><br>{{ $from }} → {{ $to }}</span>
                <span><strong>Upcoming</strong><br>{{ $upcoming['success'] ? count($upcomingFixtures).' fixture(s)' : 'error' }}</span>
                <span><strong>Live</strong><br>{{ $live['success'] ? count($liveFixtures).' fixture(s)' : 'error' }}</span>
                <span><strong>Cache</strong><br>{{ ($live['cached'] ?? false) || ($upcoming['cached'] ?? false) || ($recent['cached'] ?? false) ? 'served from cache' : 'fresh requests' }}</span>
            </div>
        </div>

        @foreach(['live' => 'Livescores', 'upcoming' => 'Upcoming', 'recent' => 'Recent fixtures'] as $key => $label)
            @php $result = $$key; @endphp
            @if(! $result['success'])
                <div class="alert alert--error">
                    <strong>{{ $label }}:</strong> {{ $result['error'] }}
                    @if($result['status'])
                        <span class="text-muted">(HTTP {{ $result['status'] }}{{ $result['error_code'] ? ' · '.$result['error_code'] : '' }})</span>
                    @endif
                </div>
            @endif
        @endforeach

        {{-- LIVE --}}
        <div class="card mb-20">
            <div class="card__head">
                <div>
                    <h3>Live Matches</h3>
                    <p>GET /livescores</p>
                </div>
            </div>
            @if(empty($liveFixtures))
                <div class="card__body">
                    <p class="text-muted" style="font-size:.8rem;margin:0">
                        {{ $live['success'] ? 'No matches are live right now.' : 'Live endpoint failed — see the error above.' }}
                    </p>
                </div>
            @else
                @include('cricket-test._table', ['rows' => $liveFixtures])
            @endif
        </div>

        {{-- UPCOMING --}}
        <div class="card mb-20">
            <div class="card__head">
                <div>
                    <h3>Upcoming Matches</h3>
                    <p>GET /fixtures · <code>filter[status]=NS</code> · soonest first</p>
                </div>
                <span class="badge badge--info">{{ count($upcomingFixtures) }} fixtures</span>
            </div>
            @if(empty($upcomingFixtures))
                <div class="card__body">
                    <p class="text-muted" style="font-size:.8rem;margin:0">
                        {{ $upcoming['success'] ? 'No upcoming fixtures returned for the next 30 days.' : 'Upcoming endpoint failed — see the error above.' }}
                    </p>
                </div>
            @else
                <div class="card__body">
                    <div class="match-grid match-grid--2">
                        @foreach($upcomingFixtures as $row)
                            @include('cricket-test._card', ['row' => $row])
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- RECENT --}}
        <div class="card">
            <div class="card__head">
                <div>
                    <h3>Recent Results</h3>
                    <p>GET /fixtures · {{ $from }} → {{ $to }}</p>
                </div>
                <div class="chip-row">
                    @if($page > 1)
                        <a class="btn btn--ghost btn--sm" href="{{ route('cricket-test.index', array_filter(['page' => $page - 1, 'league' => $league])) }}">← Prev</a>
                    @endif
                    <a class="btn btn--ghost btn--sm" href="{{ route('cricket-test.index', array_filter(['page' => $page + 1, 'league' => $league])) }}">Next →</a>
                </div>
            </div>
            @if(empty($recentRows))
                <div class="card__body">
                    <p class="text-muted" style="font-size:.8rem;margin:0">
                        {{ $recent['success'] ? 'No recent fixtures in this window/page.' : 'Recent fixtures endpoint failed — see the error above.' }}
                    </p>
                </div>
            @else
                @include('cricket-test._table', ['rows' => $recentRows])
            @endif
        </div>
    </div>
@endsection
