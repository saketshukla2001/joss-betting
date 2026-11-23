@extends('layouts.admin')

@section('title', 'Match and Session PL Report')

@section('content')
    <div class="classic-form-header">
        <span>MATCH AND SESSION PL REPORT</span>
        <a class="classic-back-btn classic-back-btn--light" href="{{ route('admin.sports.completed') }}">Back</a>
    </div>

    <div class="statement-panel">
        <div class="limit-match-head">
            <strong>{{ strtoupper($match->home_team) }} V {{ strtoupper($match->away_team) }}</strong>
            <span>{{ strtoupper($match->league) }} · {{ strtoupper($match->start_time->format('d-M-Y h:i A')) }}</span>
        </div>

        <div class="admin-stat-grid" style="padding:14px">
            <div class="stat pl-stat"><div class="stat__label">Match PL</div><div class="stat__value">{{ number_format($totals['match'], 2) }}</div></div>
            <div class="stat pl-stat"><div class="stat__label">Session PL</div><div class="stat__value">{{ number_format($totals['session'], 2) }}</div></div>
            <div class="stat pl-stat"><div class="stat__label">Total PL</div><div class="stat__value">{{ number_format($totals['total'], 2) }}</div></div>
        </div>

        @foreach([['MATCH BETS', $matchBets], ['SESSION BETS', $sessionBets]] as [$sectionTitle, $sectionBets])
            <div class="pl-section">
                <h3>{{ $sectionTitle }}</h3>
                <div class="table-wrap">
                    <table class="data-table pl-table">
                        <thead><tr><th>Date</th><th>Ticket</th><th>User</th><th>Market</th><th>Selection</th><th>Stake</th><th>Payout</th><th>PL</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($sectionBets as $bet)
                                @php
                                    $pl = $bet->status === 'won' ? $bet->payout - $bet->stake : ($bet->status === 'lost' ? -$bet->stake : 0);
                                @endphp
                                <tr>
                                    <td class="nowrap">{{ strtoupper($bet->created_at->format('d-M-Y h:i A')) }}</td>
                                    <td class="mono">{{ $bet->bet_code }}</td>
                                    <td>{{ $bet->user?->username }}</td>
                                    <td>{{ $bet->market_name }}</td>
                                    <td>{{ $bet->selection_name }}</td>
                                    <td class="mono">{{ number_format($bet->stake, 2) }}</td>
                                    <td class="mono">{{ number_format($bet->payout, 2) }}</td>
                                    <td class="mono {{ $pl < 0 ? 'text-red' : ($pl > 0 ? 'text-green' : '') }}">{{ number_format($pl, 2) }}</td>
                                    <td><span class="badge badge--{{ $bet->status_class }}">{{ strtoupper($bet->status_label) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="9"><div class="no-data"><span>NO DATA</span></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
@endsection
