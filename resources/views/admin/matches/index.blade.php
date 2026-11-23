@extends('layouts.admin')

@section('title', 'Matches')
@section('page-title', 'Matches & odds')
@section('page-subtitle', 'Fixtures, markets and settlement')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>Matches &amp; odds</h1>
            <p>Create fixtures, open markets and settle results.</p>
        </div>
        <a class="btn btn--primary btn--sm" href="{{ route('admin.matches.create') }}">+ New match</a>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Upcoming</div>
            <div class="stat__value">{{ number_format($summary['upcoming']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Live</div>
            <div class="stat__value text-red">{{ number_format($summary['live']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Settled</div>
            <div class="stat__value">{{ number_format($summary['settled']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Cancelled</div>
            <div class="stat__value">{{ number_format($summary['cancelled']) }}</div>
        </div>
    </div>

    <form class="card filter-bar mt-20" method="GET" action="{{ route('admin.matches.index') }}">
        <input class="input search" type="search" name="q" value="{{ request('q') }}" placeholder="Search teams or league…">
        <select class="select" name="sport_id">
            <option value="">All sports</option>
            @foreach($sports as $sport)
                <option value="{{ $sport->id }}" @selected((int) request('sport_id') === $sport->id)>{{ $sport->icon }} {{ $sport->name }}</option>
            @endforeach
        </select>
        <select class="select" name="status">
            <option value="">All statuses</option>
            @foreach(['upcoming' => 'Upcoming', 'live' => 'Live', 'settled' => 'Settled', 'cancelled' => 'Cancelled'] as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn--primary" type="submit">Apply</button>
        @if(request()->hasAny(['q', 'sport_id', 'status']))
            <a class="btn btn--ghost" href="{{ route('admin.matches.index') }}">Reset</a>
        @endif
    </form>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>All fixtures</h3><span class="badge badge--muted">{{ $matches->total() }} results</span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th>Match</th><th>Sport</th><th>Start</th><th>Status</th><th>Result</th><th>Markets</th><th>Bets</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($matches as $match)
                        <tr>
                            <td>
                                <span class="strong">{{ $match->home_team }} vs {{ $match->away_team }}</span>
                                <span class="sub">{{ $match->league }}</span>
                            </td>
                            <td>{{ $match->sport?->icon }} {{ $match->sport?->name }}</td>
                            <td><span class="sub" style="margin:0">{{ $match->start_time->format('d M Y, h:i A') }}</span></td>
                            <td><span class="badge badge--{{ $match->status_class }}">{{ $match->status_label }}</span></td>
                            <td>
                                @if($match->result)
                                    <span class="badge badge--lime">{{ $match->result_label }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $match->markets_count }}</td>
                            <td>{{ $match->bets_count }}</td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn btn--ghost btn--sm" href="{{ route('admin.matches.show', $match) }}">Manage</a>
                                    <a class="btn btn--ghost btn--sm" href="{{ route('admin.matches.edit', $match) }}">Edit</a>
                                    @if($match->bets_count === 0)
                                        <form method="POST" action="{{ route('admin.matches.destroy', $match) }}" data-confirm="Delete this match and its markets?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn--ghost btn--sm text-red" type="submit">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="empty"><strong>No matches found</strong><p>Create a fixture to open the book.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $matches->links('partials.pagination') }}
    </div>
@endsection
