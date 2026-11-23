<?php $__env->startSection('title', 'Cricket · '.$label); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $status = $presentedLive['status_raw'] ?? ($presentedLive['status'] ?? ($presentedCard['status'] ?? ''));
        $normalized = \App\Support\CricketStatus::normalize($status);
        $statusGroup = $presentedLive['status_group'] ?? $normalized['group'];
        $statusLabel = $presentedLive['status_label'] ?? $normalized['label'];
        $fetchedAt = $live['fetched_at'] ?? null;
        $statusStale = \App\Support\CricketStatus::isStale($fetchedAt);
        $statusBadge = match ($statusGroup) {
            'completed' => 'badge--muted',
            'upcoming' => 'badge--info',
            default => 'badge--live',
        };
    ?>

    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo e(route('home')); ?>">Home</a>
            <span class="sep">/</span>
            <a href="<?php echo e(route('cricket.index')); ?>">Cricket</a>
            <span class="sep">/</span>
            <span><?php echo e($label); ?></span>
        </div>

        <div class="page-head">
            <div>
                <h1>
                    <?php echo e($label); ?>

                    <span class="badge <?php echo e($statusBadge); ?>"><?php echo e($statusLabel); ?></span>
                    <?php if($statusStale): ?><span class="badge badge--warning">stale</span><?php endif; ?>
                </h1>
                <p>
                    <?php echo e($status ?: 'Match status unavailable'); ?>

                    <?php if($fetchedAt): ?>
                        · <span class="text-muted">updated <?php echo e(\Illuminate\Support\Carbon::parse($fetchedAt)->timezone(config('app.timezone'))->format('h:i:s A')); ?></span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="chip-row">
                <a class="btn btn--ghost btn--sm" href="<?php echo e(route('cricket.show', ['match' => $matchId, 'refresh' => 1])); ?>">Refresh</a>
                <a class="btn btn--ghost btn--sm" href="<?php echo e(route('cricket.index')); ?>">← All cricket</a>
            </div>
        </div>

        <?php if(! $live['success']): ?>
            <div class="alert alert--error">
                <strong>Live score:</strong> <?php echo e($live['error']); ?>

                <?php if($live['status']): ?> <span class="text-muted">(HTTP <?php echo e($live['status']); ?>)</span><?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if(! $card['success']): ?>
            <div class="alert alert--error">
                <strong>Scorecard:</strong> <?php echo e($card['error']); ?>

                <?php if($card['status']): ?> <span class="text-muted">(HTTP <?php echo e($card['status']); ?>)</span><?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="card mb-20">
            <div class="card__head">
                <div>
                    <h3>Match score</h3>
                    <p><?php echo e($status ?: 'Waiting for data'); ?></p>
                </div>
            </div>

            <?php if(! $presentedLive): ?>
                <div class="card__body">
                    <div class="empty">
                        <div class="empty__ico">🏏</div>
                        <strong>Score unavailable</strong>
                        <p>The live score feed did not return data. Try refreshing in a moment.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="card__body">
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px">
                        <div>
                            <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;font-weight:700">Innings</div>
                            <?php $__empty_1 = true; $__currentLoopData = $presentedLive['innings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="flex-between" style="margin-top:8px">
                                    <span class="fw-800"><?php echo e($inning['team']); ?></span>
                                    <span class="mono fw-800">
                                        <?php echo e($inning['runs'] ?? '—'); ?><?php if($inning['wickets'] !== null): ?>/<?php echo e($inning['wickets']); ?><?php endif; ?>
                                        <?php if($inning['overs'] !== null): ?><span class="sub">(<?php echo e($inning['overs']); ?> ov)</span><?php endif; ?>
                                    </span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="text-muted" style="font-size:.8rem;margin:8px 0 0">Score not started yet.</p>
                            <?php endif; ?>

                            <div class="chip-row mt-14">
                                <?php if($presentedLive['crr'] !== null): ?><span class="chip">CRR <?php echo e($presentedLive['crr']); ?></span><?php endif; ?>
                                <?php if($presentedLive['rrr'] !== null): ?><span class="chip">RRR <?php echo e($presentedLive['rrr']); ?></span><?php endif; ?>
                                <?php if($presentedLive['target'] !== null): ?><span class="chip">Target <?php echo e($presentedLive['target']); ?></span><?php endif; ?>
                                <?php if($presentedLive['partnership']): ?><span class="chip">P'ship <?php echo e($presentedLive['partnership']); ?></span><?php endif; ?>
                            </div>
                        </div>

                        <div>
                            <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;font-weight:700">At the crease</div>
                            <?php $__currentLoopData = [$presentedLive['striker'], $presentedLive['non_striker']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($bat): ?>
                                    <div class="flex-between" style="margin-top:8px">
                                        <span><?php echo e($bat['name']); ?></span>
                                        <span class="mono"><?php echo e($bat['runs']); ?> <span class="sub">(<?php echo e($bat['balls']); ?>)</span></span>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            <?php if($presentedLive['bowler']): ?>
                                <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;font-weight:700;margin-top:16px">Bowling</div>
                                <div class="flex-between" style="margin-top:8px">
                                    <span><?php echo e($presentedLive['bowler']['name']); ?></span>
                                    <span class="mono">
                                        <?php echo e($presentedLive['bowler']['overs']); ?>-<?php echo e($presentedLive['bowler']['maidens']); ?>-<?php echo e($presentedLive['bowler']['runs']); ?>-<?php echo e($presentedLive['bowler']['wickets']); ?>

                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card__head">
                <div>
                    <h3>Scorecard</h3>
                    <p>Batting and bowling by innings</p>
                </div>
            </div>

            <?php if(! $presentedCard || empty($presentedCard['innings'])): ?>
                <div class="card__body">
                    <div class="empty">
                        <div class="empty__ico">📋</div>
                        <strong>Scorecard not available yet</strong>
                        <p><?php echo e($card['success'] ? 'The API has no innings data for this match.' : 'The scorecard request failed — try refreshing.'); ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="card__body">
                    <?php $__currentLoopData = $presentedCard['innings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $innings): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="<?php echo e($loop->last ? '' : 'mb-20'); ?>">
                            <div class="flex-between mb-14" style="align-items:baseline;gap:10px">
                                <h4 style="margin:0"><?php echo e($innings['team']); ?></h4>
                                <span class="mono fw-800">
                                    <?php echo e($innings['score']); ?>/<?php echo e($innings['wickets']); ?>

                                    <span class="sub">(<?php echo e($innings['overs']); ?> ov · RR <?php echo e($innings['runrate']); ?>)</span>
                                </span>
                            </div>

                            <div class="table-wrap">
                                <table class="data-table">
                                    <thead>
                                        <tr><th>Batter</th><th>R</th><th>B</th><th>4s</th><th>6s</th><th>SR</th><th>Dismissal</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $innings['batsmen']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><strong><?php echo e($bat['name']); ?></strong></td>
                                                <td class="mono"><?php echo e($bat['runs']); ?></td>
                                                <td class="mono"><?php echo e($bat['balls']); ?></td>
                                                <td class="mono"><?php echo e($bat['fours']); ?></td>
                                                <td class="mono"><?php echo e($bat['sixes']); ?></td>
                                                <td class="mono"><?php echo e($bat['sr']); ?></td>
                                                <td class="text-muted"><?php echo e($bat['out'] ?: 'not out'); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="table-wrap mt-14">
                                <table class="data-table">
                                    <thead>
                                        <tr><th>Bowler</th><th>O</th><th>M</th><th>R</th><th>W</th><th>Econ</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $innings['bowlers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bwl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><strong><?php echo e($bwl['name']); ?></strong></td>
                                                <td class="mono"><?php echo e($bwl['overs']); ?></td>
                                                <td class="mono"><?php echo e($bwl['maidens']); ?></td>
                                                <td class="mono"><?php echo e($bwl['runs']); ?></td>
                                                <td class="mono"><?php echo e($bwl['wickets']); ?></td>
                                                <td class="mono"><?php echo e($bwl['economy']); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/cricket/show.blade.php ENDPATH**/ ?>