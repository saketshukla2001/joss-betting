<div class="card mb-20" data-cricket-market-live data-match-id="<?php echo e($match->id); ?>" data-endpoint="<?php echo e(route('cricket.markets.feed')); ?>">
    <div class="card__head">
        <div>
            <h3>Live score</h3>
            <p data-cricket-score>Score updates from the sportsbook feed.</p>
        </div>
        <span class="badge badge--live" data-cricket-status>Live</span>
    </div>
</div>

<?php echo $__env->make('partials.cricket-market-script', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/partials/cricket-market-live.blade.php ENDPATH**/ ?>