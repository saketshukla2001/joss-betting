@extends('layouts.admin')

@section('title', $config['label'].' Transaction')

@section('content')
    <div class="classic-form-header">
        <span>{{ strtoupper($config['label']) }} TRANSACTION</span>
        <a class="classic-back-btn classic-back-btn--light" href="{{ route('admin.dashboard') }}">Back</a>
    </div>

    <div class="cash-form-panel">
        <form method="POST" action="{{ route('admin.cash.store', $level) }}">
            @csrf

            <div class="cash-grid">
                <div class="field">
                    <label for="client_id">CLIENT<span class="req">*</span></label>
                    <div class="select-with-icon">
                        <select class="select" id="client_id" name="client_id" required>
                            <option value="">SELECT CLIENT</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" @selected((int) old('client_id') === $client->id)>
                                    {{ $client->username }} ({{ $client->agent_code }}) · {{ number_format($client->balance, 0) }} coins
                                </option>
                            @endforeach
                        </select>
                        <span class="select-search"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.5 15.5L21 21"/></svg></span>
                    </div>
                </div>

                <div class="field">
                    <label for="collection">COLLECTION<span class="req">*</span></label>
                    <input class="input" id="collection" name="collection" value="{{ old('collection', 'CASH') }}" placeholder="CASH" required>
                </div>

                <div class="field">
                    <label for="date">DATE<span class="req">*</span></label>
                    <input class="input" id="date" name="date" type="date" value="{{ old('date', now()->toDateString()) }}" required>
                </div>

                <div class="field">
                    <label for="amount">AMOUNT<span class="req">*</span></label>
                    <input class="input" id="amount" name="amount" type="number" min="1" step="1" value="{{ old('amount') }}" required>
                </div>

                <div class="field">
                    <label for="payment_type">PAYMENT TYPE<span class="req">*</span></label>
                    <select class="select" id="payment_type" name="payment_type" required>
                        <option value="">SELECT PAYMENT TYPE</option>
                        @foreach(['CASH' => 'CASH', 'ONLINE' => 'ONLINE', 'BANK' => 'BANK', 'UPI' => 'UPI'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('payment_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="comment">COMMENT<span class="req">*</span></label>
                    <input class="input" id="comment" name="comment" value="{{ old('comment') }}" placeholder="Comment" required>
                </div>
            </div>

            <button class="cash-submit" type="submit">Submit</button>
        </form>
    </div>
@endsection
