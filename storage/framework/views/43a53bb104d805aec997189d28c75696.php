<?php if(setting_enabled('cricket_feed_enabled', true)): ?>
<?php
    $cricketBoardUrl = route('cricket.board');
    $mode = $mode ?? 'home';
?>

<div class="section-head">
    <div>
        <h2>Live cricket</h2>
        <p>Real scores from Cricbuzz — read-only, refreshing on its own.</p>
    </div>
    <a class="link-more" href="<?php echo e(route('cricket.index')); ?>">All cricket →</a>
</div>

<div class="cricket-board"
     data-cricket-board
     data-endpoint="<?php echo e($cricketBoardUrl); ?>"
     data-mode="<?php echo e($mode); ?>">
    <div class="cricket-skeleton" aria-hidden="true">
        <div class="cricket-skeleton__card"></div>
        <div class="cricket-skeleton__card"></div>
        <div class="cricket-skeleton__card"></div>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('630d161b-a78e-424c-afa4-19164b4c0845')): $__env->markAsRenderedOnce('630d161b-a78e-424c-afa4-19164b4c0845'); ?>
    <?php $__env->startPush('scripts'); ?>
        <script src="<?php echo e(asset('js/cricket.js')); ?>?v=1" defer></script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/partials/cricket-section.blade.php ENDPATH**/ ?>