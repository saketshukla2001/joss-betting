<?php $__env->startSection('title', 'Cricbuzz API test'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container">
        <div class="page-head">
            <div>
                <h1>Cricbuzz API test <span class="badge badge--muted">read-only</span></h1>
                <p>RapidAPI · <code><?php echo e($host ?: 'cricbuzz-cricket.p.rapidapi.com'); ?></code> · fixed test match IDs · no betting involvement</p>
            </div>
            <div class="chip-row">
                <a class="btn btn--primary btn--sm" href="<?php echo e(route('cricbuzz-test.index', ['refresh' => 1])); ?>">Refresh all</a>
                <a class="btn btn--ghost btn--sm" href="<?php echo e(route('cricket-test.index')); ?>">Sportmonks page</a>
            </div>
        </div>

        <?php if(! $configured): ?>
            <div class="alert alert--error">
                RapidAPI credentials missing. Set <strong>RAPIDAPI_HOST</strong> and <strong>RAPIDAPI_KEY</strong> in <code>.env</code>.
            </div>
        <?php endif; ?>

        <?php if($refresh): ?>
            <div class="alert alert--info">Cache bypassed for this request — fresh Cricbuzz data fetched.</div>
        <?php endif; ?>

        <div class="match-grid match-grid--2">
            <?php $__currentLoopData = $matches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo $__env->make('cricbuzz-test._card', ['row' => $row], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <p class="text-muted mt-20" style="font-size:.75rem">
            Endpoints: <code>/mcenter/v1/{matchId}/livescore</code> for this list ·
            <code>/miniscorecard</code> and <code>/scard</code> on each match's scorecard page.
            Data cached briefly to respect the RapidAPI per-second rate limit.
        </p>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/cricbuzz-test/index.blade.php ENDPATH**/ ?>