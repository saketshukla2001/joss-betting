@extends('layouts.admin')

@php $editing = $user->exists; @endphp

@section('title', $editing ? 'Edit '.$user->username : 'Add player')
@section('page-title', $editing ? 'Edit player' : 'Add player')
@section('page-subtitle', $editing ? 'Update account details and access' : 'Create a client or administrator account')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>{{ $editing ? 'Edit '.$user->name : 'Add a new player' }}</h1>
            <p>{{ $editing ? 'Changes apply immediately.' : 'Set a password and optional opening balance.' }}</p>
        </div>
        <a class="btn btn--outline btn--sm" href="{{ route('admin.users.index') }}">← Back to players</a>
    </div>

    <div class="admin-card" style="max-width:820px">
        <div class="admin-card__body">
            <form method="POST" action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}">
                @csrf
                @if($editing) @method('PUT') @endif

                <div class="form-grid">
                    <div class="field">
                        <label for="name">Full name</label>
                        <input class="input" id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="field">
                        <label for="username">Username</label>
                        <input class="input" id="username" name="username" type="text" value="{{ old('username', $user->username) }}" {{ $editing ? 'disabled' : 'required' }} pattern="[A-Za-z0-9_\-]{4,20}">
                        @if($editing)<div class="hint">Usernames cannot be changed.</div>@endif
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input class="input" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="field">
                        <label for="phone">Phone</label>
                        <input class="input" id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" placeholder="Optional">
                    </div>
                    <div class="field">
                        <label for="role">Role</label>
                        <select class="select" id="role" name="role" required>
                            <option value="client" @selected(old('role', $user->role) === 'client')>Client</option>
                            <option value="admin" @selected(old('role', $user->role) === 'admin')>Administrator</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="status">Status</label>
                        <select class="select" id="status" name="status" required>
                            <option value="active" @selected(old('status', $user->status ?: 'active') === 'active')>Active</option>
                            <option value="blocked" @selected(old('status', $user->status) === 'blocked')>Blocked</option>
                        </select>
                    </div>
                    <div class="field span-2">
                        <label for="parent_id">Assigned agent</label>
                        <select class="select" id="parent_id" name="parent_id">
                            <option value="">No agent — direct admin player</option>
                            @foreach($agents as $agentOption)
                                <option value="{{ $agentOption->id }}" @selected((int) old('parent_id', $user->parent_id) === $agentOption->id)>{{ $agentOption->name }} ({{ $agentOption->username }}) · {{ number_format($agentOption->commission_rate, 2) }}%</option>
                            @endforeach
                        </select>
                        <div class="hint">Commission accrues to this agent and their upline when the player's bets settle.</div>
                    </div>
                </div>

                @if(! $editing)
                    <hr>
                    <div class="form-grid">
                        <div class="field">
                            <label for="password">Password</label>
                            <input class="input" id="password" name="password" type="password" required autocomplete="new-password">
                            <div class="hint">Minimum 8 characters with letters and numbers.</div>
                        </div>
                        <div class="field">
                            <label for="password_confirmation">Confirm password</label>
                            <input class="input" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                        </div>
                        <div class="field">
                            <label for="starting_balance">Opening balance <span class="text-muted">(optional)</span></label>
                            <div class="input-group">
                                <span class="prefix">{{ $siteSettings['currency'] }}</span>
                                <input class="input" id="starting_balance" name="starting_balance" type="number" step="0.01" min="0" value="{{ old('starting_balance', 0) }}">
                            </div>
                            <div class="hint">Credited as an admin adjustment with a ledger entry.</div>
                        </div>
                    </div>
                @endif

                <div class="form-actions">
                    <button class="btn btn--primary" type="submit">{{ $editing ? 'Save changes' : 'Create account' }}</button>
                    <a class="btn btn--ghost" href="{{ $editing ? route('admin.users.show', $user) : route('admin.users.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
