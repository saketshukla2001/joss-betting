@extends('layouts.admin')

@section('title', 'Statement · '.$user->username)

@section('content')
    <div class="classic-form-header">
        <span>STATEMENT · {{ strtoupper($user->username) }}</span>
        <a class="classic-back-btn classic-back-btn--light" href="{{ route('admin.hierarchy.index', $level) }}">Back</a>
    </div>

    <div class="statement-panel">
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Date</th><th>Type</th><th>Description</th><th>Amount</th><th>Balance after</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td class="nowrap">{{ strtoupper($transaction->created_at->format('d-M-Y h:i A')) }}</td>
                            <td>{{ $transaction->type_label }}</td>
                            <td>{{ strtoupper($transaction->description) }}</td>
                            <td class="mono {{ $transaction->amount >= 0 ? 'text-green' : 'text-red' }}">{{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 2) }}</td>
                            <td class="mono">{{ number_format($transaction->balance_after, 2) }}</td>
                            <td><span class="badge badge--{{ $transaction->status === 'completed' ? 'success' : 'warning' }}">{{ strtoupper($transaction->status) }}</span></td>
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
