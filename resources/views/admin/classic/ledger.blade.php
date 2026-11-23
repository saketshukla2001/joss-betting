@extends('layouts.admin')

@section('title', $config['label'])

@section('content')
    <div class="classic-level-head mb-14">
        <div>
            <h1>{{ strtoupper($config['label']) }}</h1>
            <p style="color:#666;font-size:.8rem;margin:3px 0 0">Coin movement for {{ $level === 'my' ? 'your account' : strtolower($config['label']).' accounts' }}.</p>
        </div>
        <a class="btn btn--outline" href="{{ route('admin.ledger.profit-loss') }}">Profit / Loss</a>
    </div>

    <div class="admin-stat-grid">
        <div class="stat"><div class="stat__label">Total credits</div><div class="stat__value">{{ number_format($summary['credit'], 0) }}</div></div>
        <div class="stat"><div class="stat__label">Total debits</div><div class="stat__value">{{ number_format($summary['debit'], 0) }}</div></div>
        <div class="stat"><div class="stat__label">Entries</div><div class="stat__value">{{ number_format($summary['entries']) }}</div></div>
        <div class="stat"><div class="stat__label">Net</div><div class="stat__value">{{ number_format($summary['credit'] - $summary['debit'], 0) }}</div></div>
    </div>

    <form class="filter-bar mt-14" method="GET" action="{{ route('admin.ledger.show', $level) }}">
        <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Search user or description…" style="flex:1;min-width:200px">
        <select class="select" name="type">
            <option value="">All types</option>
            @foreach(['deposit','withdrawal','bet','win','refund','admin_credit','admin_debit','bonus'] as $type)
                <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucwords(str_replace('_', ' ', $type)) }}</option>
            @endforeach
        </select>
        <input class="input" type="date" name="from" value="{{ request('from') }}">
        <input class="input" type="date" name="to" value="{{ request('to') }}">
        <button class="btn btn--primary" type="submit">Filter</button>
    </form>

    <div class="classic-card">
        <div class="classic-card__head"><span>{{ strtoupper($config['label']) }} ENTRIES</span><span>{{ $transactions->total() }} records</span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>User</th><th>Type</th><th>Description</th><th>Amount</th><th>Balance after</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->user?->username }}<span class="sub">{{ $transaction->user?->role_label }}</span></td>
                            <td>{{ $transaction->type_label }}</td>
                            <td>{{ $transaction->description }}</td>
                            <td class="mono {{ $transaction->amount >= 0 ? 'text-green' : 'text-red' }}">{{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 0) }}</td>
                            <td class="mono">{{ number_format($transaction->balance_after, 0) }}</td>
                            <td><span class="badge badge--{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}">{{ strtoupper($transaction->status) }}</span></td>
                            <td><span class="sub" style="margin:0">{{ $transaction->created_at->format('d M Y, h:i A') }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty"><strong>No ledger entries</strong></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $transactions->links('partials.pagination') }}
    </div>
@endsection
