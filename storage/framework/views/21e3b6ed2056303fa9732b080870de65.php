<?php $__env->startSection('title', 'Login Report · '.$user->username); ?>

<?php $__env->startSection('content'); ?>
    <div class="classic-form-header">
        <span>LOGIN REPORT · <?php echo e(strtoupper($user->username)); ?></span>
        <a class="classic-back-btn classic-back-btn--light" href="<?php echo e(route('admin.hierarchy.index', $level)); ?>">Back</a>
    </div>

    <div class="statement-panel">
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Date</th><th>IP Address</th><th>User Agent</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="nowrap"><?php echo e(strtoupper($log->created_at->format('d-M-Y h:i A'))); ?></td>
                            <td><?php echo e($log->ip_address ?? '—'); ?></td>
                            <td><?php echo e($log->user_agent ?? '—'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="3"><div class="no-data"><span>NO DATA</span></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($logs->links('partials.pagination')); ?>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/hierarchy/login-report.blade.php ENDPATH**/ ?>