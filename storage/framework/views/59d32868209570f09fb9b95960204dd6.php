<?php $__env->startSection('title', 'Match Ledger'); ?>

<?php $__env->startSection('content'); ?>
    <div class="classic-form-header">
        <span>MATCH LEDGER</span>
        <a class="classic-back-btn classic-back-btn--light" href="<?php echo e(route('admin.dashboard')); ?>">Back</a>
    </div>

    <div class="ledger-panel">
        <form method="GET" action="<?php echo e(route('admin.ledger.profit-loss')); ?>" class="ledger-filter">
            <input class="input" type="date" name="start_date" value="<?php echo e(request('start_date')); ?>" placeholder="Start Date">
            <input class="input" type="date" name="end_date" value="<?php echo e(request('end_date')); ?>" placeholder="End Date">
            <button class="ledger-search" type="submit">Search</button>
        </form>

        <div class="match-ledger-total">TOTAL: <strong><?php echo e(number_format($total, 2)); ?></strong></div>

        <div class="table-wrap">
            <table class="data-table match-ledger-table">
                <thead><tr><th>DATE</th><th>EVENT NAME</th><th>WINNER</th><th>CR</th><th>DR</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $matches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $match): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="nowrap"><?php echo e(strtoupper($match->start_time->format('d M Y'))); ?></td>
                            <td><?php echo e(strtoupper($match->home_team.' V '.$match->away_team)); ?></td>
                            <td><?php echo e(strtoupper($match->winner_label)); ?></td>
                            <td class="mono <?php echo e($match->credit > 0 ? 'text-green' : ''); ?>"><?php echo e($match->credit > 0 ? number_format($match->credit, 2) : '0'); ?></td>
                            <td class="mono <?php echo e($match->debit > 0 ? 'text-red' : ''); ?>"><?php echo e($match->debit > 0 ? number_format($match->debit, 2) : '0'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5"><div class="no-data"><span>NO DATA</span></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($matches->links('partials.pagination')); ?>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/classic/match-ledger.blade.php ENDPATH**/ ?>