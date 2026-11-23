@extends('layouts.app')

@section('title', 'Account settings')

@section('content')
    @php $user = auth()->user(); @endphp
    <div class="container">
        <div class="page-head">
            <div>
                <span class="eyebrow">Account</span>
                <h1 class="mt-8">Settings</h1>
                <p>Manage your profile, password and account preferences.</p>
            </div>
            <a class="btn btn--outline" href="{{ route('dashboard') }}">Dashboard</a>
        </div>

        @include('partials.errors')

        <div class="dash-grid">
            <div>
                <div class="card mb-20">
                    <div class="card__head">
                        <div><h3>Profile details</h3><p>Used for your account and support requests</p></div>
                        <span class="badge badge--success">Active</span>
                    </div>
                    <div class="card__body">
                        <form method="POST" action="{{ route('account.profile') }}">
                            @csrf
                            @method('PUT')
                            <div class="form-grid">
                                <div class="field">
                                    <label for="name">Full name</label>
                                    <input class="input" id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required>
                                </div>
                                <div class="field">
                                    <label for="username">Username</label>
                                    <input class="input" id="username" type="text" value="{{ $user->username }}" disabled>
                                    <div class="hint">Usernames are permanent.</div>
                                </div>
                                <div class="field">
                                    <label for="email">Email</label>
                                    <input class="input" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
                                </div>
                                <div class="field">
                                    <label for="phone">Phone</label>
                                    <input class="input" id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" placeholder="+91 98765 43210">
                                </div>
                            </div>
                            <button class="btn btn--primary" type="submit">Save profile</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card__head">
                        <div><h3>Change password</h3><p>Use at least 8 characters with letters and numbers</p></div>
                    </div>
                    <div class="card__body">
                        <form method="POST" action="{{ route('account.password') }}">
                            @csrf
                            @method('PUT')
                            <div class="field">
                                <label for="current_password">Current password</label>
                                <input class="input" id="current_password" name="current_password" type="password" required autocomplete="current-password">
                            </div>
                            <div class="form-grid">
                                <div class="field">
                                    <label for="password">New password</label>
                                    <input class="input" id="password" name="password" type="password" required autocomplete="new-password">
                                </div>
                                <div class="field">
                                    <label for="password_confirmation">Confirm new password</label>
                                    <input class="input" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                                </div>
                            </div>
                            <button class="btn btn--primary" type="submit">Update password</button>
                        </form>
                    </div>
                </div>
            </div>

            <aside>
                <div class="card side-card">
                    <div class="card__body text-center">
                        <div class="avatar avatar--lg" style="margin:0 auto 10px">{{ $user->initials }}</div>
                        <h3 style="margin-bottom:2px">{{ $user->name }}</h3>
                        <p class="text-muted" style="font-size:.8rem">&#64;{{ $user->username }}</p>
                        <span class="badge badge--lime">{{ ucfirst($user->role) }}</span>
                    </div>
                    <div class="card__body" style="border-top:1px solid var(--line)">
                        <ul class="info-list">
                            <li><span>Email</span><strong>{{ $user->email }}</strong></li>
                            <li><span>Phone</span><strong>{{ $user->phone ?: '—' }}</strong></li>
                            <li><span>Member since</span><strong>{{ $user->created_at->format('d M Y') }}</strong></li>
                            <li><span>Last login</span><strong>{{ $user->last_login_at?->format('d M Y, h:i A') ?? '—' }}</strong></li>
                            <li><span>Status</span><strong class="text-green">{{ ucfirst($user->status) }}</strong></li>
                        </ul>
                    </div>
                </div>

                <div class="card side-card">
                    <div class="card__head"><h3>Lifetime stats</h3></div>
                    <div class="card__body">
                        <div class="flex-between"><span class="text-muted" style="font-size:.78rem">Tickets</span><strong>{{ number_format($stats['bets']) }}</strong></div>
                        <div class="flex-between mt-8"><span class="text-muted" style="font-size:.78rem">Winning tickets</span><strong class="text-green">{{ number_format($stats['won']) }}</strong></div>
                        <div class="flex-between mt-8"><span class="text-muted" style="font-size:.78rem">Total staked</span><strong>{{ money($stats['staked']) }}</strong></div>
                        <div class="flex-between mt-8"><span class="text-muted" style="font-size:.78rem">Total returns</span><strong class="text-lime">{{ money($stats['returns']) }}</strong></div>
                    </div>
                </div>

                <div class="card side-card">
                    <div class="card__head"><h3>Need help?</h3></div>
                    <div class="card__body">
                        <p class="text-muted" style="font-size:.8rem">Contact support for deposits, withdrawals or account issues.</p>
                        <a class="btn btn--outline btn--block btn--sm" href="mailto:{{ $siteSettings['support_email'] }}">{{ $siteSettings['support_email'] }}</a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endsection
