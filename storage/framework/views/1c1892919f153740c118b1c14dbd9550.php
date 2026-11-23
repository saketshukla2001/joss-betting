<?php $__env->startSection('title', 'My Players'); ?>
<?php $__env->startSection('page-title', 'My Players'); ?>
<?php $__env->startSection('page-subtitle', 'Players across your downline'); ?>

<?php $__env->startSection('content'); ?>
    <div class="admin-page-head">
        <div>
            <h1>My players</h1>
            <p><?php echo e($agent->role === 'agent' ? 'Create and monitor the players assigned to you.' : 'Players in your downline.'); ?></p>
        </div>
        <?php if($agent->role === 'agent'): ?>
            <a class="btn btn--primary btn--sm" href="<?php echo e(route('agent.players.create')); ?>">+ Add player</a>
        <?php endif; ?>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Total players</div>
            <div class="stat__value"><?php echo e(number_format($summary['total'])); ?></div>
        </div>
        <div class="stat">
            <div class="stat__label">Active</div>
            <div class="stat__value text-green"><?php echo e(number_format($summary['active'])); ?></div>
        </div>
        <div class="stat">
            <div class="stat__label">Wallet balances</div>
            <div class="stat__value"><?php echo e(short_money($summary['balance'])); ?></div>
        </div>
        <div class="stat">
            <div class="stat__label">Full downline</div>
            <div class="stat__value"><?php echo e(number_format($summary['downline'])); ?></div>
            <div class="stat__meta">Including sub-agent players</div>
        </div>
    </div>

    <form class="card filter-bar mt-20" method="GET" action="<?php echo e(route('agent.players.index')); ?>">
        <input class="input search" type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search name, username, email…">
        <select class="select" name="status">
            <option value="">All statuses</option>
            <option value="active" <?php if(request('status') === 'active'): echo 'selected'; endif; ?>>Active</option>
            <option value="blocked" <?php if(request('status') === 'blocked'): echo 'selected'; endif; ?>>Blocked</option>
        </select>
        <button class="btn btn--primary" type="submit">Filter</button>
    </form>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Player list</h3><span class="badge badge--muted"><?php echo e($players->total()); ?></span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Player</th><th>Status</th><th>Balance</th><th>Exposure</th><th>Bets</th><th>Joined</th><th></th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $players; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $player): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <div class="user-cell"><span class="avatar-sm"><?php echo e($player->initials); ?></span><span><strong><?php echo e($player->name); ?></strong><span><?php echo e('@'.$player->username); ?> · <?php echo e($player->email); ?></span></span></div>
                            </td>
                            <td><span class="badge <?php echo e($player->status === 'active' ? 'badge--success' : 'badge--danger'); ?>"><?php echo e(ucfirst($player->status)); ?></span></td>
                            <td class="mono"><?php echo e(money($player->balance)); ?></td>
                            <td class="mono"><?php echo e(money($player->exposure)); ?></td>
                            <td><?php echo e($player->bets_count); ?></td>
                            <td><span class="sub" style="margin:0"><?php echo e($player->created_at->format('d M Y')); ?></span></td>
                            <td><a class="btn btn--ghost btn--sm" href="<?php echo e(route('agent.players.show', $player)); ?>">View</a></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7"><div class="empty"><strong>No players yet</strong><p><?php echo e($agent->role === 'agent' ? 'Create your first player to start earning commission.' : 'Clients created by Agent Masters will appear here.'); ?></p></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($players->links('partials.pagination')); ?>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.agent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/agent/players/index.blade.php ENDPATH**/ ?>