<?php $__env->startSection('title', $title); ?>

<?php $__env->startSection('content'); ?>
    <div class="classic-form-header">
        <span><?php echo e($title); ?></span>
        <a class="classic-back-btn classic-back-btn--light" href="<?php echo e(route('admin.sports.inplay')); ?>">Back</a>
    </div>

    <div class="statement-panel">
        <div class="limit-match-head">
            <strong><?php echo e(strtoupper($match->home_team)); ?> V <?php echo e(strtoupper($match->away_team)); ?></strong>
            <span><?php echo e(strtoupper($match->league)); ?> · <?php echo e(strtoupper($match->start_time->format('d-M-Y h:i A'))); ?></span>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Ticket</th><th>User</th><th>Market</th><th>Selection</th><th>Odds</th><th>Stake</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $bets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="mono"><?php echo e($bet->bet_code); ?></td>
                            <td><?php echo e($bet->user?->username); ?></td>
                            <td><?php echo e($bet->market_name); ?></td>
                            <td><?php echo e($bet->selection_name); ?></td>
                            <td class="mono"><?php echo e(odds($bet->odds)); ?></td>
                            <td class="mono"><?php echo e(number_format($bet->stake, 2)); ?></td>
                            <td><span class="badge badge--<?php echo e($bet->status_class); ?>"><?php echo e(strtoupper($bet->status_label)); ?></span></td>
                            <td class="nowrap"><?php echo e(strtoupper($bet->created_at->format('d-M-Y h:i A'))); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="8"><div class="no-data"><span>NO DATA</span></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($bets->links('partials.pagination')); ?>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/classic/match-bets.blade.php ENDPATH**/ ?>