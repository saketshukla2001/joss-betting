@extends('layouts.admin')

@section('title', $title)

@section('content')
    <div class="classic-form-header">
        <span>{{ $title }}</span>
        <a class="classic-back-btn classic-back-btn--light" href="{{ route('admin.dashboard') }}">Back</a>
    </div>

    <div class="ledger-panel">
        <div class="ledger-columns">
            <div class="ledger-column lena-column">
                <div class="ledger-column__head"><span>LENA</span><strong>{{ number_format($totals['lena'], 2) }}</strong></div>
                <table class="ledger-mini-table">
                    <thead><tr><th>USERNAME</th><th>AMOUNT</th><th></th></tr></thead>
                    <tbody>
                        @forelse($lena as $user)
                            <tr>
                                <td><span class="eye-icon">◉</span>{{ strtoupper($user->username) }}<small>({{ $user->agent_code }})</small></td>
                                <td class="mono">{{ number_format($user->balance, 2) }}</td>
                                <td><a class="ledger-detail-btn" href="{{ route('admin.hierarchy.statement', [$level, $user]) }}" title="Ledger">▥</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><div class="no-data"><span>NO DATA</span></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="ledger-column dena-column">
                <div class="ledger-column__head"><span>DENA</span><strong>{{ number_format($totals['dena'], 2) }}</strong></div>
                <table class="ledger-mini-table">
                    <thead><tr><th>USERNAME</th><th>AMOUNT</th><th></th></tr></thead>
                    <tbody>
                        @forelse($dena as $user)
                            <tr>
                                <td><span class="eye-icon">◉</span>{{ strtoupper($user->username) }}<small>({{ $user->agent_code }})</small></td>
                                <td class="mono">{{ number_format(abs($user->balance), 2) }}</td>
                                <td><a class="ledger-detail-btn" href="{{ route('admin.hierarchy.statement', [$level, $user]) }}" title="Ledger">▥</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><div class="no-data"><span>NO DATA</span></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="ledger-column clear-column">
                <div class="ledger-column__head"><span>CLEAR</span><strong>{{ number_format($totals['clear'], 2) }}</strong></div>
                <table class="ledger-mini-table">
                    <thead><tr><th>USERNAME</th><th>AMOUNT</th><th></th></tr></thead>
                    <tbody>
                        @forelse($clear as $user)
                            <tr>
                                <td><span class="eye-icon">◉</span>{{ strtoupper($user->username) }}<small>({{ $user->agent_code }})</small></td>
                                <td class="mono">0.00</td>
                                <td><a class="ledger-detail-btn" href="{{ route('admin.hierarchy.statement', [$level, $user]) }}" title="Ledger">▥</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><div class="no-data"><span>NO DATA</span></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
