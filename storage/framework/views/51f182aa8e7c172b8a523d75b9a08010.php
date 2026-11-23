<?php $__env->startSection('title', 'My Ledger'); ?>

<?php $__env->startSection('content'); ?>
    <div class="classic-form-header">
        <span>MY LEDGER</span>
        <a class="classic-back-btn classic-back-btn--light" href="<?php echo e(route('admin.dashboard')); ?>">Back</a>
    </div>

    <div class="ledger-panel">
        <div class="my-ledger-summary">
            <div class="my-ledger-summary__item lena">
                <span>LENA</span>
                <strong>↑ <?php echo e(number_format($summary['lena'], 2)); ?></strong>
            </div>
            <div class="my-ledger-summary__item dena">
                <span>DENA</span>
                <strong>↓ <?php echo e(number_format($summary['dena'], 2)); ?></strong>
            </div>
            <div class="my-ledger-summary__item balance">
                <span>BALANCE</span>
                <strong><?php echo e(number_format($summary['balance'], 2)); ?> DENA</strong>
            </div>
        </div>

        <div class="table-wrap">
            <table class="data-table my-ledger-table">
                <thead><tr><th>DATE</th><th>DR</th><th>CR</th><th>BALANCE</th><th>PAYMENT TYPE</th><th>REMARK</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $dr = $transaction->amount < 0 ? abs($transaction->amount) : 0;
                            $cr = $transaction->amount > 0 ? $transaction->amount : 0;
                        ?>
                        <tr>
                            <td class="nowrap"><?php echo e($transaction->created_at->format('Y-m-d H:i:s')); ?></td>
                            <td class="mono <?php echo e($dr > 0 ? 'text-red' : ''); ?>"><?php echo e($dr > 0 ? number_format($dr, 2) : '0'); ?></td>
                            <td class="mono <?php echo e($cr > 0 ? 'text-green' : ''); ?>"><?php echo e($cr > 0 ? number_format($cr, 2) : '0'); ?></td>
                            <td class="mono"><?php echo e(number_format($transaction->balance_after, 2)); ?> DENA</td>
                            <td><?php echo e(strtoupper($transaction->description)); ?></td>
                            <td><?php echo e(strtoupper($transaction->type_label)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6"><div class="no-data"><span>NO DATA</span></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($transactions->links('partials.pagination')); ?>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/classic/my-ledger.blade.php ENDPATH**/ ?>