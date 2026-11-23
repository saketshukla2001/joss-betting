<?php $__env->startSection('title', 'Downline Bets'); ?>
<?php $__env->startSection('page-title', 'Downline Bets'); ?>
<?php $__env->startSection('page-subtitle', 'Every ticket from your players'); ?>

<?php $__env->startSection('content'); ?>
    <div class="admin-page-head">
        <div>
            <h1>Downline bets</h1>
            <p>Track stakes, results and commission-generating turnover.</p>
        </div>
        <a class="btn btn--outline btn--sm" href="<?php echo e(route('agent.commissions.index')); ?>">Commission</a>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Stake in view</div>
            <div class="stat__value"><?php echo e(money($summary['stake'])); ?></div>
        </div>
        <div class="stat">
            <div class="stat__label">Payouts</div>
            <div class="stat__value"><?php echo e(money($summary['payout'])); ?></div>
        </div>
        <div class="stat">
            <div class="stat__label">Tickets</div>
            <div class="stat__value"><?php echo e(number_format($summary['count'])); ?></div>
        </div>
    </div>

    <form class="card filter-bar mt-20" method="GET" action="<?php echo e(route('agent.bets.index')); ?>">
        <input class="input search" type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="Ticket, match or player…">
        <select class="select" name="status">
            <option value="">All statuses</option>
            <?php $__currentLoopData = ['pending' => 'Open', 'won' => 'Won', 'lost' => 'Lost', 'void' => 'Void']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>" <?php if(request('status') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <button class="btn btn--primary" type="submit">Filter</button>
        <?php if(request()->hasAny(['q', 'status'])): ?>
            <a class="btn btn--ghost" href="<?php echo e(route('agent.bets.index')); ?>">Reset</a>
        <?php endif; ?>
    </form>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Bets</h3><span class="badge badge--muted"><?php echo e($bets->total()); ?></span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Ticket</th><th>Player</th><th>Match / selection</th><th>Odds</th><th>Stake</th><th>Payout</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $bets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><span class="ticket-code"><?php echo e($bet->bet_code); ?></span></td>
                            <td><a href="<?php echo e(route('agent.players.show', $bet->user_id)); ?>"><?php echo e($bet->user?->username); ?></a></td>
                            <td><?php echo e($bet->match_label); ?><span class="sub"><?php echo e($bet->selection_name); ?> · <?php echo e($bet->market_name); ?></span></td>
                            <td class="mono"><?php echo e(odds($bet->odds)); ?></td>
                            <td class="mono"><?php echo e(money($bet->stake)); ?></td>
                            <td class="mono <?php echo e($bet->status === 'won' ? 'text-green' : ''); ?>"><?php echo e(money($bet->payout)); ?></td>
                            <td><span class="badge badge--<?php echo e($bet->status_class); ?>"><?php echo e($bet->status_label); ?></span></td>
                            <td><span class="sub" style="margin:0"><?php echo e($bet->created_at->format('d M, H:i')); ?></span></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="8"><div class="empty"><strong>No bets found</strong></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($bets->links('partials.pagination')); ?>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.agent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/agent/bets/index.blade.php ENDPATH**/ ?>