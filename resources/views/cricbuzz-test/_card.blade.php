@php
    $live = $row['live'];
    $status = $live['status'] ?? '';
    $isFinished = (bool) preg_match('/\bwon\b|drawn|abandoned|no result/i', $status);
@endphp

<article class="card" style="padding:0;overflow:hidden">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:11px 14px;border-bottom:1px solid rgba(255,255,255,.06)">
        <span style="font-size:.76rem;font-weight:700;letter-spacing:.02em">{{ $row['label'] }}</span>
        <span class="badge {{ $isFinished ? 'badge--muted' : 'badge--live' }}">{{ $isFinished ? 'Result' : 'Live' }}</span>
    </div>

    <div class="card__body" style="padding:13px 14px">
        @if(! $row['result']['success'])
            <p class="text-muted" style="font-size:.78rem;margin:0">
                {{ $row['result']['error'] }}
                @if($row['result']['status']) <span class="sub">(HTTP {{ $row['result']['status'] }})</span>@endif
            </p>
        @else
            <div style="display:flex;flex-direction:column;gap:8px">
                @forelse($live['innings'] as $inning)
                    <div style="display:flex;align-items:baseline;justify-content:space-between;gap:10px">
                        <span style="font-weight:700;font-size:.82rem">{{ $inning['team'] }}</span>
                        <span class="mono" style="font-size:.9rem">
                            {{ $inning['runs'] ?? '—' }}@if($inning['wickets'] !== null)/{{ $inning['wickets'] }}@endif
                            @if($inning['overs'] !== null)<span class="sub"> ({{ $inning['overs'] }} ov)</span>@endif
                        </span>
                    </div>
                @empty
                    <p class="text-muted" style="font-size:.78rem;margin:0">Score not started yet.</p>
                @endforelse
            </div>

            <p class="text-muted" style="font-size:.75rem;margin:10px 0 0;line-height:1.4">{{ $status ?: '—' }}</p>

            <div style="display:flex;gap:14px;flex-wrap:wrap;font-size:.72rem;margin-top:8px" class="text-muted">
                @if($live['crr'] !== null)<span>CRR <strong>{{ $live['crr'] }}</strong></span>@endif
                @if($live['rrr'] !== null)<span>RRR <strong>{{ $live['rrr'] }}</strong></span>@endif
                @if($live['target'] !== null)<span>Target <strong>{{ $live['target'] }}</strong></span>@endif
            </div>
        @endif
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding:9px 14px;border-top:1px solid rgba(255,255,255,.06);font-size:.73rem">
        <span class="text-muted">{{ $row['result']['cached'] ? 'cached' : 'fresh' }}@if($row['result']['fetched_at']) · {{ \Illuminate\Support\Carbon::parse($row['result']['fetched_at'])->timezone(config('app.timezone'))->format('h:i:s A') }}@endif</span>
        <a class="link-more" href="{{ route('cricbuzz-test.show', $row['id']) }}">Scorecard →</a>
    </div>
</article>
