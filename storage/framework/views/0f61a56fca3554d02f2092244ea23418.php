<?php $__env->startSection('title', 'Market drafts'); ?>
<?php $__env->startSection('page-title', 'Market drafts'); ?>
<?php $__env->startSection('page-subtitle', 'Draft-only market configuration · nothing is published automatically'); ?>

<?php $__env->startSection('content'); ?>
    <div class="admin-page-head">
        <div>
            <h1>Market drafts</h1>
            <p>Prepare market ideas, review them, then create real markets manually in the existing match screen.</p>
        </div>
    </div>

    <form class="admin-card mb-20" method="POST" action="<?php echo e(route('admin.drafts.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="admin-card__head"><h3>New draft</h3></div>
        <div class="admin-card__body">
            <div class="form-grid">
                <div class="field">
                    <label for="match_id">Local match (optional)</label>
                    <select class="select" id="match_id" name="match_id">
                        <option value="">— provider-only draft —</option>
                        <?php $__currentLoopData = $matches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $match): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($match->id); ?>" <?php if((string) old('match_id') === (string) $match->id): echo 'selected'; endif; ?>>
                                #<?php echo e($match->id); ?> · <?php echo e($match->home_team); ?> vs <?php echo e($match->away_team); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="field">
                    <label for="provider_match_id">Provider match ID (Cricbuzz)</label>
                    <input class="input" id="provider_match_id" name="provider_match_id" type="text" maxlength="40" value="<?php echo e(old('provider_match_id')); ?>" placeholder="e.g. 165061">
                </div>
                <div class="field">
                    <label for="name">Market name</label>
                    <input class="input" id="name" name="name" type="text" maxlength="120" value="<?php echo e(old('name')); ?>" required placeholder="e.g. Top Batter">
                </div>
            </div>

            <div class="field mt-14">
                <label for="selections">Selections (one per line)</label>
                <textarea class="textarea" id="selections" name="selections" rows="4" placeholder="India&#10;England&#10;Draw"><?php echo e(old('selections')); ?></textarea>
            </div>

            <div class="field mt-14">
                <label for="notes">Notes</label>
                <input class="input" id="notes" name="notes" type="text" maxlength="1000" value="<?php echo e(old('notes')); ?>" placeholder="Why is this market being proposed?">
            </div>

            <button class="btn btn--primary btn--sm mt-14" type="submit">Create draft</button>
        </div>
    </form>

    <form class="admin-card mb-20" method="GET" action="<?php echo e(route('admin.drafts.index')); ?>">
        <div class="admin-card__body" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end">
            <div class="field" style="min-width:170px">
                <label for="status">Status</label>
                <select class="select" id="status" name="status">
                    <option value="">All</option>
                    <?php $__currentLoopData = ['draft' => 'Draft', 'review' => 'In review', 'approved' => 'Approved', 'rejected' => 'Rejected']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($value); ?>" <?php if($status === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="field" style="flex:1;min-width:220px">
                <label for="q">Search</label>
                <input class="input" id="q" name="q" type="search" value="<?php echo e($query); ?>" placeholder="Market, match or provider ID">
            </div>
            <button class="btn btn--primary btn--sm" type="submit">Filter</button>
            <a class="btn btn--ghost btn--sm" href="<?php echo e(route('admin.drafts.index')); ?>">Reset</a>
        </div>
    </form>

    <div class="admin-card">
        <div class="admin-card__head"><h3>Drafts</h3></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th>Match</th><th>Market</th><th>Selections</th><th>Status</th><th>Created</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $drafts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $draft): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $badge = match ($draft->status) {
                                'approved' => 'badge--success',
                                'rejected' => 'badge--danger',
                                'review' => 'badge--warning',
                                default => 'badge--info',
                            };
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo e($draft->match_label ?? 'Provider #'.$draft->provider_match_id); ?></strong>
                                <span class="sub">
                                    <?php echo e($draft->match_id ? 'local #'.$draft->match_id : 'no local match'); ?>

                                    <?php if($draft->provider_match_id): ?> · provider <?php echo e($draft->provider_match_id); ?> <?php endif; ?>
                                </span>
                            </td>
                            <td><?php echo e($draft->name); ?></td>
                            <td class="text-muted" style="max-width:280px"><?php echo e(implode(', ', $draft->selections ?? []) ?: '—'); ?></td>
                            <td><span class="badge <?php echo e($badge); ?>"><?php echo e($draft->status_label); ?></span></td>
                            <td><?php echo e($draft->created_at?->format('d M Y, h:i A')); ?><span class="sub"><?php echo e($draft->creator?->username ?? '—'); ?></span></td>
                            <td>
                                <div style="display:flex;gap:6px;flex-wrap:wrap">
                                    <?php if($draft->isEditable()): ?>
                                        <form method="POST" action="<?php echo e(route('admin.drafts.review', $draft)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="decision" value="approve">
                                            <button class="btn btn--ghost btn--sm" type="submit">Approve</button>
                                        </form>
                                        <form method="POST" action="<?php echo e(route('admin.drafts.review', $draft)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="decision" value="reject">
                                            <button class="btn btn--ghost btn--sm" type="submit">Reject</button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if($draft->status !== 'approved'): ?>
                                        <form method="POST" action="<?php echo e(route('admin.drafts.destroy', $draft)); ?>" data-confirm="Delete this draft?">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button class="btn btn--ghost btn--sm" type="submit">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </div>

                                <?php if($draft->isEditable()): ?>
                                    <details style="margin-top:6px">
                                        <summary style="cursor:pointer;font-size:.7rem" class="text-muted">Edit draft</summary>
                                        <form method="POST" action="<?php echo e(route('admin.drafts.update', $draft)); ?>" style="margin-top:8px">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PUT'); ?>
                                            <div class="field">
                                                <label>Name</label>
                                                <input class="input" name="name" value="<?php echo e($draft->name); ?>" required maxlength="120">
                                            </div>
                                            <div class="field mt-8">
                                                <label>Selections (one per line)</label>
                                                <textarea class="textarea" name="selections" rows="3"><?php echo e(implode("\n", $draft->selections ?? [])); ?></textarea>
                                            </div>
                                            <div class="field mt-8">
                                                <label>Notes</label>
                                                <input class="input" name="notes" value="<?php echo e($draft->notes); ?>">
                                            </div>
                                            <button class="btn btn--primary btn--sm mt-8" type="submit">Save draft</button>
                                        </form>
                                    </details>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="text-muted">No drafts yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="admin-card__body"><?php echo e($drafts->links('partials.pagination')); ?></div>
    </div>

    <p class="hint mt-14">
        Approving a draft only records the review. It does not create a market, selection, odds or bet — those remain a manual
        action on the match screen.
    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/drafts/index.blade.php ENDPATH**/ ?>