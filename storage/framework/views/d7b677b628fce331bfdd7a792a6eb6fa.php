<?php $__env->startSection('title', 'Commissions'); ?>
<?php $__env->startSection('page-title', 'Commissions'); ?>
<?php $__env->startSection('page-subtitle', 'Agent accruals and payouts'); ?>

<?php $__env->startSection('content'); ?>
    <div class="admin-page-head">
        <div>
            <h1>Commission ledger</h1>
            <p>Turnover or net-loss commission accrued from settled downline bets.</p>
        </div>
        <a class="btn btn--outline btn--sm" href="<?php echo e(route('admin.agents.index')); ?>">Agents</a>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Pending commission</div>
            <div class="stat__value"><?php echo e(money($summary['pending'])); ?></div>
            <div class="stat__meta">Unpaid agent earnings</div>
        </div>
        <div class="stat">
            <div class="stat__label">Paid commission</div>
            <div class="stat__value text-green"><?php echo e(money($summary['paid'])); ?></div>
        </div>
        <div class="stat">
            <div class="stat__label">Entries</div>
            <div class="stat__value"><?php echo e(number_format($summary['entries'])); ?></div>
            <div class="stat__meta">One per settled bet / agent</div>
        </div>
        <div class="stat">
            <div class="stat__label">Agents with balance</div>
            <div class="stat__value"><?php echo e($agents->count()); ?></div>
        </div>
    </div>

    <?php if($agents->isNotEmpty()): ?>
        <div class="admin-card mt-20">
            <div class="admin-card__head"><h3>Ready to pay</h3></div>
            <div class="admin-card__body">
                <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bet-mini">
                        <div class="bet-mini__main">
                            <div class="bet-mini__match"><?php echo e($agent->name); ?> <span class="text-muted">· <?php echo e('@'.$agent->username); ?></span></div>
                            <div class="bet-mini__sel"><?php echo e(number_format($agent->commission_rate, 2)); ?>% <?php echo e($agent->commission_type === 'net_loss' ? 'net loss' : 'turnover'); ?></div>
                        </div>
                        <div class="bet-mini__right flex-center">
                            <strong class="text-lime"><?php echo e(money($agent->pending_commission)); ?></strong>
                            <form method="POST" action="<?php echo e(route('admin.commissions.payout', $agent)); ?>" data-confirm="Pay <?php echo e(money($agent->pending_commission)); ?> to <?php echo e($agent->username); ?>?">
                                <?php echo csrf_field(); ?>
                                <button class="btn btn--success btn--sm" type="submit">Pay</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

    <form class="card filter-bar mt-20" method="GET" action="<?php echo e(route('admin.commissions.index')); ?>">
        <input class="input search" type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search agent or player…">
        <select class="select" name="agent_id">
            <option value="">All agents</option>
            <?php $__currentLoopData = \App\Models\User::agents()->orderBy('name')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($agent->id); ?>" <?php if((int) request('agent_id') === $agent->id): echo 'selected'; endif; ?>><?php echo e($agent->username); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <select class="select" name="status">
            <option value="">All statuses</option>
            <option value="pending" <?php if(request('status') === 'pending'): echo 'selected'; endif; ?>>Pending</option>
            <option value="paid" <?php if(request('status') === 'paid'): echo 'selected'; endif; ?>>Paid</option>
        </select>
        <button class="btn btn--primary" type="submit">Filter</button>
        <?php if(request()->hasAny(['q', 'agent_id', 'status'])): ?>
            <a class="btn btn--ghost" href="<?php echo e(route('admin.commissions.index')); ?>">Reset</a>
        <?php endif; ?>
    </form>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Entries</h3><span class="badge badge--muted"><?php echo e($entries->total()); ?></span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Agent</th><th>Player</th><th>Ticket</th><th>Stake</th><th>Rate / basis</th><th>Commission</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><a href="<?php echo e(route('admin.agents.show', $entry->agent_id)); ?>"><?php echo e($entry->agent?->username); ?></a></td>
                            <td><?php echo e($entry->player?->username); ?></td>
                            <td><a class="ticket-code" href="<?php echo e(route('admin.bets.show', $entry->bet_id)); ?>"><?php echo e($entry->bet?->bet_code); ?></a></td>
                            <td class="mono"><?php echo e(money($entry->stake)); ?></td>
                            <td><?php echo e(number_format($entry->rate, 2)); ?>% <span class="sub"><?php echo e($entry->basis_label); ?></span></td>
                            <td class="mono text-lime"><?php echo e(money($entry->amount)); ?></td>
                            <td><span class="badge badge--<?php echo e($entry->status_class); ?>"><?php echo e(ucfirst($entry->status)); ?></span></td>
                            <td><span class="sub" style="margin:0"><?php echo e($entry->created_at->format('d M, h:i A')); ?></span></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="8"><div class="empty"><strong>No commission entries</strong><p>They accrue automatically when downline bets settle.</p></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($entries->links('partials.pagination')); ?>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/commissions/index.blade.php ENDPATH**/ ?>