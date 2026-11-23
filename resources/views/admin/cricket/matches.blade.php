@extends('layouts.admin')

@section('title', 'Cricket matches')
@section('page-title', 'Cricket matches')
@section('page-subtitle', 'Discovered provider matches · read-only')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>Provider matches</h1>
            <p>{{ count($rows) }} match(es) from Cricbuzz discovery.</p>
        </div>
        <div class="actions-row">
            <a class="btn btn--ghost btn--sm" href="{{ route('admin.cricket.index') }}">← Dashboard</a>
            <a class="btn btn--ghost btn--sm" href="{{ route('admin.cricket.health') }}">API health</a>
        </div>
    </div>

    @if(! empty($feedErrors))
        <div class="alert alert--error">
            @foreach($feedErrors as $feedError)
                <div>{{ ucfirst($feedError['type'] ?? 'feed') }}: {{ $feedError['message'] }}</div>
            @endforeach
        </div>
    @endif

    <form class="admin-card mb-20" method="GET" action="{{ route('admin.cricket.matches') }}">
        <div class="admin-card__body" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end">
            <div class="field" style="min-width:190px">
                <label for="bucket">Status</label>
                <select class="select" id="bucket" name="bucket">
                    <option value="">All</option>
                    <option value="live" @selected($bucket === 'live')>Live</option>
                    <option value="upcoming" @selected($bucket === 'upcoming')>Upcoming</option>
                    <option value="completed" @selected($bucket === 'completed')>Completed</option>
                </select>
            </div>
            <div class="field" style="flex:1;min-width:220px">
                <label for="q">Search</label>
                <input class="input" id="q" name="q" type="search" value="{{ $query }}" placeholder="Team, series or provider ID">
            </div>
            <button class="btn btn--primary btn--sm" type="submit">Filter</button>
            <a class="btn btn--ghost btn--sm" href="{{ route('admin.cricket.matches') }}">Reset</a>
        </div>
    </form>

    <div class="admin-card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Match</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th>Visibility</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $id = (int) ($row['id'] ?? 0);
                            $isHidden = in_array($id, $hidden, true);
                            $label = $row['status_label'] ?? \App\Support\CricketStatus::normalize($row['status_raw'] ?? '')['label'];
                            $raw = $row['status_raw'] ?? '';
                        @endphp
                        <tr>
                            <td class="mono">{{ $id }}</td>
                            <td>
                                <strong>{{ $row['label'] ?? 'Match #'.$id }}</strong>
                                <span class="sub">
                                    {{ $row['series'] ?? '—' }}@if(! empty($row['format'])) · {{ $row['format'] }}@endif
                                    · {{ ucfirst($row['bucket'] ?? '—') }}
                                </span>
                                <details style="margin-top:4px">
                                    <summary style="cursor:pointer;font-size:.7rem" class="text-muted">Diagnostics</summary>
                                    <pre style="white-space:pre-wrap;font-size:.68rem;max-width:520px">{{ json_encode(array_intersect_key($row, array_flip(['id','label','series','format','desc','state','status_raw','status_label','status_group','start_date','venue','city','fetched_at'])), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                                </details>
                            </td>
                            <td>
                                <strong>{{ $label }}</strong>
                                <span class="sub">{{ $raw ?: '—' }}</span>
                            </td>
                            <td>
                                {{ $row['fetched_at'] ? \Illuminate\Support\Carbon::parse($row['fetched_at'])->timezone(config('app.timezone'))->format('d M, h:i:s A') : '—' }}
                            </td>
                            <td>
                                <span class="badge {{ $isHidden ? 'badge--danger' : 'badge--success' }}">{{ $isHidden ? 'Hidden' : 'Visible' }}</span>
                            </td>
                            <td>
                                <div style="display:flex;gap:6px;flex-wrap:wrap">
                                    <a class="btn btn--ghost btn--sm" href="{{ route('cricket.show', $id) }}" target="_blank" rel="noopener">Details</a>

                                    <form method="POST" action="{{ route('admin.cricket.visibility', $id) }}">
                                        @csrf
                                        <button class="btn btn--ghost btn--sm" type="submit">{{ $isHidden ? 'Show' : 'Hide' }}</button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.cricket.refresh', $id) }}">
                                        @csrf
                                        <button class="btn btn--outline btn--sm" type="submit">Refresh</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">No provider matches matched this filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <p class="hint mt-14">
        Visibility only hides a match from the public cricket board; nothing is deleted. Refresh is limited to one request per
        match every 15 seconds. Provider data is never written into betting tables from this screen.
    </p>
@endsection
