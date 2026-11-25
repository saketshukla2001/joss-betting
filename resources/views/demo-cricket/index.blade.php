@extends('layouts.app')

@section('title', 'Live demo cricket')

@section('content')
    <div class="container">
        <div class="section-head">
            <div>
                <h2>LIVE DEMO</h2>
                <p>Simulated cricket. These matches are not on the real sportsbook, and a demo ticket does not use your wallet.</p>
            </div>
        </div>

        @forelse($matches as $match)
            <article class="card mb-20">
                <div class="card__head">
                    <div>
                        <h3>{{ $match->home_team }} vs {{ $match->away_team }}</h3>
                        <p>LIVE DEMO · {{ $match->league }}</p>
                    </div>
                    <span class="badge badge--live">LIVE DEMO</span>
                </div>
                <div class="card__body">
                    <p>{{ $match->markets->count() }} markets</p>
                    <a class="btn btn--primary btn--sm" href="{{ route('demo-cricket.show', $match->id) }}">Open demo</a>
                </div>
            </article>
        @empty
            <div class="card empty">
                <strong>No demo match is running</strong>
                <p>An admin can start one from Live demo. It will appear here after the market cycle publishes it.</p>
            </div>
        @endforelse
    </div>
@endsection
