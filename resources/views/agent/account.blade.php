@extends('layouts.agent')

@section('title', 'My Profile')
@section('page-title', 'My Profile')
@section('page-subtitle', 'Agent account and password')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>Agent profile</h1>
            <p>Manage your contact details and login password.</p>
        </div>
    </div>

    <div class="admin-grid-2">
        <div>
            <div class="admin-card">
                <div class="admin-card__head"><h3>Profile details</h3><span class="badge badge--lime">{{ $agent->agent_code }}</span></div>
                <div class="admin-card__body">
                    <form method="POST" action="{{ route('agent.account.profile') }}">
                        @csrf
                        @method('PUT')
                        <div class="form-grid">
                            <div class="field">
                                <label for="name">Full name</label>
                                <input class="input" id="name" name="name" value="{{ old('name', $agent->name) }}" required>
                            </div>
                            <div class="field">
                                <label for="username">Username</label>
                                <input class="input" id="username" value="{{ $agent->username }}" disabled>
                            </div>
                            <div class="field">
                                <label for="email">Email</label>
                                <input class="input" id="email" name="email" type="email" value="{{ old('email', $agent->email) }}" required>
                            </div>
                            <div class="field">
                                <label for="phone">Phone</label>
                                <input class="input" id="phone" name="phone" value="{{ old('phone', $agent->phone) }}">
                            </div>
                        </div>
                        <button class="btn btn--primary" type="submit">Save profile</button>
                    </form>
                </div>
            </div>

            <div class="admin-card mt-20">
                <div class="admin-card__head"><h3>Change password</h3></div>
                <div class="admin-card__body">
                    <form method="POST" action="{{ route('agent.account.password') }}">
                        @csrf
                        @method('PUT')
                        <div class="field">
                            <label for="current_password">Current password</label>
                            <input class="input" id="current_password" name="current_password" type="password" required>
                        </div>
                        <div class="form-grid">
                            <div class="field">
                                <label for="password">New password</label>
                                <input class="input" id="password" name="password" type="password" required>
                            </div>
                            <div class="field">
                                <label for="password_confirmation">Confirm</label>
                                <input class="input" id="password_confirmation" name="password_confirmation" type="password" required>
                            </div>
                        </div>
                        <button class="btn btn--primary" type="submit">Update password</button>
                    </form>
                </div>
            </div>
        </div>

        <div>
            <div class="admin-card">
                <div class="admin-card__head"><h3>Agency snapshot</h3></div>
                <div class="admin-card__body">
                    <ul class="info-list">
                        <li><span>Downline players</span><strong>{{ number_format($stats['players']) }}</strong></li>
                        <li><span>Direct players</span><strong>{{ number_format($stats['direct_players']) }}</strong></li>
                        <li><span>Turnover</span><strong>{{ money($stats['turnover']) }}</strong></li>
                        <li><span>Pending commission</span><strong class="text-amber">{{ money($stats['pending_commission']) }}</strong></li>
                        <li><span>Paid commission</span><strong class="text-green">{{ money($stats['paid_commission']) }}</strong></li>
                        <li><span>Wallet balance</span><strong>{{ money($agent->balance) }}</strong></li>
                        <li><span>Commission rate</span><strong>{{ number_format($agent->commission_rate, 2) }}%</strong></li>
                        <li><span>Basis</span><strong>{{ $agent->commission_type === 'net_loss' ? 'Net loss' : 'Turnover' }}</strong></li>
                    </ul>
                    <a class="btn btn--primary btn--block btn--sm mt-14" href="{{ route('agent.wallet.index') }}">Open agent wallet</a>
                </div>
            </div>

            <div class="admin-card mt-20">
                <div class="admin-card__body">
                    <h3 style="font-size:.95rem">Need help?</h3>
                    <p class="text-muted" style="font-size:.8rem">Contact the platform admin for commission payouts or player issues.</p>
                    <a class="btn btn--outline btn--block btn--sm" href="mailto:{{ $siteSettings['support_email'] }}">{{ $siteSettings['support_email'] }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection
