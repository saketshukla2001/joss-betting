@extends('layouts.admin')

@section('title', 'Bet '.$bet->bet_code)
@section('page-title', 'Bet ticket')
@section('page-subtitle', $bet->bet_code.' · '.$bet->created_at->format('d M Y, h:i A'))

@section('content')
    <div class="admin-page-head">
        <div>
            <span class="badge badge--{{ $bet->status_class }}" style="margin-bottom:6px">{{ $bet->status_label }}</span>
            <h1>{{ $bet->bet_code }}</h1>
            <p>{{ $bet->match_label }} · {{ $bet->market_name }}</p>
        </div>
        <div class="actions-row">
            <a class="btn btn--outline btn--sm" href="{{ route('admin.matches.show', $bet->match_id) }}">Open match control</a>
            <a class="btn btn--ghost btn--sm" href="{{ route('admin.bets.index') }}">← All bets</a>
        </div>
    </div>

    <div class="admin-grid-2">
        <div class="admin-card">
            <div class="admin-card__head"><h3>Ticket details</h3></div>
            <div class="admin-card__body">
                <ul class="info-list">
                    <li><span>Ticket code</span><strong class="ticket-code">{{ $bet->bet_code }}</strong></li>
                    <li><span>Status</span><strong><span class="badge badge--{{ $bet->status_class }}">{{ $bet->status_label }}</span></strong></li>
                    <li><span>Sport</span><strong>{{ $bet->sport_name }}</strong></li>
                    <li><span>League</span><strong>{{ $bet->league }}</strong></li>
                    <li><span>Match</span><strong>{{ $bet->match_label }}</strong></li>
                    <li><span>Market</span><strong>{{ $bet->market_name }}</strong></li>
                    <li><span>Selection</span><strong>{{ $bet->selection_name }}</strong></li>
                    <li><span>Odds at placement</span><strong class="text-lime">{{ odds($bet->odds) }}</strong></li>
                    <li><span>Stake</span><strong>{{ money($bet->stake) }}</strong></li>
                    <li><span>Potential return</span><strong>{{ money($bet->potential_return) }}</strong></li>
                    <li><span>Payout</span><strong>{{ money($bet->payout) }}</strong></li>
                    <li><span>Placed</span><strong>{{ $bet->created_at->format('d M Y, h:i A') }}</strong></li>
                    <li><span>Settled</span><strong>{{ $bet->settled_at?->format('d M Y, h:i A') ?? '—' }}</strong></li>
                    <li><span>IP address</span><strong>{{ $bet->ip_address ?? '—' }}</strong></li>
                </ul>
            </div>
        </div>

        <div>
            <div class="admin-card">
                <div class="admin-card__head"><h3>Player</h3></div>
                <div class="admin-card__body">
                    @if($bet->user)
                        <div class="user-cell mb-14">
                            <span class="avatar">{{ $bet->user->initials }}</span>
                            <span>
                                <strong>{{ $bet->user->name }}</strong>
                                <span>{{ '@'.$bet->user->username }} · {{ $bet->user->email }}</span>
                            </span>
                        </div>
                        <ul class="info-list">
                            <li><span>Balance</span><strong>{{ money($bet->user->balance) }}</strong></li>
                            <li><span>Exposure</span><strong>{{ money($bet->user->exposure) }}</strong></li>
                            <li><span>Status</span><strong>{{ ucfirst($bet->user->status) }}</strong></li>
                        </ul>
                        <a class="btn btn--outline btn--block btn--sm mt-14" href="{{ route('admin.users.show', $bet->user) }}">Open player profile</a>
                    @else
                        <p class="text-muted">Player account no longer exists.</p>
                    @endif
                </div>
            </div>

            @if($bet->status === 'pending')
                <div class="admin-card mt-20">
                    <div class="admin-card__head"><h3>Edit bet</h3></div>
                    <div class="admin-card__body">
                        <form method="POST" action="{{ route('admin.bets.update', $bet) }}">
                            @csrf
                            @method('PUT')
                            <div class="field">
                                <label for="selection_id">Selection</label>
                                <select class="select" id="selection_id" name="selection_id">
                                    @foreach($marketOptions as $market)
                                        <optgroup label="{{ $market->name }}{{ $market->status !== 'open' ? ' · '.strtoupper($market->status) : '' }}">
                                            @foreach($market->selections as $option)
                                                <option value="{{ $option->id }}"
                                                        data-odds="{{ $option->odds }}"
                                                        @selected($option->id === $bet->selection_id)
                                                        @disabled($market->status !== 'open' || $option->status !== 'active')>
                                                    {{ $option->name }} @ {{ odds($option->odds) }}{{ $option->status !== 'active' ? ' · '.strtoupper($option->status) : '' }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-grid">
                                <div class="field">
                                    <label for="stake">Stake (₹)</label>
                                    <input class="input" id="stake" name="stake" type="number" step="0.01" min="1"
                                           value="{{ old('stake', $bet->stake) }}" required>
                                </div>
                                <div class="field">
                                    <label for="odds">Odds</label>
                                    <input class="input" id="odds" name="odds" type="number" step="0.01" min="1.01"
                                           value="{{ old('odds', $bet->odds) }}" required>
                                </div>
                            </div>
                            <button class="btn btn--primary btn--block btn--sm mt-8" type="submit"
                                    data-confirm="Update this ticket? The player's balance will be adjusted by the stake difference.">
                                Save changes
                            </button>
                        </form>
                        <p class="hint mt-8">
                            Selection can only move to another open option in the <strong>same match</strong>.
                            Changing the stake adjusts the player's balance and exposure; potential return is
                            recalculated as stake × odds. Every change is written to the audit log and the
                            ticket snapshot is rewritten, so all screens and reports show the corrected bet.
                        </p>
                    </div>
                </div>
            @else
                <div class="admin-card mt-20">
                    <div class="admin-card__head"><h3>Edit bet</h3></div>
                    <div class="admin-card__body">
                        <p class="text-muted" style="font-size:.8rem">
                            This ticket is already {{ $bet->status_label }} — editing is locked. Void or re-place
                            the bet from the match control screen if a correction is needed.
                        </p>
                    </div>
                </div>
            @endif

            <div class="admin-card mt-20">
                <div class="admin-card__head"><h3>Ledger entries</h3></div>
                <div class="admin-card__body">
                    @forelse($transactions as $transaction)
                        <div class="bet-mini">
                            <div class="bet-mini__main">
                                <div class="bet-mini__match">{{ $transaction->type_label }}</div>
                                <div class="bet-mini__sel">{{ $transaction->created_at->format('d M, h:i A') }} · {{ $transaction->status_label }}</div>
                            </div>
                            <div class="bet-mini__right">
                                <div class="bet-mini__stake {{ $transaction->amount >= 0 ? 'text-green' : 'text-red' }}">{{ $transaction->amount >= 0 ? '+' : '' }}{{ money($transaction->amount) }}</div>
                                <span class="sub" style="margin:0">Bal {{ money($transaction->balance_after) }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted" style="font-size:.8rem">No ledger entries linked to this ticket.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        var selection = document.getElementById('selection_id');
        var odds = document.getElementById('odds');
        if (!selection || !odds) return;

        selection.addEventListener('change', function () {
            var option = selection.options[selection.selectedIndex];
            var value = option && option.dataset ? option.dataset.odds : null;
            if (value && parseFloat(value) > 0) odds.value = value;
        });
    })();
</script>
@endpush
