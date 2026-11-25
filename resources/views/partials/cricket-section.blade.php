@if(setting_enabled('cricket_feed_enabled', true))
@php
    $cricketBoardUrl = route('cricket.board');
    $mode = $mode ?? 'home';
@endphp

<div class="section-head">
    <div>
        <h2>Live cricket</h2>
        <p>Live scores refresh on their own. A card marked LIVE DEMO is simulated and is not a Cricbuzz match.</p>
    </div>
    <a class="link-more" href="{{ route('cricket.index') }}">All cricket →</a>
</div>

<div class="cricket-board"
     data-cricket-board
     data-endpoint="{{ $cricketBoardUrl }}"
     data-mode="{{ $mode }}">
    <div class="cricket-skeleton" aria-hidden="true">
        <div class="cricket-skeleton__card"></div>
        <div class="cricket-skeleton__card"></div>
        <div class="cricket-skeleton__card"></div>
    </div>
</div>

@once
    @push('scripts')
        <script src="{{ asset('js/cricket-live.js') }}?v=3" defer></script>
    @endpush
@endonce
@endif
