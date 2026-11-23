@extends('layouts.admin')

@section('title', 'Players')
@section('page-title', 'Players')
@section('page-subtitle', 'Accounts, balances and exposure')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>Players</h1>
            <p>Manage client accounts, wallet balances and access.</p>
        </div>
        <a class="btn btn--primary btn--sm" href="{{ route('admin.users.create') }}">+ Add player</a>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Total clients</div>
            <div class="stat__value">{{ number_format($summary['total']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Active</div>
            <div class="stat__value text-green">{{ number_format($summary['active']) }}</div>
            <div class="stat__meta">{{ $summary['blocked'] }} blocked</div>
        </div>
        <div class="stat">
            <div class="stat__label">Wallet liability</div>
            <div class="stat__value">{{ short_money($summary['balance']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Open exposure</div>
            <div class="stat__value">{{ short_money($summary['exposure']) }}</div>
        </div>
    </div>

    <form class="card filter-bar mt-20" method="GET" action="{{ route('admin.users.index') }}">
        <input class="input search" type="search" name="q" value="{{ request('q') }}" placeholder="Search name, username, email or phone…">
        <select class="select" name="role">
            <option value="">All roles</option>
            <option value="client" @selected(request('role') === 'client')>Clients</option>
            <option value="agent" @selected(request('role') === 'agent')>Agents</option>
            <option value="admin" @selected(request('role') === 'admin')>Admins</option>
        </select>
        <select class="select" name="status">
            <option value="">All statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="blocked" @selected(request('status') === 'blocked')>Blocked</option>
        </select>
        <button class="btn btn--primary" type="submit">Apply</button>
        @if(request()->hasAny(['q', 'role', 'status']))
            <a class="btn btn--ghost" href="{{ route('admin.users.index') }}">Reset</a>
        @endif
    </form>

    <div class="admin-card mt-20">
        <div class="admin-card__head">
            <h3>All accounts</h3>
            <span class="badge badge--muted">{{ $users->total() }} results</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th>Player</th><th>Role</th><th>Agent</th><th>Status</th><th>Balance</th><th>Exposure</th><th>Bets</th><th>Joined</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <span class="avatar-sm">{{ $user->initials }}</span>
                                    <span>
                                        <strong>{{ $user->name }}</strong>
                                        <span>{{ '@'.$user->username }} · {{ $user->email }}</span>
                                    </span>
                                </div>
                            </td>
                            <td><span class="badge {{ $user->role === 'admin' ? 'badge--info' : ($user->role === 'agent' ? 'badge--lime' : 'badge--muted') }}">{{ ucfirst($user->role) }}</span></td>
                            <td>{{ $user->parent?->username ?? '—' }}</td>
                            <td><span class="badge {{ $user->status === 'active' ? 'badge--success' : 'badge--danger' }}">{{ ucfirst($user->status) }}</span></td>
                            <td class="mono">{{ money($user->balance) }}</td>
                            <td class="mono">{{ money($user->exposure) }}</td>
                            <td>{{ $user->bets_count }}</td>
                            <td><span class="sub" style="margin:0">{{ $user->created_at->format('d M Y') }}</span></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn btn--ghost btn--sm" href="{{ route('admin.users.show', $user) }}">View</a>
                                    <a class="btn btn--ghost btn--sm" href="{{ $user->isAgent() ? route('admin.agents.edit', $user) : route('admin.users.edit', $user) }}">Edit</a>
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.toggle', $user) }}" data-confirm="{{ $user->status === 'active' ? 'Block' : 'Unblock' }} {{ $user->username }}?">
                                            @csrf
                                            <button class="btn btn--ghost btn--sm {{ $user->status === 'active' ? 'text-red' : 'text-green' }}" type="submit">{{ $user->status === 'active' ? 'Block' : 'Unblock' }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9"><div class="empty"><strong>No accounts found</strong><p>Adjust filters or add a player.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $users->links('partials.pagination') }}
    </div>
@endsection
