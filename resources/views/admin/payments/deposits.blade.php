@extends('layouts.admin')

@section('title', 'Deposits')
@section('page-title', 'Deposits')
@section('page-subtitle', 'Approve or reject player deposit requests')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>Deposit requests</h1>
            <p>Verify references before crediting player wallets.</p>
        </div>
        <a class="btn btn--outline btn--sm" href="{{ route('admin.payments.withdrawals') }}">Withdrawals →</a>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Pending requests</div>
            <div class="stat__value">{{ number_format($summary['pending']) }}</div>
            <div class="stat__meta">{{ money($summary['pending_amount']) }} waiting</div>
        </div>
        <div class="stat">
            <div class="stat__label">Approved volume</div>
            <div class="stat__value">{{ short_money($summary['approved_amount']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">All requests</div>
            <div class="stat__value">{{ number_format($summary['total']) }}</div>
        </div>
    </div>

    <form class="card filter-bar mt-20" method="GET" action="{{ route('admin.payments.deposits') }}">
        <input class="input search" type="search" name="q" value="{{ request('q') }}" placeholder="Search player…">
        <select class="select" name="status">
            <option value="">All statuses</option>
            @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn--primary" type="submit">Filter</button>
        @if(request()->hasAny(['q', 'status']))
            <a class="btn btn--ghost" href="{{ route('admin.payments.deposits') }}">Reset</a>
        @endif
    </form>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Deposit queue</h3><span class="badge badge--muted">{{ $deposits->total() }} results</span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Player</th><th>Amount</th><th>Method / reference</th><th>Status</th><th>Requested</th><th>Review</th></tr>
                </thead>
                <tbody>
                    @forelse($deposits as $deposit)
                        <tr>
                            <td>#{{ $deposit->id }}</td>
                            <td>
                                <a href="{{ route('admin.users.show', $deposit->user_id) }}">{{ $deposit->user?->username }}</a>
                                <span class="sub">{{ $deposit->user?->name }} · {{ ucfirst($deposit->user?->role ?? 'client') }}</span>
                            </td>
                            <td class="mono strong">{{ money($deposit->amount) }}</td>
                            <td>{{ $deposit->method_label }}<span class="sub">{{ $deposit->transaction_ref }}</span></td>
                            <td><span class="badge badge--{{ $deposit->status_class }}">{{ ucfirst($deposit->status) }}</span></td>
                            <td><span class="sub" style="margin:0">{{ $deposit->created_at->format('d M, h:i A') }}</span></td>
                            <td>
                                @if($deposit->status === 'pending')
                                    <details class="review-pop">
                                        <summary class="btn btn--outline btn--sm">Review</summary>
                                        <div class="mt-8" style="min-width:240px">
                                            <form method="POST" action="{{ route('admin.payments.deposits.approve', $deposit) }}" class="mb-8">
                                                @csrf
                                                <input class="input" name="admin_note" placeholder="Approval note (optional)">
                                                <button class="btn btn--success btn--sm mt-8" type="submit" data-confirm="Approve and credit {{ money($deposit->amount) }} to {{ $deposit->user?->username }}?">Approve &amp; credit</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.payments.deposits.reject', $deposit) }}">
                                                @csrf
                                                <input class="input" name="admin_note" placeholder="Rejection reason">
                                                <button class="btn btn--danger btn--sm mt-8" type="submit" data-confirm="Reject this deposit request?">Reject</button>
                                            </form>
                                        </div>
                                    </details>
                                @else
                                    <span class="sub" style="margin:0">
                                        {{ $deposit->reviewer?->username ?? 'System' }} · {{ $deposit->reviewed_at?->format('d M, H:i') }}
                                        @if($deposit->admin_note)<br>{{ $deposit->admin_note }}@endif
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty"><strong>No deposit requests</strong><p>The queue is clear.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $deposits->links('partials.pagination') }}
    </div>
@endsection
