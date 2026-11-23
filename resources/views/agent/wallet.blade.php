@extends('layouts.agent')

@section('title', 'Agent Wallet')
@section('page-title', 'Wallet')
@section('page-subtitle', 'Commission payouts and withdrawals')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>Agent wallet</h1>
            <p>Commission payouts land here. Request a withdrawal when you are ready.</p>
        </div>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Wallet balance</div>
            <div class="stat__value">{{ money($agent->balance) }}</div>
            <div class="stat__meta">Available to withdraw</div>
        </div>
        <div class="stat">
            <div class="stat__label">Commission balance</div>
            <div class="stat__value">{{ money($agent->commission_balance) }}</div>
            <div class="stat__meta">Awaiting admin payout</div>
        </div>
        <div class="stat">
            <div class="stat__label">Pending withdrawals</div>
            <div class="stat__value">{{ money($summary['pending_withdrawals']) }}</div>
            <div class="stat__meta">On hold for review</div>
        </div>
        <div class="stat">
            <div class="stat__label">Paid commission</div>
            <div class="stat__value text-green">{{ money($summary['paid_commission']) }}</div>
            <div class="stat__meta">Lifetime</div>
        </div>
    </div>

    <div class="admin-grid-2 mt-20">
        <div class="admin-card">
            <div class="admin-card__head"><h3>Request withdrawal</h3><span class="badge badge--warning">Manual review</span></div>
            <div class="admin-card__body">
                <form method="POST" action="{{ route('agent.wallet.withdraw') }}" data-method-form>
                    @csrf
                    <div class="field">
                        <label for="amount">Amount</label>
                        <div class="input-group">
                            <span class="prefix">{{ $siteSettings['currency'] }}</span>
                            <input class="input" id="amount" name="amount" type="number" min="{{ $siteSettings['min_withdrawal'] }}" step="1" value="{{ old('amount') }}" required>
                        </div>
                        <div class="hint">Available {{ money($agent->balance) }} · minimum {{ money($siteSettings['min_withdrawal']) }}</div>
                    </div>

                    <div class="field">
                        <span class="label">Payout method</span>
                        <div class="payment-methods">
                            <label class="method-option"><input type="radio" name="method" value="upi" @checked(old('method', 'upi') === 'upi')><span><em>📱</em>UPI</span></label>
                            <label class="method-option"><input type="radio" name="method" value="bank" @checked(old('method') === 'bank')><span><em>🏦</em>Bank</span></label>
                            <label class="method-option"><input type="radio" name="method" value="usdt" @checked(old('method') === 'usdt')><span><em>₿</em>USDT</span></label>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="field">
                            <label for="account_name">Account holder</label>
                            <input class="input" id="account_name" name="account_name" value="{{ old('account_name', $agent->name) }}" required>
                        </div>
                        <div class="field">
                            <label for="account_number">Account number / wallet address</label>
                            <input class="input" id="account_number" name="account_number" value="{{ old('account_number') }}" required>
                        </div>
                    </div>

                    <div class="method-fields" data-method-field="upi">
                        <div class="field"><label for="upi_id">UPI ID</label><input class="input" id="upi_id" name="upi_id" value="{{ old('upi_id') }}" placeholder="name@bank"></div>
                    </div>
                    <div class="method-fields" data-method-field="bank">
                        <div class="field"><label for="ifsc">IFSC</label><input class="input" id="ifsc" name="ifsc" value="{{ old('ifsc') }}" placeholder="HDFC0001234"></div>
                    </div>

                    @if($agent->balance < (float) $siteSettings['min_withdrawal'])
                        <div class="alert alert--info" style="margin-bottom:12px">Your wallet is below the minimum withdrawal. The admin must release pending commission first.</div>
                    @endif
                    <button class="btn btn--primary btn--block" type="submit" @disabled($agent->balance < (float) $siteSettings['min_withdrawal'])>Request withdrawal</button>
                    <p class="hint mt-8">The amount is held immediately and refunded if the admin rejects the request.</p>
                </form>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card__head"><h3>Withdrawal history</h3></div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>ID</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                        @forelse($withdrawals as $withdrawal)
                            <tr>
                                <td>#{{ $withdrawal->id }}</td>
                                <td class="mono">{{ money($withdrawal->amount) }}</td>
                                <td>{{ $withdrawal->method_label }}</td>
                                <td>
                                    <span class="badge badge--{{ $withdrawal->status_class }}">{{ ucfirst($withdrawal->status) }}</span>
                                    @if($withdrawal->admin_note)<span class="sub">{{ $withdrawal->admin_note }}</span>@endif
                                </td>
                                <td><span class="sub" style="margin:0">{{ $withdrawal->created_at->format('d M, H:i') }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="empty">No withdrawals yet.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Wallet ledger</h3></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Type</th><th>Description</th><th>Amount</th><th>Balance after</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->type_label }}</td>
                            <td>{{ $transaction->description }}</td>
                            <td class="mono {{ $transaction->amount >= 0 ? 'text-green' : 'text-red' }}">{{ $transaction->amount >= 0 ? '+' : '' }}{{ money($transaction->amount) }}</td>
                            <td class="mono">{{ money($transaction->balance_after) }}</td>
                            <td><span class="badge badge--{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}">{{ $transaction->status_label }}</span></td>
                            <td><span class="sub" style="margin:0">{{ $transaction->created_at->format('d M, H:i') }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty">No wallet transactions yet. Commission payouts will appear here.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $transactions->links('partials.pagination') }}
    </div>
@endsection
