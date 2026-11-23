@extends('layouts.admin')

@section('title', 'Search Client')

@section('content')
    <div class="classic-form-header">
        <span>SEARCH CLIENT</span>
        <a class="classic-back-btn" href="{{ route('admin.settings.index') }}">Back</a>
    </div>

    <div class="client-search-panel">
        <form method="GET" action="{{ route('admin.settings.search-client') }}" class="client-search-form">
            <input class="client-search-input" type="search" name="q" value="{{ request('q') }}" placeholder="Search Code">
            <button class="client-search-btn" type="submit">Search</button>
        </form>

        @if(request()->filled('q'))
            <div class="table-wrap" style="margin-top:18px">
                <table class="data-table">
                    <thead><tr><th>Name</th><th>Username</th><th>Code</th><th>Reference</th><th>Agent</th><th>Coins</th><th>Bets</th><th>Action</th></tr></thead>
                    <tbody>
                        @forelse($clients as $client)
                            <tr>
                                <td>{{ $client->name }}</td>
                                <td>{{ $client->username }}</td>
                                <td class="mono">{{ $client->agent_code }}</td>
                                <td>{{ $client->reference ?? '—' }}</td>
                                <td>{{ $client->parent?->username ?? '—' }}</td>
                                <td class="mono">{{ number_format($client->balance, 0) }}</td>
                                <td>{{ $client->bets_count }}</td>
                                <td><a class="classic-view-btn" href="{{ route('admin.hierarchy.show', ['client_master', $client]) }}">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8"><div class="empty"><strong>No client found for this code</strong></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $clients->links('partials.pagination') }}
        @endif
    </div>
@endsection
