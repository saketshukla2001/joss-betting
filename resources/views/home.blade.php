@extends('layouts.app')

@section('title', 'Sports Betting')

@section('content')
    @php
        $heroMatches = $live->isNotEmpty() ? $live : $heroFallback;
    @endphp

    <section class="hero">
        <div class="container">
            <div class="hero__grid">
                <div>
                    <span class="eyebrow">Live sports exchange</span>
                    <h1 class="mt-14">Bet on the game.<br><em>Not on luck.</em></h1>
                    <p class="hero__sub">Real markets, transparent odds and instant wallet settlement. Football, cricket, basketball, tennis and hockey — all in one fast, mobile-ready sportsbook.</p>
                    <div class="hero__actions">
                        <a class="btn btn--primary btn--lg" href="{{ route('sports.index') }}">Browse matches</a>
                        @auth
                            @if(auth()->user()->isClient())
                                <a class="btn btn--outline btn--lg" href="{{ route('dashboard') }}">My dashboard</a>
                            @else
                                <a class="btn btn--outline btn--lg" href="{{ route('admin.dashboard') }}">Admin panel</a>
                            @endif
                        @else
                            <a class="btn btn--outline btn--lg" href="{{ route('register') }}">Create free account</a>
                        @endauth
                    </div>
                    <div class="hero__trust">
                        <div>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l8 4v6c0 5-3.4 8.4-8 10-4.6-1.6-8-5-8-10V6z"/><path d="M9 12l2 2 4-4"/></svg>
                            Secure wallet ledger
                        </div>
                        <div>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h8l-1 8 10-12h-8z"/></svg>
                            Live odds every 10s
                        </div>
                        <div>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                            Instant settlement
                        </div>
                    </div>
                </div>

                <div class="hero__panel">
                    <div class="hero__panel-head">
                        <strong>{{ $live->isNotEmpty() ? 'Happening now' : 'Featured markets' }}</strong>
                        <a class="link-more" href="{{ route('sports.index', ['status' => 'live']) }}">View all</a>
                    </div>

                    @forelse($heroMatches as $match)
                        @php
                            $mainMarket = $match->markets->firstWhere('name', 'Match Winner') ?? $match->markets->first();
                            $mainSelection = $mainMarket?->selections->first();
                        @endphp
                        <a class="hero-ticket" href="{{ route('matches.show', $match) }}">
                            <div>
                                <div class="hero-ticket__teams">{{ $match->home_team }} <span class="text-muted">vs</span> {{ $match->away_team }}</div>
                                <div class="hero-ticket__meta">
                                    {{ $match->sport?->icon }} {{ $match->league }}
                                    @if($match->status === 'live')
                                        · <span class="text-red">LIVE {{ $match->score }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="hero-ticket__odds">
                                <strong>{{ $mainSelection ? odds($mainSelection->odds) : '—' }}</strong>
                                <span>{{ $mainSelection?->name ?? 'Odds' }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="empty">
                            <div class="empty__ico">🏟️</div>
                            <strong>Markets open shortly</strong>
                            <p>New events are added daily.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="hero-stats">
                <div class="hero-stat"><strong>{{ number_format($stats['matches']) }}</strong><span>Open events</span></div>
                <div class="hero-stat"><strong>{{ number_format($stats['live']) }}</strong><span>Live right now</span></div>
                <div class="hero-stat"><strong>{{ short_money($stats['paid_out']) }}</strong><span>Winnings paid</span></div>
                <div class="hero-stat"><strong>{{ number_format($stats['bets']) }}</strong><span>Bets placed</span></div>
            </div>
        </div>
    </section>

    <div class="container">
        <div class="section-head">
            <div>
                <h2>Pick your sport</h2>
                <p>Five sports, one wallet. No casino. No matka.</p>
            </div>
            <a class="link-more" href="{{ route('sports.index') }}">All events →</a>
        </div>

        <div class="sports-strip">
            @foreach($sports as $sport)
                <a class="sport-card" href="{{ route('sports.show', $sport) }}">
                    <span class="sport-card__ico">{{ $sport->icon }}</span>
                    <span>
                        <strong>{{ $sport->name }}</strong>
                        <span>{{ $sport->matches_count }} open {{ \Illuminate\Support\Str::plural('event', $sport->matches_count) }}</span>
                    </span>
                </a>
            @endforeach
        </div>

        @if($live->isNotEmpty())
            <div class="section-head">
                <div>
                    <h2>Live right now</h2>
                    <p>Odds move with the game — get in while the market is open.</p>
                </div>
                <a class="link-more" href="{{ route('sports.index', ['status' => 'live']) }}">All live →</a>
            </div>
            <div class="match-grid">
                @foreach($live as $match)
                    @include('partials.match-card', ['match' => $match])
                @endforeach
            </div>
        @endif

        @include('partials.cricket-section')

        <div class="section-head">
            <div>
                <h2>Featured matches</h2>
                <p>Hand-picked events with deep markets and competitive odds.</p>
            </div>
            <a class="link-more" href="{{ route('sports.index') }}">View sportsbook →</a>
        </div>

        <div class="match-grid">
            @foreach($featured as $match)
                @include('partials.match-card', ['match' => $match])
            @endforeach
        </div>

        <div class="section-head">
            <div>
                <h2>Why BetZone</h2>
                <p>A sportsbook built around fast markets and clean money movement.</p>
            </div>
        </div>

        <div class="feature-grid">
            <div class="card feature">
                <div class="feature__ico">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h8l-1 8 10-12h-8z"/></svg>
                </div>
                <h3>Live odds engine</h3>
                <p>Open markets refresh continuously. Your bet locks the odds you see at placement, and every price change is auditable.</p>
            </div>
            <div class="card feature">
                <div class="feature__ico">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/><circle cx="17" cy="15" r="1.4" fill="currentColor" stroke="none"/></svg>
                </div>
                <h3>Wallet with a paper trail</h3>
                <p>Every stake, win, refund, deposit and withdrawal is written to a ledger with a running balance. Admin approvals are explicit.</p>
            </div>
            <div class="card feature">
                <div class="feature__ico">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M9 9h6v6H9z"/><path d="M9 1v4M15 1v4M9 19v4M15 19v4M1 9h4M1 15h4M19 9h4M19 15h4"/></svg>
                </div>
                <h3>Responsive by default</h3>
                <p>The same Laravel panel works on a phone, tablet or desktop. Place a bet in three taps and manage the book from anywhere.</p>
            </div>
        </div>

        <div class="cta-band">
            <div>
                <h2>Ready to back your pick?</h2>
                <p>Create an account, grab your welcome bonus and bet your first match in under a minute.</p>
            </div>
            @auth
                @if(auth()->user()->isClient())
                    <a class="btn btn--primary btn--lg" href="{{ route('sports.index') }}">Find a match</a>
                @else
                    <a class="btn btn--primary btn--lg" href="{{ route('admin.dashboard') }}">Open admin panel</a>
                @endif
            @else
                <a class="btn btn--primary btn--lg" href="{{ route('register') }}">Join BetZone</a>
            @endauth
        </div>
    </div>
@endsection
