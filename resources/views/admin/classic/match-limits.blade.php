@extends('layouts.admin')

@section('title', 'Master Update Limit')

@section('content')
    <div class="classic-form-header">
        <span>MASTER UPDATE LIMIT</span>
        <a class="classic-back-btn classic-back-btn--light" href="{{ route('admin.sports.inplay') }}">Back</a>
    </div>

    <div class="limit-panel">
        <div class="limit-match-head">
            <strong>{{ strtoupper($match->home_team) }} V {{ strtoupper($match->away_team) }}</strong>
            <span>{{ strtoupper($match->league) }} · {{ strtoupper($match->start_time->format('d-M-Y h:i A')) }}</span>
        </div>

        @foreach($masters as $master)
            <form id="match-limit-form-{{ $master->id }}" method="POST" action="{{ route('admin.sports.match-limits.update', $match) }}">
                @csrf
                <input type="hidden" name="user_id" value="{{ $master->id }}">
            </form>
        @endforeach

        <div class="table-wrap">
            <table class="data-table limit-table">
                <thead>
                    <tr>
                        <th>CODE <span class="th-search">⌕</span></th>
                        <th>NAME <span class="th-search">⌕</span></th>
                        <th>C.CHIP</th>
                        <th>ADD / MINUS LIMIT</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($masters as $master)
                        <tr>
                            <td class="mono">{{ $master->agent_code }}</td>
                            <td>{{ strtoupper($master->username) }}</td>
                            <td class="mono">
                                {{ number_format($master->balance, 2) }}
                                @if(($limits[$master->id] ?? 0) > 0)
                                    <div class="match-limit-note">Match limit: {{ number_format($limits[$master->id], 2) }}</div>
                                @endif
                            </td>
                            <td>
                                <input class="input limit-input" type="number" name="amount" min="0.01" step="0.01" placeholder="Enter amount" form="match-limit-form-{{ $master->id }}" required>
                            </td>
                            <td>
                                <div class="limit-actions">
                                    <button class="limit-add" type="submit" name="direction" value="add" form="match-limit-form-{{ $master->id }}">+ Add</button>
                                    <button class="limit-minus" type="submit" name="direction" value="minus" form="match-limit-form-{{ $master->id }}">− Minus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="no-data"><span>NO DATA</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $masters->links('partials.pagination') }}
    </div>
@endsection
