<?php $__env->startSection('title', $title); ?>

<?php $__env->startSection('content'); ?>
    <div class="classic-form-header">
        <span><?php echo e($title); ?></span>
        <a class="classic-back-btn classic-back-btn--light" href="<?php echo e(route('admin.dashboard')); ?>">Back</a>
    </div>

    <div class="ledger-panel">
        <div class="ledger-columns">
            <div class="ledger-column lena-column">
                <div class="ledger-column__head"><span>LENA</span><strong><?php echo e(number_format($totals['lena'], 2)); ?></strong></div>
                <table class="ledger-mini-table">
                    <thead><tr><th>USERNAME</th><th>AMOUNT</th><th></th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $lena; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><span class="eye-icon">◉</span><?php echo e(strtoupper($user->username)); ?><small>(<?php echo e($user->agent_code); ?>)</small></td>
                                <td class="mono"><?php echo e(number_format($user->balance, 2)); ?></td>
                                <td><a class="ledger-detail-btn" href="<?php echo e(route('admin.hierarchy.statement', [$level, $user])); ?>" title="Ledger">▥</a></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="3"><div class="no-data"><span>NO DATA</span></div></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="ledger-column dena-column">
                <div class="ledger-column__head"><span>DENA</span><strong><?php echo e(number_format($totals['dena'], 2)); ?></strong></div>
                <table class="ledger-mini-table">
                    <thead><tr><th>USERNAME</th><th>AMOUNT</th><th></th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $dena; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><span class="eye-icon">◉</span><?php echo e(strtoupper($user->username)); ?><small>(<?php echo e($user->agent_code); ?>)</small></td>
                                <td class="mono"><?php echo e(number_format(abs($user->balance), 2)); ?></td>
                                <td><a class="ledger-detail-btn" href="<?php echo e(route('admin.hierarchy.statement', [$level, $user])); ?>" title="Ledger">▥</a></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="3"><div class="no-data"><span>NO DATA</span></div></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="ledger-column clear-column">
                <div class="ledger-column__head"><span>CLEAR</span><strong><?php echo e(number_format($totals['clear'], 2)); ?></strong></div>
                <table class="ledger-mini-table">
                    <thead><tr><th>USERNAME</th><th>AMOUNT</th><th></th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $clear; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><span class="eye-icon">◉</span><?php echo e(strtoupper($user->username)); ?><small>(<?php echo e($user->agent_code); ?>)</small></td>
                                <td class="mono">0.00</td>
                                <td><a class="ledger-detail-btn" href="<?php echo e(route('admin.hierarchy.statement', [$level, $user])); ?>" title="Ledger">▥</a></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="3"><div class="no-data"><span>NO DATA</span></div></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/classic/level-ledger.blade.php ENDPATH**/ ?>