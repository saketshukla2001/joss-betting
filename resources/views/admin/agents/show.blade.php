@extends('layouts.admin')

@section('title', $agent->username)
@section('page-title', 'Agent profile')
@section('page-subtitle', $agent->name.' · '.$agent->agent_code)

@section('content')
    <div class="admin-page-head">
        <div class="flex-center" style="gap:12px">
            <span class="avatar avatar--lg">{{ $agent->initials }}</span>
            <div>
                <h1 style="margin:0">{{ $agent->name }}</h1>
                <p>{{ '@'.$agent->username }} · {{ $agent->agent_code }} · {{ number_format($agent->commission_rate, 2) }}% {{ $agent->commission_type === 'net_loss' ? 'net loss' : 'turnover' }} commission</p>
            </div>
        </div>
        <div class="actions-row">
            <span class="badge {{ $agent->status === 'active' ? 'badge--success' : 'badge--danger' }}">{{ ucfirst($agent->status) }}</span>
            <a class="btn btn--outline btn--sm" href="{{ route('admin.agents.edit', $agent) }}">Edit agent</a>
            @if($stats['pending'] > 0)
                <form method="POST" action="{{ route('admin.agents.payout', $agent) }}" data-confirm="Pay {{ money($stats['pending']) }} pending commission?">
                    @csrf
                    <button class="btn btn--success btn--sm" type="submit">Pay commission</button>
                </form>
            @endif
            <a class="btn btn--ghost btn--sm" href="{{ route('admin.agents.index') }}">← Agents</a>
        </div>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Direct players</div>
            <div class="stat__value">{{ number_format($stats['players']) }}</div>
            <div class="stat__meta">{{ $stats['downline_players'] }} downline · {{ $stats['sub_agents'] }} {{ $stats['sub_agents'] === 1 ? 'sub-agent' : 'sub-agents' }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Downline turnover</div>
            <div class="stat__value">{{ short_money($stats['turnover']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Pending commission</div>
            <div class="stat__value text-amber">{{ money($stats['pending']) }}</div>
            <div class="stat__meta">Agent balance {{ money($agent->commission_balance) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Paid commission</div>
            <div class="stat__value text-green">{{ money($stats['paid']) }}</div>
            <div class="stat__meta">Wallet {{ money($agent->balance) }}</div>
        </div>
    </div>

    <div class="admin-grid-2 mt-20">
        <div class="admin-card">
            <div class="admin-card__head"><h3>Agency details</h3></div>
            <div class="admin-card__body">
                <ul class="info-list">
                    <li><span>Agent code</span><strong>{{ $agent->agent_code }}</strong></li>
                    <li><span>Parent</span><strong>{{ $agent->parent?->username ?? 'Top level' }}</strong></li>
                    <li><span>Commission rate</span><strong>{{ number_format($agent->commission_rate, 2) }}%</strong></li>
                    <li><span>Basis</span><strong>{{ $agent->commission_type === 'net_loss' ? 'Net loss' : 'Turnover' }}</strong></li>
                    <li><span>Commission balance</span><strong>{{ money($agent->commission_balance) }}</strong></li>
                    <li><span>Email</span><strong>{{ $agent->email }}</strong></li>
                    <li><span>Phone</span><strong>{{ $agent->phone ?: '—' }}</strong></li>
                    <li><span>Joined</span><strong>{{ $agent->created_at->format('d M Y') }}</strong></li>
                </ul>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card__head"><h3>Latest commission</h3><a class="link-more" href="{{ route('admin.commissions.index', ['agent_id' => $agent->id]) }}">All entries →</a></div>
            <div class="admin-card__body">
                @forelse($commissions as $entry)
                    <div class="bet-mini">
                        <div class="bet-mini__main">
                            <div class="bet-mini__match">{{ $entry->player?->username }} · {{ $entry->bet?->bet_code }}</div>
                            <div class="bet-mini__sel">{{ $entry->created_at->format('d M, h:i A') }} · {{ $entry->basis_label }} @ {{ number_format($entry->rate, 2) }}%</div>
                        </div>
                        <div class="bet-mini__right">
                            <div class="bet-mini__stake text-lime">{{ money($entry->amount) }}</div>
                            <span class="badge badge--{{ $entry->status_class }}">{{ ucfirst($entry->status) }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted" style="font-size:.8rem">No commission accrued yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    @if($subAgents->isNotEmpty())
        <div class="admin-card mt-20">
            <div class="admin-card__head"><h3>Sub-agents</h3><span class="badge badge--muted">{{ $subAgents->count() }}</span></div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Agent</th><th>Code</th><th>Players</th><th>Rate</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach($subAgents as $subAgent)
                            <tr>
                                <td>{{ $subAgent->name }} <span class="sub">{{ '@'.$subAgent->username }}</span></td>
                                <td>{{ $subAgent->agent_code }}</td>
                                <td>{{ $subAgent->players_count }}</td>
                                <td>{{ number_format($subAgent->commission_rate, 2) }}% {{ $subAgent->commission_type === 'net_loss' ? 'net loss' : 'turnover' }}</td>
                                <td><span class="badge {{ $subAgent->status === 'active' ? 'badge--success' : 'badge--danger' }}">{{ ucfirst($subAgent->status) }}</span></td>
                                <td><a class="btn btn--ghost btn--sm" href="{{ route('admin.agents.show', $subAgent) }}">View</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Direct players</h3><span class="badge badge--muted">{{ $players->count() }}</span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Player</th><th>Status</th><th>Balance</th><th>Exposure</th><th>Bets</th><th>Joined</th><th></th></tr></thead>
                <tbody>
                    @forelse($players as $player)
                        <tr>
                            <td>
                                <div class="user-cell"><span class="avatar-sm">{{ $player->initials }}</span><span><strong>{{ $player->name }}</strong><span>{{ '@'.$player->username }}</span></span></div>
                            </td>
                            <td><span class="badge {{ $player->status === 'active' ? 'badge--success' : 'badge--danger' }}">{{ ucfirst($player->status) }}</span></td>
                            <td class="mono">{{ money($player->balance) }}</td>
                            <td class="mono">{{ money($player->exposure) }}</td>
                            <td>{{ $player->bets_count }}</td>
                            <td><span class="sub" style="margin:0">{{ $player->created_at->format('d M Y') }}</span></td>
                            <td><a class="btn btn--ghost btn--sm" href="{{ route('admin.users.show', $player) }}">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty"><strong>No players under this agent</strong></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-grid-2 mt-20">
        <div class="admin-card">
            <div class="admin-card__head"><h3>Recent downline bets</h3></div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Ticket</th><th>Player</th><th>Stake</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($bets as $bet)
                            <tr>
                                <td><a class="ticket-code" href="{{ route('admin.bets.show', $bet) }}">{{ $bet->bet_code }}</a></td>
                                <td>{{ $bet->user?->username }}</td>
                                <td class="mono">{{ money($bet->stake) }}</td>
                                <td><span class="badge badge--{{ $bet->status_class }}">{{ $bet->status_label }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><div class="empty">No bets yet.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card__head"><h3>Commission entries</h3></div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Player</th><th>Stake</th><th>Rate</th><th>Amount</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($commissions as $entry)
                            <tr>
                                <td>{{ $entry->player?->username }}</td>
                                <td class="mono">{{ money($entry->stake) }}</td>
                                <td>{{ number_format($entry->rate, 2) }}%</td>
                                <td class="mono text-lime">{{ money($entry->amount) }}</td>
                                <td><span class="badge badge--{{ $entry->status_class }}">{{ ucfirst($entry->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="empty">No commission entries.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
