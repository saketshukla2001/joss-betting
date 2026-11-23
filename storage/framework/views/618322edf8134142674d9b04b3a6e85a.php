<?php $__env->startSection('title', 'Bet '.$bet->bet_code); ?>
<?php $__env->startSection('page-title', 'Bet ticket'); ?>
<?php $__env->startSection('page-subtitle', $bet->bet_code.' · '.$bet->created_at->format('d M Y, h:i A')); ?>

<?php $__env->startSection('content'); ?>
    <div class="admin-page-head">
        <div>
            <span class="badge badge--<?php echo e($bet->status_class); ?>" style="margin-bottom:6px"><?php echo e($bet->status_label); ?></span>
            <h1><?php echo e($bet->bet_code); ?></h1>
            <p><?php echo e($bet->match_label); ?> · <?php echo e($bet->market_name); ?></p>
        </div>
        <div class="actions-row">
            <a class="btn btn--outline btn--sm" href="<?php echo e(route('admin.matches.show', $bet->match_id)); ?>">Open match control</a>
            <a class="btn btn--ghost btn--sm" href="<?php echo e(route('admin.bets.index')); ?>">← All bets</a>
        </div>
    </div>

    <div class="admin-grid-2">
        <div class="admin-card">
            <div class="admin-card__head"><h3>Ticket details</h3></div>
            <div class="admin-card__body">
                <ul class="info-list">
                    <li><span>Ticket code</span><strong class="ticket-code"><?php echo e($bet->bet_code); ?></strong></li>
                    <li><span>Status</span><strong><span class="badge badge--<?php echo e($bet->status_class); ?>"><?php echo e($bet->status_label); ?></span></strong></li>
                    <li><span>Sport</span><strong><?php echo e($bet->sport_name); ?></strong></li>
                    <li><span>League</span><strong><?php echo e($bet->league); ?></strong></li>
                    <li><span>Match</span><strong><?php echo e($bet->match_label); ?></strong></li>
                    <li><span>Market</span><strong><?php echo e($bet->market_name); ?></strong></li>
                    <li><span>Selection</span><strong><?php echo e($bet->selection_name); ?></strong></li>
                    <li><span>Odds at placement</span><strong class="text-lime"><?php echo e(odds($bet->odds)); ?></strong></li>
                    <li><span>Stake</span><strong><?php echo e(money($bet->stake)); ?></strong></li>
                    <li><span>Potential return</span><strong><?php echo e(money($bet->potential_return)); ?></strong></li>
                    <li><span>Payout</span><strong><?php echo e(money($bet->payout)); ?></strong></li>
                    <li><span>Placed</span><strong><?php echo e($bet->created_at->format('d M Y, h:i A')); ?></strong></li>
                    <li><span>Settled</span><strong><?php echo e($bet->settled_at?->format('d M Y, h:i A') ?? '—'); ?></strong></li>
                    <li><span>IP address</span><strong><?php echo e($bet->ip_address ?? '—'); ?></strong></li>
                </ul>
            </div>
        </div>

        <div>
            <div class="admin-card">
                <div class="admin-card__head"><h3>Player</h3></div>
                <div class="admin-card__body">
                    <?php if($bet->user): ?>
                        <div class="user-cell mb-14">
                            <span class="avatar"><?php echo e($bet->user->initials); ?></span>
                            <span>
                                <strong><?php echo e($bet->user->name); ?></strong>
                                <span><?php echo e('@'.$bet->user->username); ?> · <?php echo e($bet->user->email); ?></span>
                            </span>
                        </div>
                        <ul class="info-list">
                            <li><span>Balance</span><strong><?php echo e(money($bet->user->balance)); ?></strong></li>
                            <li><span>Exposure</span><strong><?php echo e(money($bet->user->exposure)); ?></strong></li>
                            <li><span>Status</span><strong><?php echo e(ucfirst($bet->user->status)); ?></strong></li>
                        </ul>
                        <a class="btn btn--outline btn--block btn--sm mt-14" href="<?php echo e(route('admin.users.show', $bet->user)); ?>">Open player profile</a>
                    <?php else: ?>
                        <p class="text-muted">Player account no longer exists.</p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if($bet->status === 'pending'): ?>
                <div class="admin-card mt-20">
                    <div class="admin-card__head"><h3>Edit bet</h3></div>
                    <div class="admin-card__body">
                        <form method="POST" action="<?php echo e(route('admin.bets.update', $bet)); ?>">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <div class="field">
                                <label for="selection_id">Selection</label>
                                <select class="select" id="selection_id" name="selection_id">
                                    <?php $__currentLoopData = $marketOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $market): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <optgroup label="<?php echo e($market->name); ?><?php echo e($market->status !== 'open' ? ' · '.strtoupper($market->status) : ''); ?>">
                                            <?php $__currentLoopData = $market->selections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($option->id); ?>"
                                                        data-odds="<?php echo e($option->odds); ?>"
                                                        <?php if($option->id === $bet->selection_id): echo 'selected'; endif; ?>
                                                        <?php if($market->status !== 'open' || $option->status !== 'active'): echo 'disabled'; endif; ?>>
                                                    <?php echo e($option->name); ?> @ <?php echo e(odds($option->odds)); ?><?php echo e($option->status !== 'active' ? ' · '.strtoupper($option->status) : ''); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </optgroup>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="form-grid">
                                <div class="field">
                                    <label for="stake">Stake (₹)</label>
                                    <input class="input" id="stake" name="stake" type="number" step="0.01" min="1"
                                           value="<?php echo e(old('stake', $bet->stake)); ?>" required>
                                </div>
                                <div class="field">
                                    <label for="odds">Odds</label>
                                    <input class="input" id="odds" name="odds" type="number" step="0.01" min="1.01"
                                           value="<?php echo e(old('odds', $bet->odds)); ?>" required>
                                </div>
                            </div>
                            <button class="btn btn--primary btn--block btn--sm mt-8" type="submit"
                                    data-confirm="Update this ticket? The player's balance will be adjusted by the stake difference.">
                                Save changes
                            </button>
                        </form>
                        <p class="hint mt-8">
                            Selection can only move to another open option in the <strong>same match</strong>.
                            Changing the stake adjusts the player's balance and exposure; potential return is
                            recalculated as stake × odds. Every change is written to the audit log and the
                            ticket snapshot is rewritten, so all screens and reports show the corrected bet.
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <div class="admin-card mt-20">
                    <div class="admin-card__head"><h3>Edit bet</h3></div>
                    <div class="admin-card__body">
                        <p class="text-muted" style="font-size:.8rem">
                            This ticket is already <?php echo e($bet->status_label); ?> — editing is locked. Void or re-place
                            the bet from the match control screen if a correction is needed.
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="admin-card mt-20">
                <div class="admin-card__head"><h3>Ledger entries</h3></div>
                <div class="admin-card__body">
                    <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="bet-mini">
                            <div class="bet-mini__main">
                                <div class="bet-mini__match"><?php echo e($transaction->type_label); ?></div>
                                <div class="bet-mini__sel"><?php echo e($transaction->created_at->format('d M, h:i A')); ?> · <?php echo e($transaction->status_label); ?></div>
                            </div>
                            <div class="bet-mini__right">
                                <div class="bet-mini__stake <?php echo e($transaction->amount >= 0 ? 'text-green' : 'text-red'); ?>"><?php echo e($transaction->amount >= 0 ? '+' : ''); ?><?php echo e(money($transaction->amount)); ?></div>
                                <span class="sub" style="margin:0">Bal <?php echo e(money($transaction->balance_after)); ?></span>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-muted" style="font-size:.8rem">No ledger entries linked to this ticket.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    (function () {
        var selection = document.getElementById('selection_id');
        var odds = document.getElementById('odds');
        if (!selection || !odds) return;

        selection.addEventListener('change', function () {
            var option = selection.options[selection.selectedIndex];
            var value = option && option.dataset ? option.dataset.odds : null;
            if (value && parseFloat(value) > 0) odds.value = value;
        });
    })();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/bets/show.blade.php ENDPATH**/ ?>