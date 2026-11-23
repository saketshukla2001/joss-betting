<?php $__env->startSection('title', 'Bets'); ?>
<?php $__env->startSection('page-title', 'Bets'); ?>
<?php $__env->startSection('page-subtitle', 'Every ticket across the book'); ?>

<?php $__env->startSection('content'); ?>
    <div class="admin-page-head">
        <div>
            <h1>All bets</h1>
            <p>Search tickets, audit odds and review settlements.</p>
        </div>
        <a class="btn btn--outline btn--sm" href="<?php echo e(route('admin.reports.index')); ?>">Reports</a>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Tickets</div>
            <div class="stat__value"><?php echo e(number_format($summary['count'])); ?></div>
        </div>
        <div class="stat">
            <div class="stat__label">Total stake</div>
            <div class="stat__value"><?php echo e(short_money($summary['stake'])); ?></div>
        </div>
        <div class="stat">
            <div class="stat__label">Total payout</div>
            <div class="stat__value"><?php echo e(short_money($summary['payout'])); ?></div>
        </div>
        <div class="stat">
            <div class="stat__label">Open</div>
            <div class="stat__value text-amber"><?php echo e(number_format($summary['pending'])); ?></div>
        </div>
    </div>

    <form class="card filter-bar mt-20" method="GET" action="<?php echo e(route('admin.bets.index')); ?>">
        <input class="input search" type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="Ticket, player, match or selection…">
        <select class="select" name="status">
            <option value="">All statuses</option>
            <?php $__currentLoopData = ['pending' => 'Open', 'won' => 'Won', 'lost' => 'Lost', 'void' => 'Void']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>" <?php if(request('status') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <select class="select" name="sport_id">
            <option value="">All sports</option>
            <?php $__currentLoopData = $sports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sport): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($sport->id); ?>" <?php if((int) request('sport_id') === $sport->id): echo 'selected'; endif; ?>><?php echo e($sport->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <input class="input" type="date" name="from" value="<?php echo e(request('from')); ?>" title="From date">
        <input class="input" type="date" name="to" value="<?php echo e(request('to')); ?>" title="To date">
        <button class="btn btn--primary" type="submit">Filter</button>
        <?php if(request()->hasAny(['q', 'status', 'sport_id', 'from', 'to'])): ?>
            <a class="btn btn--ghost" href="<?php echo e(route('admin.bets.index')); ?>">Reset</a>
        <?php endif; ?>
    </form>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Tickets</h3><span class="badge badge--muted"><?php echo e($bets->total()); ?> results</span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th>Ticket</th><th>Player</th><th>Match</th><th>Selection</th><th>Odds</th><th>Stake</th><th>Return</th><th>Status</th><th>Placed</th><th></th></tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $bets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><a class="ticket-code" href="<?php echo e(route('admin.bets.show', $bet)); ?>"><?php echo e($bet->bet_code); ?></a></td>
                            <td>
                                <?php if($bet->user): ?>
                                    <a href="<?php echo e(route('admin.users.show', $bet->user)); ?>"><?php echo e($bet->user->username); ?></a>
                                <?php else: ?>
                                    <span class="text-muted">Deleted</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($bet->match_label); ?><span class="sub"><?php echo e($bet->sport_name); ?></span></td>
                            <td><?php echo e($bet->selection_name); ?><span class="sub"><?php echo e($bet->market_name); ?></span></td>
                            <td class="mono"><?php echo e(odds($bet->odds)); ?></td>
                            <td class="mono"><?php echo e(money($bet->stake)); ?></td>
                            <td class="mono <?php echo e($bet->status === 'won' ? 'text-green' : ''); ?>"><?php echo e(money($bet->payout)); ?></td>
                            <td><span class="badge badge--<?php echo e($bet->status_class); ?>"><?php echo e($bet->status_label); ?></span></td>
                            <td><span class="sub" style="margin:0"><?php echo e($bet->created_at->format('d M, h:i A')); ?></span></td>
                            <td><a class="btn btn--ghost btn--sm" href="<?php echo e(route('admin.bets.show', $bet)); ?>">View</a></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="10"><div class="empty"><strong>No bets found</strong><p>Adjust the filters to see more.</p></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($bets->links('partials.pagination')); ?>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/bets/index.blade.php ENDPATH**/ ?>