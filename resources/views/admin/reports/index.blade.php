@extends('layouts.admin')

@section('title', 'Reports')
@section('page-title', 'Reports')
@section('page-subtitle', $from->format('d M Y').' — '.$to->format('d M Y'))

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>Reports</h1>
            <p>Stakes, payouts and gross gaming revenue for the selected period.</p>
        </div>
        <a class="btn btn--primary btn--sm" href="{{ route('admin.reports.export', request()->only(['from', 'to', 'status'])) }}">Export CSV</a>
    </div>

    <form class="card filter-bar" method="GET" action="{{ route('admin.reports.index') }}">
        <div class="field" style="margin:0">
            <label class="sr-only">From</label>
            <input class="input" type="date" name="from" value="{{ $from->toDateString() }}">
        </div>
        <div class="field" style="margin:0">
            <label class="sr-only">To</label>
            <input class="input" type="date" name="to" value="{{ $to->toDateString() }}">
        </div>
        <select class="select" name="status">
            <option value="">All statuses</option>
            @foreach(['pending' => 'Open', 'won' => 'Won', 'lost' => 'Lost', 'void' => 'Void'] as $value => $label)
                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn--primary" type="submit">Run report</button>
    </form>

    <div class="admin-stat-grid mt-20">
        <div class="stat">
            <div class="stat__label">Tickets</div>
            <div class="stat__value">{{ number_format($summary['bets']) }}</div>
            <div class="stat__meta">{{ money($summary['pending_stake']) }} still open</div>
        </div>
        <div class="stat stat--lime">
            <div class="stat__label">Stake</div>
            <div class="stat__value">{{ short_money($summary['stake']) }}</div>
            <div class="stat__meta">{{ money($summary['stake']) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Payouts</div>
            <div class="stat__value">{{ short_money($summary['payout']) }}</div>
            <div class="stat__meta">{{ money($summary['payout']) }}</div>
        </div>
        <div class="stat {{ $summary['ggr'] >= 0 ? 'stat--lime' : 'stat--danger' }}">
            <div class="stat__label">GGR</div>
            <div class="stat__value">{{ short_money($summary['ggr']) }}</div>
            <div class="stat__meta">Stake − payout</div>
        </div>
    </div>

    <div class="admin-grid-2 mt-20">
        <div class="admin-card">
            <div class="admin-card__head"><h3>Daily stake</h3></div>
            <div class="admin-card__body">
                @if(count($daily) === 0)
                    <div class="empty">No activity in this period.</div>
                @else
                    <div class="chart">
                        @foreach($daily as $point)
                            <div class="chart__col">
                                <div class="chart__bars">
                                    <div class="chart__bar chart__bar--stake" style="height:{{ max(2, round(($point['stake'] / $dailyMax) * 100)) }}%" title="{{ money($point['stake']) }}"></div>
                                </div>
                                <span class="chart__label">{{ $point['date'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card__head"><h3>By sport</h3></div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Sport</th><th>Bets</th><th>Stake</th><th>Payout</th></tr></thead>
                    <tbody>
                        @forelse($bySport as $row)
                            <tr>
                                <td>{{ $row->sport_name ?? 'Other' }}</td>
                                <td>{{ number_format($row->bets_count) }}</td>
                                <td class="mono">{{ money($row->total_stake) }}</td>
                                <td class="mono">{{ money($row->total_payout) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><div class="empty">No data.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Top players by stake</h3></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Player</th><th>Tickets</th><th>Stake</th><th>Payout</th><th>Net</th></tr></thead>
                <tbody>
                    @forelse($topUsers as $row)
                        <tr>
                            <td>
                                @if($row->user)
                                    <a href="{{ route('admin.users.show', $row->user) }}">{{ $row->user->username }}</a>
                                    <span class="sub">{{ $row->user->name }}</span>
                                @else
                                    <span class="text-muted">Deleted</span>
                                @endif
                            </td>
                            <td>{{ number_format($row->bets_count) }}</td>
                            <td class="mono">{{ money($row->total_stake) }}</td>
                            <td class="mono">{{ money($row->total_payout) }}</td>
                            <td class="mono {{ ($row->total_stake - $row->total_payout) >= 0 ? 'text-green' : 'text-red' }}">{{ money($row->total_stake - $row->total_payout) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty">No player activity in this period.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
