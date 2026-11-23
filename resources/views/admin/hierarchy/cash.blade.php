@extends('layouts.admin')

@section('title', ucfirst($direction).' · '.$user->username)

@section('content')
    <div class="classic-form-header">
        <span>{{ strtoupper($direction) }} · {{ strtoupper($user->username) }}</span>
        <a class="classic-back-btn classic-back-btn--light" href="{{ route('admin.hierarchy.index', $level) }}">Back</a>
    </div>

    <div class="classic-form-card" style="margin-top:0;max-width:820px">
        <div class="info-list" style="margin-bottom:16px">
            <li><span>Account</span><strong>{{ $user->name }} ({{ $user->username }})</strong></li>
            <li><span>Role</span><strong>{{ $config['label'] }}</strong></li>
            <li><span>Code</span><strong>{{ $user->agent_code }}</strong></li>
            <li><span>Current coins</span><strong>{{ number_format($user->balance, 2) }}</strong></li>
        </div>

        <form method="POST" action="{{ route('admin.hierarchy.cash.store', [$level, $user]) }}">
            @csrf
            <input type="hidden" name="direction" value="{{ $direction }}">

            <div class="field">
                <label for="amount">Amount (coins)</label>
                <input class="input" id="amount" name="amount" type="number" min="1" step="1" required>
            </div>
            <div class="field">
                <label for="note">Note</label>
                <input class="input" id="note" name="note" required placeholder="Reason / reference">
            </div>
            <button class="btn btn--primary" type="submit">{{ ucfirst($direction) }} coins</button>
        </form>
    </div>
@endsection
