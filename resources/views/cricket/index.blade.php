@extends('layouts.app')

@section('title', 'Live cricket')

@section('content')
    <div class="container">
        <div class="page-head">
            <div>
                <h1>Live cricket <span class="badge badge--live">Live</span></h1>
                <p>Cricbuzz discovery · live scores, wickets, overs and full scorecards · read-only</p>
            </div>
            <div class="chip-row">
                <button type="button" class="btn btn--ghost btn--sm" data-cricket-refresh>Refresh</button>
                <a class="btn btn--ghost btn--sm" href="{{ route('sports.index') }}">Sportsbook</a>
            </div>
        </div>

        @if(! $configured)
            <div class="alert alert--error">
                Cricket scores are not configured. Set <strong>RAPIDAPI_HOST</strong> and <strong>RAPIDAPI_KEY</strong> in
                <code>.env</code>, then add match IDs to <code>services.cricbuzz.match_ids</code>.
            </div>
        @endif

        @if(! $feedEnabled)
            <div class="alert alert--info">The cricket feed is currently disabled by the administrator.</div>
        @else
            <div class="cricket-board"
                 data-cricket-board
                 data-endpoint="{{ route('cricket.board') }}"
                 data-mode="full">
                <div class="cricket-skeleton" aria-hidden="true">
                    <div class="cricket-skeleton__card"></div>
                    <div class="cricket-skeleton__card"></div>
                    <div class="cricket-skeleton__card"></div>
                </div>
            </div>
        @endif

        <p class="hint mt-20">
            Updates automatically every 30 seconds while this tab is visible. Scores are cached for a few
            seconds and the API calls are throttled to stay inside the RapidAPI plan limits.
        </p>
    </div>
@endsection

@if($feedEnabled)
    @push('scripts')
        <script src="{{ asset('js/cricket.js') }}?v=1" defer></script>
    @endpush
@endif
