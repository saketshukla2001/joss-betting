@extends('layouts.admin')

@section('title', 'Withdrawals')
@section('page-title', 'Withdrawals')
@section('page-subtitle', 'Review payout requests and release held funds')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>Withdrawal requests</h1>
            <p>Funds are already on hold. Approve to mark paid, or reject to refund the player.</p>
        </div>
        <a class="btn btn--outline btn--sm" href="{{ route('admin.payments.deposits') }}">← Deposits</a>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Pending payouts</div>
            <div class="stat__value">{{ number_format($summary['pending']) }}</div>
            <div class="stat__meta">{{ money($summary['pending_amount']) }} on hold</div>
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

    <form class="card filter-bar mt-20" method="GET" action="{{ route('admin.payments.withdrawals') }}">
        <input class="input search" type="search" name="q" value="{{ request('q') }}" placeholder="Search player…">
        <select class="select" name="status">
            <option value="">All statuses</option>
            @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn--primary" type="submit">Filter</button>
        @if(request()->hasAny(['q', 'status']))
            <a class="btn btn--ghost" href="{{ route('admin.payments.withdrawals') }}">Reset</a>
        @endif
    </form>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Payout queue</h3><span class="badge badge--muted">{{ $withdrawals->total() }} results</span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Player</th><th>Amount</th><th>Payout details</th><th>Status</th><th>Requested</th><th>Review</th></tr>
                </thead>
                <tbody>
                    @forelse($withdrawals as $withdrawal)
                        <tr>
                            <td>#{{ $withdrawal->id }}</td>
                            <td>
                                <a href="{{ route('admin.users.show', $withdrawal->user_id) }}">{{ $withdrawal->user?->username }}</a>
                                <span class="sub">{{ $withdrawal->user?->name }} · {{ ucfirst($withdrawal->user?->role ?? 'client') }}</span>
                            </td>
                            <td class="mono strong">{{ money($withdrawal->amount) }}</td>
                            <td>
                                {{ $withdrawal->method_label }} · {{ $withdrawal->account_name }}
                                <span class="sub">
                                    {{ $withdrawal->account_number }}
                                    @if($withdrawal->upi_id) · {{ $withdrawal->upi_id }} @endif
                                    @if($withdrawal->ifsc) · {{ $withdrawal->ifsc }} @endif
                                </span>
                            </td>
                            <td><span class="badge badge--{{ $withdrawal->status_class }}">{{ ucfirst($withdrawal->status) }}</span></td>
                            <td><span class="sub" style="margin:0">{{ $withdrawal->created_at->format('d M, h:i A') }}</span></td>
                            <td>
                                @if($withdrawal->status === 'pending')
                                    <details class="review-pop">
                                        <summary class="btn btn--outline btn--sm">Review</summary>
                                        <div class="mt-8" style="min-width:240px">
                                            <form method="POST" action="{{ route('admin.payments.withdrawals.approve', $withdrawal) }}" class="mb-8">
                                                @csrf
                                                <input class="input" name="admin_note" placeholder="Payment reference / note">
                                                <button class="btn btn--success btn--sm mt-8" type="submit" data-confirm="Mark this withdrawal paid?">Approve payout</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.payments.withdrawals.reject', $withdrawal) }}">
                                                @csrf
                                                <input class="input" name="admin_note" placeholder="Rejection reason">
                                                <button class="btn btn--danger btn--sm mt-8" type="submit" data-confirm="Reject and refund this withdrawal?">Reject &amp; refund</button>
                                            </form>
                                        </div>
                                    </details>
                                @else
                                    <span class="sub" style="margin:0">
                                        {{ $withdrawal->reviewer?->username ?? 'System' }} · {{ $withdrawal->reviewed_at?->format('d M, H:i') }}
                                        @if($withdrawal->admin_note)<br>{{ $withdrawal->admin_note }}@endif
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty"><strong>No withdrawal requests</strong><p>The payout queue is clear.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $withdrawals->links('partials.pagination') }}
    </div>
@endsection
