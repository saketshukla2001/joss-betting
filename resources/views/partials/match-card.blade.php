@php
    $canBet = auth()->check() && auth()->user()->isClient();
    $initials = function (string $name): string {
        return collect(explode(' ', $name))->filter()->take(2)
            ->map(fn ($word) => strtoupper(substr($word, 0, 1)))->implode('');
    };
    $bettingAllowed = auth()->guest() || $canBet;
@endphp
<article class="card match-card {{ $match->status === 'live' ? 'match-card--live' : '' }}">
    <div class="match-card__top">
        <div class="league">
            <span class="sport-ico">{{ $match->sport?->icon ?? '🏆' }}</span>
            <span>{{ $match->league }}</span>
        </div>
        @if($match->status === 'live')
            <span class="badge badge--live">LIVE {{ $match->score ? '· '.$match->score : '' }}</span>
        @else
            <span class="match-card__time">{{ $match->start_time->format('d M · h:i A') }}</span>
        @endif
    </div>

    <div class="teams">
        <div class="team">
            <div class="team__name">
                <span class="team__crest">{{ $initials($match->home_team) }}</span>
                <span>{{ $match->home_team }}</span>
            </div>
            @if($match->home_score !== null)
                <span class="team__score">{{ $match->home_score }}</span>
            @endif
        </div>
        <div class="team">
            <div class="team__name">
                <span class="team__crest">{{ $initials($match->away_team) }}</span>
                <span>{{ $match->away_team }}</span>
            </div>
            @if($match->away_score !== null)
                <span class="team__score">{{ $match->away_score }}</span>
            @endif
        </div>
    </div>

    <div class="match-card__markets">
        @forelse($match->markets->take(2) as $market)
            <div class="market-row">
                <div class="market-row__label">{{ $market->name }}</div>
                <div class="odds-grid {{ $market->selections->count() === 2 ? 'odds-grid--2' : '' }}">
                    @foreach($market->selections as $selection)
                        @php $isDisabled = !$bettingAllowed || $market->status !== 'open' || $selection->status !== 'active'; @endphp
                        <button
                            type="button"
                            class="odds-btn"
                            data-selection-id="{{ $selection->id }}"
                            data-odds-id="{{ $selection->id }}"
                            data-odds="{{ number_format($selection->odds, 2, '.', '') }}"
                            data-selection-name="{{ $selection->name }}"
                            data-market-name="{{ $market->name }}"
                            data-match-label="{{ $match->label }}"
                            data-market-id="{{ $market->id }}"
                            data-match-id="{{ $match->id }}"
                            @if($isDisabled) disabled @endif
                        >
                            <span class="odds-name">{{ $selection->name }}</span>
                            <span class="odds-val" data-odds-value>{{ odds($selection->odds) }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-muted" style="font-size:.78rem;margin:0">Markets opening soon.</p>
        @endforelse
    </div>

    <div class="match-card__foot">
        <span>{{ $match->markets->sum(fn ($m) => $m->selections->count()) }} selections</span>
        <a class="link-more" href="{{ route('matches.show', $match) }}">All markets →</a>
    </div>
</article>
