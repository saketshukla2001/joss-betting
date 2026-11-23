@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $user = auth()->user();
        $matchesToShow = $liveMatches->concat($upcomingMatches)->take(4);
    @endphp

    <div class="container">
        <div class="dash-welcome">
            <div>
                <span class="eyebrow">Player dashboard</span>
                <h1 class="mt-8">Welcome back, {{ \Illuminate\Support\Str::of($user->name)->explode(' ')->first() }}.</h1>
                <p>Your wallet, open bets and live markets — all in one place.</p>
            </div>
            <div class="quick-actions">
                <a class="btn btn--primary btn--sm" href="{{ route('sports.index') }}">Browse sports</a>
                <a class="btn btn--outline btn--sm" href="{{ route('bets.index') }}">My bets</a>
                <a class="btn btn--dark btn--sm" href="{{ route('wallet.index') }}">Wallet</a>
            </div>
        </div>

        <div class="stat-grid">
            <div class="stat stat--lime">
                <div class="stat__label">Available balance</div>
                <div class="stat__value" data-balance>{{ money($user->balance) }}</div>
                <div class="stat__meta">Ready to stake</div>
            </div>
            <div class="stat">
                <div class="stat__label">Exposure</div>
                <div class="stat__value" data-exposure>{{ money($user->exposure) }}</div>
                <div class="stat__meta">Locked in open bets</div>
            </div>
            <div class="stat">
                <div class="stat__label">Open bets</div>
                <div class="stat__value">{{ number_format($openBets) }}</div>
                <div class="stat__meta">{{ number_format($wonBets) }} won · {{ number_format($lostBets) }} lost</div>
            </div>
            <div class="stat">
                <div class="stat__label">Win rate</div>
                <div class="stat__value">{{ number_format($winRate, 1) }}%</div>
                <div class="stat__meta">{{ money($totalWon) }} returns earned</div>
            </div>
        </div>

        <div class="dash-grid mt-20">
            <div>
                <div class="card mb-20">
                    <div class="card__head">
                        <div>
                            <h3>Live &amp; upcoming</h3>
                            <p>Tap odds to build your bet slip</p>
                        </div>
                        <a class="link-more" href="{{ route('sports.index') }}">All events →</a>
                    </div>
                    <div class="card__body">
                        @if($matchesToShow->isEmpty())
                            <div class="empty">
                                <div class="empty__ico">🏟️</div>
                                <strong>No open matches</strong>
                                <p>Check back soon for new events.</p>
                            </div>
                        @else
                            <div class="match-grid match-grid--2">
                                @foreach($matchesToShow as $match)
                                    @include('partials.match-card', ['match' => $match])
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                @if(! empty($cricketFeed['matches']))
                    <div class="card mb-20">
                        <div class="card__head">
                            <div>
                                <h3>Cricket · API feed</h3>
                                <p>Match data &amp; scores from {{ $cricketFeed['source'] ?? 'OpenCricketAPI' }}</p>
                            </div>
                        </div>
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr><th>Match</th><th>Score</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($cricketFeed['matches'] as $apiMatch)
                                        <tr>
                                            <td>
                                                <strong>{{ $apiMatch['label'] }}</strong>
                                                <span class="sub">
                                                    {{ $apiMatch['competition'] ?? 'Cricket' }}@if($apiMatch['format']) · {{ $apiMatch['format'] }}@endif @if($apiMatch['date']) · {{ $apiMatch['date'] }}@endif
                                                </span>
                                            </td>
                                            <td class="mono">
                                                @forelse($apiMatch['scores'] as $score)
                                                    <div>{{ $score['team'] ? $score['team'].' ' : '' }}{{ $score['score'] }}</div>
                                                @empty
                                                    <span class="sub">—</span>
                                                @endforelse
                                            </td>
                                            <td>
                                                <span class="badge badge--{{ $apiMatch['status'] === 'Completed' ? 'success' : 'info' }}">{{ $apiMatch['status'] }}</span>
                                                @if($apiMatch['result'])
                                                    <span class="sub">{{ $apiMatch['result'] }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @elseif(! empty($cricketFeed['error']))
                    <div class="card mb-20">
                        <div class="card__body">
                            <p class="text-muted" style="font-size:.8rem;margin:0">
                                Cricket feed unavailable right now — the live markets above are unaffected.
                            </p>
                        </div>
                    </div>
                @endif

                <div class="card">
                    <div class="card__head">
                        <div>
                            <h3>Recent bets</h3>
                            <p>Your latest tickets</p>
                        </div>
                        <a class="link-more" href="{{ route('bets.index') }}">View all →</a>
                    </div>
                    @if($recentBets->isEmpty())
                        <div class="empty">
                            <div class="empty__ico">🎯</div>
                            <strong>No bets yet</strong>
                            <p>Your first ticket will show up here.</p>
                            <a class="btn btn--primary btn--sm mt-14" href="{{ route('sports.index') }}">Find a match</a>
                        </div>
                    @else
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr><th>Ticket</th><th>Selection</th><th>Stake</th><th>Status</th><th></th></tr>
                                </thead>
                                <tbody>
                                    @foreach($recentBets as $bet)
                                        <tr>
                                            <td><span class="ticket-code">{{ $bet->bet_code }}</span><span class="sub">{{ $bet->match_label }}</span></td>
                                            <td>{{ $bet->selection_name }} <span class="sub">{{ $bet->market_name }} @ {{ odds($bet->odds) }}</span></td>
                                            <td class="mono">{{ money($bet->stake) }}</td>
                                            <td><span class="badge badge--{{ $bet->status_class }}">{{ $bet->status_label }}</span></td>
                                            <td class="text-right"><a class="btn btn--ghost btn--sm" href="{{ route('bets.show', $bet) }}">View</a></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <aside>
                <div class="card side-card">
                    <div class="card__head"><h3>Wallet</h3></div>
                    <div class="card__body">
                        <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;font-weight:700">Available balance</div>
                        <div class="text-lime fw-800 mt-8" style="font-size:1.9rem;letter-spacing:-.03em" data-balance>{{ money($user->balance) }}</div>
                        <div class="flex-between mt-14">
                            <span class="text-muted" style="font-size:.78rem">In play (exposure)</span>
                            <strong style="font-size:.85rem" data-exposure>{{ money($user->exposure) }}</strong>
                        </div>
                        <div class="flex-between mt-8">
                            <span class="text-muted" style="font-size:.78rem">Lifetime staked</span>
                            <strong style="font-size:.85rem">{{ money($totalStaked) }}</strong>
                        </div>
                        <div class="flex gap-8 mt-14">
                            <a class="btn btn--primary btn--sm w-full" href="{{ route('wallet.index') }}">Deposit</a>
                            <a class="btn btn--outline btn--sm w-full" href="{{ route('wallet.index') }}#withdraw">Withdraw</a>
                        </div>
                    </div>
                </div>

                <div class="card side-card">
                    <div class="card__head">
                        <h3>Wallet activity</h3>
                        <a class="link-more" href="{{ route('wallet.index') }}">All</a>
                    </div>
                    <div class="card__body">
                        @forelse($recentTransactions as $transaction)
                            <div class="tx-mini">
                                <div class="tx-mini__ico">{{ $transaction->type_icon }}</div>
                                <div class="tx-mini__main">
                                    <div class="tx-mini__title">{{ $transaction->type_label }}</div>
                                    <div class="tx-mini__date">{{ $transaction->created_at->format('d M, h:i A') }}</div>
                                </div>
                                <div class="tx-mini__amount {{ $transaction->amount >= 0 ? 'text-green' : 'text-red' }}">
                                    {{ $transaction->amount >= 0 ? '+' : '' }}{{ money($transaction->amount) }}
                                </div>
                            </div>
                        @empty
                            <div class="empty" style="padding:20px 8px">
                                <strong>No activity yet</strong>
                                <p>Deposits and bets will appear here.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="card side-card" style="background:linear-gradient(150deg,rgba(201,255,69,.13),rgba(20,33,56,.9));border-color:rgba(201,255,69,.2)">
                    <div class="card__body">
                        <div style="font-size:1.5rem;margin-bottom:8px">🛡️</div>
                        <h3 style="font-size:.95rem">Play it safe</h3>
                        <p class="text-muted" style="font-size:.78rem;margin-bottom:0">Set a budget before you bet. Never chase losses. 18+ only.</p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endsection
