@extends('layouts.admin')

@section('title', 'Cricket API health')
@section('page-title', 'Cricket API health')
@section('page-subtitle', 'Cricbuzz / RapidAPI diagnostics')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>API health</h1>
            <p>No credentials, headers or secrets are shown here.</p>
        </div>
        <div class="actions-row">
            <a class="btn btn--ghost btn--sm" href="{{ route('admin.cricket.index') }}">← Dashboard</a>
            <a class="btn btn--ghost btn--sm" href="{{ route('admin.cricket.matches') }}">Matches</a>
        </div>
    </div>

    <div class="admin-stat-grid">
        <div class="stat {{ $health['configured'] ? 'stat--lime' : '' }}">
            <div class="stat__label">Connection</div>
            <div class="stat__value">{{ $health['configured'] ? 'Configured' : 'Missing' }}</div>
            <div class="stat__meta">{{ $health['host'] ?: 'no host' }}</div>
        </div>
        <div class="stat"><div class="stat__label">Failures</div><div class="stat__value">{{ number_format($health['failures']) }}</div></div>
        <div class="stat"><div class="stat__label">Rate limits (429)</div><div class="stat__value text-red">{{ number_format($health['rate_limits']) }}</div></div>
        <div class="stat"><div class="stat__label">Parsing errors</div><div class="stat__value">{{ number_format($health['parse_errors']) }}</div></div>
    </div>

    <div class="admin-grid-2 mt-20">
        <div class="admin-card">
            <div class="admin-card__head"><h3>Last requests</h3></div>
            <div class="admin-card__body">
                <ul class="info-list">
                    <li>
                        <span>Last success</span>
                        <strong>
                            {{ $health['last_success']['at'] ?? '—' }}
                            @if(! empty($health['last_success']['status']))
                                · HTTP {{ $health['last_success']['status'] }}
                            @endif
                        </strong>
                    </li>
                    <li><span>Last success path</span><strong>{{ $health['last_success']['path'] ?? '—' }}</strong></li>
                    <li>
                        <span>Last failure</span>
                        <strong>
                            {{ $health['last_failure']['at'] ?? '—' }}
                            @if(! empty($health['last_failure']['status']))
                                · HTTP {{ $health['last_failure']['status'] }}
                            @endif
                        </strong>
                    </li>
                    <li><span>Last failure path</span><strong>{{ $health['last_failure']['path'] ?? '—' }}</strong></li>
                </ul>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card__head"><h3>Cache &amp; limits</h3></div>
            <div class="admin-card__body">
                <ul class="info-list">
                    <li><span>Board cached</span><strong>{{ $boardCached ? 'Yes' : 'No' }}</strong></li>
                    <li><span>Board generated</span><strong>{{ $boardGeneratedAt ?? '—' }}</strong></li>
                    <li><span>Discovery cache (live/upcoming/recent)</span><strong>{{ collect($discoveryCached)->map(fn ($v) => $v ? 'yes' : 'no')->implode(' / ') }}</strong></li>
                    <li><span>Board TTL</span><strong>{{ $boardTtl }}s</strong></li>
                    <li><span>Discovery TTL</span><strong>{{ $discoveryTtl }}s</strong></li>
                    <li><span>Request spacing</span><strong>{{ $throttle }}ms</strong></li>
                    <li><span>HTTP timeout</span><strong>{{ $timeout }}s</strong></li>
                </ul>
            </div>
        </div>
    </div>

    <p class="hint mt-14">
        RapidAPI BASIC plans allow a small number of requests per second and a monthly quota. The feed throttles requests and
        reuses cache; 429 responses are counted above and retried once with a back-off.
    </p>
@endsection
