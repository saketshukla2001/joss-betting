<div class="card mb-20" data-cricket-market-live data-match-id="{{ $match->id }}" data-endpoint="{{ route('cricket.markets.feed') }}">
    <div class="card__head">
        <div>
            <h3>Live score</h3>
            <p data-cricket-score>Score updates from the sportsbook feed.</p>
        </div>
        <span class="badge badge--live" data-cricket-status>Live</span>
    </div>
</div>

@include('partials.cricket-market-script')
