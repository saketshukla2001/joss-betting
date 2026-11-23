@extends('layouts.app')

@section('title', 'My bets')

@section('content')
    <div class="container">
        <div class="page-head">
            <div>
                <span class="eyebrow">History</span>
                <h1 class="mt-8">My bets</h1>
                <p>Every ticket, live or settled, with the exact odds you locked in.</p>
            </div>
            <a class="btn btn--primary" href="{{ route('sports.index') }}">Place a bet</a>
        </div>

        <div class="stat-grid mb-20">
            <div class="stat">
                <div class="stat__label">Total tickets</div>
                <div class="stat__value">{{ number_format($summary['total']) }}</div>
            </div>
            <div class="stat">
                <div class="stat__label">Open</div>
                <div class="stat__value text-amber">{{ number_format($summary['open']) }}</div>
                <div class="stat__meta">{{ money(auth()->user()->exposure) }} exposure</div>
            </div>
            <div class="stat">
                <div class="stat__label">Staked</div>
                <div class="stat__value">{{ money($summary['staked']) }}</div>
            </div>
            <div class="stat stat--lime">
                <div class="stat__label">Returns</div>
                <div class="stat__value">{{ money($summary['returns']) }}</div>
                <div class="stat__meta">{{ $summary['won'] }} winning tickets</div>
            </div>
        </div>

        <div class="chip-row mb-14">
            @foreach(['' => 'All', 'pending' => 'Open', 'won' => 'Won', 'lost' => 'Lost', 'void' => 'Void'] as $value => $label)
                <a class="chip {{ $status === $value ? 'active' : '' }}" href="{{ route('bets.index', array_filter(['status' => $value, 'q' => $search])) }}">{{ $label }}</a>
            @endforeach
        </div>

        <form class="card filter-bar mb-20" method="GET" action="{{ route('bets.index') }}">
            @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
            <input class="input search" type="search" name="q" value="{{ $search }}" placeholder="Search ticket, match or selection…">
            <button class="btn btn--primary" type="submit">Search</button>
            @if($search)
                <a class="btn btn--ghost" href="{{ route('bets.index', array_filter(['status' => $status])) }}">Clear</a>
            @endif
        </form>

        <div class="card">
            @if($bets->isEmpty())
                <div class="empty">
                    <div class="empty__ico">🎫</div>
                    <strong>No tickets found</strong>
                    <p>Try a different filter, or place your first bet.</p>
                    <a class="btn btn--primary btn--sm mt-14" href="{{ route('sports.index') }}">Browse matches</a>
                </div>
            @else
                <div class="table-wrap">
                    <table class="data-table table--stack">
                        <thead>
                            <tr>
                                <th>Ticket</th>
                                <th>Match / selection</th>
                                <th>Odds</th>
                                <th>Stake</th>
                                <th>Return</th>
                                <th>Status</th>
                                <th>Placed</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bets as $bet)
                                <tr>
                                    <td data-label="Ticket">
                                        <span class="ticket-code">{{ $bet->bet_code }}</span>
                                        <span class="sub">{{ $bet->sport_name }}</span>
                                    </td>
                                    <td data-label="Match / selection">
                                        <span class="strong">{{ $bet->match_label }}</span>
                                        <span class="sub">{{ $bet->selection_name }} · {{ $bet->market_name }}</span>
                                    </td>
                                    <td data-label="Odds" class="mono">{{ odds($bet->odds) }}</td>
                                    <td data-label="Stake" class="mono">{{ money($bet->stake) }}</td>
                                    <td data-label="Return" class="mono">
                                        @if($bet->status === 'won')
                                            <span class="text-green">{{ money($bet->payout) }}</span>
                                        @elseif($bet->status === 'lost')
                                            <span class="text-muted">—</span>
                                        @elseif($bet->status === 'void')
                                            <span class="text-muted">{{ money($bet->stake) }}</span>
                                        @else
                                            <span class="text-lime">{{ money($bet->potential_return) }}</span>
                                        @endif
                                    </td>
                                    <td data-label="Status"><span class="badge badge--{{ $bet->status_class }}">{{ $bet->status_label }}</span></td>
                                    <td data-label="Placed"><span class="sub" style="margin:0">{{ $bet->created_at->format('d M, h:i A') }}</span></td>
                                    <td data-label="Action" class="text-right"><a class="btn btn--ghost btn--sm" href="{{ route('bets.show', $bet) }}">Details</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $bets->links('partials.pagination') }}
            @endif
        </div>
    </div>
@endsection
