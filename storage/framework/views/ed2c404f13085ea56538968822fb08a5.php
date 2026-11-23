<?php $__env->startSection('title', 'Match and Session PL Report'); ?>

<?php $__env->startSection('content'); ?>
    <div class="classic-form-header">
        <span>MATCH AND SESSION PL REPORT</span>
        <a class="classic-back-btn classic-back-btn--light" href="<?php echo e(route('admin.sports.completed')); ?>">Back</a>
    </div>

    <div class="statement-panel">
        <div class="limit-match-head">
            <strong><?php echo e(strtoupper($match->home_team)); ?> V <?php echo e(strtoupper($match->away_team)); ?></strong>
            <span><?php echo e(strtoupper($match->league)); ?> · <?php echo e(strtoupper($match->start_time->format('d-M-Y h:i A'))); ?></span>
        </div>

        <div class="admin-stat-grid" style="padding:14px">
            <div class="stat pl-stat"><div class="stat__label">Match PL</div><div class="stat__value"><?php echo e(number_format($totals['match'], 2)); ?></div></div>
            <div class="stat pl-stat"><div class="stat__label">Session PL</div><div class="stat__value"><?php echo e(number_format($totals['session'], 2)); ?></div></div>
            <div class="stat pl-stat"><div class="stat__label">Total PL</div><div class="stat__value"><?php echo e(number_format($totals['total'], 2)); ?></div></div>
        </div>

        <?php $__currentLoopData = [['MATCH BETS', $matchBets], ['SESSION BETS', $sessionBets]]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$sectionTitle, $sectionBets]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="pl-section">
                <h3><?php echo e($sectionTitle); ?></h3>
                <div class="table-wrap">
                    <table class="data-table pl-table">
                        <thead><tr><th>Date</th><th>Ticket</th><th>User</th><th>Market</th><th>Selection</th><th>Stake</th><th>Payout</th><th>PL</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $sectionBets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $pl = $bet->status === 'won' ? $bet->payout - $bet->stake : ($bet->status === 'lost' ? -$bet->stake : 0);
                                ?>
                                <tr>
                                    <td class="nowrap"><?php echo e(strtoupper($bet->created_at->format('d-M-Y h:i A'))); ?></td>
                                    <td class="mono"><?php echo e($bet->bet_code); ?></td>
                                    <td><?php echo e($bet->user?->username); ?></td>
                                    <td><?php echo e($bet->market_name); ?></td>
                                    <td><?php echo e($bet->selection_name); ?></td>
                                    <td class="mono"><?php echo e(number_format($bet->stake, 2)); ?></td>
                                    <td class="mono"><?php echo e(number_format($bet->payout, 2)); ?></td>
                                    <td class="mono <?php echo e($pl < 0 ? 'text-red' : ($pl > 0 ? 'text-green' : '')); ?>"><?php echo e(number_format($pl, 2)); ?></td>
                                    <td><span class="badge badge--<?php echo e($bet->status_class); ?>"><?php echo e(strtoupper($bet->status_label)); ?></span></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="9"><div class="no-data"><span>NO DATA</span></div></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/classic/match-pl-report.blade.php ENDPATH**/ ?>