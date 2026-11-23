<?php $__env->startSection('title', 'Match and Session PL'); ?>

<?php $__env->startSection('content'); ?>
    <div class="classic-form-header">
        <span>MATCH AND SESSION PL</span>
        <a class="classic-back-btn classic-back-btn--light" href="<?php echo e(route('admin.sports.completed')); ?>">Back</a>
    </div>

    <div class="statement-panel">
        <div class="limit-match-head">
            <strong><?php echo e(strtoupper($match->home_team)); ?> V <?php echo e(strtoupper($match->away_team)); ?></strong>
            <span><?php echo e(strtoupper($match->league)); ?> · <?php echo e(strtoupper($match->start_time->format('d-M-Y h:i A'))); ?></span>
        </div>

        <div class="admin-stat-grid" style="padding:14px">
            <div class="stat pl-stat"><div class="stat__label">Match PL</div><div class="stat__value"><?php echo e(number_format($totals['match'], 2)); ?></div></div>
            <div class="stat pl-stat"><div class="stat__label">Session PL</div><div class="stat__value"><?php echo e(number_format($totals['session'], 2)); ?></div></div>
            <div class="stat pl-stat"><div class="stat__label">Total PL</div><div class="stat__value"><?php echo e(number_format($totals['total'], 2)); ?></div></div>
        </div>

        <div class="table-wrap" style="padding:0 14px 14px">
            <table class="data-table pl-table">
                <thead><tr><th>Market</th><th>Type</th><th>Bets</th><th>Stake</th><th>Payout</th><th>PL</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($row['market']); ?></td>
                            <td><span class="badge <?php echo e($row['type'] === 'SESSION' ? 'badge--info' : 'badge--muted'); ?>"><?php echo e($row['type']); ?></span></td>
                            <td><?php echo e($row['bets']); ?></td>
                            <td class="mono"><?php echo e(number_format($row['stake'], 2)); ?></td>
                            <td class="mono"><?php echo e(number_format($row['payout'], 2)); ?></td>
                            <td class="mono <?php echo e($row['pl'] < 0 ? 'text-red' : ($row['pl'] > 0 ? 'text-green' : '')); ?>"><?php echo e(number_format($row['pl'], 2)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6"><div class="no-data"><span>NO DATA</span></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/classic/match-pl.blade.php ENDPATH**/ ?>