@extends('layouts.agent')

@section('title', 'My Bets')
@section('page-title', 'My Bets')
@section('page-subtitle', 'Your own bet tickets')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>My Bets</h1>
            <p>All bets placed from your master wallet.</p>
        </div>
        <div class="actions-row">
            <a class="btn btn--primary btn--sm" href="{{ route('agent.matches.index') }}">+ Place Bet</a>
        </div>
    </div>

    <div class="admin-stat-grid">
        <div class="stat"><div class="stat__label">Total bets</div><div class="stat__value">{{ $summary['total'] }}</div></div>
        <div class="stat"><div class="stat__label">Open</div><div class="stat__value">{{ $summary['open'] }}</div></div>
        <div class="stat"><div class="stat__label">Staked</div><div class="stat__value">{{ number_format($summary['staked'], 2) }}</div></div>
        <div class="stat"><div class="stat__label">Returns</div><div class="stat__value">{{ number_format($summary['returns'], 2) }}</div></div>
    </div>

    <form class="filter-bar mt-14" method="GET" action="{{ route('agent.my-bets') }}">
        <select class="select" name="status">
            <option value="">All tickets</option>
            @foreach(['pending' => 'Open', 'won' => 'Won', 'lost' => 'Lost', 'void' => 'Void'] as $key => $label)
                <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn--primary btn--sm" type="submit">Filter</button>
    </form>

    <div class="classic-card">
        <div class="classic-card__head"><span>MY TICKETS</span><span>{{ $bets->total() }}</span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Ticket</th><th>Match</th><th>Selection</th><th>Odds</th><th>Stake</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($bets as $bet)
                        <tr>
                            <td class="mono">{{ $bet->bet_code }}</td>
                            <td>{{ $bet->match_label }}</td>
                            <td>{{ $bet->selection_name }}</td>
                            <td class="mono">{{ number_format($bet->odds, 2) }}</td>
                            <td class="mono">{{ number_format($bet->stake, 2) }}</td>
                            <td><span class="badge badge--{{ $bet->status_class }}">{{ $bet->status_label }}</span></td>
                            <td>{{ $bet->created_at->format('d M Y, H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty"><strong>No bets yet</strong><p>Open a match to place your first bet.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $bets->links('partials.pagination') }}
    </div>
@endsection
