@extends('layouts.admin')

@section('title', $title)

@section('content')
    <div class="classic-form-header">
        <span>{{ $title }}</span>
        <a class="classic-back-btn classic-back-btn--light" href="{{ route('admin.sports.inplay') }}">Back</a>
    </div>

    <div class="statement-panel">
        <div class="limit-match-head">
            <strong>{{ strtoupper($match->home_team) }} V {{ strtoupper($match->away_team) }}</strong>
            <span>{{ strtoupper($match->league) }} · {{ strtoupper($match->start_time->format('d-M-Y h:i A')) }}</span>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Ticket</th><th>User</th><th>Market</th><th>Selection</th><th>Odds</th><th>Stake</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($bets as $bet)
                        <tr>
                            <td class="mono">{{ $bet->bet_code }}</td>
                            <td>{{ $bet->user?->username }}</td>
                            <td>{{ $bet->market_name }}</td>
                            <td>{{ $bet->selection_name }}</td>
                            <td class="mono">{{ odds($bet->odds) }}</td>
                            <td class="mono">{{ number_format($bet->stake, 2) }}</td>
                            <td><span class="badge badge--{{ $bet->status_class }}">{{ strtoupper($bet->status_label) }}</span></td>
                            <td class="nowrap">{{ strtoupper($bet->created_at->format('d-M-Y h:i A')) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="no-data"><span>NO DATA</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $bets->links('partials.pagination') }}
    </div>
@endsection
