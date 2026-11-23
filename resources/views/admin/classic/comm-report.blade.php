@extends('layouts.admin')

@section('title', 'Agent Commission Report')

@section('content')
    <div class="classic-form-header">
        <span>AGENT COMMISSION REPORT</span>
        <a class="classic-back-btn classic-back-btn--light" href="{{ route('admin.settings.index') }}">Back</a>
    </div>

    <div class="commission-report-panel">
        <form method="GET" action="{{ route('admin.comm-report.index') }}" class="commission-filter">
            <div class="commission-date-group">
                <input type="date" name="start_date" value="{{ request('start_date') }}" placeholder="Start Date">
                <span class="date-arrow">→</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}" placeholder="End Date">
            </div>
            <input class="commission-agent" type="text" name="agent" value="{{ request('agent') }}" placeholder="Agent Username">
            <button class="commission-apply" type="submit">Apply</button>
            <a class="commission-reset" href="{{ route('admin.comm-report.index') }}">↻ Reset History</a>
        </form>

        <div class="table-wrap">
            <table class="data-table commission-table">
                <thead>
                    <tr>
                        <th rowspan="2">DATE</th>
                        <th rowspan="2">USERNAME</th>
                        <th colspan="6">MILA HAI</th>
                        <th colspan="5">DIYA HAI</th>
                    </tr>
                    <tr>
                        <th>M.COMM</th><th>S.COMM</th><th>C.COMM</th><th>MATKA.COMM</th><th>T.COMM</th><th>ACTION</th>
                        <th>M.COMM</th><th>S.COMM</th><th>C.COMM</th><th>MATKA.COMM</th><th>T.COMM</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="nowrap">{{ strtoupper(\Illuminate\Support\Carbon::parse($row['date'])->format('d-M-Y')) }}</td>
                            <td>{{ $row['username'] }}</td>
                            <td class="mono text-green">{{ number_format($row['received']['m'], 2) }}</td>
                            <td class="mono text-green">{{ number_format($row['received']['s'], 2) }}</td>
                            <td class="mono text-green">{{ number_format($row['received']['c'], 2) }}</td>
                            <td class="mono text-green">{{ number_format($row['received']['matka'], 2) }}</td>
                            <td class="mono text-green">{{ number_format($row['received']['t'], 2) }}</td>
                            <td><a class="classic-view-btn" href="{{ route('admin.comm-report.index', ['agent' => $row['username']]) }}">View</a></td>
                            <td class="mono text-red">{{ number_format($row['given']['m'], 2) }}</td>
                            <td class="mono text-red">{{ number_format($row['given']['s'], 2) }}</td>
                            <td class="mono text-red">{{ number_format($row['given']['c'], 2) }}</td>
                            <td class="mono text-red">{{ number_format($row['given']['matka'], 2) }}</td>
                            <td class="mono text-red">{{ number_format($row['given']['t'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13">
                                <div class="no-data">
                                    <svg viewBox="0 0 64 48" fill="none" stroke="#cfcfcf" stroke-width="2"><path d="M4 8h56v32H4z"/><path d="M4 8l28 20L60 8"/></svg>
                                    <span>NO DATA</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td class="strong">TOTAL</td>
                        <td></td>
                        <td class="mono text-green">{{ number_format($totals['received']['m'], 2) }}</td>
                        <td class="mono text-green">{{ number_format($totals['received']['s'], 2) }}</td>
                        <td class="mono text-green">{{ number_format($totals['received']['c'], 2) }}</td>
                        <td class="mono text-green">{{ number_format($totals['received']['matka'], 2) }}</td>
                        <td class="mono text-green">{{ number_format($totals['received']['t'], 2) }}</td>
                        <td></td>
                        <td class="mono text-red">{{ number_format($totals['given']['m'], 2) }}</td>
                        <td class="mono text-red">{{ number_format($totals['given']['s'], 2) }}</td>
                        <td class="mono text-red">{{ number_format($totals['given']['c'], 2) }}</td>
                        <td class="mono text-red">{{ number_format($totals['given']['matka'], 2) }}</td>
                        <td class="mono text-red">{{ number_format($totals['given']['t'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
