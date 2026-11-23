@extends('layouts.admin')

@php
    $editing = $user->exists;
    $adminUser = auth()->user();
    $myCommissionType = $adminUser->commission_type === 'net_loss' ? 'NET LOSS' : 'BET BY BET';
    $formLabel = strtoupper($config['form_label']);
@endphp

@section('title', $editing ? 'Edit '.$config['label'] : $config['create_title'])

@section('content')
    <div class="classic-form-header">
        <span>{{ $editing ? 'EDIT '.strtoupper($config['label']) : $config['create_title'] }}</span>
        <a class="classic-back-btn" href="{{ route('admin.hierarchy.index', $level) }}">Back</a>
    </div>

    <div class="classic-form-card" style="margin-top:0">
        <form method="POST" action="{{ $editing ? route('admin.hierarchy.update', [$level, $user]) : route('admin.hierarchy.store', $level) }}" data-hierarchy-form>
            @csrf
            @if($editing) @method('PUT') @endif

            <div class="classic-form-grid">
                <div>
                    <div class="field">
                        <label for="name">NAME<span class="req">*</span></label>
                        <input class="input" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="field">
                        <label for="my_coins">MY COINS<span class="req">*</span></label>
                        <input class="input" id="my_coins" value="{{ number_format($adminUser->balance, 0, '', '') }}" readonly>
                    </div>

                    <div class="field">
                        <label for="password">PASSWORD<span class="req">*</span></label>
                        @if($editing)
                            <input class="input" id="password" name="password" type="text" placeholder="Leave blank to keep current password" autocomplete="off">
                        @else
                            <input class="input" id="password" name="password" type="text" value="{{ old('password', $suggestedPassword) }}" required autocomplete="off">
                        @endif
                    </div>
                </div>

                <div>
                    <div class="field">
                        <label for="reference">REFERENCE<span class="req">*</span></label>
                        <input class="input" id="reference" name="reference" value="{{ old('reference', $reference) }}" required>
                    </div>

                    <div class="field">
                        <label for="coins">COINS<span class="req">*</span></label>
                        <input class="input" id="coins" name="coins" type="number" min="0" step="1"
                               value="{{ old('coins', $editing ? $user->balance : '') }}"
                               @if(! $editing) required @endif
                               @if($editing) readonly @endif>
                    </div>
                </div>
            </div>

            <div class="classic-section-title">MATCH AND SHARE INFO</div>

            <div class="classic-form-grid">
                <div>
                    <div class="field">
                        <label>MY MATCH SHARE<span class="req">*</span></label>
                        <input class="input" value="{{ number_format($adminUser->share_percent, 0, '', '') }}" readonly>
                    </div>

                    <div class="field">
                        <label>MY COMMISSION TYPE<span class="req">*</span></label>
                        <select class="select" disabled>
                            <option>{{ $myCommissionType }}</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>MY MATCH COMM<span class="req">*</span></label>
                        <input class="input" value="{{ number_format($adminUser->commission_rate, 0, '', '') }}" readonly>
                    </div>

                    <div class="field">
                        <label>MY SESSION COMM<span class="req">*</span></label>
                        <input class="input" value="{{ number_format($adminUser->session_commission_percent, 0, '', '') }}" readonly>
                    </div>

                    <div class="field">
                        <label>MY CASINO COMM<span class="req">*</span></label>
                        <input class="input" value="{{ number_format($adminUser->casino_commission_percent, 0, '', '') }}" readonly>
                    </div>

                    <div class="field">
                        <label>MY MATKA COMM<span class="req">*</span></label>
                        <input class="input" value="{{ number_format($adminUser->matka_commission_percent, 0, '', '') }}" readonly>
                    </div>
                </div>

                <div>
                    <div class="field">
                        <label for="share_percent">{{ $formLabel }} MATCH SHARE<span class="req">*</span></label>
                        <input class="input" id="share_percent" name="share_percent" type="number" min="0" max="100" step="0.01"
                               value="{{ old('share_percent', $editing ? $user->share_percent : '') }}" required>
                    </div>

                    <div class="field">
                        <label for="commission_type">{{ $formLabel }} COMMISSION TYPE<span class="req">*</span></label>
                        <select class="select" id="commission_type" name="commission_type" required>
                            <option value="">Select commission type</option>
                            <option value="bet_by_bet" @selected(old('commission_type', $editing ? ($user->commission_type === 'net_loss' ? 'net_loss' : 'bet_by_bet') : '') === 'bet_by_bet')>BET BY BET</option>
                            <option value="net_loss" @selected(old('commission_type', $editing ? $user->commission_type : '') === 'net_loss')>NET LOSS</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="commission_rate">{{ $formLabel }} MATCH COMM<span class="req">*</span></label>
                        <input class="input" id="commission_rate" name="commission_rate" type="number" min="0" max="100" step="0.01"
                               value="{{ old('commission_rate', $editing ? $user->commission_rate : '') }}" required>
                    </div>

                    <div class="field">
                        <label for="session_commission_percent">{{ $formLabel }} SESSION COMM<span class="req">*</span></label>
                        <input class="input" id="session_commission_percent" name="session_commission_percent" type="number" min="0" max="100" step="0.01"
                               value="{{ old('session_commission_percent', $editing ? $user->session_commission_percent : '') }}" required>
                    </div>

                    <div class="field">
                        <label for="casino_commission_percent">{{ $formLabel }} CASINO COMM<span class="req">*</span></label>
                        <input class="input" id="casino_commission_percent" name="casino_commission_percent" type="number" min="0" max="100" step="0.01"
                               value="{{ old('casino_commission_percent', $editing ? $user->casino_commission_percent : '') }}" required>
                    </div>

                    <div class="field">
                        <label for="matka_commission_percent">{{ $formLabel }} MATKA COMM<span class="req">*</span></label>
                        <input class="input" id="matka_commission_percent" name="matka_commission_percent" type="number" min="0" max="100" step="0.01"
                               value="{{ old('matka_commission_percent', $editing ? $user->matka_commission_percent : '') }}" required>
                    </div>
                </div>
            </div>

            <div class="classic-submit-row">
                <button class="btn btn--primary" type="submit" data-hierarchy-submit {{ $editing ? '' : 'disabled' }}>Submit</button>
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
