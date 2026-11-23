<?php $__env->startSection('title', 'Matches'); ?>
<?php $__env->startSection('page-title', 'Matches'); ?>
<?php $__env->startSection('page-subtitle', 'Live and upcoming betting events'); ?>

<?php $__env->startSection('content'); ?>
    <div class="admin-page-head">
        <div>
            <h1>Matches</h1>
            <p>Open a match to view markets and place a bet from your master wallet.</p>
        </div>
    </div>

    <form class="filter-bar" method="GET" action="<?php echo e(route('agent.matches.index')); ?>">
        <input class="input" type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search team or competition">
        <select class="select" name="sport">
            <option value="">All sports</option>
            <?php $__currentLoopData = $sports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sport): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($sport->slug); ?>" <?php if(request('sport') === $sport->slug): echo 'selected'; endif; ?>><?php echo e($sport->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <button class="btn btn--primary" type="submit">Filter</button>
    </form>

    <div class="classic-card">
        <div class="classic-card__head"><span>OPEN MATCHES</span><span><?php echo e($matches->total()); ?> events</span></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Match</th><th>Competition</th><th>Date &amp; Time</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $matches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $match): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="strong"><?php echo e(strtoupper($match->home_team)); ?> V <?php echo e(strtoupper($match->away_team)); ?></td>
                            <td><?php echo e(strtoupper($match->league)); ?></td>
                            <td><?php echo e($match->start_time->format('d M Y, h:i A')); ?></td>
                            <td><span class="badge <?php echo e($match->status === 'live' ? 'badge--live' : 'badge--muted'); ?>"><?php echo e($match->status_label); ?></span></td>
                            <td><a class="classic-view-btn" href="<?php echo e(route('agent.matches.show', $match)); ?>">Open</a></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5"><div class="empty"><strong>No open matches</strong></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($matches->links('partials.pagination')); ?>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.agent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/agent/matches/index.blade.php ENDPATH**/ ?>