@extends('layouts.agent')

@section('title', 'Matches')
@section('page-title', 'Matches')
@section('page-subtitle', 'Live and upcoming betting events')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>Matches</h1>
            <p>Open a match to view markets and place a bet from your master wallet.</p>
        </div>
    </div>

    <form class="filter-bar" method="GET" action="{{ route('agent.matches.index') }}">
        <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Search team or competition">
        <select class="select" name="sport">
            <option value="">All sports</option>
            @foreach($sports as $sport)
                <option value="{{ $sport->slug }}" @selected(request('sport') === $sport->slug)>{{ $sport->name }}</option>
            @endforeach
        </select>
        <button class="btn btn--primary" type="submit">Filter</button>
    </form>

    <div class="classic-card">
        <div class="classic-card__head"><span>OPEN MATCHES</span><span>{{ $matches->total() }} events</span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Match</th><th>Competition</th><th>Date &amp; Time</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($matches as $match)
                        <tr>
                            <td class="strong">{{ strtoupper($match->home_team) }} V {{ strtoupper($match->away_team) }}</td>
                            <td>{{ strtoupper($match->league) }}</td>
                            <td>{{ $match->start_time->format('d M Y, h:i A') }}</td>
                            <td><span class="badge {{ $match->status === 'live' ? 'badge--live' : 'badge--muted' }}">{{ $match->status_label }}</span></td>
                            <td><a class="classic-view-btn" href="{{ route('agent.matches.show', $match) }}">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty"><strong>No open matches</strong></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $matches->links('partials.pagination') }}
    </div>
@endsection
