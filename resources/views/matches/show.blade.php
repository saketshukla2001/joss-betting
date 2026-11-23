@extends('layouts.app')

@section('title', $match->label)

@section('content')
    @php
        $canBet = auth()->check() && auth()->user()->isClient();
        $bettingAllowed = auth()->guest() || $canBet;
        $statusBadge = match ($match->status) {
            'live' => 'badge--live',
            'upcoming' => 'badge--info',
            'settled' => 'badge--muted',
            'cancelled' => 'badge--danger',
            default => 'badge--muted',
        };
    @endphp

    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span class="sep">/</span>
            <a href="{{ route('sports.show', $match->sport) }}">{{ $match->sport?->name }}</a>
            <span class="sep">/</span>
            <span>{{ $match->home_team }} vs {{ $match->away_team }}</span>
        </div>

        @if($match->status === 'cancelled')
            <div class="alert alert--error">This match was cancelled. All open bets on it have been refunded to player wallets.</div>
        @elseif($match->status === 'settled')
            <div class="alert alert--info">This match is settled. Winning bets have been paid and losing bets closed.</div>
        @endif

        <div class="match-layout">
            <div>
                <section class="match-hero">
                    <div class="match-hero__meta">
                        <span class="badge {{ $statusBadge }}">{{ $match->status_label }}</span>
                        <span class="text-muted">{{ $match->sport?->icon }} {{ $match->league }}</span>
                        <span class="text-muted">·</span>
                        <span class="text-muted">{{ $match->start_time->format('D, d M Y · h:i A') }}</span>
                        @if($match->result)
                            <span class="badge badge--lime">Result: {{ $match->result_label }}</span>
                        @endif
                    </div>

                    <div class="match-hero__teams">
                        <div class="match-hero__team">
                            <div class="match-hero__crest">{{ strtoupper(substr($match->home_team, 0, 2)) }}</div>
                            <strong>{{ $match->home_team }}</strong>
                            <span>Home</span>
                        </div>
                        <div class="match-hero__score">
                            @if($match->home_score !== null && $match->away_score !== null)
                                <strong>{{ $match->home_score }} : {{ $match->away_score }}</strong>
                                <span>{{ $match->status === 'live' ? 'Live score' : 'Final score' }}</span>
                            @else
                                <strong>VS</strong>
                                <span>{{ $match->start_time->diffForHumans() }}</span>
                            @endif
                        </div>
                        <div class="match-hero__team">
                            <div class="match-hero__crest">{{ strtoupper(substr($match->away_team, 0, 2)) }}</div>
                            <strong>{{ $match->away_team }}</strong>
                            <span>Away</span>
                        </div>
                    </div>
                </section>

                <div class="section-head">
                    <div>
                        <h2>Markets</h2>
                        <p>{{ $match->markets->count() }} markets · tap odds to add to your slip</p>
                    </div>
                    @if($canBet)
                        <a class="link-more" href="{{ route('bets.index') }}">My bets →</a>
                    @endif
                </div>

                @forelse($match->markets as $market)
                    @php
                        $marketBadge = match ($market->status) {
                            'open' => 'badge--success',
                            'suspended' => 'badge--warning',
                            'settled' => 'badge--muted',
                            'void' => 'badge--danger',
                            default => 'badge--muted',
                        };
                    @endphp
                    <section class="card match-market">
                        <div class="card__head">
                            <div>
                                <h3>{{ $market->name }}</h3>
                                @if($market->auto_settle)
                                    <p>Auto-settles from the match result</p>
                                @endif
                            </div>
                            <span class="badge {{ $marketBadge }}">{{ $market->status_label }}</span>
                        </div>
                        <div class="card__body">
                            <div class="selection-grid {{ $market->selections->count() === 2 ? 'selection-grid--2' : '' }}">
                                @foreach($market->selections as $selection)
                                    @php $disabled = !$bettingAllowed || $market->status !== 'open' || $selection->status !== 'active'; @endphp
                                    <button
                                        type="button"
                                        class="selection-btn"
                                        data-selection-id="{{ $selection->id }}"
                                        data-odds-id="{{ $selection->id }}"
                                        data-odds="{{ number_format($selection->odds, 2, '.', '') }}"
                                        data-selection-name="{{ $selection->name }}"
                                        data-market-name="{{ $market->name }}"
                                        data-match-label="{{ $match->label }}"
                                        data-market-id="{{ $market->id }}"
                                        data-match-id="{{ $match->id }}"
                                        @if($disabled) disabled @endif
                                    >
                                        <span class="selection-btn__name">
                                            {{ $selection->name }}
                                            @if($selection->result === 'win')
                                                <span class="badge badge--success win-tag">Won</span>
                                            @elseif($selection->result === 'void')
                                                <span class="badge badge--muted win-tag">Void</span>
                                            @elseif($selection->status === 'suspended')
                                                <span class="badge badge--warning win-tag">Suspended</span>
                                            @endif
                                        </span>
                                        <span class="selection-btn__odds" data-odds-value>{{ odds($selection->odds) }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @empty
                    <div class="card empty">
                        <div class="empty__ico">📉</div>
                        <strong>No markets available</strong>
                        <p>Markets for this event have not been opened yet.</p>
                    </div>
                @endforelse
            </div>

            <aside>
                <div class="card side-card">
                    <div class="card__head"><h3>Match info</h3></div>
                    <div class="card__body">
                        <ul class="info-list">
                            <li><span>Sport</span><strong>{{ $match->sport?->icon }} {{ $match->sport?->name }}</strong></li>
                            <li><span>League</span><strong>{{ $match->league }}</strong></li>
                            <li><span>Start</span><strong>{{ $match->start_time->format('d M, h:i A') }}</strong></li>
                            <li><span>Status</span><strong>{{ $match->status_label }}</strong></li>
                            @if($match->result)
                                <li><span>Result</span><strong class="text-lime">{{ $match->result_label }}</strong></li>
                            @endif
                            <li><span>Markets</span><strong>{{ $match->markets->count() }}</strong></li>
                        </ul>
                    </div>
                </div>

                @auth
                    @if($canBet)
                        <div class="card side-card">
                            <div class="card__head"><h3>Your wallet</h3></div>
                            <div class="card__body">
                                <div class="flex-between">
                                    <span class="text-muted" style="font-size:.78rem">Available</span>
                                    <strong class="text-lime" data-balance>{{ money(auth()->user()->balance) }}</strong>
                                </div>
                                <div class="flex-between mt-8">
                                    <span class="text-muted" style="font-size:.78rem">Exposure</span>
                                    <strong data-exposure>{{ money(auth()->user()->exposure) }}</strong>
                                </div>
                                <a class="btn btn--outline btn--block mt-14" href="{{ route('wallet.index') }}">Manage wallet</a>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="card side-card">
                        <div class="card__head"><h3>Want to bet?</h3></div>
                        <div class="card__body">
                            <p class="text-muted" style="font-size:.82rem">Sign in or create a free account to add this selection to your bet slip.</p>
                            <a class="btn btn--primary btn--block" href="{{ route('login') }}">Sign in</a>
                            <a class="btn btn--outline btn--block mt-8" href="{{ route('register') }}">Create account</a>
                        </div>
                    </div>
                @endauth

                <div class="card side-card">
                    <div class="card__head"><h3>Betting rules</h3></div>
                    <div class="card__body">
                        <ul class="info-list">
                            <li><span>Bet type</span><strong>Single</strong></li>
                            <li><span>Odds</span><strong>Decimal</strong></li>
                            <li><span>Min stake</span><strong>{{ money($siteSettings['min_stake']) }}</strong></li>
                            <li><span>Max stake</span><strong>{{ money($siteSettings['max_stake']) }}</strong></li>
                        </ul>
                        <p class="text-muted mt-14" style="font-size:.72rem;margin-bottom:0">Odds are locked when the bet is placed. If a market is voided, stakes are refunded automatically. Settlement credits the full return (stake × odds) to your wallet.</p>
                    </div>
                </div>

                @if($related->isNotEmpty())
                    <div class="card side-card">
                        <div class="card__head"><h3>More {{ $match->sport?->name }}</h3></div>
                        <div class="card__body">
                            @foreach($related as $event)
                                <a class="bet-mini" href="{{ route('matches.show', $event) }}">
                                    <div class="bet-mini__main">
                                        <div class="bet-mini__match">{{ $event->home_team }} vs {{ $event->away_team }}</div>
                                        <div class="bet-mini__sel">{{ $event->league }} · {{ $event->start_time->format('d M, h:i A') }}</div>
                                    </div>
                                    <span class="badge {{ $event->status === 'live' ? 'badge--live' : 'badge--muted' }}">{{ $event->status === 'live' ? 'Live' : 'Open' }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>
@endsection
