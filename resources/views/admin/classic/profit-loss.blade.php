@extends('layouts.admin')

@section('title', 'Match Profit Loss')

@section('content')
    <div class="classic-form-header">
        <span>MATCH PROFIT LOSS</span>
        <a class="classic-back-btn" href="{{ route('admin.settings.index') }}">Back</a>
    </div>

    <div class="statement-panel">
        <div class="profit-total">TOTAL: <strong>{{ number_format($total, 0) }}</strong></div>

        <div class="table-wrap">
            <table class="data-table profit-table">
                <thead>
                    <tr><th>Title</th><th>PL</th><th>COMM+</th><th>COMM−</th><th>NET PL</th></tr>
                </thead>
                <tbody>
                    @forelse($matches as $match)
                        @php
                            $pl = (float) $match->pl;
                            $commission = (float) $match->commission_sum;
                            $net = round($pl + $commission, 2);
                        @endphp
                        <tr>
                            <td>{{ strtoupper($match->home_team.' V '.$match->away_team) }}-{{ $match->start_time->format('Y-m-d H:i:s') }}</td>
                            <td class="mono {{ $pl < 0 ? 'text-red' : ($pl > 0 ? 'text-green' : '') }}">{{ number_format($pl, 2) }}</td>
                            <td class="mono {{ $commission > 0 ? 'text-green' : '' }}">{{ number_format($commission, 2) }}</td>
                            <td class="mono">0.00</td>
                            <td class="mono {{ $net < 0 ? 'text-red' : ($net > 0 ? 'text-green' : '') }}">{{ number_format($net, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty"><strong>No match profit/loss data</strong></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $matches->links('partials.pagination') }}
    </div>
@endsection
