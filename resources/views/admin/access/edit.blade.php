@extends('layouts.admin')

@section('title', 'Panel Access')

@section('content')
    <div class="classic-form-header">
        <span>PANEL ACCESS · {{ strtoupper($user->username) }}</span>
        <a class="classic-back-btn classic-back-btn--light" href="{{ route('admin.hierarchy.index', $level) }}">Back</a>
    </div>

    <div class="access-panel">
        <div class="access-user">
            <div>
                <strong>{{ $user->name }}</strong>
                <span>{{ $user->username }} · {{ $user->agent_code }} · {{ $user->role_label }}</span>
            </div>
            <div class="access-user__meta">
                <span>Parent: <strong>{{ $user->parent?->username ?? '—' }}</strong></span>
                <span>Balance: <strong>{{ number_format($user->balance, 2) }}</strong></span>
                <span>Status: <strong>{{ strtoupper($user->status) }}</strong></span>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.access.update', $user) }}">
            @csrf
            <p class="access-note">Select which modules this master sees in the common <code>/agent</code> panel. Unchecked modules are hidden and route-blocked.</p>

            <div class="permission-grid">
                @foreach($modules as $key => $module)
                    <label class="permission-card">
                        <input type="checkbox" name="modules[]" value="{{ $key }}" @checked(in_array($key, $effective, true))>
                        <span>
                            <strong>{{ $module['label'] }}</strong>
                            <small>{{ $module['description'] }}</small>
                        </span>
                    </label>
                @endforeach
            </div>

            <div class="access-actions">
                <button class="btn btn--primary" type="submit">Save Access</button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.access.reset', $user) }}" data-confirm="Reset this master to default role access?">
            @csrf
            <button class="btn btn--ghost btn--sm" type="submit">Reset to role defaults</button>
        </form>
    </div>
@endsection

@push('head')
<style>
.access-panel { background:#fff; border:1px solid #d8d8d8; border-top:0; padding:18px; }
.access-user { display:flex; justify-content:space-between; gap:20px; flex-wrap:wrap; border-bottom:1px solid #e5e5e5; padding-bottom:14px; margin-bottom:14px; }
.access-user strong { display:block; color:var(--panel-primary); font-size:.9rem; }
.access-user span { display:block; color:#666; font-size:.7rem; margin-top:3px; }
.access-user__meta { text-align:right; }
.access-note { color:#666; font-size:.75rem; margin-bottom:12px; }
.permission-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; }
.permission-card { display:flex; gap:10px; align-items:flex-start; border:1px solid #ddd; border-radius:4px; padding:11px; cursor:pointer; background:#fafafa; }
.permission-card:has(input:checked) { border-color:var(--panel-primary); background:var(--panel-soft); }
.permission-card input { margin-top:3px; accent-color:var(--panel-primary); }
.permission-card strong { display:block; font-size:.75rem; color:#222; }
.permission-card small { display:block; color:#777; font-size:.63rem; margin-top:2px; line-height:1.4; }
.access-actions { margin:16px 0 8px; }
@media(max-width:900px){ .permission-grid { grid-template-columns:1fr 1fr; } }
@media(max-width:600px){ .permission-grid { grid-template-columns:1fr; } .access-user__meta{text-align:left} }
</style>
@endpush
