<?php $__env->startSection('title', 'Master Update Limit'); ?>

<?php $__env->startSection('content'); ?>
    <div class="classic-form-header">
        <span>MASTER UPDATE LIMIT</span>
        <a class="classic-back-btn classic-back-btn--light" href="<?php echo e(route('admin.sports.inplay')); ?>">Back</a>
    </div>

    <div class="limit-panel">
        <div class="limit-match-head">
            <strong><?php echo e(strtoupper($match->home_team)); ?> V <?php echo e(strtoupper($match->away_team)); ?></strong>
            <span><?php echo e(strtoupper($match->league)); ?> · <?php echo e(strtoupper($match->start_time->format('d-M-Y h:i A'))); ?></span>
        </div>

        <?php $__currentLoopData = $masters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $master): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <form id="match-limit-form-<?php echo e($master->id); ?>" method="POST" action="<?php echo e(route('admin.sports.match-limits.update', $match)); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="user_id" value="<?php echo e($master->id); ?>">
            </form>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <div class="table-wrap">
            <table class="data-table limit-table">
                <thead>
                    <tr>
                        <th>CODE <span class="th-search">⌕</span></th>
                        <th>NAME <span class="th-search">⌕</span></th>
                        <th>C.CHIP</th>
                        <th>ADD / MINUS LIMIT</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $masters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $master): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="mono"><?php echo e($master->agent_code); ?></td>
                            <td><?php echo e(strtoupper($master->username)); ?></td>
                            <td class="mono">
                                <?php echo e(number_format($master->balance, 2)); ?>

                                <?php if(($limits[$master->id] ?? 0) > 0): ?>
                                    <div class="match-limit-note">Match limit: <?php echo e(number_format($limits[$master->id], 2)); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <input class="input limit-input" type="number" name="amount" min="0.01" step="0.01" placeholder="Enter amount" form="match-limit-form-<?php echo e($master->id); ?>" required>
                            </td>
                            <td>
                                <div class="limit-actions">
                                    <button class="limit-add" type="submit" name="direction" value="add" form="match-limit-form-<?php echo e($master->id); ?>">+ Add</button>
                                    <button class="limit-minus" type="submit" name="direction" value="minus" form="match-limit-form-<?php echo e($master->id); ?>">− Minus</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5"><div class="no-data"><span>NO DATA</span></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php echo e($masters->links('partials.pagination')); ?>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/classic/match-limits.blade.php ENDPATH**/ ?>