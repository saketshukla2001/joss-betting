@extends('layouts.admin')

@section('title', 'Live demo cricket')
@section('page-title', 'Live demo cricket')
@section('page-subtitle', 'Simulated score. Regular accounts are charged from their wallet.')

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>LIVE DEMO</h1>
            <p>This scoreboard is simulated. Markets use the same cycle as live cricket. A regular account is charged and settled like any other match. Only user ids listed in <code>CRICKET_DEMO_WALLET_EXEMPT_USER_IDS</code> skip the wallet. The match appears on <a href="{{ route('cricket.index') }}">Live cricket</a> with a LIVE DEMO badge.</p>
        </div>
    </div>

    <form class="admin-card mb-20" method="POST" action="{{ route('admin.cricket.demo.store') }}">
        @csrf
        <div class="admin-card__head"><h3>New demo match</h3></div>
        <div class="admin-card__body">
            <div class="form-grid">
                <div class="field"><label for="team1">Team 1</label><input class="input" id="team1" name="team1" required></div>
                <div class="field"><label for="team2">Team 2</label><input class="input" id="team2" name="team2" required></div>
            </div>
            <button class="btn btn--primary btn--sm mt-14" type="submit">Create demo</button>
        </div>
    </form>

    @foreach($matches as $match)
        <div class="admin-card mb-20">
            <div class="admin-card__head">
                <h3>LIVE DEMO · {{ $match->team1 }} vs {{ $match->team2 }}</h3>
                <span>{{ $match->status }}</span>
            </div>
            <div class="admin-card__body">
                <p>{{ $match->batting }} {{ $match->runs }}/{{ $match->wickets }} ({{ intdiv($match->legal_balls, 6) }}.{{ $match->legal_balls % 6 === 0 ? '0' : $match->legal_balls % 6 }})</p>
                <div class="actions-row">
                    @if($match->status !== 'running')
                        <form method="POST" action="{{ route('admin.cricket.demo.start', $match) }}">@csrf<button class="btn btn--primary btn--sm" type="submit">Start</button></form>
                    @else
                        <form method="POST" action="{{ route('admin.cricket.demo.pause', $match) }}">@csrf<button class="btn btn--ghost btn--sm" type="submit">Pause</button></form>
                    @endif
                    @if($match->status === 'paused')
                        <form method="POST" action="{{ route('admin.cricket.demo.resume', $match) }}">@csrf<button class="btn btn--ghost btn--sm" type="submit">Resume</button></form>
                    @endif
                    <form method="POST" action="{{ route('admin.cricket.demo.auto', $match) }}">@csrf<button class="btn btn--ghost btn--sm" type="submit">{{ $match->auto_bowl ? 'Stop auto balls' : 'Auto balls' }}</button></form>
                </div>
                @if($match->status === 'running')
                    <div class="actions-row mt-14">
                        @foreach($kinds as $kind)
                            <form method="POST" action="{{ route('admin.cricket.demo.ball', $match) }}">@csrf<input type="hidden" name="kind" value="{{ $kind }}"><button class="btn btn--ghost btn--sm" type="submit">{{ $kind }}</button></form>
                        @endforeach
                    </div>
                @endif
                <div class="text-muted mt-14">{{ $match->balls->count() }} balls recorded. Provider id {{ $match->providerMatchId() }}.</div>
            </div>
        </div>
    @endforeach
@endsection
