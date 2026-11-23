<?php $__env->startSection('title', 'Statement'); ?>

<?php $__env->startSection('content'); ?>
    <div class="classic-form-header">
        <span>STATEMENT</span>
        <a class="classic-back-btn" href="<?php echo e(route('admin.settings.index')); ?>">Back</a>
    </div>

    <div class="statement-panel">
        <div class="statement-toolbar">
            <div class="statement-tabs">
                <a class="statement-tab <?php echo e($view === 'all' ? 'active' : ''); ?>" href="<?php echo e(route('admin.settings.statement', ['limit' => $limit])); ?>">All</a>
                <a class="statement-tab <?php echo e($view === 'pl' ? 'active' : ''); ?>" href="<?php echo e(route('admin.settings.statement', ['view' => 'pl', 'limit' => $limit])); ?>">P/L</a>
                <a class="statement-tab <?php echo e(request('limit') ? 'active' : ''); ?>" href="<?php echo e(route('admin.settings.statement', ['limit' => 25])); ?>">Limit</a>
                <form method="GET" action="<?php echo e(route('admin.settings.statement')); ?>" class="statement-limit">
                    <?php if($view === 'pl'): ?><input type="hidden" name="view" value="pl"><?php endif; ?>
                    <select name="limit" onchange="this.form.submit()">
                        <?php $__currentLoopData = [15, 25, 50, 100]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($option); ?>" <?php if($limit === $option): echo 'selected'; endif; ?>><?php echo e($option); ?> rows</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </form>
            </div>
            <button class="btn btn--danger btn--sm" type="submit" form="bulk-delete-form">Delete Selected</button>
        </div>

        <?php if($view === 'pl'): ?>
            <div class="admin-stat-grid" style="margin-bottom:12px">
                <div class="stat"><div class="stat__label">Total CR</div><div class="stat__value"><?php echo e(number_format($summary['credit'], 0)); ?></div></div>
                <div class="stat"><div class="stat__label">Total DR</div><div class="stat__value"><?php echo e(number_format($summary['debit'], 0)); ?></div></div>
                <div class="stat"><div class="stat__label">Net</div><div class="stat__value"><?php echo e(number_format($summary['net'], 0)); ?></div></div>
                <div class="stat"><div class="stat__label">Balance</div><div class="stat__value"><?php echo e(number_format($summary['balance'], 0)); ?></div></div>
            </div>
        <?php endif; ?>

        <form id="bulk-delete-form" method="POST" action="<?php echo e(route('admin.settings.statement.bulk-delete')); ?>" data-confirm="Delete selected statement entries?">
            <?php echo csrf_field(); ?>
        </form>

        <div class="table-wrap">
            <table class="data-table statement-table">
                <thead>
                    <tr>
                        <th class="statement-check"><input type="checkbox" id="statement-select-all" aria-label="Select all"></th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Old.Bal</th>
                        <th>CR</th>
                        <th>DR</th>
                        <th>COMM+</th>
                        <th>COMM-</th>
                        <th>Balance</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $oldBalance = $transaction->balance_after - $transaction->amount;
                            $credit = max(0, $transaction->amount);
                            $debit = max(0, -$transaction->amount);
                        ?>
                        <tr>
                            <td class="statement-check">
                                <input type="checkbox" name="ids[]" value="<?php echo e($transaction->id); ?>" form="bulk-delete-form" class="statement-checkbox" aria-label="Select entry">
                            </td>
                            <td class="nowrap"><?php echo e(strtoupper($transaction->created_at->format('d-M-Y h:i A'))); ?></td>
                            <td>
                                <?php echo e(strtoupper($transaction->description)); ?>

                                <?php if($transaction->is_demo): ?><span class="badge badge--muted" style="margin-left:5px">DEMO</span><?php endif; ?>
                            </td>
                            <td class="mono"><?php echo e(number_format($oldBalance, 2)); ?></td>
                            <td class="mono text-green"><?php echo e(number_format($credit, 2)); ?></td>
                            <td class="mono text-red"><?php echo e(number_format($debit, 2)); ?></td>
                            <td class="mono">0.00</td>
                            <td class="mono">0.00</td>
                            <td class="mono"><?php echo e(number_format($transaction->balance_after, 2)); ?></td>
                            <td>
                                <form method="POST" action="<?php echo e(route('admin.settings.statement.destroy', $transaction)); ?>" data-confirm="Delete this statement entry?">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button class="statement-delete" type="submit" title="Delete">×</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="10"><div class="empty"><strong>No statement entries</strong></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php echo e($transactions->links('partials.pagination')); ?>

    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    var all = document.getElementById('statement-select-all');
    if (!all) return;
    var boxes = Array.prototype.slice.call(document.querySelectorAll('.statement-checkbox'));
    all.addEventListener('change', function () {
        boxes.forEach(function (box) { box.checked = all.checked; });
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/settings/statement.blade.php ENDPATH**/ ?>