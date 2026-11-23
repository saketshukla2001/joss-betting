<?php $__env->startSection('title', 'Current Bets'); ?>

<?php $__env->startSection('content'); ?>
    <div class="classic-level-head mb-14">
        <div>
            <h1>CURRENT BETS</h1>
            <p style="color:#666;font-size:.8rem;margin:3px 0 0">Open tickets across the platform.</p>
        </div>
        <a class="btn btn--outline" href="<?php echo e(route('admin.dashboard')); ?>">Dashboard</a>
    </div>

    <div class="admin-stat-grid">
        <div class="stat"><div class="stat__label">Open tickets</div><div class="stat__value"><?php echo e(number_format($summary['count'])); ?></div></div>
        <div class="stat"><div class="stat__label">Open stake</div><div class="stat__value"><?php echo e(number_format($summary['stake'], 0)); ?></div></div>
        <div class="stat"><div class="stat__label">Inplay</div><div class="stat__value"><?php echo e(\App\Models\MatchGame::where('status', 'live')->count()); ?></div></div>
        <div class="stat"><div class="stat__label">Total matches</div><div class="stat__value"><?php echo e(\App\Models\MatchGame::count()); ?></div></div>
    </div>

    <div class="classic-card">
        <div class="classic-card__head"><span>CURRENT BET TICKETS</span><span><?php echo e($bets->total()); ?> bets</span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Ticket</th><th>Client</th><th>Match</th><th>Selection</th><th>Odds</th><th>Stake</th><th>Status</th><th>Details</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $bets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><span class="ticket-code"><?php echo e($bet->bet_code); ?></span></td>
                            <td><?php echo e($bet->user?->username); ?></td>
                            <td><?php echo e($bet->match_label); ?></td>
                            <td><?php echo e($bet->selection_name); ?><span class="sub"><?php echo e($bet->market_name); ?></span></td>
                            <td class="mono"><?php echo e(odds($bet->odds)); ?></td>
                            <td class="mono"><?php echo e(number_format($bet->stake, 0)); ?></td>
                            <td><span class="badge badge--warning">OPEN</span></td>
                            <td><a class="classic-view-btn" href="<?php echo e(route('admin.bets.show', $bet)); ?>">◉ View</a></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="8"><div class="empty"><strong>No open bets</strong></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($bets->links('partials.pagination')); ?>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/classic/current-bets.blade.php ENDPATH**/ ?>