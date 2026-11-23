@extends('layouts.admin')

@section('title', 'Statement')

@section('content')
    <div class="classic-form-header">
        <span>STATEMENT</span>
        <a class="classic-back-btn" href="{{ route('admin.settings.index') }}">Back</a>
    </div>

    <div class="statement-panel">
        <div class="statement-toolbar">
            <div class="statement-tabs">
                <a class="statement-tab {{ $view === 'all' ? 'active' : '' }}" href="{{ route('admin.settings.statement', ['limit' => $limit]) }}">All</a>
                <a class="statement-tab {{ $view === 'pl' ? 'active' : '' }}" href="{{ route('admin.settings.statement', ['view' => 'pl', 'limit' => $limit]) }}">P/L</a>
                <a class="statement-tab {{ request('limit') ? 'active' : '' }}" href="{{ route('admin.settings.statement', ['limit' => 25]) }}">Limit</a>
                <form method="GET" action="{{ route('admin.settings.statement') }}" class="statement-limit">
                    @if($view === 'pl')<input type="hidden" name="view" value="pl">@endif
                    <select name="limit" onchange="this.form.submit()">
                        @foreach([15, 25, 50, 100] as $option)
                            <option value="{{ $option }}" @selected($limit === $option)>{{ $option }} rows</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <button class="btn btn--danger btn--sm" type="submit" form="bulk-delete-form">Delete Selected</button>
        </div>

        @if($view === 'pl')
            <div class="admin-stat-grid" style="margin-bottom:12px">
                <div class="stat"><div class="stat__label">Total CR</div><div class="stat__value">{{ number_format($summary['credit'], 0) }}</div></div>
                <div class="stat"><div class="stat__label">Total DR</div><div class="stat__value">{{ number_format($summary['debit'], 0) }}</div></div>
                <div class="stat"><div class="stat__label">Net</div><div class="stat__value">{{ number_format($summary['net'], 0) }}</div></div>
                <div class="stat"><div class="stat__label">Balance</div><div class="stat__value">{{ number_format($summary['balance'], 0) }}</div></div>
            </div>
        @endif

        <form id="bulk-delete-form" method="POST" action="{{ route('admin.settings.statement.bulk-delete') }}" data-confirm="Delete selected statement entries?">
            @csrf
        </form>

        <div class="table-wrap">
            <table class="data-table statement-table">
                <thead>
                    <tr>
                        <th class="statement-check"><input type="checkbox" id="statement-select-all" aria-label="Select all"></th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Old.Bal</th>
                        <th>CR</th>
                        <th>DR</th>
                        <th>COMM+</th>
                        <th>COMM-</th>
                        <th>Balance</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        @php
                            $oldBalance = $transaction->balance_after - $transaction->amount;
                            $credit = max(0, $transaction->amount);
                            $debit = max(0, -$transaction->amount);
                        @endphp
                        <tr>
                            <td class="statement-check">
                                <input type="checkbox" name="ids[]" value="{{ $transaction->id }}" form="bulk-delete-form" class="statement-checkbox" aria-label="Select entry">
                            </td>
                            <td class="nowrap">{{ strtoupper($transaction->created_at->format('d-M-Y h:i A')) }}</td>
                            <td>
                                {{ strtoupper($transaction->description) }}
                                @if($transaction->is_demo)<span class="badge badge--muted" style="margin-left:5px">DEMO</span>@endif
                            </td>
                            <td class="mono">{{ number_format($oldBalance, 2) }}</td>
                            <td class="mono text-green">{{ number_format($credit, 2) }}</td>
                            <td class="mono text-red">{{ number_format($debit, 2) }}</td>
                            <td class="mono">0.00</td>
                            <td class="mono">0.00</td>
                            <td class="mono">{{ number_format($transaction->balance_after, 2) }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.settings.statement.destroy', $transaction) }}" data-confirm="Delete this statement entry?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="statement-delete" type="submit" title="Delete">×</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10"><div class="empty"><strong>No statement entries</strong></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $transactions->links('partials.pagination') }}
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var all = document.getElementById('statement-select-all');
    if (!all) return;
    var boxes = Array.prototype.slice.call(document.querySelectorAll('.statement-checkbox'));
    all.addEventListener('change', function () {
        boxes.forEach(function (box) { box.checked = all.checked; });
    });
})();
</script>
@endpush
