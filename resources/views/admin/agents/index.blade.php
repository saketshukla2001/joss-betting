@extends('layouts.agent')

@section('title', 'My Network')
@section('page-title', 'My Network')
@section('page-subtitle', 'Create and manage your next hierarchy level')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>My Network</h1>
            <p>Sequence: Main Super Admin → Mini Master → Master Master → Super Master → Agent Master → Client Master.</p>
        </div>
        <div class="actions-row">
            <a class="btn btn--primary btn--sm" href="{{ route('agent.hierarchy.create') }}">+ Create {{ $config['label'] }}</a>
        </div>
    </div>

    <div class="hierarchy-chain">
        @foreach($chain as $index => $member)
            <span class="hierarchy-chain__item {{ $member->id === $actor->id ? 'current' : '' }}">
                <strong>{{ $member->role_label }}</strong>
                <small>{{ $member->username }}</small>
            </span>
            @if(!$loop->last)<span class="hierarchy-chain__arrow">→</span>@endif
        @endforeach
        <span class="hierarchy-chain__arrow">→</span>
        <span class="hierarchy-chain__item next">
            <strong>{{ $config['label'] }}</strong>
            <small>Next level</small>
        </span>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Direct {{ $config['label'] }}s</div>
            <div class="stat__value">{{ $stats['children'] }}</div>
            <div class="stat__meta">{{ $stats['active'] }} active</div>
        </div>
        <div class="stat">
            <div class="stat__label">Full downline</div>
            <div class="stat__value">{{ $stats['downline'] }}</div>
            <div class="stat__meta">All levels below you</div>
        </div>
        <div class="stat">
            <div class="stat__label">Downline coins</div>
            <div class="stat__value">{{ number_format($stats['coins'], 2) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Your balance</div>
            <div class="stat__value">{{ number_format($actor->balance, 2) }}</div>
        </div>
        <div class="stat">
            <div class="stat__label">Pending commission</div>
            <div class="stat__value text-amber">{{ number_format($stats['pending_commission'], 2) }}</div>
        </div>
    </div>

    <div class="admin-card mt-20">
        <div class="admin-card__head">
            <h3>My full downline</h3>
            <span class="badge badge--lime">{{ $downline->count() }} total</span>
        </div>
        <div class="admin-card__body">
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr><th>Code</th><th>Name</th><th>Username</th><th>Role</th><th>Parent</th><th>Share</th><th>Match</th><th>Session</th><th>Chips</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        @forelse($downline as $member)
                            <tr>
                                <td class="mono">{{ $member->agent_code }}</td>
                                <td>{{ $member->name }}</td>
                                <td>{{ $member->username }}</td>
                                <td><span class="badge badge--muted">{{ $member->role_label }}</span></td>
                                <td>{{ $member->parent?->username ?? '—' }}</td>
                                <td>{{ number_format($member->share_percent, 2) }}%</td>
                                <td>{{ number_format($member->commission_rate, 2) }}%</td>
                                <td>{{ number_format($member->session_commission_percent, 2) }}%</td>
                                <td class="mono">{{ number_format($member->balance, 2) }}</td>
                                <td><span class="badge {{ $member->status === 'active' ? 'badge--success' : 'badge--danger' }}">{{ strtoupper($member->status) }}</span></td>
                                <td><a class="btn btn--outline btn--sm" href="{{ route('agent.hierarchy.edit', $member) }}">Edit</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="11"><div class="empty"><strong>No downline yet</strong><p>Create the first account in your downline.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('head')
<style>
.hierarchy-chain { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin:14px 0 18px; }
.hierarchy-chain__item { display:flex; flex-direction:column; gap:2px; padding:8px 12px; border:1px solid #cfe0cf; border-radius:4px; background:#f5faf5; color:#333; }
.hierarchy-chain__item strong { font-size:.66rem; text-transform:uppercase; color:var(--panel-primary); }
.hierarchy-chain__item small { font-size:.62rem; color:#777; }
.hierarchy-chain__item.current { border-color:var(--panel-primary); background:var(--panel-soft); }
.hierarchy-chain__item.next { border-style:dashed; }
.hierarchy-chain__arrow { color:#8aa38a; font-weight:800; }
</style>
@endpush
