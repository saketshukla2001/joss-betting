@extends('layouts.admin')

@section('title', $match->label)

@section('content')
    <div class="live-page">
        <div class="live-head">
            <span>{{ strtoupper($match->home_team) }} V {{ strtoupper($match->away_team) }}</span>
            <div class="live-head-icons">
                <button type="button" onclick="window.location.reload()" title="Refresh">⟳</button>
                <button type="button" onclick="window.print()" title="Print">▤</button>
            </div>
        </div>

        <div class="live-tabs">
            <button type="button" class="live-tab active" data-live-tab="my">My Book</button>
            <button type="button" class="live-tab" data-live-tab="tii">Tii Book</button>
        </div>

        <div class="live-panel" data-live-panel="my">
            <div class="live-team-table">
                <table>
                    <thead><tr><th></th><th>LAGAI</th><th>KHAI</th></tr></thead>
                    <tbody>
                        @foreach($teamRows as $row)
                            <tr>
                                <td class="live-team-name">{{ strtoupper($row['team']) }}</td>
                                <td class="live-lagai">{{ $row['lagai'] }}</td>
                                <td class="live-khai">{{ $row['khai'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="live-section-head fancy-head">
                <span>FANCY (COMM.)</span><span>NOT</span><span>YES</span>
            </div>
            <div class="live-fancy-list">
                @forelse($fancyRows as $row)
                    @if($row['suspended'])
                        <div class="live-fancy-row suspended">
                            <div class="live-fancy-name">{{ strtoupper($row['name']) }}<small>SESSION LIMIT : {{ number_format($row['limit']) }}</small></div>
                            <div class="live-fancy-suspended">SUSPENDED</div>
                        </div>
                    @else
                        <div class="live-fancy-row">
                            <div class="live-fancy-name">
                                {{ strtoupper($row['name']) }}
                                <small>SESSION LIMIT : {{ number_format($row['limit']) }}</small>
                            </div>
                            <div class="live-fancy-badge">{{ $row['badge'] ? 'B' : '' }}</div>
                            <div class="live-fancy-not">{{ $row['not'] }}</div>
                            <div class="live-fancy-yes">{{ $row['yes'] }}</div>
                        </div>
                    @endif
                @empty
                    <div class="live-empty">NO FANCY MARKETS</div>
                @endforelse
            </div>

            <div class="live-section-head clients-head"><span>CLIENTS SESSION PL</span></div>
            <div class="table-wrap">
                <table class="live-table live-clients-table">
                    <thead>
                        <tr>
                            <th>TOTAL</th><th>SESSION AMT.</th><th>TOTAL</th><th>SESSION COMM</th>
                            <th>TOTAL COMM</th><th>TOTAL AMOUNT</th><th>MY SHARE</th><th>NET PL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clientSessionRows as $row)
                            <tr>
                                <td>{{ $row['user']?->username }} ({{ $row['user']?->agent_code }})</td>
                                <td>{{ number_format($row['session_amt'], 2) }}</td>
                                <td>{{ number_format($row['total'], 2) }}</td>
                                <td class="text-red">{{ number_format($row['session_comm'], 2) }}</td>
                                <td class="text-red">{{ number_format($row['total_comm'], 2) }}</td>
                                <td>{{ number_format($row['total_amount'], 2) }}</td>
                                <td class="text-green">{{ number_format($row['my_share'], 2) }}</td>
                                <td class="text-green">{{ number_format($row['net_pl'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td>TOTAL</td><td>0.00</td><td>0.00</td><td class="text-red">0.00</td>
                                <td class="text-red">0.00</td><td>0.00</td><td class="text-green">0.00</td><td class="text-green">0.00</td>
                            </tr>
                        @endforelse
                        @if($clientSessionRows->isNotEmpty())
                            <tr class="live-total-row">
                                <td>TOTAL</td>
                                <td>{{ number_format($clientTotals['session_amt'], 2) }}</td>
                                <td>{{ number_format($clientTotals['total'], 2) }}</td>
                                <td class="text-red">{{ number_format($clientTotals['session_comm'], 2) }}</td>
                                <td class="text-red">{{ number_format($clientTotals['total_comm'], 2) }}</td>
                                <td>{{ number_format($clientTotals['total_amount'], 2) }}</td>
                                <td class="text-green">{{ number_format($clientTotals['my_share'], 2) }}</td>
                                <td class="text-green">{{ number_format($clientTotals['net_pl'], 2) }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="live-section-head match-stats-head">
                <span>MATCH STATS</span>
                <button type="button" class="live-small-btn" onclick="document.getElementById('live-match-stats').scrollIntoView({behavior:'smooth'})">MATCH BETS</button>
            </div>
            <div class="table-wrap" id="live-match-stats">
                <table class="live-table">
                    <thead><tr><th>PLACE TIME</th><th>USERNAME</th><th>RUNNER NAME</th><th>BET TYPE</th><th>BET PRICE</th><th>BET VALUE</th><th>BET AMOUNT</th></tr></thead>
                    <tbody>
                        @forelse($bets as $bet)
                            <tr>
                                <td>{{ strtoupper($bet->created_at->format('d-M-Y h:i A')) }}</td>
                                <td>{{ $bet->user?->username }}</td>
                                <td>{{ $bet->selection_name }}</td>
                                <td>{{ $bet->market_name }}</td>
                                <td>{{ odds($bet->odds) }}</td>
                                <td>{{ number_format($bet->potential_return, 2) }}</td>
                                <td>{{ number_format($bet->stake, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="live-empty">NO DATA</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="live-section-head completed-head">
                <span>COMPLETED SESSIONS</span>
                <button type="button" class="live-small-btn" onclick="window.location.reload()">Refresh</button>
            </div>
            <div class="table-wrap">
                <table class="live-table">
                    <thead><tr><th>S.NO</th><th>RUNNER NAME</th><th>WINNER</th><th>COMM+</th><th>COMM-</th><th>PL</th><th>ACTION</th></tr></thead>
                    <tbody>
                        @forelse($completedSessions as $session)
                            <tr>
                                <td>{{ $session['no'] }}</td>
                                <td>{{ $session['name'] }}</td>
                                <td>{{ $session['winner'] }}</td>
                                <td class="text-green">{{ number_format($session['comm_plus'], 2) }}</td>
                                <td class="text-red">{{ number_format($session['comm_minus'], 2) }}</td>
                                <td class="{{ $session['pl'] < 0 ? 'text-red' : 'text-green' }}">{{ number_format($session['pl'], 2) }}</td>
                                <td class="live-actions">
                                    <a href="{{ route('admin.sports.match-pl', $match) }}">Total Pl</a>
                                    <a href="{{ route('admin.sports.match-bets', [$match, 'session-all']) }}">Bets</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="live-empty">NO DATA</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="live-panel" data-live-panel="tii" hidden>
            <div class="live-empty" style="padding:60px">NO DATA</div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    document.querySelectorAll('[data-live-tab]').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('[data-live-tab]').forEach(function (item) { item.classList.remove('active'); });
            document.querySelectorAll('[data-live-panel]').forEach(function (panel) { panel.hidden = true; });
            tab.classList.add('active');
            var panel = document.querySelector('[data-live-panel="' + tab.dataset.liveTab + '"]');
            if (panel) panel.hidden = false;
        });
    });
})();
</script>
@endpush
