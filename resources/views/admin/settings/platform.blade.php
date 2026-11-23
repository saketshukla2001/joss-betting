@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'Platform settings')
@section('page-subtitle', 'Branding, limits and odds engine')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>Settings</h1>
            <p>Changes apply to the storefront and all client forms immediately.</p>
        </div>
    </div>

    <div class="admin-grid-2">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf

            <div class="admin-card">
                <div class="admin-card__head"><h3>Brand</h3></div>
                <div class="admin-card__body">
                    <div class="form-grid">
                        <div class="field">
                            <label for="site_name">Site name</label>
                            <input class="input" id="site_name" name="site_name" value="{{ old('site_name', $siteSettings['site_name']) }}" required>
                        </div>
                        <div class="field">
                            <label for="currency">Currency symbol</label>
                            <input class="input" id="currency" name="currency" value="{{ old('currency', $siteSettings['currency']) }}" required>
                        </div>
                        <div class="field span-2">
                            <label for="tagline">Tagline</label>
                            <input class="input" id="tagline" name="tagline" value="{{ old('tagline', $siteSettings['tagline']) }}">
                        </div>
                        <div class="field span-2">
                            <label for="support_email">Support email</label>
                            <input class="input" id="support_email" name="support_email" type="email" value="{{ old('support_email', $siteSettings['support_email']) }}" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-card mt-20">
                <div class="admin-card__head"><h3>Betting limits</h3></div>
                <div class="admin-card__body">
                    <div class="form-grid">
                        <div class="field">
                            <label for="min_stake">Minimum stake</label>
                            <input class="input" id="min_stake" name="min_stake" type="number" step="0.01" min="1" value="{{ old('min_stake', $siteSettings['min_stake']) }}" required>
                        </div>
                        <div class="field">
                            <label for="max_stake">Maximum stake</label>
                            <input class="input" id="max_stake" name="max_stake" type="number" step="0.01" min="2" value="{{ old('max_stake', $siteSettings['max_stake']) }}" required>
                        </div>
                        <div class="field">
                            <label for="signup_bonus">Signup bonus</label>
                            <input class="input" id="signup_bonus" name="signup_bonus" type="number" step="0.01" min="0" value="{{ old('signup_bonus', $siteSettings['signup_bonus']) }}" required>
                            <div class="hint">Credited once when a new player registers.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-card mt-20">
                <div class="admin-card__head"><h3>Cashier</h3></div>
                <div class="admin-card__body">
                    <div class="form-grid">
                        <div class="field">
                            <label for="min_deposit">Minimum deposit</label>
                            <input class="input" id="min_deposit" name="min_deposit" type="number" step="0.01" min="1" value="{{ old('min_deposit', $siteSettings['min_deposit']) }}" required>
                        </div>
                        <div class="field">
                            <label for="min_withdrawal">Minimum withdrawal</label>
                            <input class="input" id="min_withdrawal" name="min_withdrawal" type="number" step="0.01" min="1" value="{{ old('min_withdrawal', $siteSettings['min_withdrawal']) }}" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-card mt-20" id="rules">
                <div class="admin-card__head"><h3>Announcements, rules &amp; maintenance</h3></div>
                <div class="admin-card__body">
                    <div class="field">
                        <label for="announcement">Storefront announcement</label>
                        <textarea class="textarea" id="announcement" name="announcement" placeholder="Shown in the ticker under the header">{{ old('announcement', $siteSettings['announcement']) }}</textarea>
                    </div>
                    <div class="field">
                        <label for="maintenance_message">Maintenance message</label>
                        <input class="input" id="maintenance_message" name="maintenance_message" value="{{ old('maintenance_message', $siteSettings['maintenance_message']) }}" placeholder="Optional message for planned downtime">
                    </div>
                    <div class="field">
                        <label for="rules">Platform rules</label>
                        <textarea class="textarea" id="rules" name="rules" placeholder="Rules shown in the admin dashboard">{{ old('rules', $siteSettings['rules'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="admin-card mt-20">
                <div class="admin-card__head"><h3>Live visibility</h3></div>
                <div class="admin-card__body">
                    <div class="form-grid">
                        <div class="field">
                            <label for="live_only_sport">Live section sports filter</label>
                            <select class="select" id="live_only_sport" name="live_only_sport">
                                <option value="cricket" @selected(($siteSettings['live_only_sport'] ?? 'cricket') === 'cricket')>Cricket only</option>
                                <option value="all" @selected(($siteSettings['live_only_sport'] ?? 'cricket') === 'all')>All sports</option>
                            </select>
                            <p class="hint">Only affects Live surfaces (home live strip, Live tab, client dashboard). All/Upcoming pages stay unchanged.</p>
                        </div>
                        <div class="field">
                            <label for="live_stale_hours">Hide database live matches older than (hours)</label>
                            <input class="input" id="live_stale_hours" name="live_stale_hours" type="number" min="0" max="168"
                                   value="{{ old('live_stale_hours', $siteSettings['live_stale_hours'] ?? 6) }}">
                            <p class="hint">0 disables the age filter. Records are only hidden, never deleted.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-card mt-20">
                <div class="admin-card__head"><h3>Odds engine</h3></div>
                <div class="admin-card__body">
                    <div class="toggle-row">
                        <div>
                            <strong style="font-size:.84rem">Automatic odds movement</strong>
                            <p class="text-muted" style="font-size:.75rem;margin:2px 0 0">When on, the odds tick command nudges open prices and clients see them update every 10 seconds.</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="odds_auto_move" value="1" @checked($siteSettings['odds_auto_move'] === '1')>
                            <span></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button class="btn btn--primary" type="submit">Save settings</button>
            </div>
        </form>

        <aside>
            <div class="admin-card">
                <div class="admin-card__head"><h3>My share &amp; commission</h3></div>
                <div class="admin-card__body">
                    <form method="POST" action="{{ route('admin.settings.shares') }}">
                        @csrf
                        <div class="form-grid">
                            <div class="field">
                                <label for="my_share">My share (%)</label>
                                <input class="input" id="my_share" name="share_percent" type="number" step="0.01" min="0" max="100" value="{{ old('share_percent', auth()->user()->share_percent) }}" required>
                            </div>
                            <div class="field">
                                <label for="company_share">Company share (%)</label>
                                <input class="input" id="company_share" name="company_share_percent" type="number" step="0.01" min="0" max="100" value="{{ old('company_share_percent', auth()->user()->company_share_percent) }}" required>
                            </div>
                            <div class="field">
                                <label for="match_comm">Match commission (%)</label>
                                <input class="input" id="match_comm" name="commission_rate" type="number" step="0.01" min="0" max="100" value="{{ old('commission_rate', auth()->user()->commission_rate) }}" required>
                            </div>
                            <div class="field">
                                <label for="session_comm">Session commission (%)</label>
                                <input class="input" id="session_comm" name="session_commission_percent" type="number" step="0.01" min="0" max="100" value="{{ old('session_commission_percent', auth()->user()->session_commission_percent) }}" required>
                            </div>
                        </div>
                        <button class="btn btn--primary btn--sm" type="submit">Save shares</button>
                    </form>
                </div>
            </div>

            <div class="admin-card mt-20">
                <div class="admin-card__head"><h3>Platform snapshot</h3></div>
                <div class="admin-card__body">
                    <ul class="info-list">
                        <li><span>Players</span><strong>{{ number_format($stats['users']) }}</strong></li>
                        <li><span>Matches</span><strong>{{ number_format($stats['matches']) }}</strong></li>
                        <li><span>Bets</span><strong>{{ number_format($stats['bets']) }}</strong></li>
                        <li><span>Deposit requests</span><strong>{{ number_format($stats['deposits']) }}</strong></li>
                        <li><span>Withdrawal requests</span><strong>{{ number_format($stats['withdrawals']) }}</strong></li>
                    </ul>
                </div>
            </div>

            <div class="admin-card mt-20">
                <div class="admin-card__head"><h3>Demo accounts</h3></div>
                <div class="admin-card__body">
                    <p class="text-muted" style="font-size:.78rem">Share these with reviewers.</p>
                    <ul class="info-list">
                        <li><span>Admin</span><strong>admin / Admin@12345</strong></li>
                        <li><span>Client</span><strong>demo / Client@12345</strong></li>
                        <li><span>Agent</span><strong>agent1 / Agent@12345</strong></li>
                        <li><span>Agent</span><strong>agent2 / Agent2@12345</strong></li>
                        <li><span>Client</span><strong>rahul / Rahul@12345</strong></li>
                        <li><span>Client</span><strong>priya / Priya@12345</strong></li>
                    </ul>
                </div>
            </div>

            <div class="admin-card mt-20">
                <div class="admin-card__head"><h3>Odds ticker</h3></div>
                <div class="admin-card__body">
                    <p class="text-muted" style="font-size:.78rem">Run this command to move open prices:</p>
                    <code style="display:block;background:#0b1525;border:1px solid var(--line);padding:9px 11px;border-radius:8px;font-size:.72rem;color:var(--lime)">php artisan odds:tick --loop</code>
                </div>
            </div>
        </aside>
    </div>
@endsection
