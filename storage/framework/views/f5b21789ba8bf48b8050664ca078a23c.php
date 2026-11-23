<?php $__env->startSection('title', 'Cricket API test'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container">
        <div class="page-head">
            <div>
                <h1>Cricket API test <span class="badge badge--muted">read-only</span></h1>
                <p>Sportmonks Cricket API 2.0 · isolated test page · does not touch betting, wallets or odds</p>
            </div>
            <div class="chip-row">
                <a class="btn btn--ghost btn--sm" href="<?php echo e(route('cricket-test.index')); ?>">Refresh</a>
            </div>
        </div>

        <?php if(! $configured): ?>
            <div class="alert alert--error">
                No Sportmonks token found. Add <strong>SPORTMONKS_CRICKET_KEY</strong> (or <strong>CRICKET_API_KEY</strong>) to <code>.env</code>.
            </div>
        <?php endif; ?>

        <div class="card mb-20">
            <div class="card__body" style="display:flex;gap:24px;flex-wrap:wrap;font-size:.8rem">
                <span><strong>Base URL</strong><br><?php echo e($baseUrl); ?></span>
                <span><strong>Upcoming window</strong><br>today → +30 days</span>
                <span><strong>Recent window</strong><br><?php echo e($from); ?> → <?php echo e($to); ?></span>
                <span><strong>Upcoming</strong><br><?php echo e($upcoming['success'] ? count($upcomingFixtures).' fixture(s)' : 'error'); ?></span>
                <span><strong>Live</strong><br><?php echo e($live['success'] ? count($liveFixtures).' fixture(s)' : 'error'); ?></span>
                <span><strong>Cache</strong><br><?php echo e(($live['cached'] ?? false) || ($upcoming['cached'] ?? false) || ($recent['cached'] ?? false) ? 'served from cache' : 'fresh requests'); ?></span>
            </div>
        </div>

        <?php $__currentLoopData = ['live' => 'Livescores', 'upcoming' => 'Upcoming', 'recent' => 'Recent fixtures']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $result = $$key; ?>
            <?php if(! $result['success']): ?>
                <div class="alert alert--error">
                    <strong><?php echo e($label); ?>:</strong> <?php echo e($result['error']); ?>

                    <?php if($result['status']): ?>
                        <span class="text-muted">(HTTP <?php echo e($result['status']); ?><?php echo e($result['error_code'] ? ' · '.$result['error_code'] : ''); ?>)</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <div class="card mb-20">
            <div class="card__head">
                <div>
                    <h3>Live Matches</h3>
                    <p>GET /livescores</p>
                </div>
            </div>
            <?php if(empty($liveFixtures)): ?>
                <div class="card__body">
                    <p class="text-muted" style="font-size:.8rem;margin:0">
                        <?php echo e($live['success'] ? 'No matches are live right now.' : 'Live endpoint failed — see the error above.'); ?>

                    </p>
                </div>
            <?php else: ?>
                <?php echo $__env->make('cricket-test._table', ['rows' => $liveFixtures], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>
        </div>

        
        <div class="card mb-20">
            <div class="card__head">
                <div>
                    <h3>Upcoming Matches</h3>
                    <p>GET /fixtures · <code>filter[status]=NS</code> · soonest first</p>
                </div>
                <span class="badge badge--info"><?php echo e(count($upcomingFixtures)); ?> fixtures</span>
            </div>
            <?php if(empty($upcomingFixtures)): ?>
                <div class="card__body">
                    <p class="text-muted" style="font-size:.8rem;margin:0">
                        <?php echo e($upcoming['success'] ? 'No upcoming fixtures returned for the next 30 days.' : 'Upcoming endpoint failed — see the error above.'); ?>

                    </p>
                </div>
            <?php else: ?>
                <div class="card__body">
                    <div class="match-grid match-grid--2">
                        <?php $__currentLoopData = $upcomingFixtures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo $__env->make('cricket-test._card', ['row' => $row], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        
        <div class="card">
            <div class="card__head">
                <div>
                    <h3>Recent Results</h3>
                    <p>GET /fixtures · <?php echo e($from); ?> → <?php echo e($to); ?></p>
                </div>
                <div class="chip-row">
                    <?php if($page > 1): ?>
                        <a class="btn btn--ghost btn--sm" href="<?php echo e(route('cricket-test.index', array_filter(['page' => $page - 1, 'league' => $league]))); ?>">← Prev</a>
                    <?php endif; ?>
                    <a class="btn btn--ghost btn--sm" href="<?php echo e(route('cricket-test.index', array_filter(['page' => $page + 1, 'league' => $league]))); ?>">Next →</a>
                </div>
            </div>
            <?php if(empty($recentRows)): ?>
                <div class="card__body">
                    <p class="text-muted" style="font-size:.8rem;margin:0">
                        <?php echo e($recent['success'] ? 'No recent fixtures in this window/page.' : 'Recent fixtures endpoint failed — see the error above.'); ?>

                    </p>
                </div>
            <?php else: ?>
                <?php echo $__env->make('cricket-test._table', ['rows' => $recentRows], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/cricket-test/index.blade.php ENDPATH**/ ?>