@extends('layouts.admin')

@section('title', 'Match Ledger')

@section('content')
    <div class="classic-form-header">
        <span>MATCH LEDGER</span>
        <a class="classic-back-btn classic-back-btn--light" href="{{ route('admin.dashboard') }}">Back</a>
    </div>

    <div class="ledger-panel">
        <form method="GET" action="{{ route('admin.ledger.profit-loss') }}" class="ledger-filter">
            <input class="input" type="date" name="start_date" value="{{ request('start_date') }}" placeholder="Start Date">
            <input class="input" type="date" name="end_date" value="{{ request('end_date') }}" placeholder="End Date">
            <button class="ledger-search" type="submit">Search</button>
        </form>

        <div class="match-ledger-total">TOTAL: <strong>{{ number_format($total, 2) }}</strong></div>

        <div class="table-wrap">
            <table class="data-table match-ledger-table">
                <thead><tr><th>DATE</th><th>EVENT NAME</th><th>WINNER</th><th>CR</th><th>DR</th></tr></thead>
                <tbody>
                    @forelse($matches as $match)
                        <tr>
                            <td class="nowrap">{{ strtoupper($match->start_time->format('d M Y')) }}</td>
                            <td>{{ strtoupper($match->home_team.' V '.$match->away_team) }}</td>
                            <td>{{ strtoupper($match->winner_label) }}</td>
                            <td class="mono {{ $match->credit > 0 ? 'text-green' : '' }}">{{ $match->credit > 0 ? number_format($match->credit, 2) : '0' }}</td>
                            <td class="mono {{ $match->debit > 0 ? 'text-red' : '' }}">{{ $match->debit > 0 ? number_format($match->debit, 2) : '0' }}</td>
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
