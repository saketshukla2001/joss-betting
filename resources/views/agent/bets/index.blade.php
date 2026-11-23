@extends('layouts.agent')

@section('title', 'Downline Bets')
@section('page-title', 'Downline Bets')
@section('page-subtitle', 'Every ticket from your players')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>Downline bets</h1>
            <p>Track stakes, results and commission-generating turnover.</p>
        </div>
        <a class="btn btn--outline btn--sm" href="{{ route('agent.commissions.index') }}">Commission</a>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Stake in view</div>
            <div class="stat__value">{{ money($summary['stake']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Payouts</div>
            <div class="stat__value">{{ money($summary['payout']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Tickets</div>
            <div class="stat__value">{{ number_format($summary['count']) }}</div>
        </div>
    </div>

    <form class="card filter-bar mt-20" method="GET" action="{{ route('agent.bets.index') }}">
        <input class="input search" type="search" name="q" value="{{ request('q') }}" placeholder="Ticket, match or player…">
        <select class="select" name="status">
            <option value="">All statuses</option>
            @foreach(['pending' => 'Open', 'won' => 'Won', 'lost' => 'Lost', 'void' => 'Void'] as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn--primary" type="submit">Filter</button>
        @if(request()->hasAny(['q', 'status']))
            <a class="btn btn--ghost" href="{{ route('agent.bets.index') }}">Reset</a>
        @endif
    </form>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Bets</h3><span class="badge badge--muted">{{ $bets->total() }}</span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Ticket</th><th>Player</th><th>Match / selection</th><th>Odds</th><th>Stake</th><th>Payout</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($bets as $bet)
                        <tr>
                            <td><span class="ticket-code">{{ $bet->bet_code }}</span></td>
                            <td><a href="{{ route('agent.players.show', $bet->user_id) }}">{{ $bet->user?->username }}</a></td>
                            <td>{{ $bet->match_label }}<span class="sub">{{ $bet->selection_name }} · {{ $bet->market_name }}</span></td>
                            <td class="mono">{{ odds($bet->odds) }}</td>
                            <td class="mono">{{ money($bet->stake) }}</td>
                            <td class="mono {{ $bet->status === 'won' ? 'text-green' : '' }}">{{ money($bet->payout) }}</td>
                            <td><span class="badge badge--{{ $bet->status_class }}">{{ $bet->status_label }}</span></td>
                            <td><span class="sub" style="margin:0">{{ $bet->created_at->format('d M, H:i') }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="empty"><strong>No bets found</strong></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $bets->links('partials.pagination') }}
    </div>
@endsection
