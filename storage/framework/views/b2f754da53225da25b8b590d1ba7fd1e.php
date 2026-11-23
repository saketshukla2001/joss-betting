<?php $__env->startSection('title', 'Withdrawals'); ?>
<?php $__env->startSection('page-title', 'Withdrawals'); ?>
<?php $__env->startSection('page-subtitle', 'Review payout requests and release held funds'); ?>

<?php $__env->startSection('content'); ?>
    <div class="admin-page-head">
        <div>
            <h1>Withdrawal requests</h1>
            <p>Funds are already on hold. Approve to mark paid, or reject to refund the player.</p>
        </div>
        <a class="btn btn--outline btn--sm" href="<?php echo e(route('admin.payments.deposits')); ?>">← Deposits</a>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Pending payouts</div>
            <div class="stat__value"><?php echo e(number_format($summary['pending'])); ?></div>
            <div class="stat__meta"><?php echo e(money($summary['pending_amount'])); ?> on hold</div>
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

    <form class="card filter-bar mt-20" method="GET" action="<?php echo e(route('admin.payments.withdrawals')); ?>">
        <input class="input search" type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search player…">
        <select class="select" name="status">
            <option value="">All statuses</option>
            <?php $__currentLoopData = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>" <?php if(request('status') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <button class="btn btn--primary" type="submit">Filter</button>
        <?php if(request()->hasAny(['q', 'status'])): ?>
            <a class="btn btn--ghost" href="<?php echo e(route('admin.payments.withdrawals')); ?>">Reset</a>
        <?php endif; ?>
    </form>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Payout queue</h3><span class="badge badge--muted"><?php echo e($withdrawals->total()); ?> results</span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Player</th><th>Amount</th><th>Payout details</th><th>Status</th><th>Requested</th><th>Review</th></tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $withdrawals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $withdrawal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>#<?php echo e($withdrawal->id); ?></td>
                            <td>
                                <a href="<?php echo e(route('admin.users.show', $withdrawal->user_id)); ?>"><?php echo e($withdrawal->user?->username); ?></a>
                                <span class="sub"><?php echo e($withdrawal->user?->name); ?> · <?php echo e(ucfirst($withdrawal->user?->role ?? 'client')); ?></span>
                            </td>
                            <td class="mono strong"><?php echo e(money($withdrawal->amount)); ?></td>
                            <td>
                                <?php echo e($withdrawal->method_label); ?> · <?php echo e($withdrawal->account_name); ?>

                                <span class="sub">
                                    <?php echo e($withdrawal->account_number); ?>

                                    <?php if($withdrawal->upi_id): ?> · <?php echo e($withdrawal->upi_id); ?> <?php endif; ?>
                                    <?php if($withdrawal->ifsc): ?> · <?php echo e($withdrawal->ifsc); ?> <?php endif; ?>
                                </span>
                            </td>
                            <td><span class="badge badge--<?php echo e($withdrawal->status_class); ?>"><?php echo e(ucfirst($withdrawal->status)); ?></span></td>
                            <td><span class="sub" style="margin:0"><?php echo e($withdrawal->created_at->format('d M, h:i A')); ?></span></td>
                            <td>
                                <?php if($withdrawal->status === 'pending'): ?>
                                    <details class="review-pop">
                                        <summary class="btn btn--outline btn--sm">Review</summary>
                                        <div class="mt-8" style="min-width:240px">
                                            <form method="POST" action="<?php echo e(route('admin.payments.withdrawals.approve', $withdrawal)); ?>" class="mb-8">
                                                <?php echo csrf_field(); ?>
                                                <input class="input" name="admin_note" placeholder="Payment reference / note">
                                                <button class="btn btn--success btn--sm mt-8" type="submit" data-confirm="Mark this withdrawal paid?">Approve payout</button>
                                            </form>
                                            <form method="POST" action="<?php echo e(route('admin.payments.withdrawals.reject', $withdrawal)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <input class="input" name="admin_note" placeholder="Rejection reason">
                                                <button class="btn btn--danger btn--sm mt-8" type="submit" data-confirm="Reject and refund this withdrawal?">Reject &amp; refund</button>
                                            </form>
                                        </div>
                                    </details>
                                <?php else: ?>
                                    <span class="sub" style="margin:0">
                                        <?php echo e($withdrawal->reviewer?->username ?? 'System'); ?> · <?php echo e($withdrawal->reviewed_at?->format('d M, H:i')); ?>

                                        <?php if($withdrawal->admin_note): ?><br><?php echo e($withdrawal->admin_note); ?><?php endif; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7"><div class="empty"><strong>No withdrawal requests</strong><p>The payout queue is clear.</p></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($withdrawals->links('partials.pagination')); ?>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/payments/withdrawals.blade.php ENDPATH**/ ?>