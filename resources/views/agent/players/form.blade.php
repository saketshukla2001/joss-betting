@extends('layouts.agent')

@section('title', 'Add Player')
@section('page-title', 'Add Player')
@section('page-subtitle', 'Create a client under your agency')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>Create a player</h1>
            <p>The player logs in from the main storefront with these credentials.</p>
        </div>
        <a class="btn btn--outline btn--sm" href="{{ route('agent.players.index') }}">← My players</a>
    </div>

    <div class="admin-card" style="max-width:760px">
        <div class="admin-card__body">
            <form method="POST" action="{{ route('agent.players.store') }}">
                @csrf
                <div class="form-grid">
                    <div class="field">
                        <label for="name">Full name</label>
                        <input class="input" id="name" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="field">
                        <label for="username">Username</label>
                        <input class="input" id="username" name="username" value="{{ old('username') }}" required pattern="[A-Za-z0-9_\-]{4,20}">
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input class="input" id="email" name="email" type="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="field">
                        <label for="phone">Phone</label>
                        <input class="input" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Optional">
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <input class="input" id="password" name="password" type="password" required>
                    </div>
                    <div class="field">
                        <label for="password_confirmation">Confirm password</label>
                        <input class="input" id="password_confirmation" name="password_confirmation" type="password" required>
                    </div>
                </div>
                <div class="alert alert--info mt-14">Opening balance is ₹0. Admin can credit the player wallet from the admin panel.</div>
                <button class="btn btn--primary" type="submit">Create player</button>
            </form>
        </div>
    </div>
@endsection
