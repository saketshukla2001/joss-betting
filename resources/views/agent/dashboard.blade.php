@extends('layouts.agent')

@section('title', 'Agent Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Agency performance · '.$agent->agent_code)

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>Welcome, {{ \Illuminate\Support\Str::of($agent->name)->explode(' ')->first() }}.</h1>
            <p>Your players, turnover and commission at a glance.</p>
        </div>
        <div class="actions-row">
            <a class="btn btn--outline btn--sm" href="{{ route('agent.commissions.index') }}">Commission</a>
            @php $nextLevel = \App\Support\Hierarchy::nextLevelFor($agent->role); $nextConfig = $nextLevel ? \App\Support\Hierarchy::get($nextLevel) : null; @endphp
            @if($nextConfig && $agent->hasPanelModule('network') && $agent->hasPanelModule('create'))
                <a class="btn btn--primary btn--sm" href="{{ route('agent.hierarchy.create') }}">+ Create {{ $nextConfig['label'] }}</a>
            @endif
        </div>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Downline players</div>
            <div class="stat__value">{{ number_format($stats['players']) }}</div>
            <div class="stat__meta">{{ $stats['direct_players'] }} direct · {{ $stats['sub_agents'] }} {{ $stats['sub_agents'] === 1 ? 'sub-agent' : 'sub-agents' }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Downline turnover</div>
            <div class="stat__value">{{ short_money($stats['turnover']) }}</div>
            <div class="stat__meta">{{ $stats['open_bets'] }} open bets</div>
        </div>
        <div class="stat">
            <div class="stat__label">Pending commission</div>
            <div class="stat__value text-amber">{{ money($stats['pending_commission']) }}</div>
            <div class="stat__meta">Accrued, not yet paid</div>
        </div>
        <div class="stat">
            <div class="stat__label">Paid commission</div>
            <div class="stat__value text-green">{{ money($stats['paid_commission']) }}</div>
            <div class="stat__meta">All time</div>
        </div>
    </div>

    <div class="admin-grid-2 mt-20">
        <div>
            <div class="admin-card">
            <div class="admin-card__head">
                <h3>Recent downline bets</h3>
                <a class="link-more" href="{{ route('agent.bets.index') }}">All bets →</a>
            </div>
            <div class="admin-card__body">
                        @forelse($recentBets as $bet)
                            <div class="bet-mini">
                                <div class="bet-mini__main">
                                    <div class="bet-mini__match">{{ $bet->match_label }}</div>
                                    <div class="bet-mini__sel">{{ $bet->user?->username }} · {{ $bet->selection_name }} @ {{ odds($bet->odds) }} · {{ $bet->created_at->format('d M, H:i') }}</div>
                                </div>
                                <div class="bet-mini__right">
                                    <div class="bet-mini__stake">{{ money($bet->stake) }}</div>
                                    <span class="badge badge--{{ $bet->status_class }}">{{ $bet->status_label }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="empty"><strong>No bets from your players yet</strong><p>Add a player and share their login to start.</p></div>
                        @endforelse
                    </div>
            </div>
        </div>

        <div>
            <div class="admin-card">
                <div class="admin-card__head"><h3>Commission terms</h3></div>
                <div class="admin-card__body">
                    <ul class="info-list">
                        <li><span>Rate</span><strong class="text-lime">{{ number_format($agent->commission_rate, 2) }}%</strong></li>
                        <li><span>Basis</span><strong>{{ $agent->commission_type === 'net_loss' ? 'Net loss' : 'Turnover' }}</strong></li>
                        <li><span>Pending balance</span><strong>{{ money($agent->commission_balance) }}</strong></li>
                        <li><span>Wallet balance</span><strong>{{ money($agent->balance) }}</strong></li>
                        <li><span>Agent code</span><strong>{{ $agent->agent_code }}</strong></li>
                    </ul>
                    <a class="btn btn--outline btn--block btn--sm mt-14" href="{{ route('agent.commissions.index') }}">View commission ledger</a>
                </div>
            </div>

            <div class="admin-card mt-20">
                <div class="admin-card__head"><h3>Top players</h3></div>
                <div class="admin-card__body">
                    @forelse($topPlayers as $row)
                        <div class="bet-mini">
                            <div class="bet-mini__main">
                                <div class="bet-mini__match">{{ $row->user?->username }}</div>
                                <div class="bet-mini__sel">{{ $row->bets_count }} bets</div>
                            </div>
                            <strong>{{ money($row->total_stake) }}</strong>
                        </div>
                    @empty
                        <p class="text-muted" style="font-size:.8rem">No player activity yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
