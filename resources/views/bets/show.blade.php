@extends('layouts.app')

@section('title', 'Bet '.$bet->bet_code)

@section('content')
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="sep">/</span>
            <a href="{{ route('bets.index') }}">My bets</a>
            <span class="sep">/</span>
            <span>{{ $bet->bet_code }}</span>
        </div>

        <div class="page-head">
            <div>
                <span class="eyebrow">Bet ticket</span>
                <h1 class="mt-8">{{ $bet->bet_code }}</h1>
                <p>Placed {{ $bet->created_at->format('d M Y, h:i A') }} · {{ $bet->sport_name }}</p>
            </div>
            <div class="flex gap-8">
                <span class="badge badge--{{ $bet->status_class }}" style="padding:8px 14px;font-size:.78rem">{{ $bet->status_label }}</span>
                <a class="btn btn--outline btn--sm" href="{{ route('bets.index') }}">Back to bets</a>
            </div>
        </div>

        <div class="dash-grid">
            <div class="card">
                <div class="card__head">
                    <div>
                        <h3>{{ $bet->match_label }}</h3>
                        <p>{{ $bet->league }} · {{ $bet->market_name }}</p>
                    </div>
                    <a class="link-more" href="{{ route('matches.show', $bet->match_id) }}">Match centre →</a>
                </div>
                <div class="card__body">
                    <div class="slip-selection" style="margin-bottom:0">
                        <div class="slip-selection__match">Selection</div>
                        <div class="slip-selection__name" style="font-size:1.1rem">{{ $bet->selection_name }}</div>
                        <div class="slip-selection__market">{{ $bet->market_name }}</div>
                        <div class="slip-selection__row">
                            <span class="text-muted" style="font-size:.78rem">Locked odds</span>
                            <strong class="slip-selection__odds">{{ odds($bet->odds) }}</strong>
                        </div>
                    </div>

                    <div class="stat-grid mt-20" style="grid-template-columns:repeat(3,1fr)">
                        <div class="stat">
                            <div class="stat__label">Stake</div>
                            <div class="stat__value" style="font-size:1.15rem">{{ money($bet->stake) }}</div>
                        </div>
                        <div class="stat">
                            <div class="stat__label">Potential return</div>
                            <div class="stat__value" style="font-size:1.15rem">{{ money($bet->potential_return) }}</div>
                        </div>
                        <div class="stat {{ $bet->status === 'won' ? 'stat--lime' : '' }}">
                            <div class="stat__label">Payout</div>
                            <div class="stat__value" style="font-size:1.15rem">{{ money($bet->payout) }}</div>
                        </div>
                    </div>

                    <div class="alert alert--info mt-20" style="margin-bottom:0">
                        @if($bet->status === 'pending')
                            This bet is open. If your selection wins, {{ money($bet->potential_return) }} will be credited to your wallet automatically when the market is settled.
                        @elseif($bet->status === 'won')
                            This bet won. {{ money($bet->payout) }} was credited to your wallet on {{ $bet->settled_at?->format('d M Y, h:i A') }}.
                        @elseif($bet->status === 'lost')
                            This bet lost. The stake of {{ money($bet->stake) }} was not returned.
                        @else
                            This market was voided. Your stake of {{ money($bet->stake) }} was refunded to your wallet.
                        @endif
                    </div>
                </div>
            </div>

            <aside>
                <div class="card side-card">
                    <div class="card__head"><h3>Ticket details</h3></div>
                    <div class="card__body">
                        <ul class="info-list">
                            <li><span>Ticket</span><strong>{{ $bet->bet_code }}</strong></li>
                            <li><span>Status</span><strong>{{ $bet->status_label }}</strong></li>
                            <li><span>Sport</span><strong>{{ $bet->sport_name }}</strong></li>
                            <li><span>League</span><strong>{{ $bet->league }}</strong></li>
                            <li><span>Market</span><strong>{{ $bet->market_name }}</strong></li>
                            <li><span>Odds</span><strong>{{ odds($bet->odds) }}</strong></li>
                            <li><span>Stake</span><strong>{{ money($bet->stake) }}</strong></li>
                            <li><span>Placed</span><strong>{{ $bet->created_at->format('d M Y, h:i A') }}</strong></li>
                            @if($bet->settled_at)
                                <li><span>Settled</span><strong>{{ $bet->settled_at->format('d M Y, h:i A') }}</strong></li>
                            @endif
                        </ul>
                        <button class="btn btn--outline btn--block btn--sm mt-14" type="button" data-copy="{{ $bet->bet_code }}">Copy ticket code</button>
                    </div>
                </div>

                <div class="card side-card">
                    <div class="card__head"><h3>Wallet</h3></div>
                    <div class="card__body">
                        <div class="flex-between">
                            <span class="text-muted" style="font-size:.78rem">Available</span>
                            <strong class="text-lime" data-balance>{{ money(auth()->user()->balance) }}</strong>
                        </div>
                        <a class="btn btn--primary btn--block btn--sm mt-14" href="{{ route('sports.index') }}">Place another bet</a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endsection
