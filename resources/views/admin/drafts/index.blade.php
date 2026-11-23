@extends('layouts.admin')

@section('title', 'Market drafts')
@section('page-title', 'Market drafts')
@section('page-subtitle', 'Draft-only market configuration · nothing is published automatically')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>Market drafts</h1>
            <p>Prepare market ideas, review them, then create real markets manually in the existing match screen.</p>
        </div>
    </div>

    <form class="admin-card mb-20" method="POST" action="{{ route('admin.drafts.store') }}">
        @csrf
        <div class="admin-card__head"><h3>New draft</h3></div>
        <div class="admin-card__body">
            <div class="form-grid">
                <div class="field">
                    <label for="match_id">Local match (optional)</label>
                    <select class="select" id="match_id" name="match_id">
                        <option value="">— provider-only draft —</option>
                        @foreach($matches as $match)
                            <option value="{{ $match->id }}" @selected((string) old('match_id') === (string) $match->id)>
                                #{{ $match->id }} · {{ $match->home_team }} vs {{ $match->away_team }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="provider_match_id">Provider match ID (Cricbuzz)</label>
                    <input class="input" id="provider_match_id" name="provider_match_id" type="text" maxlength="40" value="{{ old('provider_match_id') }}" placeholder="e.g. 165061">
                </div>
                <div class="field">
                    <label for="name">Market name</label>
                    <input class="input" id="name" name="name" type="text" maxlength="120" value="{{ old('name') }}" required placeholder="e.g. Top Batter">
                </div>
            </div>

            <div class="field mt-14">
                <label for="selections">Selections (one per line)</label>
                <textarea class="textarea" id="selections" name="selections" rows="4" placeholder="India&#10;England&#10;Draw">{{ old('selections') }}</textarea>
            </div>

            <div class="field mt-14">
                <label for="notes">Notes</label>
                <input class="input" id="notes" name="notes" type="text" maxlength="1000" value="{{ old('notes') }}" placeholder="Why is this market being proposed?">
            </div>

            <button class="btn btn--primary btn--sm mt-14" type="submit">Create draft</button>
        </div>
    </form>

    <form class="admin-card mb-20" method="GET" action="{{ route('admin.drafts.index') }}">
        <div class="admin-card__body" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end">
            <div class="field" style="min-width:170px">
                <label for="status">Status</label>
                <select class="select" id="status" name="status">
                    <option value="">All</option>
                    @foreach(['draft' => 'Draft', 'review' => 'In review', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="flex:1;min-width:220px">
                <label for="q">Search</label>
                <input class="input" id="q" name="q" type="search" value="{{ $query }}" placeholder="Market, match or provider ID">
            </div>
            <button class="btn btn--primary btn--sm" type="submit">Filter</button>
            <a class="btn btn--ghost btn--sm" href="{{ route('admin.drafts.index') }}">Reset</a>
        </div>
    </form>

    <div class="admin-card">
        <div class="admin-card__head"><h3>Drafts</h3></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th>Match</th><th>Market</th><th>Selections</th><th>Status</th><th>Created</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($drafts as $draft)
                        @php
                            $badge = match ($draft->status) {
                                'approved' => 'badge--success',
                                'rejected' => 'badge--danger',
                                'review' => 'badge--warning',
                                default => 'badge--info',
                            };
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $draft->match_label ?? 'Provider #'.$draft->provider_match_id }}</strong>
                                <span class="sub">
                                    {{ $draft->match_id ? 'local #'.$draft->match_id : 'no local match' }}
                                    @if($draft->provider_match_id) · provider {{ $draft->provider_match_id }} @endif
                                </span>
                            </td>
                            <td>{{ $draft->name }}</td>
                            <td class="text-muted" style="max-width:280px">{{ implode(', ', $draft->selections ?? []) ?: '—' }}</td>
                            <td><span class="badge {{ $badge }}">{{ $draft->status_label }}</span></td>
                            <td>{{ $draft->created_at?->format('d M Y, h:i A') }}<span class="sub">{{ $draft->creator?->username ?? '—' }}</span></td>
                            <td>
                                <div style="display:flex;gap:6px;flex-wrap:wrap">
                                    @if($draft->isEditable())
                                        <form method="POST" action="{{ route('admin.drafts.review', $draft) }}">
                                            @csrf
                                            <input type="hidden" name="decision" value="approve">
                                            <button class="btn btn--ghost btn--sm" type="submit">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.drafts.review', $draft) }}">
                                            @csrf
                                            <input type="hidden" name="decision" value="reject">
                                            <button class="btn btn--ghost btn--sm" type="submit">Reject</button>
                                        </form>
                                    @endif

                                    @if($draft->status !== 'approved')
                                        <form method="POST" action="{{ route('admin.drafts.destroy', $draft) }}" data-confirm="Delete this draft?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn--ghost btn--sm" type="submit">Delete</button>
                                        </form>
                                    @endif
                                </div>

                                @if($draft->isEditable())
                                    <details style="margin-top:6px">
                                        <summary style="cursor:pointer;font-size:.7rem" class="text-muted">Edit draft</summary>
                                        <form method="POST" action="{{ route('admin.drafts.update', $draft) }}" style="margin-top:8px">
                                            @csrf
                                            @method('PUT')
                                            <div class="field">
                                                <label>Name</label>
                                                <input class="input" name="name" value="{{ $draft->name }}" required maxlength="120">
                                            </div>
                                            <div class="field mt-8">
                                                <label>Selections (one per line)</label>
                                                <textarea class="textarea" name="selections" rows="3">{{ implode("\n", $draft->selections ?? []) }}</textarea>
                                            </div>
                                            <div class="field mt-8">
                                                <label>Notes</label>
                                                <input class="input" name="notes" value="{{ $draft->notes }}">
                                            </div>
                                            <button class="btn btn--primary btn--sm mt-8" type="submit">Save draft</button>
                                        </form>
                                    </details>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">No drafts yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-card__body">{{ $drafts->links('partials.pagination') }}</div>
    </div>

    <p class="hint mt-14">
        Approving a draft only records the review. It does not create a market, selection, odds or bet — those remain a manual
        action on the match screen.
    </p>
@endsection
