@extends('layouts.admin')

@section('title', $user->username)

@section('content')
    <div class="classic-level-head mb-14">
        <div class="flex-center" style="gap:12px">
            <span class="avatar avatar--lg" style="background:var(--panel-primary);color:#fff;border:0">{{ $user->initials }}</span>
            <div>
                <h1>{{ $user->name }}</h1>
                <p style="color:#666;font-size:.8rem;margin:3px 0 0">{{ '@'.$user->username }} · {{ $user->agent_code }} · {{ strtoupper($config['label']) }}</p>
            </div>
        </div>
        <div class="actions-row">
            <span class="badge {{ $user->status === 'active' ? 'badge--success' : 'badge--danger' }}">{{ strtoupper($user->status) }}</span>
            <a class="btn btn--outline" href="{{ route('admin.hierarchy.edit', [$level, $user]) }}">Edit account</a>
            <a class="btn btn--primary" href="{{ route('admin.cash.index', $level) }}">Cash transaction</a>
            <a class="btn btn--ghost" href="{{ route('admin.hierarchy.index', $level) }}">← List</a>
        </div>
    </div>

    <div class="admin-stat-grid">
        <div class="stat">
            <div class="stat__label">Coins</div>
            <div class="stat__value">{{ number_format($stats['coins'], 0) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Members below</div>
            <div class="stat__value">{{ number_format($stats['members']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Turnover</div>
            <div class="stat__value">{{ number_format($stats['turnover'], 0) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Commission</div>
            <div class="stat__value">{{ number_format($stats['commission'], 2) }}</div>
        </div>
    </div>

    <div class="admin-grid-2 mt-14">
        <div class="classic-card" style="margin-top:0">
            <div class="classic-card__head"><span>ACCOUNT DETAILS</span></div>
            <div class="table-wrap">
                <table class="data-table" style="min-width:0">
                    <tbody>
                        <tr><td style="color:#777">Username</td><td class="strong">{{ $user->username }}</td></tr>
                        <tr><td style="color:#777">Code</td><td>{{ $user->agent_code }}</td></tr>
                        <tr><td style="color:#777">Role</td><td>{{ strtoupper($config['label']) }}</td></tr>
                        <tr><td style="color:#777">Parent</td><td>{{ $user->parent?->username ?? 'Top level' }}</td></tr>
                        <tr><td style="color:#777">Reference</td><td>{{ $user->reference ?? '—' }}</td></tr>
                        <tr><td style="color:#777">Email</td><td>{{ $user->email }}</td></tr>
                        <tr><td style="color:#777">Phone</td><td>{{ $user->phone ?: '—' }}</td></tr>
                        <tr><td style="color:#777">Share</td><td>{{ number_format($user->share_percent, 2) }}%</td></tr>
                        <tr><td style="color:#777">Company share</td><td>{{ number_format($user->company_share_percent, 2) }}%</td></tr>
                        <tr><td style="color:#777">Match commission</td><td>{{ number_format($user->commission_rate, 2) }}%</td></tr>
                        <tr><td style="color:#777">Session commission</td><td>{{ number_format($user->session_commission_percent, 2) }}%</td></tr>
                        <tr><td style="color:#777">Casino commission</td><td>{{ number_format($user->casino_commission_percent, 2) }}%</td></tr>
                        <tr><td style="color:#777">Matka commission</td><td>{{ number_format($user->matka_commission_percent, 2) }}%</td></tr>
                        <tr><td style="color:#777">Secure code</td><td class="mono">{{ $user->secure_code ?? '—' }}</td></tr>
                        <tr><td style="color:#777">Joined</td><td>{{ $user->created_at->format('d M Y, h:i A') }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="classic-card" style="margin-top:0">
            <div class="classic-card__head"><span>RECENT COIN LEDGER</span><a href="{{ route('admin.ledger.show', $level) }}">Open ledger →</a></div>
            <div class="table-wrap">
                <table class="data-table" style="min-width:0">
                    <thead><tr><th>Type</th><th>Amount</th><th>Balance</th><th>Date</th></tr></thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->type_label }}<span class="sub">{{ $transaction->description }}</span></td>
                                <td class="mono {{ $transaction->amount >= 0 ? 'text-green' : 'text-red' }}">{{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 0) }}</td>
                                <td class="mono">{{ number_format($transaction->balance_after, 0) }}</td>
                                <td><span class="sub" style="margin:0">{{ $transaction->created_at->format('d M, H:i') }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><div class="empty">No coin transactions.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($bets->isNotEmpty())
        <div class="classic-card">
            <div class="classic-card__head"><span>RECENT BETS</span></div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Ticket</th><th>Match</th><th>Selection</th><th>Stake</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                        @foreach($bets as $bet)
                            <tr>
                                <td><span class="ticket-code">{{ $bet->bet_code }}</span></td>
                                <td>{{ $bet->match_label }}</td>
                                <td>{{ $bet->selection_name }} @ {{ odds($bet->odds) }}</td>
                                <td class="mono">{{ number_format($bet->stake, 0) }}</td>
                                <td><span class="badge badge--{{ $bet->status_class }}">{{ $bet->status_label }}</span></td>
                                <td><span class="sub" style="margin:0">{{ $bet->created_at->format('d M, H:i') }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
