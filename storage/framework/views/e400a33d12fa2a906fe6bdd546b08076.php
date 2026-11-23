<?php $__env->startSection('title', 'Statement · '.$user->username); ?>

<?php $__env->startSection('content'); ?>
    <div class="classic-form-header">
        <span>STATEMENT · <?php echo e(strtoupper($user->username)); ?></span>
        <a class="classic-back-btn classic-back-btn--light" href="<?php echo e(route('admin.hierarchy.index', $level)); ?>">Back</a>
    </div>

    <div class="statement-panel">
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Date</th><th>Type</th><th>Description</th><th>Amount</th><th>Balance after</th><th>Status</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="nowrap"><?php echo e(strtoupper($transaction->created_at->format('d-M-Y h:i A'))); ?></td>
                            <td><?php echo e($transaction->type_label); ?></td>
                            <td><?php echo e(strtoupper($transaction->description)); ?></td>
                            <td class="mono <?php echo e($transaction->amount >= 0 ? 'text-green' : 'text-red'); ?>"><?php echo e($transaction->amount >= 0 ? '+' : ''); ?><?php echo e(number_format($transaction->amount, 2)); ?></td>
                            <td class="mono"><?php echo e(number_format($transaction->balance_after, 2)); ?></td>
                            <td><span class="badge badge--<?php echo e($transaction->status === 'completed' ? 'success' : 'warning'); ?>"><?php echo e(strtoupper($transaction->status)); ?></span></td>
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

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/hierarchy/statement.blade.php ENDPATH**/ ?>