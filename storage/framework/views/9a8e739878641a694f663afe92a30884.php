<?php $__env->startSection('title', 'Live demo cricket'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container">
        <div class="section-head">
            <div>
                <h2>LIVE DEMO</h2>
                <p>Simulated cricket. These matches are not on the real sportsbook, and a demo ticket does not use your wallet.</p>
            </div>
        </div>

        <?php $__empty_1 = true; $__currentLoopData = $matches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $match): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <article class="card mb-20">
                <div class="card__head">
                    <div>
                        <h3><?php echo e($match->home_team); ?> vs <?php echo e($match->away_team); ?></h3>
                        <p>LIVE DEMO · <?php echo e($match->league); ?></p>
                    </div>
                    <span class="badge badge--live">LIVE DEMO</span>
                </div>
                <div class="card__body">
                    <p><?php echo e($match->markets->count()); ?> markets</p>
                    <a class="btn btn--primary btn--sm" href="<?php echo e(route('demo-cricket.show', $match->id)); ?>">Open demo</a>
                </div>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="card empty">
                <strong>No demo match is running</strong>
                <p>An admin can start one from Live demo. It will appear here after the market cycle publishes it.</p>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/demo-cricket/index.blade.php ENDPATH**/ ?>