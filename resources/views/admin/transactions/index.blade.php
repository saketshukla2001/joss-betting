@extends('layouts.admin')

@section('title', 'Transactions')
@section('page-title', 'Transactions')
@section('page-subtitle', 'Wallet ledger across every player')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>Wallet ledger</h1>
            <p>Every credit and debit, with the balance after each movement.</p>
        </div>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Credits</div>
            <div class="stat__value">{{ short_money($summary['credit']) }}</div>
        </div>
        <div class="stat stat--danger">
            <div class="stat__label">Debits</div>
            <div class="stat__value">{{ short_money($summary['debit']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Entries</div>
            <div class="stat__value">{{ number_format($summary['count']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Net movement</div>
            <div class="stat__value">{{ short_money($summary['credit'] - $summary['debit']) }}</div>
        </div>
    </div>

    <form class="card filter-bar mt-20" method="GET" action="{{ route('admin.transactions.index') }}">
        <input class="input search" type="search" name="q" value="{{ request('q') }}" placeholder="Search player or description…">
        <select class="select" name="type">
            <option value="">All types</option>
            @foreach($types as $type)
                <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucwords(str_replace('_', ' ', $type)) }}</option>
            @endforeach
        </select>
        <select class="select" name="status">
            <option value="">All statuses</option>
            @foreach(['completed' => 'Completed', 'pending' => 'Pending', 'rejected' => 'Rejected'] as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <input class="input" type="date" name="from" value="{{ request('from') }}">
        <input class="input" type="date" name="to" value="{{ request('to') }}">
        <button class="btn btn--primary" type="submit">Filter</button>
        @if(request()->hasAny(['q', 'type', 'status', 'from', 'to']))
            <a class="btn btn--ghost" href="{{ route('admin.transactions.index') }}">Reset</a>
        @endif
    </form>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Ledger entries</h3><span class="badge badge--muted">{{ $transactions->total() }} results</span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>ID</th><th>Player</th><th>Type</th><th>Description</th><th>Amount</th><th>Balance after</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>#{{ $transaction->id }}</td>
                            <td><a href="{{ route('admin.users.show', $transaction->user_id) }}">{{ $transaction->user?->username }}</a></td>
                            <td>{{ $transaction->type_label }}</td>
                            <td>{{ $transaction->description }}</td>
                            <td class="mono {{ $transaction->amount >= 0 ? 'text-green' : 'text-red' }}">{{ $transaction->amount >= 0 ? '+' : '' }}{{ money($transaction->amount) }}</td>
                            <td class="mono">{{ money($transaction->balance_after) }}</td>
                            <td><span class="badge badge--{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}">{{ $transaction->status_label }}</span></td>
                            <td><span class="sub" style="margin:0">{{ $transaction->created_at->format('d M, h:i A') }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="empty"><strong>No ledger entries</strong><p>Transactions will appear here.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $transactions->links('partials.pagination') }}
    </div>
@endsection
