@php
    /**
     * Shared read-only table for the Sportmonks test page.
     *
     * @var array<int, array<string, mixed>> $rows
     */
@endphp

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Match</th>
                <th>Status</th>
                <th>Score</th>
                <th>Venue</th>
                <th>Starts</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                @php
                    $statusLower = strtolower((string) $row['status']);
                    $badge = $row['live'] ? 'badge--live' : (str_contains($statusLower, 'finish') ? 'badge--success' : 'badge--info');

                    try {
                        $startsAt = $row['starting_at']
                            ? \Illuminate\Support\Carbon::parse($row['starting_at'])->timezone(config('app.timezone'))
                            : null;
                    } catch (\Throwable) {
                        $startsAt = null;
                    }
                @endphp
                <tr>
                    <td>
                        <strong>{{ $row['name'] }}</strong>
                        <span class="sub">
                            {{ $row['league'] ?? '—' }}@if($row['type']) · {{ $row['type'] }}@endif @if($row['round']) · {{ $row['round'] }}@endif
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $badge }}">{{ $row['status'] }}</span>
                        @if($row['note'])
                            <span class="sub">{{ $row['note'] }}</span>
                        @endif
                    </td>
                    <td class="mono">
                        @forelse($row['scores'] as $score)
                            <div>{{ $score['team'] }} {{ $score['text'] }}</div>
                        @empty
                            <span class="sub">—</span>
                        @endforelse
                    </td>
                    <td>
                        {{ $row['venue'] ?? '—' }}
                        @if($row['city'])
                            <span class="sub">{{ $row['city'] }}</span>
                        @endif
                    </td>
                    <td>
                        {{ $startsAt?->format('d M Y, h:i A') ?? '—' }}
                        <span class="sub">{{ $startsAt?->diffForHumans() }}</span>
                    </td>
                    <td class="text-right">
                        <a class="btn btn--ghost btn--sm" href="{{ route('cricket-test.show', $row['id']) }}">Details</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
