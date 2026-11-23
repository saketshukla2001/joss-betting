<?php $__env->startSection('title', 'Cricbuzz · '.$label); ?>

<?php $__env->startSection('content'); ?>
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo e(route('cricbuzz-test.index')); ?>">Cricbuzz API test</a>
            <span class="sep">/</span>
            <span><?php echo e($label); ?></span>
        </div>

        <?php
            $status = $presentedLive['status'] ?? ($presentedCard['status'] ?? '');
            $isFinished = (bool) preg_match('/\bwon\b|drawn|abandoned|no result/i', $status);
        ?>

        <div class="page-head">
            <div>
                <h1><?php echo e($label); ?> <span class="badge <?php echo e($isFinished ? 'badge--muted' : 'badge--live'); ?>"><?php echo e($isFinished ? 'Result' : 'Live'); ?></span></h1>
                <p>
                    Cricbuzz match <code><?php echo e($matchId); ?></code> ·
                    <code>/miniscorecard</code> + <code>/scard</code>
                </p>
            </div>
            <div class="chip-row">
                <a class="btn btn--primary btn--sm" href="<?php echo e(route('cricbuzz-test.show', ['match' => $matchId, 'refresh' => 1])); ?>">Refresh</a>
                <a class="btn btn--ghost btn--sm" href="<?php echo e(route('cricbuzz-test.index')); ?>">← All matches</a>
            </div>
        </div>

        <?php $__currentLoopData = ['live' => 'Miniscorecard', 'card' => 'Scorecard']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $result = $$key; ?>
            <?php if(! $result['success']): ?>
                <div class="alert alert--error">
                    <strong><?php echo e($section); ?>:</strong> <?php echo e($result['error']); ?>

                    <?php if($result['status']): ?> <span class="text-muted">(HTTP <?php echo e($result['status']); ?><?php echo e($result['error_code'] ? ' · '.$result['error_code'] : ''); ?>)</span><?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <div class="card mb-20">
            <div class="card__head">
                <div>
                    <h3>Live now</h3>
                    <p><?php echo e($status ?: 'No status returned'); ?></p>
                </div>
                <?php if($presentedLive): ?>
                    <span class="badge <?php echo e($isFinished ? 'badge--muted' : 'badge--live'); ?>"><?php echo e($isFinished ? 'Result' : 'In play'); ?></span>
                <?php endif; ?>
            </div>

            <?php if(! $presentedLive): ?>
                <div class="card__body">
                    <p class="text-muted" style="font-size:.8rem;margin:0">Miniscorecard unavailable — see the error above.</p>
                </div>
            <?php else: ?>
                <div class="card__body">
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px">
                        <div>
                            <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.07em;font-weight:700">Innings</div>
                            <?php $__empty_1 = true; $__currentLoopData = $presentedLive['innings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div style="display:flex;justify-content:space-between;gap:10px;margin-top:6px">
                                    <span style="font-weight:700"><?php echo e($inning['team']); ?></span>
                                    <span class="mono">
                                        <?php echo e($inning['runs'] ?? '—'); ?><?php if($inning['wickets'] !== null): ?>/<?php echo e($inning['wickets']); ?><?php endif; ?>
                                        <?php if($inning['overs'] !== null): ?><span class="sub">(<?php echo e($inning['overs']); ?>)</span><?php endif; ?>
                                    </span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="text-muted" style="font-size:.78rem;margin:6px 0 0">Not started.</p>
                            <?php endif; ?>

                            <div style="display:flex;gap:14px;flex-wrap:wrap;font-size:.75rem;margin-top:12px" class="text-muted">
                                <?php if($presentedLive['crr'] !== null): ?><span>CRR <strong><?php echo e($presentedLive['crr']); ?></strong></span><?php endif; ?>
                                <?php if($presentedLive['rrr'] !== null): ?><span>RRR <strong><?php echo e($presentedLive['rrr']); ?></strong></span><?php endif; ?>
                                <?php if($presentedLive['target'] !== null): ?><span>Target <strong><?php echo e($presentedLive['target']); ?></strong></span><?php endif; ?>
                                <?php if($presentedLive['partnership']): ?><span>P'ship <strong><?php echo e($presentedLive['partnership']); ?></strong></span><?php endif; ?>
                            </div>
                            <?php if($presentedLive['last_wkt']): ?>
                                <p class="text-muted" style="font-size:.75rem;margin:8px 0 0">Last wicket: <?php echo e($presentedLive['last_wkt']); ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.07em;font-weight:700">At the crease</div>
                            <?php $__currentLoopData = [$presentedLive['striker'], $presentedLive['non_striker']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($bat): ?>
                                    <div style="display:flex;justify-content:space-between;gap:10px;margin-top:6px">
                                        <span><?php echo e($bat['name']); ?></span>
                                        <span class="mono"><?php echo e($bat['runs']); ?> <span class="sub">(<?php echo e($bat['balls']); ?>)</span></span>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            <?php if($presentedLive['bowler']): ?>
                                <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.07em;font-weight:700;margin-top:14px">Bowling</div>
                                <div style="display:flex;justify-content:space-between;gap:10px;margin-top:6px">
                                    <span><?php echo e($presentedLive['bowler']['name']); ?></span>
                                    <span class="mono"><?php echo e($presentedLive['bowler']['overs']); ?>-<?php echo e($presentedLive['bowler']['maidens']); ?>-<?php echo e($presentedLive['bowler']['runs']); ?>-<?php echo e($presentedLive['bowler']['wickets']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        
        <div class="card mb-20">
            <div class="card__head">
                <div>
                    <h3>Scorecard</h3>
                    <p><code>/scard</code></p>
                </div>
            </div>

            <?php if(! $presentedCard || empty($presentedCard['innings'])): ?>
                <div class="card__body">
                    <p class="text-muted" style="font-size:.8rem;margin:0">
                        <?php echo e($card['success'] ? 'No innings returned yet.' : 'Scorecard unavailable — see the error above.'); ?>

                    </p>
                </div>
            <?php else: ?>
                <div class="card__body">
                    <?php $__currentLoopData = $presentedCard['innings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $innings): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="mb-20">
                            <div style="display:flex;align-items:baseline;justify-content:space-between;gap:10px;margin-bottom:8px">
                                <h4 style="margin:0"><?php echo e($innings['team']); ?></h4>
                                <span class="mono">
                                    <?php echo e($innings['score']); ?>/<?php echo e($innings['wickets']); ?> <span class="sub">(<?php echo e($innings['overs']); ?> ov, RR <?php echo e($innings['runrate']); ?>)</span>
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

                            <?php if(is_array($innings['extras'])): ?>
                                <p class="text-muted" style="font-size:.75rem;margin-top:8px">
                                    Extras:
                                    <?php $__currentLoopData = $innings['extras']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(is_scalar($value) && $value !== null && $value !== 0): ?>
                                            <?php echo e(ucfirst(str_replace('_', ' ', $key))); ?> <?php echo e($value); ?><?php if(! $loop->last): ?> · <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card__head">
                <div>
                    <h3>Raw payloads</h3>
                    <p>Use this to confirm what the current RapidAPI plan actually returns</p>
                </div>
            </div>
            <div class="card__body">
                <details>
                    <summary style="cursor:pointer;font-size:.82rem">Miniscorecard JSON</summary>
                    <pre style="max-height:320px;overflow:auto;font-size:.7rem;line-height:1.45;white-space:pre-wrap"><?php echo e(json_encode($live['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)); ?></pre>
                </details>
                <details class="mt-14">
                    <summary style="cursor:pointer;font-size:.82rem">Scorecard JSON</summary>
                    <pre style="max-height:420px;overflow:auto;font-size:.7rem;line-height:1.45;white-space:pre-wrap"><?php echo e(json_encode($card['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)); ?></pre>
                </details>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/cricbuzz-test/show.blade.php ENDPATH**/ ?>