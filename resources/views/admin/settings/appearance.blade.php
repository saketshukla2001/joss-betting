@extends('layouts.admin')

@section('title', 'Panel Color')

@section('content')
    <div class="classic-form-header">
        <span>PANEL COLOR</span>
        <a class="classic-back-btn" href="{{ route('admin.settings.index') }}">Back</a>
    </div>

    <div class="appearance-panel">
        <div class="appearance-copy">
            <h2>Panel theme</h2>
            <p>Change the colours used by the Main Super Admin panel and all master panels. Storefront colours are not changed.</p>
        </div>

        <form method="POST" action="{{ route('admin.settings.appearance.update') }}" id="appearance-form">
            @csrf
            <div class="appearance-grid">
                @foreach([
                    'panel_primary' => ['Primary', 'Topbar, sidebar, table headers, cards and primary buttons'],
                    'panel_primary_dark' => ['Dark / Sidebar', 'Sidebar depth and active menu background'],
                    'panel_primary_mid' => ['Hover', 'Hover state for green buttons and links'],
                    'panel_accent' => ['Accent', 'Active menu text, active borders and highlights'],
                    'panel_soft' => ['Soft background', 'Soft hover background used by outline buttons and active rows'],
                ] as $key => [$label, $help])
                    <div class="appearance-field">
                        <label for="{{ $key }}">{{ strtoupper($label) }}</label>
                        <div class="color-row">
                            <input type="color" id="{{ $key }}" name="{{ $key }}" value="{{ old($key, $siteSettings[$key] ?? '#1e6b1e') }}" data-color-picker>
                            <input class="input color-hex" type="text" value="{{ old($key, $siteSettings[$key] ?? '#1e6b1e') }}" maxlength="7" pattern="^#[0-9a-fA-F]{6}$" data-color-text data-for="{{ $key }}" required>
                        </div>
                        <small>{{ $help }}</small>
                    </div>
                @endforeach
            </div>

            <div class="appearance-preview" id="appearance-preview">
                <div class="preview-side">
                    <span class="preview-brand">B</span>
                    <span class="preview-menu active">Dashboard</span>
                    <span class="preview-menu">My Network</span>
                    <span class="preview-menu">Matches</span>
                </div>
                <div class="preview-main">
                    <div class="preview-top">Panel preview</div>
                    <div class="preview-body">
                        <div class="preview-card">
                            <div class="preview-card-head">MATCH AND SHARE INFO</div>
                            <div class="preview-card-body">
                                <span class="preview-pill">LENA</span>
                                <span class="preview-button">Primary action</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="appearance-actions">
                <button class="btn btn--primary" type="submit">Save panel colors</button>
                <button class="btn btn--ghost" type="submit" form="appearance-reset" data-confirm="Reset panel colors to default green?">Reset defaults</button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.settings.appearance.reset') }}" id="appearance-reset">
            @csrf
        </form>
    </div>
@endsection

@push('head')
<style>
.appearance-panel { background:#fff; border:1px solid #d8d8d8; border-top:0; padding:20px; }
.appearance-copy h2 { color:var(--panel-primary); font-size:1rem; text-transform:uppercase; margin-bottom:4px; }
.appearance-copy p { color:#666; font-size:.76rem; margin-bottom:18px; }
.appearance-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px 24px; }
.appearance-field label { display:block; font-size:.68rem; font-weight:800; color:#333; margin-bottom:6px; }
.appearance-field small { display:block; color:#777; font-size:.65rem; margin-top:4px; }
.color-row { display:flex; gap:8px; align-items:center; }
.color-row input[type=color] { width:48px; height:36px; border:1px solid #ccc; padding:2px; background:#fff; cursor:pointer; }
.color-hex { font-family:monospace; text-transform:lowercase; }
.appearance-preview { display:flex; margin:22px 0; border:1px solid #ccc; min-height:210px; max-width:760px; }
.preview-side { width:190px; background:var(--panel-primary-dark); padding:12px 10px; display:flex; flex-direction:column; gap:5px; }
.preview-brand { display:inline-flex; width:28px; height:28px; border-radius:4px; align-items:center; justify-content:center; background:var(--panel-accent); color:var(--panel-primary-dark); font-weight:900; margin-bottom:8px; }
.preview-menu { color:#fff; font-size:.65rem; padding:6px 8px; border-left:3px solid transparent; }
.preview-menu.active { background:var(--panel-primary); border-left-color:var(--panel-accent); color:var(--panel-accent); font-weight:800; }
.preview-main { flex:1; background:#f4f4f4; }
.preview-top { background:var(--panel-primary); color:#fff; padding:8px 12px; font-size:.7rem; font-weight:800; }
.preview-body { padding:14px; }
.preview-card { background:#fff; border:1px solid #ddd; }
.preview-card-head { background:var(--panel-primary); color:#fff; padding:7px 10px; font-size:.62rem; font-weight:800; }
.preview-card-body { display:flex; align-items:center; gap:10px; padding:12px; }
.preview-pill { background:var(--panel-soft); color:var(--panel-primary); border:1px solid var(--panel-primary); padding:4px 9px; font-size:.6rem; font-weight:800; border-radius:2px; }
.preview-button { background:var(--panel-primary); color:#fff; padding:5px 10px; font-size:.62rem; font-weight:700; border-radius:2px; }
.appearance-actions { display:flex; gap:9px; align-items:center; }
@media(max-width:800px){ .appearance-grid{grid-template-columns:1fr} .appearance-preview{flex-direction:column;max-width:none}.preview-side{width:100%} }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var root = document.documentElement;

    document.querySelectorAll('[data-color-picker]').forEach(function (picker) {
        var text = document.querySelector('[data-color-text][data-for="' + picker.id + '"]');
        var apply = function () {
            root.style.setProperty('--' + picker.id.replaceAll('_', '-'), picker.value);
        };
        picker.addEventListener('input', function () {
            if (text) text.value = picker.value;
            apply();
        });
        if (text) {
            text.addEventListener('input', function () {
                if (/^#[0-9a-fA-F]{6}$/.test(text.value)) {
                    picker.value = text.value;
                    apply();
                }
            });
        }
    });
})();
</script>
@endpush
