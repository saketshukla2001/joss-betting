<?php $__env->startSection('title', 'My bets'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container">
        <div class="page-head">
            <div>
                <span class="eyebrow">History</span>
                <h1 class="mt-8">My bets</h1>
                <p>Every ticket, live or settled, with the exact odds you locked in.</p>
            </div>
            <a class="btn btn--primary" href="<?php echo e(route('sports.index')); ?>">Place a bet</a>
        </div>

        <div class="stat-grid mb-20">
            <div class="stat">
                <div class="stat__label">Total tickets</div>
                <div class="stat__value"><?php echo e(number_format($summary['total'])); ?></div>
            </div>
            <div class="stat">
                <div class="stat__label">Open</div>
                <div class="stat__value text-amber"><?php echo e(number_format($summary['open'])); ?></div>
                <div class="stat__meta"><?php echo e(money(auth()->user()->exposure)); ?> exposure</div>
            </div>
            <div class="stat">
                <div class="stat__label">Staked</div>
                <div class="stat__value"><?php echo e(money($summary['staked'])); ?></div>
            </div>
            <div class="stat stat--lime">
                <div class="stat__label">Returns</div>
                <div class="stat__value"><?php echo e(money($summary['returns'])); ?></div>
                <div class="stat__meta"><?php echo e($summary['won']); ?> winning tickets</div>
            </div>
        </div>

        <div class="chip-row mb-14">
            <?php $__currentLoopData = ['' => 'All', 'pending' => 'Open', 'won' => 'Won', 'lost' => 'Lost', 'void' => 'Void']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a class="chip <?php echo e($status === $value ? 'active' : ''); ?>" href="<?php echo e(route('bets.index', array_filter(['status' => $value, 'q' => $search]))); ?>"><?php echo e($label); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <form class="card filter-bar mb-20" method="GET" action="<?php echo e(route('bets.index')); ?>">
            <?php if($status): ?><input type="hidden" name="status" value="<?php echo e($status); ?>"><?php endif; ?>
            <input class="input search" type="search" name="q" value="<?php echo e($search); ?>" placeholder="Search ticket, match or selection…">
            <button class="btn btn--primary" type="submit">Search</button>
            <?php if($search): ?>
                <a class="btn btn--ghost" href="<?php echo e(route('bets.index', array_filter(['status' => $status]))); ?>">Clear</a>
            <?php endif; ?>
        </form>

        <div class="card">
            <?php if($bets->isEmpty()): ?>
                <div class="empty">
                    <div class="empty__ico">🎫</div>
                    <strong>No tickets found</strong>
                    <p>Try a different filter, or place your first bet.</p>
                    <a class="btn btn--primary btn--sm mt-14" href="<?php echo e(route('sports.index')); ?>">Browse matches</a>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="data-table table--stack">
                        <thead>
                            <tr>
                                <th>Ticket</th>
                                <th>Match / selection</th>
                                <th>Odds</th>
                                <th>Stake</th>
                                <th>Return</th>
                                <th>Status</th>
                                <th>Placed</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $bets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td data-label="Ticket">
                                        <span class="ticket-code"><?php echo e($bet->bet_code); ?></span>
                                        <span class="sub"><?php echo e($bet->sport_name); ?></span>
                                    </td>
                                    <td data-label="Match / selection">
                                        <span class="strong"><?php echo e($bet->match_label); ?></span>
                                        <span class="sub"><?php echo e($bet->selection_name); ?> · <?php echo e($bet->market_name); ?></span>
                                    </td>
                                    <td data-label="Odds" class="mono"><?php echo e(odds($bet->odds)); ?></td>
                                    <td data-label="Stake" class="mono"><?php echo e(money($bet->stake)); ?></td>
                                    <td data-label="Return" class="mono">
                                        <?php if($bet->status === 'won'): ?>
                                            <span class="text-green"><?php echo e(money($bet->payout)); ?></span>
                                        <?php elseif($bet->status === 'lost'): ?>
                                            <span class="text-muted">—</span>
                                        <?php elseif($bet->status === 'void'): ?>
                                            <span class="text-muted"><?php echo e(money($bet->stake)); ?></span>
                                        <?php else: ?>
                                            <span class="text-lime"><?php echo e(money($bet->potential_return)); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Status"><span class="badge badge--<?php echo e($bet->status_class); ?>"><?php echo e($bet->status_label); ?></span></td>
                                    <td data-label="Placed"><span class="sub" style="margin:0"><?php echo e($bet->created_at->format('d M, h:i A')); ?></span></td>
                                    <td data-label="Action" class="text-right"><a class="btn btn--ghost btn--sm" href="<?php echo e(route('bets.show', $bet)); ?>">Details</a></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <?php echo e($bets->links('partials.pagination')); ?>

            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/bets/index.blade.php ENDPATH**/ ?>