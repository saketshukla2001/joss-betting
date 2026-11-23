<?php
    $initials = function (string $name): string {
        return collect(explode(' ', $name))->filter()->take(2)
            ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))->implode('');
    };

    try {
        $startsAt = $row['starting_at']
            ? \Illuminate\Support\Carbon::parse($row['starting_at'])->timezone(config('app.timezone'))
            : null;
    } catch (\Throwable) {
        $startsAt = null;
    }
?>

<article class="card" style="padding:0;overflow:hidden">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:11px 14px;border-bottom:1px solid rgba(255,255,255,.06)">
        <span class="text-muted" style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em">
            <?php echo e($row['league'] ?? 'Cricket'); ?><?php if($row['type']): ?> · <?php echo e($row['type']); ?><?php endif; ?>
        </span>
        <span class="badge badge--info"><?php echo e($row['status_label'] ?? $row['status']); ?></span>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding:16px 14px">
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:7px;text-align:center;min-width:0">
            <?php if($row['local_logo']): ?>
                <img src="<?php echo e($row['local_logo']); ?>" alt="<?php echo e($row['local_name']); ?> logo" loading="lazy" style="width:42px;height:42px;object-fit:contain">
            <?php else: ?>
                <span class="team__crest"><?php echo e($initials($row['local_name'])); ?></span>
            <?php endif; ?>
            <strong style="font-size:.86rem;line-height:1.25"><?php echo e($row['local_name']); ?></strong>
        </div>

        <div style="flex:0 0 auto;text-align:center;color:var(--muted,#8b97a8)">
            <div style="font-size:.72rem;font-weight:700;letter-spacing:.08em">VS</div>
            <div style="font-size:.72rem;margin-top:2px"><?php echo e($startsAt?->format('d M') ?? 'TBA'); ?></div>
            <div style="font-size:.7rem"><?php echo e($startsAt?->format('h:i A') ?? ''); ?></div>
        </div>

        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:7px;text-align:center;min-width:0">
            <?php if($row['visitor_logo']): ?>
                <img src="<?php echo e($row['visitor_logo']); ?>" alt="<?php echo e($row['visitor_name']); ?> logo" loading="lazy" style="width:42px;height:42px;object-fit:contain">
            <?php else: ?>
                <span class="team__crest"><?php echo e($initials($row['visitor_name'])); ?></span>
            <?php endif; ?>
            <strong style="font-size:.86rem;line-height:1.25"><?php echo e($row['visitor_name']); ?></strong>
        </div>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding:9px 14px;border-top:1px solid rgba(255,255,255,.06);font-size:.73rem">
        <span class="text-muted">
            <?php echo e($row['venue'] ?? 'Venue TBA'); ?><?php if($row['city']): ?> · <?php echo e($row['city']); ?><?php endif; ?>
        </span>
        <a class="link-more" href="<?php echo e(route('cricket-test.show', $row['id'])); ?>">Details →</a>
    </div>
</article>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/cricket-test/_card.blade.php ENDPATH**/ ?>