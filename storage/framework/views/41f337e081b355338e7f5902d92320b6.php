<?php $__env->startSection('title', 'Commission'); ?>
<?php $__env->startSection('page-title', 'Commission'); ?>
<?php $__env->startSection('page-subtitle', number_format($agent->commission_rate, 2).'% · '.($agent->commission_type === 'net_loss' ? 'net loss' : 'turnover')); ?>

<?php $__env->startSection('content'); ?>
    <div class="admin-page-head">
        <div>
            <h1>Commission ledger</h1>
            <p>Earned from settled bets of your downline players.</p>
        </div>
        <a class="btn btn--outline btn--sm" href="<?php echo e(route('agent.bets.index')); ?>">Downline bets</a>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Pending commission</div>
            <div class="stat__value"><?php echo e(money($summary['pending'])); ?></div>
            <div class="stat__meta">Admin will release this to your wallet</div>
        </div>
        <div class="stat">
            <div class="stat__label">Paid commission</div>
            <div class="stat__value text-green"><?php echo e(money($summary['paid'])); ?></div>
        </div>
        <div class="stat">
            <div class="stat__label">Entries</div>
            <div class="stat__value"><?php echo e(number_format($summary['entries'])); ?></div>
        </div>
        <div class="stat">
            <div class="stat__label">Commission balance</div>
            <div class="stat__value"><?php echo e(money($agent->commission_balance)); ?></div>
            <div class="stat__meta">Wallet <?php echo e(money($agent->balance)); ?></div>
        </div>
    </div>

    <div class="admin-card mt-20">
        <div class="admin-card__head">
            <h3>My commission terms</h3>
            <span class="badge badge--lime"><?php echo e(number_format($agent->commission_rate, 2)); ?>%</span>
        </div>
        <div class="admin-card__body">
            <div class="info-list">
                <li><span>Basis</span><strong><?php echo e($agent->commission_type === 'net_loss' ? 'Net loss — only losing stakes' : 'Turnover — every settled stake'); ?></strong></li>
                <li><span>Agent code</span><strong><?php echo e($agent->agent_code); ?></strong></li>
                <li><span>Upline</span><strong><?php echo e($agent->parent?->username ?? 'Top level'); ?></strong></li>
            </div>
        </div>
    </div>

    <form class="card filter-bar mt-20" method="GET" action="<?php echo e(route('agent.commissions.index')); ?>">
        <input class="input search" type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="Player or ticket…">
        <select class="select" name="status">
            <option value="">All statuses</option>
            <option value="pending" <?php if(request('status') === 'pending'): echo 'selected'; endif; ?>>Pending</option>
            <option value="paid" <?php if(request('status') === 'paid'): echo 'selected'; endif; ?>>Paid</option>
        </select>
        <button class="btn btn--primary" type="submit">Filter</button>
    </form>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Entries</h3><span class="badge badge--muted"><?php echo e($entries->total()); ?></span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Player</th><th>Ticket</th><th>Stake</th><th>Rate</th><th>Basis</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($entry->player?->username); ?></td>
                            <td><span class="ticket-code"><?php echo e($entry->bet?->bet_code); ?></span></td>
                            <td class="mono"><?php echo e(money($entry->stake)); ?></td>
                            <td><?php echo e(number_format($entry->rate, 2)); ?>%</td>
                            <td><?php echo e($entry->basis_label); ?></td>
                            <td class="mono text-lime"><?php echo e(money($entry->amount)); ?></td>
                            <td><span class="badge badge--<?php echo e($entry->status_class); ?>"><?php echo e(ucfirst($entry->status)); ?></span></td>
                            <td><span class="sub" style="margin:0"><?php echo e($entry->created_at->format('d M, H:i')); ?></span></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="8"><div class="empty"><strong>No commission yet</strong><p>Commission appears when your players' bets settle.</p></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($entries->links('partials.pagination')); ?>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.agent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/agent/commissions/index.blade.php ENDPATH**/ ?>