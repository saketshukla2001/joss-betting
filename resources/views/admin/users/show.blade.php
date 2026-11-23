@extends('layouts.admin')

@section('title', $user->username)
@section('page-title', 'Player profile')
@section('page-subtitle', $user->name.' · '.$user->username)

@section('content')
    <div class="admin-page-head">
        <div class="flex-center" style="gap:12px">
            <span class="avatar avatar--lg">{{ $user->initials }}</span>
            <div>
                <h1 style="margin:0">{{ $user->name }}</h1>
                <p>{{ '@'.$user->username }} · {{ $user->email }} · joined {{ $user->created_at->format('d M Y') }}</p>
            </div>
        </div>
        <div class="actions-row">
            <span class="badge {{ $user->role === 'admin' ? 'badge--info' : ($user->role === 'agent' ? 'badge--lime' : 'badge--muted') }}">{{ ucfirst($user->role) }}</span>
            <span class="badge {{ $user->status === 'active' ? 'badge--success' : 'badge--danger' }}">{{ ucfirst($user->status) }}</span>
            <a class="btn btn--outline btn--sm" href="{{ $user->isAgent() ? route('admin.agents.edit', $user) : route('admin.users.edit', $user) }}">Edit profile</a>
            @if($user->id !== auth()->id())
                <form method="POST" action="{{ route('admin.users.toggle', $user) }}" data-confirm="Change status for {{ $user->username }}?">
                    @csrf
                    <button class="btn {{ $user->status === 'active' ? 'btn--danger' : 'btn--success' }} btn--sm" type="submit">{{ $user->status === 'active' ? 'Block player' : 'Unblock player' }}</button>
                </form>
            @endif
            <a class="btn btn--ghost btn--sm" href="{{ route('admin.users.index') }}">← Players</a>
        </div>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Wallet balance</div>
            <div class="stat__value">{{ money($user->balance) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Exposure</div>
            <div class="stat__value">{{ money($user->exposure) }}</div>
            <div class="stat__meta">{{ $stats['open'] }} open bets</div>
        </div>
        <div class="stat">
            <div class="stat__label">Lifetime staked</div>
            <div class="stat__value">{{ money($stats['staked']) }}</div>
            <div class="stat__meta">{{ $user->bets_count }} tickets</div>
        </div>
        <div class="stat">
            <div class="stat__label">Returns paid</div>
            <div class="stat__value">{{ money($stats['returns']) }}</div>
            <div class="stat__meta">{{ money($stats['deposits']) }} deposited · {{ money($stats['withdrawals']) }} withdrawn</div>
        </div>
    </div>

    <div class="admin-grid-2 mt-20">
        <div class="admin-card">
            <div class="admin-card__head"><h3>Adjust wallet</h3><span class="badge badge--muted">Ledgered</span></div>
            <div class="admin-card__body">
                <form method="POST" action="{{ route('admin.users.balance', $user) }}">
                    @csrf
                    <div class="form-grid">
                        <div class="field">
                            <label for="amount">Amount</label>
                            <div class="input-group">
                                <span class="prefix">{{ $siteSettings['currency'] }}</span>
                                <input class="input" id="amount" name="amount" type="number" min="1" step="0.01" required>
                            </div>
                        </div>
                        <div class="field">
                            <label for="direction">Direction</label>
                            <select class="select" id="direction" name="direction" required>
                                <option value="credit">Credit (+)</option>
                                <option value="debit">Debit (−)</option>
                            </select>
                        </div>
                    </div>
                    <div class="field">
                        <label for="note">Reason / note</label>
                        <input class="input" id="note" name="note" type="text" placeholder="e.g. Bonus credit, manual correction" required>
                    </div>
                    <button class="btn btn--primary btn--sm" type="submit">Apply adjustment</button>
                </form>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card__head"><h3>Reset password</h3></div>
            <div class="admin-card__body">
                <form method="POST" action="{{ route('admin.users.password', $user) }}">
                    @csrf
                    <div class="form-grid">
                        <div class="field">
                            <label for="password">New password</label>
                            <input class="input" id="password" name="password" type="password" required autocomplete="new-password">
                        </div>
                        <div class="field">
                            <label for="password_confirmation">Confirm</label>
                            <input class="input" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                        </div>
                    </div>
                    <button class="btn btn--dark btn--sm" type="submit">Reset password</button>
                    <p class="hint mt-8">The player will need the new password for their next sign-in.</p>
                </form>
            </div>
        </div>
    </div>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Bet history</h3><a class="link-more" href="{{ route('admin.bets.index', ['q' => $user->username]) }}">Filter all bets →</a></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Ticket</th><th>Match</th><th>Selection</th><th>Odds</th><th>Stake</th><th>Status</th><th>Placed</th></tr></thead>
                <tbody>
                    @forelse($bets as $bet)
                        <tr>
                            <td><a class="ticket-code" href="{{ route('admin.bets.show', $bet) }}">{{ $bet->bet_code }}</a></td>
                            <td>{{ $bet->match_label }}</td>
                            <td>{{ $bet->selection_name }}</td>
                            <td class="mono">{{ odds($bet->odds) }}</td>
                            <td class="mono">{{ money($bet->stake) }}</td>
                            <td><span class="badge badge--{{ $bet->status_class }}">{{ $bet->status_label }}</span></td>
                            <td><span class="sub" style="margin:0">{{ $bet->created_at->format('d M, h:i A') }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty">No bets for this player.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $bets->links('partials.pagination') }}
    </div>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Wallet transactions</h3></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Type</th><th>Description</th><th>Amount</th><th>Balance after</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->type_label }}</td>
                            <td>{{ $transaction->description }}</td>
                            <td class="mono {{ $transaction->amount >= 0 ? 'text-green' : 'text-red' }}">{{ $transaction->amount >= 0 ? '+' : '' }}{{ money($transaction->amount) }}</td>
                            <td class="mono">{{ money($transaction->balance_after) }}</td>
                            <td><span class="badge badge--{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}">{{ $transaction->status_label }}</span></td>
                            <td><span class="sub" style="margin:0">{{ $transaction->created_at->format('d M, h:i A') }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty">No transactions yet.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $transactions->links('partials.pagination') }}
    </div>
@endsection
