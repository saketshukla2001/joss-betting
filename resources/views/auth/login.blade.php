@extends('layouts.auth')

@section('title', 'Sign in')

@section('content')
    <h2>Welcome back</h2>
    <p>Sign in to your {{ $siteSettings['site_name'] }} account to bet, manage your wallet and track results.</p>

    <div class="card">
        @include('partials.errors')

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="field">
                <label for="username">Username or email</label>
                <input class="input" id="username" name="username" type="text" value="{{ old('username') }}" placeholder="e.g. demo" required autofocus autocomplete="username">
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="input-group">
                    <input class="input" id="password" name="password" type="password" placeholder="Your password" required autocomplete="current-password" style="border-radius:9px 0 0 9px">
                    <button class="btn btn--dark" type="button" data-toggle-password="password" style="border-radius:0 9px 9px 0">Show</button>
                </div>
            </div>

            <div class="flex-between mt-8">
                <label class="check">
                    <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                    <span>Remember me for 30 days</span>
                </label>
            </div>

            <button class="btn btn--primary btn--block btn--lg mt-20" type="submit">Sign in</button>
        </form>

        <div class="auth-switch">New to {{ $siteSettings['site_name'] }}? <a href="{{ route('register') }}">Create an account</a></div>
    </div>

    <div class="demo-creds">
        <div class="demo-creds__title">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
            Demo credentials
        </div>
        <div class="demo-row">
            <span>Client · <code>demo</code> / <code>Client@12345</code></span>
            <button type="button" data-fill-username="demo" data-fill-password="Client@12345">Use</button>
        </div>
        <div class="demo-row">
            <span>Admin · <code>admin</code> / <code>Admin@12345</code></span>
            <button type="button" data-fill-username="admin" data-fill-password="Admin@12345">Use</button>
        </div>
        <div class="demo-row">
            <span>Agent · <code>agent1</code> / <code>Agent@12345</code></span>
            <button type="button" data-fill-username="agent1" data-fill-password="Agent@12345">Use</button>
        </div>
        <div class="demo-row">
            <span>Agent · <code>agent2</code> / <code>Agent2@12345</code></span>
            <button type="button" data-fill-username="agent2" data-fill-password="Agent2@12345">Use</button>
        </div>
    </div>

    <p class="text-center mt-14"><a class="text-muted" href="{{ route('home') }}" style="font-size:.78rem">← Back to sportsbook</a></p>
@endsection
