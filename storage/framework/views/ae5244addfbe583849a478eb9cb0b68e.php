<?php
    $live = $row['live'];
    $status = $live['status'] ?? '';
    $isFinished = (bool) preg_match('/\bwon\b|drawn|abandoned|no result/i', $status);
?>

<article class="card" style="padding:0;overflow:hidden">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:11px 14px;border-bottom:1px solid rgba(255,255,255,.06)">
        <span style="font-size:.76rem;font-weight:700;letter-spacing:.02em"><?php echo e($row['label']); ?></span>
        <span class="badge <?php echo e($isFinished ? 'badge--muted' : 'badge--live'); ?>"><?php echo e($isFinished ? 'Result' : 'Live'); ?></span>
    </div>

    <div class="card__body" style="padding:13px 14px">
        <?php if(! $row['result']['success']): ?>
            <p class="text-muted" style="font-size:.78rem;margin:0">
                <?php echo e($row['result']['error']); ?>

                <?php if($row['result']['status']): ?> <span class="sub">(HTTP <?php echo e($row['result']['status']); ?>)</span><?php endif; ?>
            </p>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:8px">
                <?php $__empty_1 = true; $__currentLoopData = $live['innings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div style="display:flex;align-items:baseline;justify-content:space-between;gap:10px">
                        <span style="font-weight:700;font-size:.82rem"><?php echo e($inning['team']); ?></span>
                        <span class="mono" style="font-size:.9rem">
                            <?php echo e($inning['runs'] ?? '—'); ?><?php if($inning['wickets'] !== null): ?>/<?php echo e($inning['wickets']); ?><?php endif; ?>
                            <?php if($inning['overs'] !== null): ?><span class="sub"> (<?php echo e($inning['overs']); ?> ov)</span><?php endif; ?>
                        </span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted" style="font-size:.78rem;margin:0">Score not started yet.</p>
                <?php endif; ?>
            </div>

            <p class="text-muted" style="font-size:.75rem;margin:10px 0 0;line-height:1.4"><?php echo e($status ?: '—'); ?></p>

            <div style="display:flex;gap:14px;flex-wrap:wrap;font-size:.72rem;margin-top:8px" class="text-muted">
                <?php if($live['crr'] !== null): ?><span>CRR <strong><?php echo e($live['crr']); ?></strong></span><?php endif; ?>
                <?php if($live['rrr'] !== null): ?><span>RRR <strong><?php echo e($live['rrr']); ?></strong></span><?php endif; ?>
                <?php if($live['target'] !== null): ?><span>Target <strong><?php echo e($live['target']); ?></strong></span><?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding:9px 14px;border-top:1px solid rgba(255,255,255,.06);font-size:.73rem">
        <span class="text-muted"><?php echo e($row['result']['cached'] ? 'cached' : 'fresh'); ?><?php if($row['result']['fetched_at']): ?> · <?php echo e(\Illuminate\Support\Carbon::parse($row['result']['fetched_at'])->timezone(config('app.timezone'))->format('h:i:s A')); ?><?php endif; ?></span>
        <a class="link-more" href="<?php echo e(route('cricbuzz-test.show', $row['id'])); ?>">Scorecard →</a>
    </div>
</article>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/cricbuzz-test/_card.blade.php ENDPATH**/ ?>