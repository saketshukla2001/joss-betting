@extends('layouts.agent')

@section('title', $player->username)
@section('page-title', 'Player profile')
@section('page-subtitle', $player->name.' · '.$player->username)

@section('content')
    <div class="admin-page-head">
        <div class="flex-center" style="gap:12px">
            <span class="avatar avatar--lg">{{ $player->initials }}</span>
            <div>
                <h1 style="margin:0">{{ $player->name }}</h1>
                <p>{{ '@'.$player->username }} · {{ $player->email }} · joined {{ $player->created_at->format('d M Y') }}</p>
            </div>
        </div>
        <div class="actions-row">
            <span class="badge {{ $player->status === 'active' ? 'badge--success' : 'badge--danger' }}">{{ ucfirst($player->status) }}</span>
            <a class="btn btn--ghost btn--sm" href="{{ route('agent.players.index') }}">← My players</a>
        </div>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Turnover</div>
            <div class="stat__value">{{ money($stats['turnover']) }}</div>
            <div class="stat__meta">{{ $stats['bets'] }} bets · {{ $stats['open'] }} open</div>
        </div>
        <div class="stat">
            <div class="stat__label">Wallet balance</div>
            <div class="stat__value">{{ money($player->balance) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Exposure</div>
            <div class="stat__value">{{ money($player->exposure) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Commission from player</div>
            <div class="stat__value text-lime">{{ money($stats['commission']) }}</div>
        </div>
    </div>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Recent bets</h3><a class="link-more" href="{{ route('agent.bets.index', ['q' => $player->username]) }}">All bets →</a></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Ticket</th><th>Match</th><th>Selection</th><th>Stake</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($bets as $bet)
                        <tr>
                            <td><span class="ticket-code">{{ $bet->bet_code }}</span></td>
                            <td>{{ $bet->match_label }}</td>
                            <td>{{ $bet->selection_name }} @ {{ odds($bet->odds) }}</td>
                            <td class="mono">{{ money($bet->stake) }}</td>
                            <td><span class="badge badge--{{ $bet->status_class }}">{{ $bet->status_label }}</span></td>
                            <td><span class="sub" style="margin:0">{{ $bet->created_at->format('d M, H:i') }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty">No bets yet.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Wallet activity</h3></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Type</th><th>Description</th><th>Amount</th><th>Balance after</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->type_label }}</td>
                            <td>{{ $transaction->description }}</td>
                            <td class="mono {{ $transaction->amount >= 0 ? 'text-green' : 'text-red' }}">{{ $transaction->amount >= 0 ? '+' : '' }}{{ money($transaction->amount) }}</td>
                            <td class="mono">{{ money($transaction->balance_after) }}</td>
                            <td><span class="sub" style="margin:0">{{ $transaction->created_at->format('d M, H:i') }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty">No transactions.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
