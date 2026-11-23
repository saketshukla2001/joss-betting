<?php
    /**
     * Shared read-only table for the Sportmonks test page.
     *
     * @var array<int, array<string, mixed>> $rows
     */
?>

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
            <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $statusLower = strtolower((string) $row['status']);
                    $badge = $row['live'] ? 'badge--live' : (str_contains($statusLower, 'finish') ? 'badge--success' : 'badge--info');

                    try {
                        $startsAt = $row['starting_at']
                            ? \Illuminate\Support\Carbon::parse($row['starting_at'])->timezone(config('app.timezone'))
                            : null;
                    } catch (\Throwable) {
                        $startsAt = null;
                    }
                ?>
                <tr>
                    <td>
                        <strong><?php echo e($row['name']); ?></strong>
                        <span class="sub">
                            <?php echo e($row['league'] ?? '—'); ?><?php if($row['type']): ?> · <?php echo e($row['type']); ?><?php endif; ?> <?php if($row['round']): ?> · <?php echo e($row['round']); ?><?php endif; ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge <?php echo e($badge); ?>"><?php echo e($row['status']); ?></span>
                        <?php if($row['note']): ?>
                            <span class="sub"><?php echo e($row['note']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="mono">
                        <?php $__empty_1 = true; $__currentLoopData = $row['scores']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $score): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div><?php echo e($score['team']); ?> <?php echo e($score['text']); ?></div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <span class="sub">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php echo e($row['venue'] ?? '—'); ?>

                        <?php if($row['city']): ?>
                            <span class="sub"><?php echo e($row['city']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php echo e($startsAt?->format('d M Y, h:i A') ?? '—'); ?>

                        <span class="sub"><?php echo e($startsAt?->diffForHumans()); ?></span>
                    </td>
                    <td class="text-right">
                        <a class="btn btn--ghost btn--sm" href="<?php echo e(route('cricket-test.show', $row['id'])); ?>">Details</a>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/cricket-test/_table.blade.php ENDPATH**/ ?>