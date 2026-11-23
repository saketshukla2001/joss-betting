<?php $__env->startSection('title', 'Secure Code Report'); ?>

<?php $__env->startSection('content'); ?>
    <div class="classic-form-header">
        <span>SECURE CODE REPORT</span>
        <a class="classic-back-btn" href="<?php echo e(route('admin.settings.index')); ?>">Back</a>
    </div>

    <div class="secure-report-panel">
        <form method="GET" action="<?php echo e(route('admin.secure-code.index')); ?>" class="secure-search-form">
            <input class="secure-search-input" type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search Code">
            <button class="secure-search-btn" type="submit">Search</button>
        </form>

        <div class="table-wrap" style="margin-top:16px">
            <table class="data-table secure-code-table">
                <thead>
                    <tr><th>USERNAME</th><th>OTP</th><th>ACTION</th></tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($user->username); ?></td>
                            <td class="mono" style="font-weight:800;letter-spacing:.15em"><?php echo e($user->secure_code ?? '—'); ?></td>
                            <td>
                                <form method="POST" action="<?php echo e(route('admin.secure-code.regenerate', $user)); ?>" data-confirm="Generate a new OTP for <?php echo e($user->username); ?>?">
                                    <?php echo csrf_field(); ?>
                                    <button class="btn btn--ghost btn--sm" type="submit">Regenerate</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="3">
                                <div class="no-data">
                                    <svg viewBox="0 0 64 48" fill="none" stroke="#cfcfcf" stroke-width="2"><path d="M4 8h56v32H4z"/><path d="M4 8l28 20L60 8"/></svg>
                                    <span>NO DATA</span>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php echo e($users->links('partials.pagination')); ?>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/classic/secure-codes.blade.php ENDPATH**/ ?>