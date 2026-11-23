@extends('layouts.admin')

@section('title', 'Secure Code Report')

@section('content')
    <div class="classic-form-header">
        <span>SECURE CODE REPORT</span>
        <a class="classic-back-btn" href="{{ route('admin.settings.index') }}">Back</a>
    </div>

    <div class="secure-report-panel">
        <form method="GET" action="{{ route('admin.secure-code.index') }}" class="secure-search-form">
            <input class="secure-search-input" type="search" name="q" value="{{ request('q') }}" placeholder="Search Code">
            <button class="secure-search-btn" type="submit">Search</button>
        </form>

        <div class="table-wrap" style="margin-top:16px">
            <table class="data-table secure-code-table">
                <thead>
                    <tr><th>USERNAME</th><th>OTP</th><th>ACTION</th></tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->username }}</td>
                            <td class="mono" style="font-weight:800;letter-spacing:.15em">{{ $user->secure_code ?? '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.secure-code.regenerate', $user) }}" data-confirm="Generate a new OTP for {{ $user->username }}?">
                                    @csrf
                                    <button class="btn btn--ghost btn--sm" type="submit">Regenerate</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <div class="no-data">
                                    <svg viewBox="0 0 64 48" fill="none" stroke="#cfcfcf" stroke-width="2"><path d="M4 8h56v32H4z"/><path d="M4 8l28 20L60 8"/></svg>
                                    <span>NO DATA</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $users->links('partials.pagination') }}
    </div>
@endsection
