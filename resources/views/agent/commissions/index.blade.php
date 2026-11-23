@extends('layouts.agent')

@section('title', 'Commission')
@section('page-title', 'Commission')
@section('page-subtitle', number_format($agent->commission_rate, 2).'% · '.($agent->commission_type === 'net_loss' ? 'net loss' : 'turnover'))

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>Commission ledger</h1>
            <p>Earned from settled bets of your downline players.</p>
        </div>
        <a class="btn btn--outline btn--sm" href="{{ route('agent.bets.index') }}">Downline bets</a>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Pending commission</div>
            <div class="stat__value">{{ money($summary['pending']) }}</div>
            <div class="stat__meta">Admin will release this to your wallet</div>
        </div>
        <div class="stat">
            <div class="stat__label">Paid commission</div>
            <div class="stat__value text-green">{{ money($summary['paid']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Entries</div>
            <div class="stat__value">{{ number_format($summary['entries']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Commission balance</div>
            <div class="stat__value">{{ money($agent->commission_balance) }}</div>
            <div class="stat__meta">Wallet {{ money($agent->balance) }}</div>
        </div>
    </div>

    <div class="admin-card mt-20">
        <div class="admin-card__head">
            <h3>My commission terms</h3>
            <span class="badge badge--lime">{{ number_format($agent->commission_rate, 2) }}%</span>
        </div>
        <div class="admin-card__body">
            <div class="info-list">
                <li><span>Basis</span><strong>{{ $agent->commission_type === 'net_loss' ? 'Net loss — only losing stakes' : 'Turnover — every settled stake' }}</strong></li>
                <li><span>Agent code</span><strong>{{ $agent->agent_code }}</strong></li>
                <li><span>Upline</span><strong>{{ $agent->parent?->username ?? 'Top level' }}</strong></li>
            </div>
        </div>
    </div>

    <form class="card filter-bar mt-20" method="GET" action="{{ route('agent.commissions.index') }}">
        <input class="input search" type="search" name="q" value="{{ request('q') }}" placeholder="Player or ticket…">
        <select class="select" name="status">
            <option value="">All statuses</option>
            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            <option value="paid" @selected(request('status') === 'paid')>Paid</option>
        </select>
        <button class="btn btn--primary" type="submit">Filter</button>
    </form>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Entries</h3><span class="badge badge--muted">{{ $entries->total() }}</span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Player</th><th>Ticket</th><th>Stake</th><th>Rate</th><th>Basis</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($entries as $entry)
                        <tr>
                            <td>{{ $entry->player?->username }}</td>
                            <td><span class="ticket-code">{{ $entry->bet?->bet_code }}</span></td>
                            <td class="mono">{{ money($entry->stake) }}</td>
                            <td>{{ number_format($entry->rate, 2) }}%</td>
                            <td>{{ $entry->basis_label }}</td>
                            <td class="mono text-lime">{{ money($entry->amount) }}</td>
                            <td><span class="badge badge--{{ $entry->status_class }}">{{ ucfirst($entry->status) }}</span></td>
                            <td><span class="sub" style="margin:0">{{ $entry->created_at->format('d M, H:i') }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="empty"><strong>No commission yet</strong><p>Commission appears when your players' bets settle.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $entries->links('partials.pagination') }}
    </div>
@endsection
