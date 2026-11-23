@extends('layouts.admin')

@section('title', $mode === 'inplay' ? 'Active Games' : 'Completed Games')

@section('content')
    <div class="classic-form-header">
        <span>{{ $mode === 'inplay' ? 'ACTIVE GAMES' : 'COMPLETED GAMES' }}</span>
    </div>

    <div class="sports-games-panel">
        <form method="GET" action="{{ $mode === 'inplay' ? route('admin.sports.inplay') : route('admin.sports.completed') }}" class="sports-filter">
            <div class="sports-date-group">
                <input type="date" name="start_date" value="{{ request('start_date') }}" aria-label="Start date">
                <span>→</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}" aria-label="End date">
            </div>
            <div class="sports-search-wrap">
                <span class="sports-search-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.5 15.5L21 21"/></svg>
                </span>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by name or competition">
            </div>
            <button class="sports-filter-submit" type="submit">Filter</button>
        </form>

        <div class="table-wrap sports-table-wrap">
            <table class="data-table sports-games-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>NAME</th>
                        <th>
                            DATE &amp; TIME
                            @php
                                $nextSort = ($sort ?? 'asc') === 'asc' ? 'desc' : 'asc';
                                $sortUrl = route($mode === 'inplay' ? 'admin.sports.inplay' : 'admin.sports.completed', array_merge(request()->except('sort', 'page'), ['sort' => $nextSort]));
                            @endphp
                            <a class="sort-icon" href="{{ $sortUrl }}" title="Sort by date">⇅</a>
                        </th>
                        <th>COMPETITION NAME</th>
                        <th>DETAILS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($matches as $match)
                        <tr>
                            <td class="sports-menu-cell">
                                <details class="game-menu">
                                    <summary aria-label="Match actions">⌄</summary>
                                    <div class="game-menu__popup">
                                        @if($mode === 'inplay')
                                            <a href="{{ route('admin.sports.match-bets', [$match, 'match']) }}">MATCH BET</a>
                                            <a href="{{ route('admin.sports.match-bets', [$match, 'session']) }}">SESSION BET</a>
                                            <a href="{{ route('admin.sports.match-bets', [$match, 'completed-session']) }}">COMPLETED SESSION</a>
                                            <a href="{{ route('admin.sports.match-bets', [$match, 'rejected']) }}">REJECTED BETS</a>
                                        @else
                                            <a href="{{ route('admin.sports.match-pl-report', $match) }}">MATCH AND SESSION PL REPORT</a>
                                            <a href="{{ route('admin.sports.match-pl', $match) }}">MATCH AND SESSION PL</a>
                                            <a href="{{ route('admin.sports.match-bets', [$match, 'completed-match']) }}">MATCH BET</a>
                                            <a href="{{ route('admin.sports.match-bets', [$match, 'session-all']) }}">SESSION BET</a>
                                            <a href="{{ route('admin.sports.match-bets', [$match, 'completed-session']) }}">COMPLETED SESSION</a>
                                            <a href="{{ route('admin.sports.match-bets', [$match, 'rejected']) }}">REJECTED BETS</a>
                                        @endif
                                    </div>
                                </details>
                            </td>
                            <td class="sports-match-name">{{ strtoupper($match->home_team) }} V {{ strtoupper($match->away_team) }}</td>
                            <td class="nowrap">{{ strtoupper($match->start_time->format('m/d/Y h:i A')) }}</td>
                            <td>{{ strtoupper($match->league) }}</td>
                            <td><a class="classic-view-btn" href="{{ route('admin.sports.match-view', $match) }}">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="no-data"><span>NO DATA</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $matches->links('partials.pagination') }}
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var form = document.querySelector('.sports-filter');
    if (!form) return;
    form.querySelectorAll('input[type=date]').forEach(function (input) {
        input.addEventListener('change', function () { form.submit(); });
    });
})();
</script>
@endpush
