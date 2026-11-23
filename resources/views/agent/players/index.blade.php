@extends('layouts.agent')

@section('title', 'My Players')
@section('page-title', 'My Players')
@section('page-subtitle', 'Players across your downline')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>My players</h1>
            <p>{{ $agent->role === 'agent' ? 'Create and monitor the players assigned to you.' : 'Players in your downline.' }}</p>
        </div>
        @if($agent->role === 'agent')
            <a class="btn btn--primary btn--sm" href="{{ route('agent.players.create') }}">+ Add player</a>
        @endif
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Total players</div>
            <div class="stat__value">{{ number_format($summary['total']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Active</div>
            <div class="stat__value text-green">{{ number_format($summary['active']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Wallet balances</div>
            <div class="stat__value">{{ short_money($summary['balance']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Full downline</div>
            <div class="stat__value">{{ number_format($summary['downline']) }}</div>
            <div class="stat__meta">Including sub-agent players</div>
        </div>
    </div>

    <form class="card filter-bar mt-20" method="GET" action="{{ route('agent.players.index') }}">
        <input class="input search" type="search" name="q" value="{{ request('q') }}" placeholder="Search name, username, email…">
        <select class="select" name="status">
            <option value="">All statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="blocked" @selected(request('status') === 'blocked')>Blocked</option>
        </select>
        <button class="btn btn--primary" type="submit">Filter</button>
    </form>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Player list</h3><span class="badge badge--muted">{{ $players->total() }}</span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Player</th><th>Status</th><th>Balance</th><th>Exposure</th><th>Bets</th><th>Joined</th><th></th></tr></thead>
                <tbody>
                    @forelse($players as $player)
                        <tr>
                            <td>
                                <div class="user-cell"><span class="avatar-sm">{{ $player->initials }}</span><span><strong>{{ $player->name }}</strong><span>{{ '@'.$player->username }} · {{ $player->email }}</span></span></div>
                            </td>
                            <td><span class="badge {{ $player->status === 'active' ? 'badge--success' : 'badge--danger' }}">{{ ucfirst($player->status) }}</span></td>
                            <td class="mono">{{ money($player->balance) }}</td>
                            <td class="mono">{{ money($player->exposure) }}</td>
                            <td>{{ $player->bets_count }}</td>
                            <td><span class="sub" style="margin:0">{{ $player->created_at->format('d M Y') }}</span></td>
                            <td><a class="btn btn--ghost btn--sm" href="{{ route('agent.players.show', $player) }}">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty"><strong>No players yet</strong><p>{{ $agent->role === 'agent' ? 'Create your first player to start earning commission.' : 'Clients created by Agent Masters will appear here.' }}</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $players->links('partials.pagination') }}
    </div>
@endsection
