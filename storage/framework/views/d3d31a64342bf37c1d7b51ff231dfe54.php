<?php $__env->startSection('title', $match->label); ?>
<?php $__env->startSection('page-title', 'Match control'); ?>
<?php $__env->startSection('page-subtitle', $match->league.' · '.$match->start_time->format('d M Y, h:i A')); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $marketBadge = fn ($status) => match ($status) {
            'open' => 'badge--success',
            'suspended' => 'badge--warning',
            'settled' => 'badge--muted',
            'void' => 'badge--danger',
            default => 'badge--muted',
        };
    ?>

    <div class="admin-page-head">
        <div>
            <div class="flex-center" style="gap:8px;margin-bottom:4px">
                <span class="badge badge--<?php echo e($match->status_class); ?>"><?php echo e($match->status_label); ?></span>
                <span class="text-muted"><?php echo e($match->sport?->icon); ?> <?php echo e($match->sport?->name); ?></span>
            </div>
            <h1><?php echo e($match->home_team); ?> <span class="text-muted" style="font-size:1rem">vs</span> <?php echo e($match->away_team); ?></h1>
            <p><?php echo e($match->league); ?> · <?php echo e($match->start_time->format('D, d M Y · h:i A')); ?> <?php if($match->result): ?> · Result: <strong class="text-lime"><?php echo e($match->result_label); ?></strong><?php endif; ?></p>
        </div>
        <div class="actions-row">
            <a class="btn btn--outline btn--sm" href="<?php echo e(route('admin.matches.edit', $match)); ?>">Edit match</a>
            <a class="btn btn--ghost btn--sm" href="<?php echo e(route('admin.matches.index')); ?>">← Matches</a>
            <?php if($match->status !== 'cancelled'): ?>
                <form method="POST" action="<?php echo e(route('admin.matches.void', $match)); ?>" data-confirm="Void this match and refund every open bet?">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn--danger btn--sm" type="submit">Void &amp; refund</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-stat-grid">
        <div class="stat">
            <div class="stat__label">Tickets</div>
            <div class="stat__value"><?php echo e(number_format($betStats['count'])); ?></div>
        </div>
        <div class="stat">
            <div class="stat__label">Pending bets</div>
            <div class="stat__value text-amber"><?php echo e(number_format($betStats['pending'])); ?></div>
        </div>
        <div class="stat">
            <div class="stat__label">Total stake</div>
            <div class="stat__value" style="font-size:1.25rem"><?php echo e(money($betStats['stake'])); ?></div>
        </div>
        <div class="stat">
            <div class="stat__label">Payouts</div>
            <div class="stat__value" style="font-size:1.25rem"><?php echo e(money($betStats['payout'])); ?></div>
        </div>
    </div>

    <div class="admin-grid-2 admin-grid-2--wide mt-20">
        <div>
            <details class="admin-card mb-20">
                <summary class="admin-card__head" style="cursor:pointer"><h3>+ Add market</h3><span class="text-muted" style="font-size:.72rem">Open a new betting market</span></summary>
                <div class="admin-card__body">
                    <form method="POST" action="<?php echo e(route('admin.markets.store', $match)); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="form-grid">
                            <div class="field">
                                <label for="market-name">Market name</label>
                                <input class="input" id="market-name" name="name" type="text" placeholder="e.g. Total Goals — Over/Under 2.5" required>
                            </div>
                            <div class="field">
                                <label>&nbsp;</label>
                                <label class="check">
                                    <input type="checkbox" name="auto_settle" value="1">
                                    <span>Auto-settle from match result (1X2 only)</span>
                                </label>
                            </div>
                        </div>
                        <button class="btn btn--primary btn--sm" type="submit">Add market</button>
                    </form>
                </div>
            </details>

            <div class="admin-card">
                <div class="admin-card__head">
                    <h3>Markets &amp; selections</h3>
                    <span class="badge badge--muted"><?php echo e($match->markets->count()); ?> markets</span>
                </div>
                <div class="admin-card__body">
                    <?php $__empty_1 = true; $__currentLoopData = $match->markets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $market): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="market-admin">
                            <div class="market-admin__head">
                                <div>
                                    <h4><?php echo e($market->name); ?></h4>
                                    <div class="flex-center" style="gap:6px;margin-top:4px;flex-wrap:wrap">
                                        <span class="badge <?php echo e($marketBadge($market->status)); ?>"><?php echo e($market->status_label); ?></span>
                                        <?php if($market->auto_settle): ?><span class="badge badge--info">Auto 1X2</span><?php endif; ?>
                                        <span class="text-muted" style="font-size:.68rem"><?php echo e($market->pending_bets_count); ?> pending · <?php echo e($market->all_bets_count); ?> total</span>
                                    </div>
                                </div>

                                <?php if(in_array($market->status, ['open', 'suspended'], true)): ?>
                                    <details class="edit-market">
                                        <summary>Edit market</summary>
                                        <form method="POST" action="<?php echo e(route('admin.markets.update', $market)); ?>" class="mt-8">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <div class="flex gap-8" style="flex-wrap:wrap">
                                                <input class="input" name="name" value="<?php echo e($market->name); ?>" required style="min-width:160px">
                                                <select class="select" name="status" style="min-width:110px">
                                                    <option value="open" <?php if($market->status === 'open'): echo 'selected'; endif; ?>>Open</option>
                                                    <option value="suspended" <?php if($market->status === 'suspended'): echo 'selected'; endif; ?>>Suspended</option>
                                                </select>
                                                <label class="check">
                                                    <input type="checkbox" name="auto_settle" value="1" <?php if($market->auto_settle): echo 'checked'; endif; ?>>
                                                    <span>Auto 1X2</span>
                                                </label>
                                                <button class="btn btn--primary btn--sm" type="submit">Save</button>
                                            </div>
                                        </form>
                                        <?php if($market->all_bets_count === 0): ?>
                                            <form method="POST" action="<?php echo e(route('admin.markets.destroy', $market)); ?>" class="mt-8" data-confirm="Delete this market?">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button class="btn btn--ghost btn--sm text-red" type="submit">Delete market</button>
                                            </form>
                                        <?php endif; ?>
                                    </details>
                                <?php endif; ?>
                            </div>

                            <div class="market-admin__body">
                                <?php if(in_array($market->status, ['open', 'suspended'], true)): ?>
                                    <?php $__currentLoopData = $market->selections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $selection): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="selection-row">
                                            <form method="POST" action="<?php echo e(route('admin.selections.update', $selection)); ?>" class="flex" style="flex:1;gap:6px;flex-wrap:wrap;align-items:center">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <input class="input input--name" name="name" value="<?php echo e($selection->name); ?>" required>
                                                <input class="input" name="odds" type="number" step="0.01" min="1.01" max="1000" value="<?php echo e(number_format($selection->odds, 2, '.', '')); ?>" style="width:80px" required>
                                                <input class="input" name="result_key" value="<?php echo e($selection->result_key); ?>" placeholder="key" style="width:76px">
                                                <select class="select" name="status" style="width:104px">
                                                    <option value="active" <?php if($selection->status === 'active'): echo 'selected'; endif; ?>>Active</option>
                                                    <option value="suspended" <?php if($selection->status === 'suspended'): echo 'selected'; endif; ?>>Suspended</option>
                                                </select>
                                                <button class="btn btn--primary btn--sm" type="submit">Save</button>
                                            </form>
                                            <?php if($selection->bets()->count() === 0): ?>
                                                <form method="POST" action="<?php echo e(route('admin.selections.destroy', $selection)); ?>" data-confirm="Delete selection <?php echo e($selection->name); ?>?">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button class="btn btn--ghost btn--sm text-red" type="submit" title="Delete">×</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                    <form method="POST" action="<?php echo e(route('admin.selections.store', $market)); ?>" class="selection-row mt-8">
                                        <?php echo csrf_field(); ?>
                                        <input class="input input--name" name="name" placeholder="New selection name" required>
                                        <input class="input" name="odds" type="number" step="0.01" min="1.01" max="1000" placeholder="Odds" style="width:80px" required>
                                        <input class="input" name="result_key" placeholder="home/away/draw" style="width:120px">
                                        <select class="select" name="status" style="width:104px">
                                            <option value="active">Active</option>
                                            <option value="suspended">Suspended</option>
                                        </select>
                                        <button class="btn btn--dark btn--sm" type="submit">+ Add</button>
                                    </form>
                                <?php else: ?>
                                    <?php $__currentLoopData = $market->selections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $selection): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="selection-row">
                                            <span class="selection-row__name"><?php echo e($selection->name); ?></span>
                                            <span class="mono text-lime"><?php echo e(odds($selection->odds)); ?></span>
                                            <?php if($selection->result === 'win'): ?>
                                                <span class="badge badge--success">Won</span>
                                            <?php elseif($selection->result === 'lose'): ?>
                                                <span class="badge badge--muted">Lost</span>
                                            <?php elseif($selection->result === 'void'): ?>
                                                <span class="badge badge--muted">Void</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>

                                <?php if(in_array($market->status, ['open', 'suspended'], true)): ?>
                                    <details class="settle-box">
                                        <summary>Settle market</summary>
                                        <div class="settle-form">
                                            <form method="POST" action="<?php echo e(route('admin.markets.settle', $market)); ?>" class="settle-form">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="mode" value="win">
                                                <select class="select" name="winning_selection_id" required>
                                                    <option value="">Winning selection…</option>
                                                    <?php $__currentLoopData = $market->selections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $selection): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($selection->id); ?>"><?php echo e($selection->name); ?> @ <?php echo e(odds($selection->odds)); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                                <button class="btn btn--primary btn--sm" type="submit" data-confirm="Settle this market and pay out winning bets?">Settle winner</button>
                                            </form>

                                            <form method="POST" action="<?php echo e(route('admin.markets.settle', $market)); ?>" data-confirm="Void this market and refund all pending stakes?">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="mode" value="void">
                                                <button class="btn btn--danger btn--sm" type="submit">Void &amp; refund</button>
                                            </form>
                                        </div>
                                        <p class="hint">Settling credits stake × odds to each winning ticket and releases exposure. Void refunds every pending stake.</p>
                                    </details>
                                <?php else: ?>
                                    <p class="hint mt-8">
                                        <?php if($market->status === 'settled'): ?>
                                            Settled. Winning selection: <strong><?php echo e($market->selections->firstWhere('result', 'win')?->name ?? '—'); ?></strong>.
                                        <?php else: ?>
                                            Market voided — pending stakes were refunded.
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="empty"><strong>No markets yet</strong><p>Add a market above to start taking bets.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <aside>
            <div class="admin-card">
                <div class="admin-card__head"><h3>Declare result</h3></div>
                <div class="admin-card__body">
                    <?php if($match->result): ?>
                        <div class="alert alert--success" style="margin-bottom:14px">
                            Current result: <strong><?php echo e($match->result_label); ?></strong>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo e(route('admin.matches.result', $match)); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="field">
                            <label for="result">Winning outcome</label>
                            <select class="select" id="result" name="result" required>
                                <option value="">Choose result…</option>
                                <option value="home" <?php if($match->result === 'home'): echo 'selected'; endif; ?>><?php echo e($match->home_team); ?></option>
                                <option value="draw" <?php if($match->result === 'draw'): echo 'selected'; endif; ?>>Draw</option>
                                <option value="away" <?php if($match->result === 'away'): echo 'selected'; endif; ?>><?php echo e($match->away_team); ?></option>
                            </select>
                        </div>
                        <div class="form-grid">
                            <div class="field">
                                <label for="home_score">Home score</label>
                                <input class="input" id="home_score" name="home_score" type="number" min="0" value="<?php echo e($match->home_score); ?>">
                            </div>
                            <div class="field">
                                <label for="away_score">Away score</label>
                                <input class="input" id="away_score" name="away_score" type="number" min="0" value="<?php echo e($match->away_score); ?>">
                            </div>
                        </div>
                        <button class="btn btn--primary btn--block btn--sm" type="submit" <?php if($match->status === 'cancelled'): echo 'disabled'; endif; ?>>Save result &amp; auto-settle</button>
                    </form>
                    <p class="hint mt-8">Only markets flagged “Auto 1X2” settle immediately. Use the settle controls for totals and other markets.</p>
                </div>
            </div>

            <div class="admin-card mt-20">
                <div class="admin-card__head"><h3>Match details</h3></div>
                <div class="admin-card__body">
                    <ul class="info-list">
                        <li><span>Sport</span><strong><?php echo e($match->sport?->name); ?></strong></li>
                        <li><span>League</span><strong><?php echo e($match->league); ?></strong></li>
                        <li><span>Start</span><strong><?php echo e($match->start_time->format('d M Y, h:i A')); ?></strong></li>
                        <li><span>Status</span><strong><?php echo e($match->status_label); ?></strong></li>
                        <li><span>Score</span><strong><?php echo e($match->score ?? '—'); ?></strong></li>
                        <li><span>Result</span><strong><?php echo e($match->result_label ?? '—'); ?></strong></li>
                        <li><span>Featured</span><strong><?php echo e($match->featured ? 'Yes' : 'No'); ?></strong></li>
                    </ul>
                    <a class="btn btn--outline btn--block btn--sm mt-14" href="<?php echo e(route('matches.show', $match)); ?>" target="_blank" rel="noopener">View public page ↗</a>
                </div>
            </div>
        </aside>
    </div>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Bets on this match</h3><a class="link-more" href="<?php echo e(route('admin.bets.index', ['q' => $match->home_team])); ?>">All bets →</a></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Ticket</th><th>Player</th><th>Market</th><th>Selection</th><th>Odds</th><th>Stake</th><th>Status</th><th>Placed</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $bets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><a class="ticket-code" href="<?php echo e(route('admin.bets.show', $bet)); ?>"><?php echo e($bet->bet_code); ?></a></td>
                            <td><?php echo e($bet->user?->username); ?></td>
                            <td><?php echo e($bet->market_name); ?></td>
                            <td><?php echo e($bet->selection_name); ?></td>
                            <td class="mono"><?php echo e(odds($bet->odds)); ?></td>
                            <td class="mono"><?php echo e(money($bet->stake)); ?></td>
                            <td><span class="badge badge--<?php echo e($bet->status_class); ?>"><?php echo e($bet->status_label); ?></span></td>
                            <td><span class="sub" style="margin:0"><?php echo e($bet->created_at->format('d M, h:i A')); ?></span></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="8"><div class="empty">No bets on this match yet.</div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/matches/show.blade.php ENDPATH**/ ?>