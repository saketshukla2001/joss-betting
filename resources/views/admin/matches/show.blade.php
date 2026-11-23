@extends('layouts.admin')

@section('title', $match->label)
@section('page-title', 'Match control')
@section('page-subtitle', $match->league.' · '.$match->start_time->format('d M Y, h:i A'))

@section('content')
    @php
        $marketBadge = fn ($status) => match ($status) {
            'open' => 'badge--success',
            'suspended' => 'badge--warning',
            'settled' => 'badge--muted',
            'void' => 'badge--danger',
            default => 'badge--muted',
        };
    @endphp

    <div class="admin-page-head">
        <div>
            <div class="flex-center" style="gap:8px;margin-bottom:4px">
                <span class="badge badge--{{ $match->status_class }}">{{ $match->status_label }}</span>
                <span class="text-muted">{{ $match->sport?->icon }} {{ $match->sport?->name }}</span>
            </div>
            <h1>{{ $match->home_team }} <span class="text-muted" style="font-size:1rem">vs</span> {{ $match->away_team }}</h1>
            <p>{{ $match->league }} · {{ $match->start_time->format('D, d M Y · h:i A') }} @if($match->result) · Result: <strong class="text-lime">{{ $match->result_label }}</strong>@endif</p>
        </div>
        <div class="actions-row">
            <a class="btn btn--outline btn--sm" href="{{ route('admin.matches.edit', $match) }}">Edit match</a>
            <a class="btn btn--ghost btn--sm" href="{{ route('admin.matches.index') }}">← Matches</a>
            @if($match->status !== 'cancelled')
                <form method="POST" action="{{ route('admin.matches.void', $match) }}" data-confirm="Void this match and refund every open bet?">
                    @csrf
                    <button class="btn btn--danger btn--sm" type="submit">Void &amp; refund</button>
                </form>
            @endif
        </div>
    </div>

    <div class="admin-stat-grid">
        <div class="stat">
            <div class="stat__label">Tickets</div>
            <div class="stat__value">{{ number_format($betStats['count']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Pending bets</div>
            <div class="stat__value text-amber">{{ number_format($betStats['pending']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Total stake</div>
            <div class="stat__value" style="font-size:1.25rem">{{ money($betStats['stake']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Payouts</div>
            <div class="stat__value" style="font-size:1.25rem">{{ money($betStats['payout']) }}</div>
        </div>
    </div>

    <div class="admin-grid-2 admin-grid-2--wide mt-20">
        <div>
            <details class="admin-card mb-20">
                <summary class="admin-card__head" style="cursor:pointer"><h3>+ Add market</h3><span class="text-muted" style="font-size:.72rem">Open a new betting market</span></summary>
                <div class="admin-card__body">
                    <form method="POST" action="{{ route('admin.markets.store', $match) }}">
                        @csrf
                        <div class="form-grid">
                            <div class="field">
                                <label for="market-name">Market name</label>
                                <input class="input" id="market-name" name="name" type="text" placeholder="e.g. Total Goals — Over/Under 2.5" required>
                            </div>
                            <div class="field">
                                <label>&nbsp;</label>
                                <label class="check">
                                    <input type="checkbox" name="auto_settle" value="1">
                                    <span>Auto-settle from match result (1X2 only)</span>
                                </label>
                            </div>
                        </div>
                        <button class="btn btn--primary btn--sm" type="submit">Add market</button>
                    </form>
                </div>
            </details>

            <div class="admin-card">
                <div class="admin-card__head">
                    <h3>Markets &amp; selections</h3>
                    <span class="badge badge--muted">{{ $match->markets->count() }} markets</span>
                </div>
                <div class="admin-card__body">
                    @forelse($match->markets as $market)
                        <div class="market-admin">
                            <div class="market-admin__head">
                                <div>
                                    <h4>{{ $market->name }}</h4>
                                    <div class="flex-center" style="gap:6px;margin-top:4px;flex-wrap:wrap">
                                        <span class="badge {{ $marketBadge($market->status) }}">{{ $market->status_label }}</span>
                                        @if($market->auto_settle)<span class="badge badge--info">Auto 1X2</span>@endif
                                        <span class="text-muted" style="font-size:.68rem">{{ $market->pending_bets_count }} pending · {{ $market->all_bets_count }} total</span>
                                    </div>
                                </div>

                                @if(in_array($market->status, ['open', 'suspended'], true))
                                    <details class="edit-market">
                                        <summary>Edit market</summary>
                                        <form method="POST" action="{{ route('admin.markets.update', $market) }}" class="mt-8">
                                            @csrf
                                            @method('PATCH')
                                            <div class="flex gap-8" style="flex-wrap:wrap">
                                                <input class="input" name="name" value="{{ $market->name }}" required style="min-width:160px">
                                                <select class="select" name="status" style="min-width:110px">
                                                    <option value="open" @selected($market->status === 'open')>Open</option>
                                                    <option value="suspended" @selected($market->status === 'suspended')>Suspended</option>
                                                </select>
                                                <label class="check">
                                                    <input type="checkbox" name="auto_settle" value="1" @checked($market->auto_settle)>
                                                    <span>Auto 1X2</span>
                                                </label>
                                                <button class="btn btn--primary btn--sm" type="submit">Save</button>
                                            </div>
                                        </form>
                                        @if($market->all_bets_count === 0)
                                            <form method="POST" action="{{ route('admin.markets.destroy', $market) }}" class="mt-8" data-confirm="Delete this market?">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn--ghost btn--sm text-red" type="submit">Delete market</button>
                                            </form>
                                        @endif
                                    </details>
                                @endif
                            </div>

                            <div class="market-admin__body">
                                @if(in_array($market->status, ['open', 'suspended'], true))
                                    @foreach($market->selections as $selection)
                                        <div class="selection-row">
                                            <form method="POST" action="{{ route('admin.selections.update', $selection) }}" class="flex" style="flex:1;gap:6px;flex-wrap:wrap;align-items:center">
                                                @csrf
                                                @method('PATCH')
                                                <input class="input input--name" name="name" value="{{ $selection->name }}" required>
                                                <input class="input" name="odds" type="number" step="0.01" min="1.01" max="1000" value="{{ number_format($selection->odds, 2, '.', '') }}" style="width:80px" required>
                                                <input class="input" name="result_key" value="{{ $selection->result_key }}" placeholder="key" style="width:76px">
                                                <select class="select" name="status" style="width:104px">
                                                    <option value="active" @selected($selection->status === 'active')>Active</option>
                                                    <option value="suspended" @selected($selection->status === 'suspended')>Suspended</option>
                                                </select>
                                                <button class="btn btn--primary btn--sm" type="submit">Save</button>
                                            </form>
                                            @if($selection->bets()->count() === 0)
                                                <form method="POST" action="{{ route('admin.selections.destroy', $selection) }}" data-confirm="Delete selection {{ $selection->name }}?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn--ghost btn--sm text-red" type="submit" title="Delete">×</button>
                                                </form>
                                            @endif
                                        </div>
                                    @endforeach

                                    <form method="POST" action="{{ route('admin.selections.store', $market) }}" class="selection-row mt-8">
                                        @csrf
                                        <input class="input input--name" name="name" placeholder="New selection name" required>
                                        <input class="input" name="odds" type="number" step="0.01" min="1.01" max="1000" placeholder="Odds" style="width:80px" required>
                                        <input class="input" name="result_key" placeholder="home/away/draw" style="width:120px">
                                        <select class="select" name="status" style="width:104px">
                                            <option value="active">Active</option>
                                            <option value="suspended">Suspended</option>
                                        </select>
                                        <button class="btn btn--dark btn--sm" type="submit">+ Add</button>
                                    </form>
                                @else
                                    @foreach($market->selections as $selection)
                                        <div class="selection-row">
                                            <span class="selection-row__name">{{ $selection->name }}</span>
                                            <span class="mono text-lime">{{ odds($selection->odds) }}</span>
                                            @if($selection->result === 'win')
                                                <span class="badge badge--success">Won</span>
                                            @elseif($selection->result === 'lose')
                                                <span class="badge badge--muted">Lost</span>
                                            @elseif($selection->result === 'void')
                                                <span class="badge badge--muted">Void</span>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif

                                @if(in_array($market->status, ['open', 'suspended'], true))
                                    <details class="settle-box">
                                        <summary>Settle market</summary>
                                        <div class="settle-form">
                                            <form method="POST" action="{{ route('admin.markets.settle', $market) }}" class="settle-form">
                                                @csrf
                                                <input type="hidden" name="mode" value="win">
                                                <select class="select" name="winning_selection_id" required>
                                                    <option value="">Winning selection…</option>
                                                    @foreach($market->selections as $selection)
                                                        <option value="{{ $selection->id }}">{{ $selection->name }} @ {{ odds($selection->odds) }}</option>
                                                    @endforeach
                                                </select>
                                                <button class="btn btn--primary btn--sm" type="submit" data-confirm="Settle this market and pay out winning bets?">Settle winner</button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.markets.settle', $market) }}" data-confirm="Void this market and refund all pending stakes?">
                                                @csrf
                                                <input type="hidden" name="mode" value="void">
                                                <button class="btn btn--danger btn--sm" type="submit">Void &amp; refund</button>
                                            </form>
                                        </div>
                                        <p class="hint">Settling credits stake × odds to each winning ticket and releases exposure. Void refunds every pending stake.</p>
                                    </details>
                                @else
                                    <p class="hint mt-8">
                                        @if($market->status === 'settled')
                                            Settled. Winning selection: <strong>{{ $market->selections->firstWhere('result', 'win')?->name ?? '—' }}</strong>.
                                        @else
                                            Market voided — pending stakes were refunded.
                                        @endif
                                    </p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="empty"><strong>No markets yet</strong><p>Add a market above to start taking bets.</p></div>
                    @endforelse
                </div>
            </div>
        </div>

        <aside>
            <div class="admin-card">
                <div class="admin-card__head"><h3>Declare result</h3></div>
                <div class="admin-card__body">
                    @if($match->result)
                        <div class="alert alert--success" style="margin-bottom:14px">
                            Current result: <strong>{{ $match->result_label }}</strong>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.matches.result', $match) }}">
                        @csrf
                        <div class="field">
                            <label for="result">Winning outcome</label>
                            <select class="select" id="result" name="result" required>
                                <option value="">Choose result…</option>
                                <option value="home" @selected($match->result === 'home')>{{ $match->home_team }}</option>
                                <option value="draw" @selected($match->result === 'draw')>Draw</option>
                                <option value="away" @selected($match->result === 'away')>{{ $match->away_team }}</option>
                            </select>
                        </div>
                        <div class="form-grid">
                            <div class="field">
                                <label for="home_score">Home score</label>
                                <input class="input" id="home_score" name="home_score" type="number" min="0" value="{{ $match->home_score }}">
                            </div>
                            <div class="field">
                                <label for="away_score">Away score</label>
                                <input class="input" id="away_score" name="away_score" type="number" min="0" value="{{ $match->away_score }}">
                            </div>
                        </div>
                        <button class="btn btn--primary btn--block btn--sm" type="submit" @disabled($match->status === 'cancelled')>Save result &amp; auto-settle</button>
                    </form>
                    <p class="hint mt-8">Only markets flagged “Auto 1X2” settle immediately. Use the settle controls for totals and other markets.</p>
                </div>
            </div>

            <div class="admin-card mt-20">
                <div class="admin-card__head"><h3>Match details</h3></div>
                <div class="admin-card__body">
                    <ul class="info-list">
                        <li><span>Sport</span><strong>{{ $match->sport?->name }}</strong></li>
                        <li><span>League</span><strong>{{ $match->league }}</strong></li>
                        <li><span>Start</span><strong>{{ $match->start_time->format('d M Y, h:i A') }}</strong></li>
                        <li><span>Status</span><strong>{{ $match->status_label }}</strong></li>
                        <li><span>Score</span><strong>{{ $match->score ?? '—' }}</strong></li>
                        <li><span>Result</span><strong>{{ $match->result_label ?? '—' }}</strong></li>
                        <li><span>Featured</span><strong>{{ $match->featured ? 'Yes' : 'No' }}</strong></li>
                    </ul>
                    <a class="btn btn--outline btn--block btn--sm mt-14" href="{{ route('matches.show', $match) }}" target="_blank" rel="noopener">View public page ↗</a>
                </div>
            </div>
        </aside>
    </div>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Bets on this match</h3><a class="link-more" href="{{ route('admin.bets.index', ['q' => $match->home_team]) }}">All bets →</a></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Ticket</th><th>Player</th><th>Market</th><th>Selection</th><th>Odds</th><th>Stake</th><th>Status</th><th>Placed</th></tr></thead>
                <tbody>
                    @forelse($bets as $bet)
                        <tr>
                            <td><a class="ticket-code" href="{{ route('admin.bets.show', $bet) }}">{{ $bet->bet_code }}</a></td>
                            <td>{{ $bet->user?->username }}</td>
                            <td>{{ $bet->market_name }}</td>
                            <td>{{ $bet->selection_name }}</td>
                            <td class="mono">{{ odds($bet->odds) }}</td>
                            <td class="mono">{{ money($bet->stake) }}</td>
                            <td><span class="badge badge--{{ $bet->status_class }}">{{ $bet->status_label }}</span></td>
                            <td><span class="sub" style="margin:0">{{ $bet->created_at->format('d M, h:i A') }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="empty">No bets on this match yet.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
