<?php $__env->startSection('title', 'Cricket matches'); ?>
<?php $__env->startSection('page-title', 'Cricket matches'); ?>
<?php $__env->startSection('page-subtitle', 'Discovered provider matches · read-only'); ?>

<?php $__env->startSection('content'); ?>
    <div class="admin-page-head">
        <div>
            <h1>Provider matches</h1>
            <p><?php echo e(count($rows)); ?> match(es) from Cricbuzz discovery.</p>
        </div>
        <div class="actions-row">
            <a class="btn btn--ghost btn--sm" href="<?php echo e(route('admin.cricket.index')); ?>">← Dashboard</a>
            <a class="btn btn--ghost btn--sm" href="<?php echo e(route('admin.cricket.health')); ?>">API health</a>
        </div>
    </div>

    <?php if(! empty($feedErrors)): ?>
        <div class="alert alert--error">
            <?php $__currentLoopData = $feedErrors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feedError): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div><?php echo e(ucfirst($feedError['type'] ?? 'feed')); ?>: <?php echo e($feedError['message']); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <form class="admin-card mb-20" method="GET" action="<?php echo e(route('admin.cricket.matches')); ?>">
        <div class="admin-card__body" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end">
            <div class="field" style="min-width:190px">
                <label for="bucket">Status</label>
                <select class="select" id="bucket" name="bucket">
                    <option value="">All</option>
                    <option value="live" <?php if($bucket === 'live'): echo 'selected'; endif; ?>>Live</option>
                    <option value="upcoming" <?php if($bucket === 'upcoming'): echo 'selected'; endif; ?>>Upcoming</option>
                    <option value="completed" <?php if($bucket === 'completed'): echo 'selected'; endif; ?>>Completed</option>
                </select>
            </div>
            <div class="field" style="flex:1;min-width:220px">
                <label for="q">Search</label>
                <input class="input" id="q" name="q" type="search" value="<?php echo e($query); ?>" placeholder="Team, series or provider ID">
            </div>
            <button class="btn btn--primary btn--sm" type="submit">Filter</button>
            <a class="btn btn--ghost btn--sm" href="<?php echo e(route('admin.cricket.matches')); ?>">Reset</a>
        </div>
    </form>

    <div class="admin-card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Match</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th>Visibility</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $id = (int) ($row['id'] ?? 0);
                            $isHidden = in_array($id, $hidden, true);
                            $label = $row['status_label'] ?? \App\Support\CricketStatus::normalize($row['status_raw'] ?? '')['label'];
                            $raw = $row['status_raw'] ?? '';
                        ?>
                        <tr>
                            <td class="mono"><?php echo e($id); ?></td>
                            <td>
                                <strong><?php echo e($row['label'] ?? 'Match #'.$id); ?></strong>
                                <span class="sub">
                                    <?php echo e($row['series'] ?? '—'); ?><?php if(! empty($row['format'])): ?> · <?php echo e($row['format']); ?><?php endif; ?>
                                    · <?php echo e(ucfirst($row['bucket'] ?? '—')); ?>

                                </span>
                                <details style="margin-top:4px">
                                    <summary style="cursor:pointer;font-size:.7rem" class="text-muted">Diagnostics</summary>
                                    <pre style="white-space:pre-wrap;font-size:.68rem;max-width:520px"><?php echo e(json_encode(array_intersect_key($row, array_flip(['id','label','series','format','desc','state','status_raw','status_label','status_group','start_date','venue','city','fetched_at'])), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)); ?></pre>
                                </details>
                            </td>
                            <td>
                                <strong><?php echo e($label); ?></strong>
                                <span class="sub"><?php echo e($raw ?: '—'); ?></span>
                            </td>
                            <td>
                                <?php echo e($row['fetched_at'] ? \Illuminate\Support\Carbon::parse($row['fetched_at'])->timezone(config('app.timezone'))->format('d M, h:i:s A') : '—'); ?>

                            </td>
                            <td>
                                <span class="badge <?php echo e($isHidden ? 'badge--danger' : 'badge--success'); ?>"><?php echo e($isHidden ? 'Hidden' : 'Visible'); ?></span>
                            </td>
                            <td>
                                <div style="display:flex;gap:6px;flex-wrap:wrap">
                                    <a class="btn btn--ghost btn--sm" href="<?php echo e(route('cricket.show', $id)); ?>" target="_blank" rel="noopener">Details</a>

                                    <form method="POST" action="<?php echo e(route('admin.cricket.visibility', $id)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button class="btn btn--ghost btn--sm" type="submit"><?php echo e($isHidden ? 'Show' : 'Hide'); ?></button>
                                    </form>

                                    <form method="POST" action="<?php echo e(route('admin.cricket.refresh', $id)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button class="btn btn--outline btn--sm" type="submit">Refresh</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="text-muted">No provider matches matched this filter.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="hint mt-14">
        Visibility only hides a match from the public cricket board; nothing is deleted. Refresh is limited to one request per
        match every 15 seconds. Provider data is never written into betting tables from this screen.
    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/cricket/matches.blade.php ENDPATH**/ ?>