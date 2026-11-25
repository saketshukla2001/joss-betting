<?php $__env->startSection('title', 'Cricket'); ?>
<?php $__env->startSection('page-title', 'Cricket API'); ?>
<?php $__env->startSection('page-subtitle', 'Read-only Cricbuzz monitoring & configuration'); ?>

<?php $__env->startSection('content'); ?>
    <div class="admin-page-head">
        <div>
            <h1>Cricket feed</h1>
            <p>Live, upcoming and completed matches from Cricbuzz (read-only).</p>
        </div>
        <div class="actions-row">
            <a class="btn btn--ghost btn--sm" href="<?php echo e(route('admin.cricket.matches')); ?>">Matches</a>
            <a class="btn btn--ghost btn--sm" href="<?php echo e(route('admin.cricket.markets')); ?>">AI markets</a>
            <a class="btn btn--ghost btn--sm" href="<?php echo e(route('admin.cricket.health')); ?>">API health</a>
            <a class="btn btn--primary btn--sm" href="<?php echo e(route('admin.cricket.settings')); ?>">Settings</a>
        </div>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime"><div class="stat__label">Live</div><div class="stat__value"><?php echo e(count($board['live'] ?? [])); ?></div></div>
        <div class="stat"><div class="stat__label">Upcoming</div><div class="stat__value"><?php echo e(count($board['upcoming'] ?? [])); ?></div></div>
        <div class="stat"><div class="stat__label">Completed</div><div class="stat__value"><?php echo e(count($board['results'] ?? [])); ?></div></div>
        <div class="stat"><div class="stat__label">Configured IDs</div><div class="stat__value"><?php echo e(number_format($configured)); ?></div></div>
        <div class="stat"><div class="stat__label">Stale rows</div><div class="stat__value"><?php echo e(number_format($stale)); ?></div></div>
        <div class="stat"><div class="stat__label">Feed errors</div><div class="stat__value text-red"><?php echo e(number_format($errorCount)); ?></div></div>
    </div>

    <div class="admin-grid-2 mt-20">
        <div class="admin-card">
            <div class="admin-card__head"><h3>Feed status</h3></div>
            <div class="admin-card__body">
                <ul class="info-list">
                    <li><span>Cricket feed enabled</span><strong><?php echo e($feedEnabled ? 'Yes' : 'No'); ?></strong></li>
                    <li><span>Public board enabled</span><strong><?php echo e($boardEnabled ? 'Yes' : 'No'); ?></strong></li>
                    <li><span>Discovery mode</span><strong><?php echo e($discovery ? 'Cricbuzz discovery' : 'Configured IDs'); ?></strong></li>
                    <li><span>Hidden matches</span><strong><?php echo e(count($hidden)); ?></strong></li>
                    <li><span>Board generated</span><strong><?php echo e($board['generated_at'] ?? '—'); ?></strong></li>
                    <li><span>Board source</span><strong><?php echo e($board['source'] ?? '—'); ?></strong></li>
                </ul>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card__head"><h3>API health</h3></div>
            <div class="admin-card__body">
                <ul class="info-list">
                    <li><span>Configured</span><strong><?php echo e($health['configured'] ? 'Yes' : 'No'); ?></strong></li>
                    <li><span>Host</span><strong><?php echo e($health['host'] ?: '—'); ?></strong></li>
                    <li><span>Last success</span><strong><?php echo e($health['last_success']['at'] ?? '—'); ?></strong></li>
                    <li><span>Last failure</span><strong><?php echo e($health['last_failure']['at'] ?? '—'); ?></strong></li>
                    <li><span>Failures / 429 / parse</span><strong><?php echo e($health['failures']); ?> / <?php echo e($health['rate_limits']); ?> / <?php echo e($health['parse_errors']); ?></strong></li>
                </ul>
                <a class="btn btn--outline btn--sm mt-14" href="<?php echo e(route('admin.cricket.health')); ?>">Open API health</a>
            </div>
        </div>
    </div>

    <?php if(! empty($board['errors'])): ?>
        <div class="admin-card mt-20">
            <div class="admin-card__head"><h3>Feed errors</h3></div>
            <div class="admin-card__body">
                <ul class="info-list">
                    <?php $__currentLoopData = $board['errors']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><span><?php echo e($error['id'] ?? 'feed'); ?></span><strong><?php echo e($error['message'] ?? 'Unknown error'); ?></strong></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/cricket/index.blade.php ENDPATH**/ ?>