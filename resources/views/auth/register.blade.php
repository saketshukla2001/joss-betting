@extends('layouts.auth')

@section('title', 'Create account')

@section('content')
    <h2>Join {{ $siteSettings['site_name'] }}</h2>
    <p>Open a free account in seconds. New players receive a {{ money($siteSettings['signup_bonus']) }} welcome bonus.</p>

    <div class="card">
        @include('partials.errors')

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-grid">
                <div class="field">
                    <label for="name">Full name</label>
                    <input class="input" id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Your full name" required autofocus autocomplete="name">
                </div>
                <div class="field">
                    <label for="username">Username</label>
                    <input class="input" id="username" name="username" type="text" value="{{ old('username') }}" placeholder="4–20 characters" required autocomplete="username" pattern="[A-Za-z0-9_\-]+">
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input class="input" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="email">
                </div>
                <div class="field">
                    <label for="phone">Phone <span class="text-muted">(optional)</span></label>
                    <input class="input" id="phone" name="phone" type="text" value="{{ old('phone') }}" placeholder="+91 98765 43210" autocomplete="tel">
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input class="input" id="password" name="password" type="password" placeholder="Min 8 chars, letters + numbers" required autocomplete="new-password">
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirm password</label>
                    <input class="input" id="password_confirmation" name="password_confirmation" type="password" placeholder="Repeat password" required autocomplete="new-password">
                </div>
            </div>

            <label class="check mt-8">
                <input type="checkbox" name="terms" value="1" {{ old('terms') ? 'checked' : '' }}>
                <span>I am 18+ and I agree to the betting rules, fair play policy and responsible gaming guidelines.</span>
            </label>

            <button class="btn btn--primary btn--block btn--lg mt-20" type="submit">Create my account</button>
        </form>

        <div class="auth-switch">Already have an account? <a href="{{ route('login') }}">Sign in</a></div>
    </div>

    <p class="text-center mt-14"><a class="text-muted" href="{{ route('home') }}" style="font-size:.78rem">← Back to sportsbook</a></p>
@endsection
