@extends('layouts.admin')

@section('title', 'Current Bets')

@section('content')
    <div class="classic-level-head mb-14">
        <div>
            <h1>CURRENT BETS</h1>
            <p style="color:#666;font-size:.8rem;margin:3px 0 0">Open tickets across the platform.</p>
        </div>
        <a class="btn btn--outline" href="{{ route('admin.dashboard') }}">Dashboard</a>
    </div>

    <div class="admin-stat-grid">
        <div class="stat"><div class="stat__label">Open tickets</div><div class="stat__value">{{ number_format($summary['count']) }}</div></div>
        <div class="stat"><div class="stat__label">Open stake</div><div class="stat__value">{{ number_format($summary['stake'], 0) }}</div></div>
        <div class="stat"><div class="stat__label">Inplay</div><div class="stat__value">{{ \App\Models\MatchGame::where('status', 'live')->count() }}</div></div>
        <div class="stat"><div class="stat__label">Total matches</div><div class="stat__value">{{ \App\Models\MatchGame::count() }}</div></div>
    </div>

    <div class="classic-card">
        <div class="classic-card__head"><span>CURRENT BET TICKETS</span><span>{{ $bets->total() }} bets</span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Ticket</th><th>Client</th><th>Match</th><th>Selection</th><th>Odds</th><th>Stake</th><th>Status</th><th>Details</th></tr></thead>
                <tbody>
                    @forelse($bets as $bet)
                        <tr>
                            <td><span class="ticket-code">{{ $bet->bet_code }}</span></td>
                            <td>{{ $bet->user?->username }}</td>
                            <td>{{ $bet->match_label }}</td>
                            <td>{{ $bet->selection_name }}<span class="sub">{{ $bet->market_name }}</span></td>
                            <td class="mono">{{ odds($bet->odds) }}</td>
                            <td class="mono">{{ number_format($bet->stake, 0) }}</td>
                            <td><span class="badge badge--warning">OPEN</span></td>
                            <td><a class="classic-view-btn" href="{{ route('admin.bets.show', $bet) }}">◉ View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="empty"><strong>No open bets</strong></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $bets->links('partials.pagination') }}
    </div>
@endsection
