@extends('layouts.agent')

@section('title', $match->label)
@section('page-title', 'Bet on match')
@section('page-subtitle', $match->league.' · '.$match->start_time->format('d M Y, h:i A'))

@section('content')
    <div class="admin-page-head">
        <div>
            <h1>{{ $match->home_team }} vs {{ $match->away_team }}</h1>
            <p>{{ $match->league }} · {{ $match->status_label }} · Your balance: <strong>{{ number_format(auth()->user()->balance, 2) }}</strong></p>
        </div>
        <div class="actions-row">
            <a class="btn btn--outline btn--sm" href="{{ route('agent.matches.index') }}">← Matches</a>
        </div>
    </div>

    @if($match->provider === 'cricbuzz')
        @include('partials.cricket-market-live', ['match' => $match])
    @endif

    @php $minStake = max(1, (int) \App\Models\Setting::get('min_stake', 100)); @endphp

    @forelse($match->markets as $market)
        <div class="classic-card" style="margin-bottom:14px">
            <div class="classic-card__head">
                <span>{{ strtoupper($market->name) }}</span>
                <span class="badge {{ $market->status === 'open' ? 'badge--success' : 'badge--muted' }}" data-market-status="{{ $market->id }}">{{ strtoupper($market->status) }}</span>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Selection</th><th>Odds</th><th>Stake</th><th>Place bet</th></tr></thead>
                    <tbody>
                        @foreach($market->selections as $selection)
                            <tr>
                                <td class="strong">{{ $selection->name }}</td>
                                <td class="mono">{{ number_format($selection->odds, 2) }}</td>
                                <td>
                                    @if($market->status === 'open' && $selection->status === 'active' && $match->isBettingOpen())
                                        <form id="bet-{{ $selection->id }}" method="POST" action="{{ route('agent.bet.place') }}">
                                            @csrf
                                            <input type="hidden" name="selection_id" value="{{ $selection->id }}">
                                            <input class="input" type="number" name="stake" min="{{ $minStake }}" step="1" placeholder="Min {{ $minStake }}" style="width:140px" required>
                                        </form>
                                    @else
                                        <span class="text-muted">Suspended</span>
                                    @endif
                                </td>
                                <td>
                                    @if($market->status === 'open' && $selection->status === 'active' && $match->isBettingOpen())
                                        <button class="btn btn--primary btn--sm" type="submit" form="bet-{{ $selection->id }}" data-market-id="{{ $market->id }}" data-confirm="Place this bet?">Place Bet</button>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="classic-card"><div class="empty"><strong>No markets open</strong><p>Betting is not available for this match.</p></div></div>
    @endforelse
@endsection
