@extends('layouts.admin')

@section('title', 'Bets')
@section('page-title', 'Bets')
@section('page-subtitle', 'Every ticket across the book')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>All bets</h1>
            <p>Search tickets, audit odds and review settlements.</p>
        </div>
        <a class="btn btn--outline btn--sm" href="{{ route('admin.reports.index') }}">Reports</a>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Tickets</div>
            <div class="stat__value">{{ number_format($summary['count']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Total stake</div>
            <div class="stat__value">{{ short_money($summary['stake']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Total payout</div>
            <div class="stat__value">{{ short_money($summary['payout']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Open</div>
            <div class="stat__value text-amber">{{ number_format($summary['pending']) }}</div>
        </div>
    </div>

    <form class="card filter-bar mt-20" method="GET" action="{{ route('admin.bets.index') }}">
        <input class="input search" type="search" name="q" value="{{ request('q') }}" placeholder="Ticket, player, match or selection…">
        <select class="select" name="status">
            <option value="">All statuses</option>
            @foreach(['pending' => 'Open', 'won' => 'Won', 'lost' => 'Lost', 'void' => 'Void'] as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select class="select" name="sport_id">
            <option value="">All sports</option>
            @foreach($sports as $sport)
                <option value="{{ $sport->id }}" @selected((int) request('sport_id') === $sport->id)>{{ $sport->name }}</option>
            @endforeach
        </select>
        <input class="input" type="date" name="from" value="{{ request('from') }}" title="From date">
        <input class="input" type="date" name="to" value="{{ request('to') }}" title="To date">
        <button class="btn btn--primary" type="submit">Filter</button>
        @if(request()->hasAny(['q', 'status', 'sport_id', 'from', 'to']))
            <a class="btn btn--ghost" href="{{ route('admin.bets.index') }}">Reset</a>
        @endif
    </form>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Tickets</h3><span class="badge badge--muted">{{ $bets->total() }} results</span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th>Ticket</th><th>Player</th><th>Match</th><th>Selection</th><th>Odds</th><th>Stake</th><th>Return</th><th>Status</th><th>Placed</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($bets as $bet)
                        <tr>
                            <td><a class="ticket-code" href="{{ route('admin.bets.show', $bet) }}">{{ $bet->bet_code }}</a></td>
                            <td>
                                @if($bet->user)
                                    <a href="{{ route('admin.users.show', $bet->user) }}">{{ $bet->user->username }}</a>
                                @else
                                    <span class="text-muted">Deleted</span>
                                @endif
                            </td>
                            <td>{{ $bet->match_label }}<span class="sub">{{ $bet->sport_name }}</span></td>
                            <td>{{ $bet->selection_name }}<span class="sub">{{ $bet->market_name }}</span></td>
                            <td class="mono">{{ odds($bet->odds) }}</td>
                            <td class="mono">{{ money($bet->stake) }}</td>
                            <td class="mono {{ $bet->status === 'won' ? 'text-green' : '' }}">{{ money($bet->payout) }}</td>
                            <td><span class="badge badge--{{ $bet->status_class }}">{{ $bet->status_label }}</span></td>
                            <td><span class="sub" style="margin:0">{{ $bet->created_at->format('d M, h:i A') }}</span></td>
                            <td><a class="btn btn--ghost btn--sm" href="{{ route('admin.bets.show', $bet) }}">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="10"><div class="empty"><strong>No bets found</strong><p>Adjust the filters to see more.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $bets->links('partials.pagination') }}
    </div>
@endsection
