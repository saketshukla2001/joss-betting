<?php $__env->startSection('title', $mode === 'inplay' ? 'Active Games' : 'Completed Games'); ?>

<?php $__env->startSection('content'); ?>
    <div class="classic-form-header">
        <span><?php echo e($mode === 'inplay' ? 'ACTIVE GAMES' : 'COMPLETED GAMES'); ?></span>
    </div>

    <div class="sports-games-panel">
        <form method="GET" action="<?php echo e($mode === 'inplay' ? route('admin.sports.inplay') : route('admin.sports.completed')); ?>" class="sports-filter">
            <div class="sports-date-group">
                <input type="date" name="start_date" value="<?php echo e(request('start_date')); ?>" aria-label="Start date">
                <span>→</span>
                <input type="date" name="end_date" value="<?php echo e(request('end_date')); ?>" aria-label="End date">
            </div>
            <div class="sports-search-wrap">
                <span class="sports-search-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.5 15.5L21 21"/></svg>
                </span>
                <input type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search by name or competition">
            </div>
            <button class="sports-filter-submit" type="submit">Filter</button>
        </form>

        <div class="table-wrap sports-table-wrap">
            <table class="data-table sports-games-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>NAME</th>
                        <th>
                            DATE &amp; TIME
                            <?php
                                $nextSort = ($sort ?? 'asc') === 'asc' ? 'desc' : 'asc';
                                $sortUrl = route($mode === 'inplay' ? 'admin.sports.inplay' : 'admin.sports.completed', array_merge(request()->except('sort', 'page'), ['sort' => $nextSort]));
                            ?>
                            <a class="sort-icon" href="<?php echo e($sortUrl); ?>" title="Sort by date">⇅</a>
                        </th>
                        <th>COMPETITION NAME</th>
                        <th>DETAILS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $matches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $match): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="sports-menu-cell">
                                <details class="game-menu">
                                    <summary aria-label="Match actions">⌄</summary>
                                    <div class="game-menu__popup">
                                        <?php if($mode === 'inplay'): ?>
                                            <a href="<?php echo e(route('admin.sports.match-bets', [$match, 'match'])); ?>">MATCH BET</a>
                                            <a href="<?php echo e(route('admin.sports.match-bets', [$match, 'session'])); ?>">SESSION BET</a>
                                            <a href="<?php echo e(route('admin.sports.match-bets', [$match, 'completed-session'])); ?>">COMPLETED SESSION</a>
                                            <a href="<?php echo e(route('admin.sports.match-bets', [$match, 'rejected'])); ?>">REJECTED BETS</a>
                                        <?php else: ?>
                                            <a href="<?php echo e(route('admin.sports.match-pl-report', $match)); ?>">MATCH AND SESSION PL REPORT</a>
                                            <a href="<?php echo e(route('admin.sports.match-pl', $match)); ?>">MATCH AND SESSION PL</a>
                                            <a href="<?php echo e(route('admin.sports.match-bets', [$match, 'completed-match'])); ?>">MATCH BET</a>
                                            <a href="<?php echo e(route('admin.sports.match-bets', [$match, 'session-all'])); ?>">SESSION BET</a>
                                            <a href="<?php echo e(route('admin.sports.match-bets', [$match, 'completed-session'])); ?>">COMPLETED SESSION</a>
                                            <a href="<?php echo e(route('admin.sports.match-bets', [$match, 'rejected'])); ?>">REJECTED BETS</a>
                                        <?php endif; ?>
                                    </div>
                                </details>
                            </td>
                            <td class="sports-match-name"><?php echo e(strtoupper($match->home_team)); ?> V <?php echo e(strtoupper($match->away_team)); ?></td>
                            <td class="nowrap"><?php echo e(strtoupper($match->start_time->format('m/d/Y h:i A'))); ?></td>
                            <td><?php echo e(strtoupper($match->league)); ?></td>
                            <td><a class="classic-view-btn" href="<?php echo e(route('admin.sports.match-view', $match)); ?>">View</a></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5"><div class="no-data"><span>NO DATA</span></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php echo e($matches->links('partials.pagination')); ?>

    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    var form = document.querySelector('.sports-filter');
    if (!form) return;
    form.querySelectorAll('input[type=date]').forEach(function (input) {
        input.addEventListener('change', function () { form.submit(); });
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/classic/sports.blade.php ENDPATH**/ ?>