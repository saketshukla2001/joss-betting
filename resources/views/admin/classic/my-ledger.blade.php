@extends('layouts.admin')

@section('title', 'My Ledger')

@section('content')
    <div class="classic-form-header">
        <span>MY LEDGER</span>
        <a class="classic-back-btn classic-back-btn--light" href="{{ route('admin.dashboard') }}">Back</a>
    </div>

    <div class="ledger-panel">
        <div class="my-ledger-summary">
            <div class="my-ledger-summary__item lena">
                <span>LENA</span>
                <strong>↑ {{ number_format($summary['lena'], 2) }}</strong>
            </div>
            <div class="my-ledger-summary__item dena">
                <span>DENA</span>
                <strong>↓ {{ number_format($summary['dena'], 2) }}</strong>
            </div>
            <div class="my-ledger-summary__item balance">
                <span>BALANCE</span>
                <strong>{{ number_format($summary['balance'], 2) }} DENA</strong>
            </div>
        </div>

        <div class="table-wrap">
            <table class="data-table my-ledger-table">
                <thead><tr><th>DATE</th><th>DR</th><th>CR</th><th>BALANCE</th><th>PAYMENT TYPE</th><th>REMARK</th></tr></thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        @php
                            $dr = $transaction->amount < 0 ? abs($transaction->amount) : 0;
                            $cr = $transaction->amount > 0 ? $transaction->amount : 0;
                        @endphp
                        <tr>
                            <td class="nowrap">{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="mono {{ $dr > 0 ? 'text-red' : '' }}">{{ $dr > 0 ? number_format($dr, 2) : '0' }}</td>
                            <td class="mono {{ $cr > 0 ? 'text-green' : '' }}">{{ $cr > 0 ? number_format($cr, 2) : '0' }}</td>
                            <td class="mono">{{ number_format($transaction->balance_after, 2) }} DENA</td>
                            <td>{{ strtoupper($transaction->description) }}</td>
                            <td>{{ strtoupper($transaction->type_label) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="no-data"><span>NO DATA</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $transactions->links('partials.pagination') }}
    </div>
@endsection
