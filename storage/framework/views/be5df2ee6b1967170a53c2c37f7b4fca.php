<?php $__env->startSection('title', $player->username); ?>
<?php $__env->startSection('page-title', 'Player profile'); ?>
<?php $__env->startSection('page-subtitle', $player->name.' · '.$player->username); ?>

<?php $__env->startSection('content'); ?>
    <div class="admin-page-head">
        <div class="flex-center" style="gap:12px">
            <span class="avatar avatar--lg"><?php echo e($player->initials); ?></span>
            <div>
                <h1 style="margin:0"><?php echo e($player->name); ?></h1>
                <p><?php echo e('@'.$player->username); ?> · <?php echo e($player->email); ?> · joined <?php echo e($player->created_at->format('d M Y')); ?></p>
            </div>
        </div>
        <div class="actions-row">
            <span class="badge <?php echo e($player->status === 'active' ? 'badge--success' : 'badge--danger'); ?>"><?php echo e(ucfirst($player->status)); ?></span>
            <a class="btn btn--ghost btn--sm" href="<?php echo e(route('agent.players.index')); ?>">← My players</a>
        </div>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Turnover</div>
            <div class="stat__value"><?php echo e(money($stats['turnover'])); ?></div>
            <div class="stat__meta"><?php echo e($stats['bets']); ?> bets · <?php echo e($stats['open']); ?> open</div>
        </div>
        <div class="stat">
            <div class="stat__label">Wallet balance</div>
            <div class="stat__value"><?php echo e(money($player->balance)); ?></div>
        </div>
        <div class="stat">
            <div class="stat__label">Exposure</div>
            <div class="stat__value"><?php echo e(money($player->exposure)); ?></div>
        </div>
        <div class="stat">
            <div class="stat__label">Commission from player</div>
            <div class="stat__value text-lime"><?php echo e(money($stats['commission'])); ?></div>
        </div>
    </div>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Recent bets</h3><a class="link-more" href="<?php echo e(route('agent.bets.index', ['q' => $player->username])); ?>">All bets →</a></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Ticket</th><th>Match</th><th>Selection</th><th>Stake</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $bets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><span class="ticket-code"><?php echo e($bet->bet_code); ?></span></td>
                            <td><?php echo e($bet->match_label); ?></td>
                            <td><?php echo e($bet->selection_name); ?> @ <?php echo e(odds($bet->odds)); ?></td>
                            <td class="mono"><?php echo e(money($bet->stake)); ?></td>
                            <td><span class="badge badge--<?php echo e($bet->status_class); ?>"><?php echo e($bet->status_label); ?></span></td>
                            <td><span class="sub" style="margin:0"><?php echo e($bet->created_at->format('d M, H:i')); ?></span></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6"><div class="empty">No bets yet.</div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Wallet activity</h3></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Type</th><th>Description</th><th>Amount</th><th>Balance after</th><th>Date</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($transaction->type_label); ?></td>
                            <td><?php echo e($transaction->description); ?></td>
                            <td class="mono <?php echo e($transaction->amount >= 0 ? 'text-green' : 'text-red'); ?>"><?php echo e($transaction->amount >= 0 ? '+' : ''); ?><?php echo e(money($transaction->amount)); ?></td>
                            <td class="mono"><?php echo e(money($transaction->balance_after)); ?></td>
                            <td><span class="sub" style="margin:0"><?php echo e($transaction->created_at->format('d M, H:i')); ?></span></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5"><div class="empty">No transactions.</div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.agent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/agent/players/show.blade.php ENDPATH**/ ?>