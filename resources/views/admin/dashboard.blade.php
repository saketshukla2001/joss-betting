@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="classic-tiles">
        <a class="classic-tile" href="{{ route('admin.hierarchy.index', 'mini_master') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a8 8 0 0116 0v1"/></svg>
            Mini Admin
        </a>
        <a class="classic-tile" href="{{ route('admin.sports.inplay') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 3a15 15 0 010 18M3 12h18"/></svg>
            Sport's Details
        </a>
        <a class="classic-tile" href="{{ route('admin.ledger.show', 'my') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>
            Ledger
        </a>
        <a class="classic-tile" href="{{ route('admin.cash.index', 'mini_master') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18"/><circle cx="16.5" cy="15" r="1.2" fill="currentColor" stroke="none"/></svg>
            Cash Transaction
        </a>

        <a class="classic-tile" href="{{ route('admin.settings.index') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 00.3 1.9l.1.1a2 2 0 11-2.8 2.8l-.1-.1a1.7 1.7 0 00-1.9-.3 1.7 1.7 0 00-1 1.5V21a2 2 0 11-4 0v-.1a1.7 1.7 0 00-1-1.6 1.7 1.7 0 00-1.9.3l-.1.1a2 2 0 11-2.8-2.8l.1-.1a1.7 1.7 0 00.3-1.9 1.7 1.7 0 00-1.5-1H3a2 2 0 110-4h.1a1.7 1.7 0 001.6-1 1.7 1.7 0 00-.3-1.9l-.1-.1a2 2 0 112.8-2.8l.1.1a1.7 1.7 0 001.9.3H9a1.7 1.7 0 001-1.5V3a2 2 0 114 0v.1a1.7 1.7 0 001 1.5 1.7 1.7 0 001.9-.3l.1-.1a2 2 0 112.8 2.8l-.1.1a1.7 1.7 0 00-.3 1.9V9a1.7 1.7 0 001.5 1H21a2 2 0 110 4h-.1a1.7 1.7 0 00-1.5 1z"/></svg>
            Settings
        </a>
        <form method="POST" action="{{ route('logout') }}" style="display:contents">
            @csrf
            <button class="classic-tile" type="submit" style="border:0;cursor:pointer;text-align:left">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                Logout
            </button>
        </form>
    </div>

    <div class="classic-stats">
        <div class="classic-stat classic-stat--user">
            <strong>{{ $admin->agent_code ?: $admin->username }}</strong>
            <span>YOU ARE {{ strtoupper($admin->role_label) }}</span>
        </div>
        <div class="classic-stat">
            <div class="classic-stat__label">Coins</div>
            <div class="classic-stat__value">{{ number_format($admin->balance, 0) }}</div>
        </div>
        <div class="classic-stat">
            <div class="classic-stat__label">Members</div>
            <div class="classic-stat__value">{{ number_format($members) }}</div>
        </div>
        <div class="classic-stat">
            <div class="classic-stat__label">My Share</div>
            <div class="classic-stat__value">{{ number_format($admin->share_percent, 0) }}<small>%</small></div>
        </div>

        <div class="classic-stat">
            <div class="classic-stat__label">Company Share</div>
            <div class="classic-stat__value">{{ number_format($admin->company_share_percent, 0) }}<small>%</small></div>
        </div>
        <div class="classic-stat">
            <div class="classic-stat__label">Match Commission</div>
            <div class="classic-stat__value">{{ number_format($admin->commission_rate, 0) }}<small>%</small></div>
        </div>
        <div class="classic-stat">
            <div class="classic-stat__label">Session Commission</div>
            <div class="classic-stat__value">{{ number_format($admin->session_commission_percent, 0) }}<small>%</small></div>
        </div>
        <a class="classic-stat classic-stat--rules" href="{{ route('admin.settings.index') }}#rules">
            <div class="classic-stat__label">Rules</div>
            <div class="classic-stat__value" style="font-size:.9rem">Platform Rules</div>
        </a>
    </div>

    <div class="classic-card">
        <div class="classic-card__head">
            <span>Active Matches</span>
            <a href="{{ route('admin.sports.inplay') }}">All Games →</a>
        </div>
        <div class="table-wrap dashboard-table-wrap">
            <table class="data-table table--stack">
                <thead>
                    <tr><th>Name</th><th>Open Date</th><th>Competition</th><th>Inplay</th><th>Details</th></tr>
                </thead>
                <tbody>
                    @forelse($activeMatches as $match)
                        <tr>
                            <td class="strong" data-label="Name">{{ strtoupper($match->home_team) }} V {{ strtoupper($match->away_team) }}</td>
                            <td data-label="Open Date">{{ $match->start_time->format('m/d/Y h:i A') }}</td>
                            <td data-label="Competition">{{ strtoupper($match->league) }}</td>
                            <td data-label="Inplay">
                                @if($match->status === 'live')
                                    <span class="badge badge--live">INPLAY</span>
                                @else
                                    <span class="badge badge--muted">INPLAY</span>
                                @endif
                            </td>
                            <td data-label="Details">
                                <div class="dashboard-match-actions">
                                    <a class="classic-view-btn" href="{{ route('admin.sports.match-view', $match) }}">◉ View</a>
                                    <details class="game-menu">
                                        <summary aria-label="Match actions">⌄</summary>
                                        <div class="game-menu__popup">
                                            <a href="{{ route('admin.sports.match-bets', [$match, 'match']) }}">MATCH BET</a>
                                            <a href="{{ route('admin.sports.match-bets', [$match, 'session']) }}">SESSION BET</a>
                                            <a href="{{ route('admin.sports.match-bets', [$match, 'completed-session']) }}">COMPLETED SESSION</a>
                                            <a href="{{ route('admin.sports.match-bets', [$match, 'rejected']) }}">REJECTED BETS</a>
                                        </div>
                                    </details>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty"><strong>No active matches</strong><p>Create a fixture to open the book.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
