@extends('layouts.app')

@section('title', 'Cricbuzz API test')

@section('content')
    <div class="container">
        <div class="page-head">
            <div>
                <h1>Cricbuzz API test <span class="badge badge--muted">read-only</span></h1>
                <p>RapidAPI · <code>{{ $host ?: 'cricbuzz-cricket.p.rapidapi.com' }}</code> · fixed test match IDs · no betting involvement</p>
            </div>
            <div class="chip-row">
                <a class="btn btn--primary btn--sm" href="{{ route('cricbuzz-test.index', ['refresh' => 1]) }}">Refresh all</a>
                <a class="btn btn--ghost btn--sm" href="{{ route('cricket-test.index') }}">Sportmonks page</a>
            </div>
        </div>

        @if(! $configured)
            <div class="alert alert--error">
                RapidAPI credentials missing. Set <strong>RAPIDAPI_HOST</strong> and <strong>RAPIDAPI_KEY</strong> in <code>.env</code>.
            </div>
        @endif

        @if($refresh)
            <div class="alert alert--info">Cache bypassed for this request — fresh Cricbuzz data fetched.</div>
        @endif

        <div class="match-grid match-grid--2">
            @foreach($matches as $row)
                @include('cricbuzz-test._card', ['row' => $row])
            @endforeach
        </div>

        <p class="text-muted mt-20" style="font-size:.75rem">
            Endpoints: <code>/mcenter/v1/{matchId}/livescore</code> for this list ·
            <code>/miniscorecard</code> and <code>/scard</code> on each match's scorecard page.
            Data cached briefly to respect the RapidAPI per-second rate limit.
        </p>
    </div>
@endsection
