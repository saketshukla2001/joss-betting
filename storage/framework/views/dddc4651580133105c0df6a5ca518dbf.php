<?php $__env->startSection('title', $match->label); ?>
<?php $__env->startSection('page-title', 'Bet on match'); ?>
<?php $__env->startSection('page-subtitle', $match->league.' · '.$match->start_time->format('d M Y, h:i A')); ?>

<?php $__env->startSection('content'); ?>
    <div class="admin-page-head">
        <div>
            <h1><?php echo e($match->home_team); ?> vs <?php echo e($match->away_team); ?></h1>
            <p><?php echo e($match->league); ?> · <?php echo e($match->status_label); ?> · Your balance: <strong><?php echo e(number_format(auth()->user()->balance, 2)); ?></strong></p>
        </div>
        <div class="actions-row">
            <a class="btn btn--outline btn--sm" href="<?php echo e(route('agent.matches.index')); ?>">← Matches</a>
        </div>
    </div>

    <?php if($match->provider === 'cricbuzz'): ?>
        <?php echo $__env->make('partials.cricket-market-live', ['match' => $match], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <?php $minStake = max(1, (int) \App\Models\Setting::get('min_stake', 100)); ?>

    <?php $__empty_1 = true; $__currentLoopData = $match->markets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $market): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="classic-card" style="margin-bottom:14px">
            <div class="classic-card__head">
                <span><?php echo e(strtoupper($market->name)); ?></span>
                <span class="badge <?php echo e($market->status === 'open' ? 'badge--success' : 'badge--muted'); ?>" data-market-status="<?php echo e($market->id); ?>"><?php echo e(strtoupper($market->status)); ?></span>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Selection</th><th>Odds</th><th>Stake</th><th>Place bet</th></tr></thead>
                    <tbody>
                        <?php $__currentLoopData = $market->selections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $selection): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="strong"><?php echo e($selection->name); ?></td>
                                <td class="mono"><?php echo e(number_format($selection->odds, 2)); ?></td>
                                <td>
                                    <?php if($market->status === 'open' && $selection->status === 'active' && $match->isBettingOpen()): ?>
                                        <form id="bet-<?php echo e($selection->id); ?>" method="POST" action="<?php echo e(route('agent.bet.place')); ?>">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="selection_id" value="<?php echo e($selection->id); ?>">
                                            <input class="input" type="number" name="stake" min="<?php echo e($minStake); ?>" step="1" placeholder="Min <?php echo e($minStake); ?>" style="width:140px" required>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">Suspended</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($market->status === 'open' && $selection->status === 'active' && $match->isBettingOpen()): ?>
                                        <button class="btn btn--primary btn--sm" type="submit" form="bet-<?php echo e($selection->id); ?>" data-market-id="<?php echo e($market->id); ?>" data-confirm="Place this bet?">Place Bet</button>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="classic-card"><div class="empty"><strong>No markets open</strong><p>Betting is not available for this match.</p></div></div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.agent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/agent/matches/show.blade.php ENDPATH**/ ?>