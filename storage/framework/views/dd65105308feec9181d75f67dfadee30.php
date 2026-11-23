<?php $__env->startSection('title', 'My Network'); ?>
<?php $__env->startSection('page-title', 'My Network'); ?>
<?php $__env->startSection('page-subtitle', 'Create and manage your next hierarchy level'); ?>

<?php $__env->startSection('content'); ?>
    <div class="admin-page-head">
        <div>
            <h1>My Network</h1>
            <p>Sequence: Main Super Admin → Mini Master → Master Master → Super Master → Agent Master → Client Master.</p>
        </div>
        <div class="actions-row">
            <a class="btn btn--primary btn--sm" href="<?php echo e(route('agent.hierarchy.create')); ?>">+ Create <?php echo e($config['label']); ?></a>
        </div>
    </div>

    <div class="hierarchy-chain">
        <?php $__currentLoopData = $chain; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <span class="hierarchy-chain__item <?php echo e($member->id === $actor->id ? 'current' : ''); ?>">
                <strong><?php echo e($member->role_label); ?></strong>
                <small><?php echo e($member->username); ?></small>
            </span>
            <?php if(!$loop->last): ?><span class="hierarchy-chain__arrow">→</span><?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <span class="hierarchy-chain__arrow">→</span>
        <span class="hierarchy-chain__item next">
            <strong><?php echo e($config['label']); ?></strong>
            <small>Next level</small>
        </span>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Direct <?php echo e($config['label']); ?>s</div>
            <div class="stat__value"><?php echo e($stats['children']); ?></div>
            <div class="stat__meta"><?php echo e($stats['active']); ?> active</div>
        </div>
        <div class="stat">
            <div class="stat__label">Full downline</div>
            <div class="stat__value"><?php echo e($stats['downline']); ?></div>
            <div class="stat__meta">All levels below you</div>
        </div>
        <div class="stat">
            <div class="stat__label">Downline coins</div>
            <div class="stat__value"><?php echo e(number_format($stats['coins'], 2)); ?></div>
        </div>
        <div class="stat">
            <div class="stat__label">Your balance</div>
            <div class="stat__value"><?php echo e(number_format($actor->balance, 2)); ?></div>
        </div>
        <div class="stat">
            <div class="stat__label">Pending commission</div>
            <div class="stat__value text-amber"><?php echo e(number_format($stats['pending_commission'], 2)); ?></div>
        </div>
    </div>

    <div class="admin-card mt-20">
        <div class="admin-card__head">
            <h3>My full downline</h3>
            <span class="badge badge--lime"><?php echo e($downline->count()); ?> total</span>
        </div>
        <div class="admin-card__body">
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr><th>Code</th><th>Name</th><th>Username</th><th>Role</th><th>Parent</th><th>Share</th><th>Match</th><th>Session</th><th>Chips</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $downline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="mono"><?php echo e($member->agent_code); ?></td>
                                <td><?php echo e($member->name); ?></td>
                                <td><?php echo e($member->username); ?></td>
                                <td><span class="badge badge--muted"><?php echo e($member->role_label); ?></span></td>
                                <td><?php echo e($member->parent?->username ?? '—'); ?></td>
                                <td><?php echo e(number_format($member->share_percent, 2)); ?>%</td>
                                <td><?php echo e(number_format($member->commission_rate, 2)); ?>%</td>
                                <td><?php echo e(number_format($member->session_commission_percent, 2)); ?>%</td>
                                <td class="mono"><?php echo e(number_format($member->balance, 2)); ?></td>
                                <td><span class="badge <?php echo e($member->status === 'active' ? 'badge--success' : 'badge--danger'); ?>"><?php echo e(strtoupper($member->status)); ?></span></td>
                                <td><a class="btn btn--outline btn--sm" href="<?php echo e(route('agent.hierarchy.edit', $member)); ?>">Edit</a></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="11"><div class="empty"><strong>No downline yet</strong><p>Create the first account in your downline.</p></div></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('head'); ?>
<style>
.hierarchy-chain { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin:14px 0 18px; }
.hierarchy-chain__item { display:flex; flex-direction:column; gap:2px; padding:8px 12px; border:1px solid #cfe0cf; border-radius:4px; background:#f5faf5; color:#333; }
.hierarchy-chain__item strong { font-size:.66rem; text-transform:uppercase; color:#1e6b1e; }
.hierarchy-chain__item small { font-size:.62rem; color:#777; }
.hierarchy-chain__item.current { border-color:#1e6b1e; background:#e9f4e9; }
.hierarchy-chain__item.next { border-style:dashed; }
.hierarchy-chain__arrow { color:#8aa38a; font-weight:800; }
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.agent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/agent/hierarchy/index.blade.php ENDPATH**/ ?>