@extends('layouts.admin')

@section('title', 'Commissions')
@section('page-title', 'Commissions')
@section('page-subtitle', 'Agent accruals and payouts')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>Commission ledger</h1>
            <p>Turnover or net-loss commission accrued from settled downline bets.</p>
        </div>
        <a class="btn btn--outline btn--sm" href="{{ route('admin.agents.index') }}">Agents</a>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Pending commission</div>
            <div class="stat__value">{{ money($summary['pending']) }}</div>
            <div class="stat__meta">Unpaid agent earnings</div>
        </div>
        <div class="stat">
            <div class="stat__label">Paid commission</div>
            <div class="stat__value text-green">{{ money($summary['paid']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Entries</div>
            <div class="stat__value">{{ number_format($summary['entries']) }}</div>
            <div class="stat__meta">One per settled bet / agent</div>
        </div>
        <div class="stat">
            <div class="stat__label">Agents with balance</div>
            <div class="stat__value">{{ $agents->count() }}</div>
        </div>
    </div>

    @if($agents->isNotEmpty())
        <div class="admin-card mt-20">
            <div class="admin-card__head"><h3>Ready to pay</h3></div>
            <div class="admin-card__body">
                @foreach($agents as $agent)
                    <div class="bet-mini">
                        <div class="bet-mini__main">
                            <div class="bet-mini__match">{{ $agent->name }} <span class="text-muted">· {{ '@'.$agent->username }}</span></div>
                            <div class="bet-mini__sel">{{ number_format($agent->commission_rate, 2) }}% {{ $agent->commission_type === 'net_loss' ? 'net loss' : 'turnover' }}</div>
                        </div>
                        <div class="bet-mini__right flex-center">
                            <strong class="text-lime">{{ money($agent->pending_commission) }}</strong>
                            <form method="POST" action="{{ route('admin.commissions.payout', $agent) }}" data-confirm="Pay {{ money($agent->pending_commission) }} to {{ $agent->username }}?">
                                @csrf
                                <button class="btn btn--success btn--sm" type="submit">Pay</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <form class="card filter-bar mt-20" method="GET" action="{{ route('admin.commissions.index') }}">
        <input class="input search" type="search" name="q" value="{{ request('q') }}" placeholder="Search agent or player…">
        <select class="select" name="agent_id">
            <option value="">All agents</option>
            @foreach(\App\Models\User::agents()->orderBy('name')->get() as $agent)
                <option value="{{ $agent->id }}" @selected((int) request('agent_id') === $agent->id)>{{ $agent->username }}</option>
            @endforeach
        </select>
        <select class="select" name="status">
            <option value="">All statuses</option>
            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            <option value="paid" @selected(request('status') === 'paid')>Paid</option>
        </select>
        <button class="btn btn--primary" type="submit">Filter</button>
        @if(request()->hasAny(['q', 'agent_id', 'status']))
            <a class="btn btn--ghost" href="{{ route('admin.commissions.index') }}">Reset</a>
        @endif
    </form>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Entries</h3><span class="badge badge--muted">{{ $entries->total() }}</span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Agent</th><th>Player</th><th>Ticket</th><th>Stake</th><th>Rate / basis</th><th>Commission</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($entries as $entry)
                        <tr>
                            <td><a href="{{ route('admin.agents.show', $entry->agent_id) }}">{{ $entry->agent?->username }}</a></td>
                            <td>{{ $entry->player?->username }}</td>
                            <td><a class="ticket-code" href="{{ route('admin.bets.show', $entry->bet_id) }}">{{ $entry->bet?->bet_code }}</a></td>
                            <td class="mono">{{ money($entry->stake) }}</td>
                            <td>{{ number_format($entry->rate, 2) }}% <span class="sub">{{ $entry->basis_label }}</span></td>
                            <td class="mono text-lime">{{ money($entry->amount) }}</td>
                            <td><span class="badge badge--{{ $entry->status_class }}">{{ ucfirst($entry->status) }}</span></td>
                            <td><span class="sub" style="margin:0">{{ $entry->created_at->format('d M, h:i A') }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="empty"><strong>No commission entries</strong><p>They accrue automatically when downline bets settle.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $entries->links('partials.pagination') }}
    </div>
@endsection
