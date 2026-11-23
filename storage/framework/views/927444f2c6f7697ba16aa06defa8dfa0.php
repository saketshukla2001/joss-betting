<?php $__env->startSection('title', 'Account Operation'); ?>

<?php $__env->startSection('content'); ?>
    <div class="classic-form-header">
        <span>ACCOUNT OPERATION</span>
        <a class="classic-back-btn" href="<?php echo e(route('admin.settings.index')); ?>">Back</a>
    </div>

    <div class="statement-panel">
        <div class="table-wrap">
            <table class="data-table operation-table">
                <thead>
                    <tr><th>Date</th><th>Operation</th><th>Description</th></tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="nowrap"><?php echo e(strtoupper($log->created_at->format('d-M-Y h:i A'))); ?></td>
                            <td><span class="operation-badge"><?php echo e($log->operation); ?></span></td>
                            <td><?php echo e($log->description); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="3"><div class="empty"><strong>No operations recorded</strong></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php echo e($logs->links('partials.pagination')); ?>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/settings/account-operations.blade.php ENDPATH**/ ?>