@extends('layouts.admin')

@section('title', 'Cricket')
@section('page-title', 'Cricket API')
@section('page-subtitle', 'Read-only Cricbuzz monitoring & configuration')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>Cricket feed</h1>
            <p>Live, upcoming and completed matches from Cricbuzz (read-only).</p>
        </div>
        <div class="actions-row">
            <a class="btn btn--ghost btn--sm" href="{{ route('admin.cricket.matches') }}">Matches</a>
            <a class="btn btn--ghost btn--sm" href="{{ route('admin.cricket.health') }}">API health</a>
            <a class="btn btn--primary btn--sm" href="{{ route('admin.cricket.settings') }}">Settings</a>
        </div>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime"><div class="stat__label">Live</div><div class="stat__value">{{ count($board['live'] ?? []) }}</div></div>
        <div class="stat"><div class="stat__label">Upcoming</div><div class="stat__value">{{ count($board['upcoming'] ?? []) }}</div></div>
        <div class="stat"><div class="stat__label">Completed</div><div class="stat__value">{{ count($board['results'] ?? []) }}</div></div>
        <div class="stat"><div class="stat__label">Configured IDs</div><div class="stat__value">{{ number_format($configured) }}</div></div>
        <div class="stat"><div class="stat__label">Stale rows</div><div class="stat__value">{{ number_format($stale) }}</div></div>
        <div class="stat"><div class="stat__label">Feed errors</div><div class="stat__value text-red">{{ number_format($errorCount) }}</div></div>
    </div>

    <div class="admin-grid-2 mt-20">
        <div class="admin-card">
            <div class="admin-card__head"><h3>Feed status</h3></div>
            <div class="admin-card__body">
                <ul class="info-list">
                    <li><span>Cricket feed enabled</span><strong>{{ $feedEnabled ? 'Yes' : 'No' }}</strong></li>
                    <li><span>Public board enabled</span><strong>{{ $boardEnabled ? 'Yes' : 'No' }}</strong></li>
                    <li><span>Discovery mode</span><strong>{{ $discovery ? 'Cricbuzz discovery' : 'Configured IDs' }}</strong></li>
                    <li><span>Hidden matches</span><strong>{{ count($hidden) }}</strong></li>
                    <li><span>Board generated</span><strong>{{ $board['generated_at'] ?? '—' }}</strong></li>
                    <li><span>Board source</span><strong>{{ $board['source'] ?? '—' }}</strong></li>
                </ul>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card__head"><h3>API health</h3></div>
            <div class="admin-card__body">
                <ul class="info-list">
                    <li><span>Configured</span><strong>{{ $health['configured'] ? 'Yes' : 'No' }}</strong></li>
                    <li><span>Host</span><strong>{{ $health['host'] ?: '—' }}</strong></li>
                    <li><span>Last success</span><strong>{{ $health['last_success']['at'] ?? '—' }}</strong></li>
                    <li><span>Last failure</span><strong>{{ $health['last_failure']['at'] ?? '—' }}</strong></li>
                    <li><span>Failures / 429 / parse</span><strong>{{ $health['failures'] }} / {{ $health['rate_limits'] }} / {{ $health['parse_errors'] }}</strong></li>
                </ul>
                <a class="btn btn--outline btn--sm mt-14" href="{{ route('admin.cricket.health') }}">Open API health</a>
            </div>
        </div>
    </div>

    @if(! empty($board['errors']))
        <div class="admin-card mt-20">
            <div class="admin-card__head"><h3>Feed errors</h3></div>
            <div class="admin-card__body">
                <ul class="info-list">
                    @foreach($board['errors'] as $error)
                        <li><span>{{ $error['id'] ?? 'feed' }}</span><strong>{{ $error['message'] ?? 'Unknown error' }}</strong></li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
@endsection
