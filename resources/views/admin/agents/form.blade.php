@extends('layouts.admin')

@php $editing = $agent->exists; @endphp

@section('title', $editing ? 'Edit agent' : 'Add agent')
@section('page-title', $editing ? 'Edit agent' : 'Add agent')
@section('page-subtitle', $editing ? $agent->username.' · '.$agent->agent_code : 'Create an agency login with commission')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>{{ $editing ? 'Edit '.$agent->name : 'Add an agent' }}</h1>
            <p>Commission is calculated when downline bets settle.</p>
        </div>
        <a class="btn btn--outline btn--sm" href="{{ route('admin.agents.index') }}">← Agents</a>
    </div>

    <div class="admin-card" style="max-width:900px">
        <div class="admin-card__body">
            <form method="POST" action="{{ $editing ? route('admin.agents.update', $agent) : route('admin.agents.store') }}">
                @csrf
                @if($editing) @method('PUT') @endif

                <div class="form-grid">
                    <div class="field">
                        <label for="name">Full name</label>
                        <input class="input" id="name" name="name" value="{{ old('name', $agent->name) }}" required>
                    </div>
                    <div class="field">
                        <label for="username">Username</label>
                        <input class="input" id="username" name="username" value="{{ old('username', $agent->username) }}" {{ $editing ? 'disabled' : 'required' }} pattern="[A-Za-z0-9_\-]{4,20}">
                        @if($editing)<input type="hidden" name="username" value="{{ $agent->username }}">@endif
                        @if($editing)<div class="hint">Username cannot be changed.</div>@endif
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input class="input" id="email" name="email" type="email" value="{{ old('email', $agent->email) }}" required>
                    </div>
                    <div class="field">
                        <label for="phone">Phone</label>
                        <input class="input" id="phone" name="phone" value="{{ old('phone', $agent->phone) }}" placeholder="Optional">
                    </div>
                    <div class="field">
                        <label for="parent_id">Parent agent</label>
                        <select class="select" id="parent_id" name="parent_id">
                            <option value="">Top-level agent</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" @selected((int) old('parent_id', $agent->parent_id) === $parent->id)>{{ $parent->name }} ({{ $parent->username }})</option>
                            @endforeach
                        </select>
                        <div class="hint">Upline agents also earn their own configured rate.</div>
                    </div>
                    <div class="field">
                        <label for="status">Status</label>
                        <select class="select" id="status" name="status" required>
                            <option value="active" @selected(old('status', $agent->status ?: 'active') === 'active')>Active</option>
                            <option value="blocked" @selected(old('status', $agent->status) === 'blocked')>Blocked</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="commission_rate">Commission rate (%)</label>
                        <div class="input-group">
                            <input class="input" id="commission_rate" name="commission_rate" type="number" step="0.01" min="0" max="50" value="{{ old('commission_rate', $agent->commission_rate ?? 5) }}" required>
                            <span class="prefix" style="border-left:1px solid var(--line-strong);border-right:0;border-radius:0 9px 9px 0">%</span>
                        </div>
                    </div>
                    <div class="field">
                        <label for="commission_type">Commission basis</label>
                        <select class="select" id="commission_type" name="commission_type" required>
                            <option value="turnover" @selected(old('commission_type', $agent->commission_type ?: 'turnover') === 'turnover')>Turnover — % of every settled stake</option>
                            <option value="net_loss" @selected(old('commission_type', $agent->commission_type) === 'net_loss')>Net loss — % of losing stakes only</option>
                        </select>
                    </div>
                </div>

                <hr>

                <div class="form-grid">
                    <div class="field">
                        <label for="password">Password {{ $editing ? '(leave blank to keep)' : '' }}</label>
                        <input class="input" id="password" name="password" type="password" autocomplete="new-password" {{ $editing ? '' : 'required' }}>
                    </div>
                    <div class="field">
                        <label for="password_confirmation">Confirm password</label>
                        <input class="input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" {{ $editing ? '' : 'required' }}>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn--primary" type="submit">{{ $editing ? 'Save agent' : 'Create agent' }}</button>
                    <a class="btn btn--ghost" href="{{ $editing ? route('admin.agents.show', $agent) : route('admin.agents.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
