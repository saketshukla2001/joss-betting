@extends('layouts.agent')

@php
    $formLabel = strtoupper($config['form_label']);
    $targetCommissionType = old('commission_type', $user->commission_type === 'net_loss' ? 'net_loss' : 'bet_by_bet');
@endphp

@section('title', 'Edit '.$config['label'])
@section('page-title', 'Edit '.$config['label'])
@section('page-subtitle', $user->username.' · '.$user->agent_code)

@section('content')
    <div class="classic-form-header">
        <span>EDIT {{ strtoupper($config['label']) }}</span>
        <a class="classic-back-btn" href="{{ route('agent.hierarchy.index') }}">Back</a>
    </div>

    <div class="classic-form-card" style="margin-top:0">
        <form method="POST" action="{{ route('agent.hierarchy.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="classic-form-grid">
                <div>
                    <div class="field">
                        <label for="name">NAME<span class="req">*</span></label>
                        <input class="input" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="field">
                        <label for="username">USERNAME<span class="req">*</span></label>
                        <input class="input" id="username" name="username" value="{{ old('username', $user->username) }}" required>
                    </div>
                    <div class="field">
                        <label for="email">EMAIL<span class="req">*</span></label>
                        <input class="input" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="field">
                        <label for="password">PASSWORD</label>
                        <input class="input" id="password" name="password" type="text" placeholder="Leave blank to keep current password" autocomplete="off">
                    </div>
                </div>

                <div>
                    <div class="field">
                        <label for="reference">REFERENCE<span class="req">*</span></label>
                        <input class="input" id="reference" name="reference" value="{{ old('reference', $user->reference ?: ($actor->reference ?: 'Mau NP')) }}" required>
                    </div>
                    <div class="field">
                        <label for="coins">COINS<span class="req">*</span></label>
                        <input class="input" id="coins" name="coins" type="number" min="0" step="0.01" value="{{ old('coins', $user->balance) }}" required>
                        <p class="hint">Current balance {{ number_format($user->balance, 2) }}. Any increase is transferred from your wallet; a decrease is returned to your wallet.</p>
                    </div>
                    <div class="field">
                        <label for="status">STATUS<span class="req">*</span></label>
                        <select class="select" id="status" name="status" required>
                            <option value="active" @selected(old('status', $user->status) === 'active')>ACTIVE</option>
                            <option value="inactive" @selected(old('status', $user->status) === 'inactive')>INACTIVE</option>
                            <option value="blocked" @selected(old('status', $user->status) === 'blocked')>BLOCKED</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Parent</label>
                        <input class="input" value="{{ $parent?->name ?? '—' }} ({{ $parent?->agent_code ?? '—' }})" readonly>
                    </div>
                </div>
            </div>

            <div class="classic-section-title">MATCH AND SHARE INFO</div>

            <div class="classic-form-grid">
                <div>
                    <div class="field">
                        <label>PARENT MATCH SHARE<span class="req">*</span></label>
                        <input class="input" value="{{ number_format($parent?->share_percent ?? 0, 0, '', '') }}" readonly>
                    </div>
                    <div class="field">
                        <label>PARENT COMMISSION TYPE<span class="req">*</span></label>
                        <select class="select" disabled>
                            <option>{{ ($parent?->commission_type === 'net_loss') ? 'NET LOSS' : 'BET BY BET' }}</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>PARENT MATCH COMM<span class="req">*</span></label>
                        <input class="input" value="{{ number_format($parent?->commission_rate ?? 0, 0, '', '') }}" readonly>
                    </div>
                    <div class="field">
                        <label>PARENT SESSION COMM<span class="req">*</span></label>
                        <input class="input" value="{{ number_format($parent?->session_commission_percent ?? 0, 0, '', '') }}" readonly>
                    </div>
                    <div class="field">
                        <label>PARENT CASINO COMM<span class="req">*</span></label>
                        <input class="input" value="{{ number_format($parent?->casino_commission_percent ?? 0, 0, '', '') }}" readonly>
                    </div>
                    <div class="field">
                        <label>PARENT MATKA COMM<span class="req">*</span></label>
                        <input class="input" value="{{ number_format($parent?->matka_commission_percent ?? 0, 0, '', '') }}" readonly>
                    </div>
                </div>

                <div>
                    @if($config['role'] !== 'client')
                        <div class="field">
                            <label for="share_percent">{{ $formLabel }} MATCH SHARE<span class="req">*</span></label>
                            <input class="input" id="share_percent" name="share_percent" type="number" min="0" max="{{ $parent?->share_percent ?? 100 }}" step="0.01" value="{{ old('share_percent', $user->share_percent) }}" required>
                        </div>
                        <div class="field">
                            <label for="commission_type">{{ $formLabel }} COMMISSION TYPE<span class="req">*</span></label>
                            <select class="select" id="commission_type" name="commission_type" required>
                                <option value="bet_by_bet" @selected($targetCommissionType === 'bet_by_bet')>BET BY BET</option>
                                <option value="net_loss" @selected($targetCommissionType === 'net_loss')>NET LOSS</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="commission_rate">{{ $formLabel }} MATCH COMM<span class="req">*</span></label>
                            <input class="input" id="commission_rate" name="commission_rate" type="number" min="0" max="{{ $parent?->commission_rate ?? 100 }}" step="0.01" value="{{ old('commission_rate', $user->commission_rate) }}" required>
                        </div>
                        <div class="field">
                            <label for="session_commission_percent">{{ $formLabel }} SESSION COMM<span class="req">*</span></label>
                            <input class="input" id="session_commission_percent" name="session_commission_percent" type="number" min="0" max="{{ $parent?->session_commission_percent ?? 100 }}" step="0.01" value="{{ old('session_commission_percent', $user->session_commission_percent) }}" required>
                        </div>
                        <div class="field">
                            <label for="casino_commission_percent">{{ $formLabel }} CASINO COMM<span class="req">*</span></label>
                            <input class="input" id="casino_commission_percent" name="casino_commission_percent" type="number" min="0" max="{{ $parent?->casino_commission_percent ?? 100 }}" step="0.01" value="{{ old('casino_commission_percent', $user->casino_commission_percent) }}" required>
                        </div>
                        <div class="field">
                            <label for="matka_commission_percent">{{ $formLabel }} MATKA COMM<span class="req">*</span></label>
                            <input class="input" id="matka_commission_percent" name="matka_commission_percent" type="number" min="0" max="{{ $parent?->matka_commission_percent ?? 100 }}" step="0.01" value="{{ old('matka_commission_percent', $user->matka_commission_percent) }}" required>
                        </div>
                    @else
                        <div class="alert alert--info">Client Master is a player account — share and commission remain 0. Name, login, reference, coins, password and status are editable.</div>
                    @endif
                </div>
            </div>

            <div class="classic-submit-row">
                <button class="btn btn--primary" type="submit">Update {{ $config['label'] }}</button>
            </div>
        </form>
    </div>
@endsection
