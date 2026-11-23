@extends('layouts.admin')

@php
    $editing = $match->exists;
    $startValue = old('start_time', $match->start_time ? $match->start_time->format('Y-m-d\TH:i') : now()->addDay()->format('Y-m-d\TH:i'));
@endphp

@section('title', $editing ? 'Edit match' : 'New match')
@section('page-title', $editing ? 'Edit match' : 'New match')
@section('page-subtitle', $editing ? $match->label : 'Add a fixture to the sportsbook')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>{{ $editing ? 'Edit fixture' : 'Create a fixture' }}</h1>
            <p>{{ $editing ? 'Update teams, start time or score.' : 'A Match Winner market is created automatically unless you untick the option.' }}</p>
        </div>
        <a class="btn btn--outline btn--sm" href="{{ route('admin.matches.index') }}">← Back to matches</a>
    </div>

    <div class="admin-card" style="max-width:900px">
        <div class="admin-card__body">
            <form method="POST" action="{{ $editing ? route('admin.matches.update', $match) : route('admin.matches.store') }}">
                @csrf
                @if($editing) @method('PUT') @endif

                <div class="form-grid">
                    <div class="field">
                        <label for="sport_id">Sport</label>
                        <select class="select" id="sport_id" name="sport_id" required>
                            @foreach($sports as $sport)
                                <option value="{{ $sport->id }}" @selected((int) old('sport_id', $match->sport_id) === $sport->id)>{{ $sport->icon }} {{ $sport->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="league">League / competition</label>
                        <input class="input" id="league" name="league" type="text" value="{{ old('league', $match->league) }}" placeholder="e.g. Premier League" required>
                    </div>
                    <div class="field">
                        <label for="home_team">Home team</label>
                        <input class="input" id="home_team" name="home_team" type="text" value="{{ old('home_team', $match->home_team) }}" required>
                    </div>
                    <div class="field">
                        <label for="away_team">Away team</label>
                        <input class="input" id="away_team" name="away_team" type="text" value="{{ old('away_team', $match->away_team) }}" required>
                    </div>
                    <div class="field">
                        <label for="start_time">Start time</label>
                        <input class="input" id="start_time" name="start_time" type="datetime-local" value="{{ $startValue }}" required>
                    </div>
                    <div class="field">
                        <label for="status">Status</label>
                        <select class="select" id="status" name="status" required>
                            @foreach(['upcoming' => 'Upcoming', 'live' => 'Live', 'settled' => 'Settled', 'cancelled' => 'Cancelled'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $match->status ?: 'upcoming') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="hint">To cancel with refunds, use the Void action on the match page.</div>
                    </div>
                    <div class="field">
                        <label for="home_score">Home score <span class="text-muted">(optional)</span></label>
                        <input class="input" id="home_score" name="home_score" type="number" min="0" value="{{ old('home_score', $match->home_score) }}">
                    </div>
                    <div class="field">
                        <label for="away_score">Away score <span class="text-muted">(optional)</span></label>
                        <input class="input" id="away_score" name="away_score" type="number" min="0" value="{{ old('away_score', $match->away_score) }}">
                    </div>
                </div>

                <label class="check mt-14">
                    <input type="checkbox" name="featured" value="1" @checked(old('featured', $match->featured))>
                    <span>Feature this match on the home page</span>
                </label>

                @if(! $editing)
                    <label class="check mt-8">
                        <input type="checkbox" name="create_default_market" value="1" checked>
                        <span>Create a Match Winner market with home / draw / away selections automatically</span>
                    </label>
                @endif

                <div class="form-actions">
                    <button class="btn btn--primary" type="submit">{{ $editing ? 'Save match' : 'Create match' }}</button>
                    <a class="btn btn--ghost" href="{{ $editing ? route('admin.matches.show', $match) : route('admin.matches.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
