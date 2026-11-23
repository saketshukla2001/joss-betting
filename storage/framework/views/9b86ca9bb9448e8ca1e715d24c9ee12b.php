<?php $__env->startSection('title', 'Matches'); ?>
<?php $__env->startSection('page-title', 'Matches & odds'); ?>
<?php $__env->startSection('page-subtitle', 'Fixtures, markets and settlement'); ?>

<?php $__env->startSection('content'); ?>
    <div class="admin-page-head">
        <div>
            <h1>Matches &amp; odds</h1>
            <p>Create fixtures, open markets and settle results.</p>
        </div>
        <a class="btn btn--primary btn--sm" href="<?php echo e(route('admin.matches.create')); ?>">+ New match</a>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Upcoming</div>
            <div class="stat__value"><?php echo e(number_format($summary['upcoming'])); ?></div>
        </div>
        <div class="stat">
            <div class="stat__label">Live</div>
            <div class="stat__value text-red"><?php echo e(number_format($summary['live'])); ?></div>
        </div>
        <div class="stat">
            <div class="stat__label">Settled</div>
            <div class="stat__value"><?php echo e(number_format($summary['settled'])); ?></div>
        </div>
        <div class="stat">
            <div class="stat__label">Cancelled</div>
            <div class="stat__value"><?php echo e(number_format($summary['cancelled'])); ?></div>
        </div>
    </div>

    <form class="card filter-bar mt-20" method="GET" action="<?php echo e(route('admin.matches.index')); ?>">
        <input class="input search" type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search teams or league…">
        <select class="select" name="sport_id">
            <option value="">All sports</option>
            <?php $__currentLoopData = $sports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sport): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($sport->id); ?>" <?php if((int) request('sport_id') === $sport->id): echo 'selected'; endif; ?>><?php echo e($sport->icon); ?> <?php echo e($sport->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <select class="select" name="status">
            <option value="">All statuses</option>
            <?php $__currentLoopData = ['upcoming' => 'Upcoming', 'live' => 'Live', 'settled' => 'Settled', 'cancelled' => 'Cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>" <?php if(request('status') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <button class="btn btn--primary" type="submit">Apply</button>
        <?php if(request()->hasAny(['q', 'sport_id', 'status'])): ?>
            <a class="btn btn--ghost" href="<?php echo e(route('admin.matches.index')); ?>">Reset</a>
        <?php endif; ?>
    </form>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>All fixtures</h3><span class="badge badge--muted"><?php echo e($matches->total()); ?> results</span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th>Match</th><th>Sport</th><th>Start</th><th>Status</th><th>Result</th><th>Markets</th><th>Bets</th><th></th></tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $matches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $match): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <span class="strong"><?php echo e($match->home_team); ?> vs <?php echo e($match->away_team); ?></span>
                                <span class="sub"><?php echo e($match->league); ?></span>
                            </td>
                            <td><?php echo e($match->sport?->icon); ?> <?php echo e($match->sport?->name); ?></td>
                            <td><span class="sub" style="margin:0"><?php echo e($match->start_time->format('d M Y, h:i A')); ?></span></td>
                            <td><span class="badge badge--<?php echo e($match->status_class); ?>"><?php echo e($match->status_label); ?></span></td>
                            <td>
                                <?php if($match->result): ?>
                                    <span class="badge badge--lime"><?php echo e($match->result_label); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($match->markets_count); ?></td>
                            <td><?php echo e($match->bets_count); ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn btn--ghost btn--sm" href="<?php echo e(route('admin.matches.show', $match)); ?>">Manage</a>
                                    <a class="btn btn--ghost btn--sm" href="<?php echo e(route('admin.matches.edit', $match)); ?>">Edit</a>
                                    <?php if($match->bets_count === 0): ?>
                                        <form method="POST" action="<?php echo e(route('admin.matches.destroy', $match)); ?>" data-confirm="Delete this match and its markets?">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button class="btn btn--ghost btn--sm text-red" type="submit">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="8"><div class="empty"><strong>No matches found</strong><p>Create a fixture to open the book.</p></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($matches->links('partials.pagination')); ?>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/matches/index.blade.php ENDPATH**/ ?>