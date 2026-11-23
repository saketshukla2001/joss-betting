<?php $__env->startSection('title', 'Live cricket'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container">
        <div class="page-head">
            <div>
                <h1>Live cricket <span class="badge badge--live">Live</span></h1>
                <p>Cricbuzz discovery · live scores, wickets, overs and full scorecards · read-only</p>
            </div>
            <div class="chip-row">
                <button type="button" class="btn btn--ghost btn--sm" data-cricket-refresh>Refresh</button>
                <a class="btn btn--ghost btn--sm" href="<?php echo e(route('sports.index')); ?>">Sportsbook</a>
            </div>
        </div>

        <?php if(! $configured): ?>
            <div class="alert alert--error">
                Cricket scores are not configured. Set <strong>RAPIDAPI_HOST</strong> and <strong>RAPIDAPI_KEY</strong> in
                <code>.env</code>, then add match IDs to <code>services.cricbuzz.match_ids</code>.
            </div>
        <?php endif; ?>

        <?php if(! $feedEnabled): ?>
            <div class="alert alert--info">The cricket feed is currently disabled by the administrator.</div>
        <?php else: ?>
            <div class="cricket-board"
                 data-cricket-board
                 data-endpoint="<?php echo e(route('cricket.board')); ?>"
                 data-mode="full">
                <div class="cricket-skeleton" aria-hidden="true">
                    <div class="cricket-skeleton__card"></div>
                    <div class="cricket-skeleton__card"></div>
                    <div class="cricket-skeleton__card"></div>
                </div>
            </div>
        <?php endif; ?>

        <p class="hint mt-20">
            Updates automatically every 30 seconds while this tab is visible. Scores are cached for a few
            seconds and the API calls are throttled to stay inside the RapidAPI plan limits.
        </p>
    </div>
<?php $__env->stopSection(); ?>

<?php if($feedEnabled): ?>
    <?php $__env->startPush('scripts'); ?>
        <script src="<?php echo e(asset('js/cricket.js')); ?>?v=1" defer></script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/cricket/index.blade.php ENDPATH**/ ?>