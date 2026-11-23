<?php $__env->startSection('title', 'Cricket API health'); ?>
<?php $__env->startSection('page-title', 'Cricket API health'); ?>
<?php $__env->startSection('page-subtitle', 'Cricbuzz / RapidAPI diagnostics'); ?>

<?php $__env->startSection('content'); ?>
    <div class="admin-page-head">
        <div>
            <h1>API health</h1>
            <p>No credentials, headers or secrets are shown here.</p>
        </div>
        <div class="actions-row">
            <a class="btn btn--ghost btn--sm" href="<?php echo e(route('admin.cricket.index')); ?>">← Dashboard</a>
            <a class="btn btn--ghost btn--sm" href="<?php echo e(route('admin.cricket.matches')); ?>">Matches</a>
        </div>
    </div>

    <div class="admin-stat-grid">
        <div class="stat <?php echo e($health['configured'] ? 'stat--lime' : ''); ?>">
            <div class="stat__label">Connection</div>
            <div class="stat__value"><?php echo e($health['configured'] ? 'Configured' : 'Missing'); ?></div>
            <div class="stat__meta"><?php echo e($health['host'] ?: 'no host'); ?></div>
        </div>
        <div class="stat"><div class="stat__label">Failures</div><div class="stat__value"><?php echo e(number_format($health['failures'])); ?></div></div>
        <div class="stat"><div class="stat__label">Rate limits (429)</div><div class="stat__value text-red"><?php echo e(number_format($health['rate_limits'])); ?></div></div>
        <div class="stat"><div class="stat__label">Parsing errors</div><div class="stat__value"><?php echo e(number_format($health['parse_errors'])); ?></div></div>
    </div>

    <div class="admin-grid-2 mt-20">
        <div class="admin-card">
            <div class="admin-card__head"><h3>Last requests</h3></div>
            <div class="admin-card__body">
                <ul class="info-list">
                    <li>
                        <span>Last success</span>
                        <strong>
                            <?php echo e($health['last_success']['at'] ?? '—'); ?>

                            <?php if(! empty($health['last_success']['status'])): ?>
                                · HTTP <?php echo e($health['last_success']['status']); ?>

                            <?php endif; ?>
                        </strong>
                    </li>
                    <li><span>Last success path</span><strong><?php echo e($health['last_success']['path'] ?? '—'); ?></strong></li>
                    <li>
                        <span>Last failure</span>
                        <strong>
                            <?php echo e($health['last_failure']['at'] ?? '—'); ?>

                            <?php if(! empty($health['last_failure']['status'])): ?>
                                · HTTP <?php echo e($health['last_failure']['status']); ?>

                            <?php endif; ?>
                        </strong>
                    </li>
                    <li><span>Last failure path</span><strong><?php echo e($health['last_failure']['path'] ?? '—'); ?></strong></li>
                </ul>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card__head"><h3>Cache &amp; limits</h3></div>
            <div class="admin-card__body">
                <ul class="info-list">
                    <li><span>Board cached</span><strong><?php echo e($boardCached ? 'Yes' : 'No'); ?></strong></li>
                    <li><span>Board generated</span><strong><?php echo e($boardGeneratedAt ?? '—'); ?></strong></li>
                    <li><span>Discovery cache (live/upcoming/recent)</span><strong><?php echo e(collect($discoveryCached)->map(fn ($v) => $v ? 'yes' : 'no')->implode(' / ')); ?></strong></li>
                    <li><span>Board TTL</span><strong><?php echo e($boardTtl); ?>s</strong></li>
                    <li><span>Discovery TTL</span><strong><?php echo e($discoveryTtl); ?>s</strong></li>
                    <li><span>Request spacing</span><strong><?php echo e($throttle); ?>ms</strong></li>
                    <li><span>HTTP timeout</span><strong><?php echo e($timeout); ?>s</strong></li>
                </ul>
            </div>
        </div>
    </div>

    <p class="hint mt-14">
        RapidAPI BASIC plans allow a small number of requests per second and a monthly quota. The feed throttles requests and
        reuses cache; 429 responses are counted above and retried once with a back-off.
    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/cricket/health.blade.php ENDPATH**/ ?>