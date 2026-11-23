@extends('layouts.admin')

@section('title', 'Match and Session PL')

@section('content')
    <div class="classic-form-header">
        <span>MATCH AND SESSION PL</span>
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

        <div class="table-wrap" style="padding:0 14px 14px">
            <table class="data-table pl-table">
                <thead><tr><th>Market</th><th>Type</th><th>Bets</th><th>Stake</th><th>Payout</th><th>PL</th></tr></thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row['market'] }}</td>
                            <td><span class="badge {{ $row['type'] === 'SESSION' ? 'badge--info' : 'badge--muted' }}">{{ $row['type'] }}</span></td>
                            <td>{{ $row['bets'] }}</td>
                            <td class="mono">{{ number_format($row['stake'], 2) }}</td>
                            <td class="mono">{{ number_format($row['payout'], 2) }}</td>
                            <td class="mono {{ $row['pl'] < 0 ? 'text-red' : ($row['pl'] > 0 ? 'text-green' : '') }}">{{ number_format($row['pl'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="no-data"><span>NO DATA</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
