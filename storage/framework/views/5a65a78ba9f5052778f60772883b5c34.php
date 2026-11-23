<?php $__env->startSection('title', 'Cricket fixture #'.$fixtureId); ?>

<?php $__env->startSection('content'); ?>
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo e(route('cricket-test.index')); ?>">Cricket API test</a>
            <span class="sep">/</span>
            <span>Fixture #<?php echo e($fixtureId); ?></span>
        </div>

        <div class="page-head">
            <div>
                <h1><?php echo e($match['name'] ?? 'Fixture #'.$fixtureId); ?> <span class="badge badge--muted">read-only</span></h1>
                <p>GET /fixtures/<?php echo e($fixtureId); ?> · Sportmonks Cricket API 2.0</p>
            </div>
            <div class="chip-row">
                <a class="btn btn--ghost btn--sm" href="<?php echo e(route('cricket-test.show', $fixtureId)); ?>">Refresh</a>
                <a class="btn btn--ghost btn--sm" href="<?php echo e(route('cricket-test.index')); ?>">← All fixtures</a>
            </div>
        </div>

        <?php if(! $result['success']): ?>
            <div class="alert alert--error">
                <?php echo e($result['error']); ?>

                <?php if($result['status']): ?>
                    <span class="text-muted">(HTTP <?php echo e($result['status']); ?><?php echo e($result['error_code'] ? ' · '.$result['error_code'] : ''); ?>)</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if($match): ?>
            <?php
                $statusLower = strtolower((string) $match['status']);
                $badge = $match['live'] ? 'badge--live' : (str_contains($statusLower, 'finish') ? 'badge--success' : 'badge--info');

                try {
                    $startsAt = $match['starting_at']
                        ? \Illuminate\Support\Carbon::parse($match['starting_at'])->timezone(config('app.timezone'))
                        : null;
                } catch (\Throwable) {
                    $startsAt = null;
                }
            ?>

            <div class="card mb-20">
                <div class="card__head">
                    <div>
                        <h3><?php echo e($match['name']); ?></h3>
                        <p>
                            <span class="badge <?php echo e($badge); ?>"><?php echo e($match['status']); ?></span>
                            <?php if($match['note']): ?> · <?php echo e($match['note']); ?> <?php endif; ?>
                        </p>
                    </div>
                </div>
                <div class="card__body">
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;font-size:.85rem">
                        <div><span class="text-muted">Tournament</span><br><strong><?php echo e($match['league'] ?? '—'); ?></strong></div>
                        <div><span class="text-muted">Season</span><br><strong><?php echo e($match['season'] ?? '—'); ?></strong></div>
                        <div><span class="text-muted">Stage / round</span><br><strong><?php echo e($match['stage'] ?? '—'); ?><?php if($match['round']): ?> · <?php echo e($match['round']); ?><?php endif; ?></strong></div>
                        <div><span class="text-muted">Format</span><br><strong><?php echo e($match['type'] ?? '—'); ?></strong></div>
                        <div><span class="text-muted">Venue</span><br><strong><?php echo e($match['venue'] ?? '—'); ?><?php if($match['city']): ?> · <?php echo e($match['city']); ?><?php endif; ?></strong></div>
                        <div><span class="text-muted">Starts</span><br><strong><?php echo e($startsAt?->format('d M Y, h:i A') ?? '—'); ?></strong></div>
                        <div><span class="text-muted">Toss won by team ID</span><br><strong><?php echo e($match['toss_won_team_id'] ?? '—'); ?></strong></div>
                        <div><span class="text-muted">Winner team ID</span><br><strong><?php echo e($match['winner_team_id'] ?? '—'); ?></strong></div>
                        <div><span class="text-muted">Overs played</span><br><strong><?php echo e($match['total_overs_played'] ?? '—'); ?></strong></div>
                    </div>
                </div>
            </div>

            <div class="card mb-20">
                <div class="card__head">
                    <div>
                        <h3>Scores</h3>
                        <p>From the <code>runs</code> include</p>
                    </div>
                </div>
                <?php if(empty($match['scores'])): ?>
                    <div class="card__body">
                        <p class="text-muted" style="font-size:.8rem;margin:0">
                            No score rows in this response. Add/enable the <code>runs</code> include, or check the plan restriction above.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr><th>Team</th><th>Runs</th><th>Wickets</th><th>Overs</th><th>Innings</th></tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $match['scores']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $score): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><strong><?php echo e($score['team']); ?></strong></td>
                                        <td class="mono"><?php echo e($score['runs']); ?></td>
                                        <td class="mono"><?php echo e($score['wickets'] ?? '—'); ?></td>
                                        <td class="mono"><?php echo e($score['overs'] ?? '—'); ?></td>
                                        <td><?php echo e($score['inning'] ?? '—'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card__head">
                    <div>
                        <h3>Raw payload</h3>
                        <p>What <?php echo e($result['degraded'] ? 'the fallback request' : 'the API'); ?> actually returned — use this to confirm available fields/plan limits</p>
                    </div>
                </div>
                <div class="card__body">
                    <?php if($result['degraded']): ?>
                        <div class="alert alert--warning" style="margin-bottom:12px">
                            Rich includes were refused by the API, so this page retried with core includes only.
                        </div>
                    <?php endif; ?>
                    <details>
                        <summary style="cursor:pointer;font-size:.82rem">Show raw JSON</summary>
                        <pre style="max-height:520px;overflow:auto;font-size:.72rem;line-height:1.5;white-space:pre-wrap"><?php echo e(json_encode($match['raw'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)); ?></pre>
                    </details>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/cricket-test/show.blade.php ENDPATH**/ ?>