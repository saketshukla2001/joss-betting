<?php $__env->startSection('title', 'Agent Dashboard'); ?>
<?php $__env->startSection('page-title', 'Dashboard'); ?>
<?php $__env->startSection('page-subtitle', 'Agency performance · '.$agent->agent_code); ?>

<?php $__env->startSection('content'); ?>
    <div class="admin-page-head">
        <div>
            <h1>Welcome, <?php echo e(\Illuminate\Support\Str::of($agent->name)->explode(' ')->first()); ?>.</h1>
            <p>Your players, turnover and commission at a glance.</p>
        </div>
        <div class="actions-row">
            <a class="btn btn--outline btn--sm" href="<?php echo e(route('agent.commissions.index')); ?>">Commission</a>
            <?php $nextLevel = \App\Support\Hierarchy::nextLevelFor($agent->role); $nextConfig = $nextLevel ? \App\Support\Hierarchy::get($nextLevel) : null; ?>
            <?php if($nextConfig && $agent->hasPanelModule('network') && $agent->hasPanelModule('create')): ?>
                <a class="btn btn--primary btn--sm" href="<?php echo e(route('agent.hierarchy.create')); ?>">+ Create <?php echo e($nextConfig['label']); ?></a>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Downline players</div>
            <div class="stat__value"><?php echo e(number_format($stats['players'])); ?></div>
            <div class="stat__meta"><?php echo e($stats['direct_players']); ?> direct · <?php echo e($stats['sub_agents']); ?> <?php echo e($stats['sub_agents'] === 1 ? 'sub-agent' : 'sub-agents'); ?></div>
        </div>
        <div class="stat">
            <div class="stat__label">Downline turnover</div>
            <div class="stat__value"><?php echo e(short_money($stats['turnover'])); ?></div>
            <div class="stat__meta"><?php echo e($stats['open_bets']); ?> open bets</div>
        </div>
        <div class="stat">
            <div class="stat__label">Pending commission</div>
            <div class="stat__value text-amber"><?php echo e(money($stats['pending_commission'])); ?></div>
            <div class="stat__meta">Accrued, not yet paid</div>
        </div>
        <div class="stat">
            <div class="stat__label">Paid commission</div>
            <div class="stat__value text-green"><?php echo e(money($stats['paid_commission'])); ?></div>
            <div class="stat__meta">All time</div>
        </div>
    </div>

    <div class="admin-grid-2 mt-20">
        <div>
            <div class="admin-card">
            <div class="admin-card__head">
                <h3>Recent downline bets</h3>
                <a class="link-more" href="<?php echo e(route('agent.bets.index')); ?>">All bets →</a>
            </div>
            <div class="admin-card__body">
                        <?php $__empty_1 = true; $__currentLoopData = $recentBets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="bet-mini">
                                <div class="bet-mini__main">
                                    <div class="bet-mini__match"><?php echo e($bet->match_label); ?></div>
                                    <div class="bet-mini__sel"><?php echo e($bet->user?->username); ?> · <?php echo e($bet->selection_name); ?> @ <?php echo e(odds($bet->odds)); ?> · <?php echo e($bet->created_at->format('d M, H:i')); ?></div>
                                </div>
                                <div class="bet-mini__right">
                                    <div class="bet-mini__stake"><?php echo e(money($bet->stake)); ?></div>
                                    <span class="badge badge--<?php echo e($bet->status_class); ?>"><?php echo e($bet->status_label); ?></span>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="empty"><strong>No bets from your players yet</strong><p>Add a player and share their login to start.</p></div>
                        <?php endif; ?>
                    </div>
            </div>
        </div>

        <div>
            <div class="admin-card">
                <div class="admin-card__head"><h3>Commission terms</h3></div>
                <div class="admin-card__body">
                    <ul class="info-list">
                        <li><span>Rate</span><strong class="text-lime"><?php echo e(number_format($agent->commission_rate, 2)); ?>%</strong></li>
                        <li><span>Basis</span><strong><?php echo e($agent->commission_type === 'net_loss' ? 'Net loss' : 'Turnover'); ?></strong></li>
                        <li><span>Pending balance</span><strong><?php echo e(money($agent->commission_balance)); ?></strong></li>
                        <li><span>Wallet balance</span><strong><?php echo e(money($agent->balance)); ?></strong></li>
                        <li><span>Agent code</span><strong><?php echo e($agent->agent_code); ?></strong></li>
                    </ul>
                    <a class="btn btn--outline btn--block btn--sm mt-14" href="<?php echo e(route('agent.commissions.index')); ?>">View commission ledger</a>
                </div>
            </div>

            <div class="admin-card mt-20">
                <div class="admin-card__head"><h3>Top players</h3></div>
                <div class="admin-card__body">
                    <?php $__empty_1 = true; $__currentLoopData = $topPlayers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="bet-mini">
                            <div class="bet-mini__main">
                                <div class="bet-mini__match"><?php echo e($row->user?->username); ?></div>
                                <div class="bet-mini__sel"><?php echo e($row->bets_count); ?> bets</div>
                            </div>
                            <strong><?php echo e(money($row->total_stake)); ?></strong>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-muted" style="font-size:.8rem">No player activity yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.agent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/agent/dashboard.blade.php ENDPATH**/ ?>