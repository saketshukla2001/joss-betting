@extends('layouts.app')

@section('title', $sport ? $sport->name.' betting' : 'Sportsbook')

@section('content')
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span class="sep">/</span>
            <span>{{ $sport ? $sport->name : 'Sportsbook' }}</span>
        </div>

        <div class="page-head">
            <div>
                <h1>{{ $sport ? $sport->icon.' '.$sport->name : 'Sportsbook' }}</h1>
                <p>
                    @if(request('status') === 'live' && setting_enabled('cricket_feed_enabled', true) && setting_enabled('cricket_board_enabled', true) && (! $sport || $sport->slug === 'cricket'))
                        Live cricket scores below · odds lock at placement · singles only
                    @else
                        {{ $matches->total() }} events available · odds lock at placement · singles only
                    @endif
                </p>
            </div>
            <div class="chip-row">
                <a class="chip {{ !request('status') ? 'active' : '' }}" href="{{ $sport ? route('sports.show', $sport) : route('sports.index') }}">All</a>
                <a class="chip {{ request('status') === 'live' ? 'active' : '' }}" href="{{ $sport ? route('sports.show', [$sport, 'status' => 'live']) : route('sports.index', ['status' => 'live']) }}">Live</a>
                <a class="chip {{ request('status') === 'upcoming' ? 'active' : '' }}" href="{{ $sport ? route('sports.show', [$sport, 'status' => 'upcoming']) : route('sports.index', ['status' => 'upcoming']) }}">Upcoming</a>
            </div>
        </div>

        <form class="card filter-bar mb-20" method="GET" action="{{ url()->current() }}">
            <input class="input search" type="search" name="q" value="{{ request('q') }}" placeholder="Search teams or leagues…" aria-label="Search matches">
            <select class="select" name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                <option value="live" @selected(request('status') === 'live')>Live</option>
                <option value="upcoming" @selected(request('status') === 'upcoming')>Upcoming</option>
            </select>
            <button class="btn btn--primary" type="submit">Filter</button>
            @if(request()->hasAny(['q', 'status']))
                <a class="btn btn--ghost" href="{{ $sport ? route('sports.show', $sport) : route('sports.index') }}">Reset</a>
            @endif
        </form>

        <div class="chip-row mb-20">
            <a class="chip {{ !$sport ? 'active' : '' }}" href="{{ route('sports.index', request()->only(['q', 'status'])) }}">All sports</a>
            @foreach($sports as $sportOption)
                <a class="chip {{ $sport && $sport->id === $sportOption->id ? 'active' : '' }}" href="{{ route('sports.show', [$sportOption, ...request()->only(['q', 'status'])]) }}">
                    {{ $sportOption->icon }} {{ $sportOption->name }}
                </a>
            @endforeach
        </div>

        @if(request('status') === 'live' && setting_enabled('cricket_feed_enabled', true) && setting_enabled('cricket_board_enabled', true) && (! $sport || $sport->slug === 'cricket'))
            <div class="mb-20">
                @include('partials.cricket-section', ['mode' => 'live'])
            </div>
        @endif

        @if($matches->isEmpty() && ! (request('status') === 'live' && setting_enabled('cricket_feed_enabled', true) && setting_enabled('cricket_board_enabled', true) && (! $sport || $sport->slug === 'cricket')))
            <div class="card empty">
                <div class="empty__ico">🔍</div>
                <strong>No events match that filter</strong>
                <p>Try another sport or clear your search.</p>
                <a class="btn btn--primary btn--sm mt-14" href="{{ route('sports.index') }}">See all events</a>
            </div>
        @else
            <div class="match-grid">
                @foreach($matches as $match)
                    @include('partials.match-card', ['match' => $match])
                @endforeach
            </div>

            {{ $matches->links('partials.pagination') }}
        @endif
    </div>
@endsection
