<?php $__env->startSection('title', 'Deposits'); ?>
<?php $__env->startSection('page-title', 'Deposits'); ?>
<?php $__env->startSection('page-subtitle', 'Approve or reject player deposit requests'); ?>

<?php $__env->startSection('content'); ?>
    <div class="admin-page-head">
        <div>
            <h1>Deposit requests</h1>
            <p>Verify references before crediting player wallets.</p>
        </div>
        <a class="btn btn--outline btn--sm" href="<?php echo e(route('admin.payments.withdrawals')); ?>">Withdrawals →</a>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Pending requests</div>
            <div class="stat__value"><?php echo e(number_format($summary['pending'])); ?></div>
            <div class="stat__meta"><?php echo e(money($summary['pending_amount'])); ?> waiting</div>
        </div>
        <div class="stat">
            <div class="stat__label">Approved volume</div>
            <div class="stat__value"><?php echo e(short_money($summary['approved_amount'])); ?></div>
        </div>
        <div class="stat">
            <div class="stat__label">All requests</div>
            <div class="stat__value"><?php echo e(number_format($summary['total'])); ?></div>
        </div>
    </div>

    <form class="card filter-bar mt-20" method="GET" action="<?php echo e(route('admin.payments.deposits')); ?>">
        <input class="input search" type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search player…">
        <select class="select" name="status">
            <option value="">All statuses</option>
            <?php $__currentLoopData = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>" <?php if(request('status') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <button class="btn btn--primary" type="submit">Filter</button>
        <?php if(request()->hasAny(['q', 'status'])): ?>
            <a class="btn btn--ghost" href="<?php echo e(route('admin.payments.deposits')); ?>">Reset</a>
        <?php endif; ?>
    </form>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Deposit queue</h3><span class="badge badge--muted"><?php echo e($deposits->total()); ?> results</span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Player</th><th>Amount</th><th>Method / reference</th><th>Status</th><th>Requested</th><th>Review</th></tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $deposits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deposit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>#<?php echo e($deposit->id); ?></td>
                            <td>
                                <a href="<?php echo e(route('admin.users.show', $deposit->user_id)); ?>"><?php echo e($deposit->user?->username); ?></a>
                                <span class="sub"><?php echo e($deposit->user?->name); ?> · <?php echo e(ucfirst($deposit->user?->role ?? 'client')); ?></span>
                            </td>
                            <td class="mono strong"><?php echo e(money($deposit->amount)); ?></td>
                            <td><?php echo e($deposit->method_label); ?><span class="sub"><?php echo e($deposit->transaction_ref); ?></span></td>
                            <td><span class="badge badge--<?php echo e($deposit->status_class); ?>"><?php echo e(ucfirst($deposit->status)); ?></span></td>
                            <td><span class="sub" style="margin:0"><?php echo e($deposit->created_at->format('d M, h:i A')); ?></span></td>
                            <td>
                                <?php if($deposit->status === 'pending'): ?>
                                    <details class="review-pop">
                                        <summary class="btn btn--outline btn--sm">Review</summary>
                                        <div class="mt-8" style="min-width:240px">
                                            <form method="POST" action="<?php echo e(route('admin.payments.deposits.approve', $deposit)); ?>" class="mb-8">
                                                <?php echo csrf_field(); ?>
                                                <input class="input" name="admin_note" placeholder="Approval note (optional)">
                                                <button class="btn btn--success btn--sm mt-8" type="submit" data-confirm="Approve and credit <?php echo e(money($deposit->amount)); ?> to <?php echo e($deposit->user?->username); ?>?">Approve &amp; credit</button>
                                            </form>
                                            <form method="POST" action="<?php echo e(route('admin.payments.deposits.reject', $deposit)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <input class="input" name="admin_note" placeholder="Rejection reason">
                                                <button class="btn btn--danger btn--sm mt-8" type="submit" data-confirm="Reject this deposit request?">Reject</button>
                                            </form>
                                        </div>
                                    </details>
                                <?php else: ?>
                                    <span class="sub" style="margin:0">
                                        <?php echo e($deposit->reviewer?->username ?? 'System'); ?> · <?php echo e($deposit->reviewed_at?->format('d M, H:i')); ?>

                                        <?php if($deposit->admin_note): ?><br><?php echo e($deposit->admin_note); ?><?php endif; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7"><div class="empty"><strong>No deposit requests</strong><p>The queue is clear.</p></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($deposits->links('partials.pagination')); ?>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/payments/deposits.blade.php ENDPATH**/ ?>