@extends('layouts.agent')

@php
    $myTypeValue = old('my_commission_type', $actor->commission_type === 'net_loss' ? 'net_loss' : 'bet_by_bet');
    $formLabel = strtoupper($config['form_label']);
    $targetCommissionType = old('commission_type', $actor->commission_type === 'net_loss' ? 'net_loss' : 'bet_by_bet');
@endphp

@section('title', $config['create_title'])
@section('page-title', 'Create '.$config['label'])
@section('page-subtitle', 'Parent: '.$actor->role_label.' · '.$actor->agent_code)

@section('content')
    <div class="classic-form-header">
        <span>{{ $config['create_title'] }}</span>
        <a class="classic-back-btn" href="{{ route('agent.hierarchy.index') }}">Back</a>
    </div>

    <div class="classic-form-card" style="margin-top:0">
        <form method="POST" action="{{ route('agent.hierarchy.store') }}" data-hierarchy-form>
            @csrf

            <div class="classic-form-grid">
                <div>
                    <div class="field">
                        <label for="name">NAME<span class="req">*</span></label>
                        <input class="input" id="name" name="name" value="{{ old('name') }}" required>
                    </div>

                    <div class="field">
                        <label for="my_coins">MY COINS<span class="req">*</span></label>
                        <input class="input" id="my_coins" value="{{ number_format($actor->balance, 0, '', '') }}" readonly>
                    </div>

                    <div class="field">
                        <label for="password">PASSWORD<span class="req">*</span></label>
                        <input class="input" id="password" name="password" type="text" value="{{ old('password', $suggestedPassword) }}" required autocomplete="off">
                    </div>
                </div>

                <div>
                    <div class="field">
                        <label for="reference">REFERENCE<span class="req">*</span></label>
                        <input class="input" id="reference" name="reference" value="{{ old('reference', $actor->reference ?: 'Mau NP') }}" required>
                    </div>

                    <div class="field">
                        <label for="coins">COINS<span class="req">*</span></label>
                        <input class="input" id="coins" name="coins" type="number" min="0" max="{{ $actor->balance }}" step="1" value="{{ old('coins', 0) }}" required>
                        <p class="hint">Maximum {{ number_format($actor->balance, 0) }} coins. Amount is transferred from your wallet.</p>
                    </div>
                </div>
            </div>

            <div class="classic-section-title">MATCH AND SHARE INFO</div>

            <div class="classic-form-grid">
                <div>
                    <div class="field">
                        <label for="my_share_percent">MY MATCH SHARE<span class="req">*</span></label>
                        <input class="input" id="my_share_percent" name="my_share_percent" type="number" min="0" max="100" step="0.01" value="{{ old('my_share_percent', $actor->share_percent) }}" required>
                    </div>
                    <div class="field">
                        <label for="my_commission_type">MY COMMISSION TYPE<span class="req">*</span></label>
                        <select class="select" id="my_commission_type" name="my_commission_type" required>
                            <option value="bet_by_bet" @selected($myTypeValue === 'bet_by_bet')>BET BY BET</option>
                            <option value="net_loss" @selected($myTypeValue === 'net_loss')>NET LOSS</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="my_commission_rate">MY MATCH COMM<span class="req">*</span></label>
                        <input class="input" id="my_commission_rate" name="my_commission_rate" type="number" min="0" max="100" step="0.01" value="{{ old('my_commission_rate', $actor->commission_rate) }}" required>
                    </div>
                    <div class="field">
                        <label for="my_session_commission_percent">MY SESSION COMM<span class="req">*</span></label>
                        <input class="input" id="my_session_commission_percent" name="my_session_commission_percent" type="number" min="0" max="100" step="0.01" value="{{ old('my_session_commission_percent', $actor->session_commission_percent) }}" required>
                    </div>
                    <div class="field">
                        <label for="my_casino_commission_percent">MY CASINO COMM<span class="req">*</span></label>
                        <input class="input" id="my_casino_commission_percent" name="my_casino_commission_percent" type="number" min="0" max="100" step="0.01" value="{{ old('my_casino_commission_percent', $actor->casino_commission_percent) }}" required>
                    </div>
                    <div class="field">
                        <label for="my_matka_commission_percent">MY MATKA COMM<span class="req">*</span></label>
                        <input class="input" id="my_matka_commission_percent" name="my_matka_commission_percent" type="number" min="0" max="100" step="0.01" value="{{ old('my_matka_commission_percent', $actor->matka_commission_percent) }}" required>
                    </div>
                    <p class="hint">These are your own account terms. Saving this form updates your account and becomes the maximum allowed for the child.</p>
                </div>

                <div>
                    @if($config['role'] !== 'client')
                        <div class="field">
                            <label for="share_percent">{{ $formLabel }} MATCH SHARE<span class="req">*</span></label>
                            <input class="input" id="share_percent" name="share_percent" type="number" min="0" max="{{ $actor->share_percent }}" step="0.01" value="{{ old('share_percent', $actor->share_percent) }}" required>
                        </div>
                        <div class="field">
                            <label for="commission_type">{{ $formLabel }} COMMISSION TYPE<span class="req">*</span></label>
                            <select class="select" id="commission_type" name="commission_type" required>
                                <option value="">Select commission type</option>
                                <option value="bet_by_bet" @selected($targetCommissionType === 'bet_by_bet')>BET BY BET</option>
                                <option value="net_loss" @selected($targetCommissionType === 'net_loss')>NET LOSS</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="commission_rate">{{ $formLabel }} MATCH COMM<span class="req">*</span></label>
                            <input class="input" id="commission_rate" name="commission_rate" type="number" min="0" max="{{ $actor->commission_rate }}" step="0.01" value="{{ old('commission_rate', $actor->commission_rate) }}" required>
                        </div>
                        <div class="field">
                            <label for="session_commission_percent">{{ $formLabel }} SESSION COMM<span class="req">*</span></label>
                            <input class="input" id="session_commission_percent" name="session_commission_percent" type="number" min="0" max="{{ $actor->session_commission_percent }}" step="0.01" value="{{ old('session_commission_percent', $actor->session_commission_percent) }}" required>
                        </div>
                        <div class="field">
                            <label for="casino_commission_percent">{{ $formLabel }} CASINO COMM<span class="req">*</span></label>
                            <input class="input" id="casino_commission_percent" name="casino_commission_percent" type="number" min="0" max="{{ $actor->casino_commission_percent }}" step="0.01" value="{{ old('casino_commission_percent', $actor->casino_commission_percent) }}" required>
                        </div>
                        <div class="field">
                            <label for="matka_commission_percent">{{ $formLabel }} MATKA COMM<span class="req">*</span></label>
                            <input class="input" id="matka_commission_percent" name="matka_commission_percent" type="number" min="0" max="{{ $actor->matka_commission_percent }}" step="0.01" value="{{ old('matka_commission_percent', $actor->matka_commission_percent) }}" required>
                        </div>
                    @else
                        <div class="alert alert--info">Client Master is a player account — share and commission are set to 0.</div>
                    @endif
                </div>
            </div>

            <div class="classic-submit-row">
                <button class="btn btn--primary" type="submit" data-hierarchy-submit>Submit</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    document.querySelectorAll('[data-hierarchy-form]').forEach(function (form) {
        var submit = form.querySelector('[data-hierarchy-submit]');
        if (!submit) return;
        var required = Array.prototype.slice.call(form.querySelectorAll('[required]'));
        var sync = function () {
            submit.disabled = required.some(function (field) { return !String(field.value || '').trim(); });
        };
        required.forEach(function (field) {
            field.addEventListener('input', sync);
            field.addEventListener('change', sync);
        });
        sync();
    });
})();
</script>
@endpush
