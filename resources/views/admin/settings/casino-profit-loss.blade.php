@extends('layouts.admin')

@section('title', 'Casino Profit Loss')

@section('content')
    <div class="classic-form-header">
        <span>CASINO PROFIT LOSS</span>
        <a class="classic-back-btn" href="{{ route('admin.settings.index') }}">Back</a>
    </div>

    <div class="admin-stat-grid" style="margin-top:0">
        <div class="stat"><div class="stat__label">Casino stake</div><div class="stat__value">0</div></div>
        <div class="stat"><div class="stat__label">Casino payout</div><div class="stat__value">0</div></div>
        <div class="stat"><div class="stat__label">Casino profit</div><div class="stat__value">0</div></div>
        <div class="stat"><div class="stat__label">Casino commission</div><div class="stat__value">0</div></div>
    </div>

    <div class="classic-card">
        <div class="classic-card__head"><span>CASINO REPORT</span></div>
        <div class="empty" style="padding:40px 20px">
            <strong>Casino module is not enabled</strong>
            <p>This build is sportsbook-only, so there is no casino betting data to report.</p>
        </div>
    </div>
@endsection
