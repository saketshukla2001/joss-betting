<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $user = auth()->user();
        $matchesToShow = $liveMatches->concat($upcomingMatches)->take(4);
    ?>

    <div class="container">
        <div class="dash-welcome">
            <div>
                <span class="eyebrow">Player dashboard</span>
                <h1 class="mt-8">Welcome back, <?php echo e(\Illuminate\Support\Str::of($user->name)->explode(' ')->first()); ?>.</h1>
                <p>Your wallet, open bets and live markets — all in one place.</p>
            </div>
            <div class="quick-actions">
                <a class="btn btn--primary btn--sm" href="<?php echo e(route('sports.index')); ?>">Browse sports</a>
                <a class="btn btn--outline btn--sm" href="<?php echo e(route('bets.index')); ?>">My bets</a>
                <a class="btn btn--dark btn--sm" href="<?php echo e(route('wallet.index')); ?>">Wallet</a>
            </div>
        </div>

        <div class="stat-grid">
            <div class="stat stat--lime">
                <div class="stat__label">Available balance</div>
                <div class="stat__value" data-balance><?php echo e(money($user->balance)); ?></div>
                <div class="stat__meta">Ready to stake</div>
            </div>
            <div class="stat">
                <div class="stat__label">Exposure</div>
                <div class="stat__value" data-exposure><?php echo e(money($user->exposure)); ?></div>
                <div class="stat__meta">Locked in open bets</div>
            </div>
            <div class="stat">
                <div class="stat__label">Open bets</div>
                <div class="stat__value"><?php echo e(number_format($openBets)); ?></div>
                <div class="stat__meta"><?php echo e(number_format($wonBets)); ?> won · <?php echo e(number_format($lostBets)); ?> lost</div>
            </div>
            <div class="stat">
                <div class="stat__label">Win rate</div>
                <div class="stat__value"><?php echo e(number_format($winRate, 1)); ?>%</div>
                <div class="stat__meta"><?php echo e(money($totalWon)); ?> returns earned</div>
            </div>
        </div>

        <div class="dash-grid mt-20">
            <div>
                <div class="card mb-20">
                    <div class="card__head">
                        <div>
                            <h3>Live &amp; upcoming</h3>
                            <p>Tap odds to build your bet slip</p>
                        </div>
                        <a class="link-more" href="<?php echo e(route('sports.index')); ?>">All events →</a>
                    </div>
                    <div class="card__body">
                        <?php if($matchesToShow->isEmpty()): ?>
                            <div class="empty">
                                <div class="empty__ico">🏟️</div>
                                <strong>No open matches</strong>
                                <p>Check back soon for new events.</p>
                            </div>
                        <?php else: ?>
                            <div class="match-grid match-grid--2">
                                <?php $__currentLoopData = $matchesToShow; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $match): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php echo $__env->make('partials.match-card', ['match' => $match], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if(! empty($cricketFeed['matches'])): ?>
                    <div class="card mb-20">
                        <div class="card__head">
                            <div>
                                <h3>Cricket · API feed</h3>
                                <p>Match data &amp; scores from <?php echo e($cricketFeed['source'] ?? 'OpenCricketAPI'); ?></p>
                            </div>
                        </div>
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr><th>Match</th><th>Score</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $cricketFeed['matches']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $apiMatch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo e($apiMatch['label']); ?></strong>
                                                <span class="sub">
                                                    <?php echo e($apiMatch['competition'] ?? 'Cricket'); ?><?php if($apiMatch['format']): ?> · <?php echo e($apiMatch['format']); ?><?php endif; ?> <?php if($apiMatch['date']): ?> · <?php echo e($apiMatch['date']); ?><?php endif; ?>
                                                </span>
                                            </td>
                                            <td class="mono">
                                                <?php $__empty_1 = true; $__currentLoopData = $apiMatch['scores']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $score): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                    <div><?php echo e($score['team'] ? $score['team'].' ' : ''); ?><?php echo e($score['score']); ?></div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                    <span class="sub">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge--<?php echo e($apiMatch['status'] === 'Completed' ? 'success' : 'info'); ?>"><?php echo e($apiMatch['status']); ?></span>
                                                <?php if($apiMatch['result']): ?>
                                                    <span class="sub"><?php echo e($apiMatch['result']); ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php elseif(! empty($cricketFeed['error'])): ?>
                    <div class="card mb-20">
                        <div class="card__body">
                            <p class="text-muted" style="font-size:.8rem;margin:0">
                                Cricket feed unavailable right now — the live markets above are unaffected.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card__head">
                        <div>
                            <h3>Recent bets</h3>
                            <p>Your latest tickets</p>
                        </div>
                        <a class="link-more" href="<?php echo e(route('bets.index')); ?>">View all →</a>
                    </div>
                    <?php if($recentBets->isEmpty()): ?>
                        <div class="empty">
                            <div class="empty__ico">🎯</div>
                            <strong>No bets yet</strong>
                            <p>Your first ticket will show up here.</p>
                            <a class="btn btn--primary btn--sm mt-14" href="<?php echo e(route('sports.index')); ?>">Find a match</a>
                        </div>
                    <?php else: ?>
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr><th>Ticket</th><th>Selection</th><th>Stake</th><th>Status</th><th></th></tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $recentBets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><span class="ticket-code"><?php echo e($bet->bet_code); ?></span><span class="sub"><?php echo e($bet->match_label); ?></span></td>
                                            <td><?php echo e($bet->selection_name); ?> <span class="sub"><?php echo e($bet->market_name); ?> @ <?php echo e(odds($bet->odds)); ?></span></td>
                                            <td class="mono"><?php echo e(money($bet->stake)); ?></td>
                                            <td><span class="badge badge--<?php echo e($bet->status_class); ?>"><?php echo e($bet->status_label); ?></span></td>
                                            <td class="text-right"><a class="btn btn--ghost btn--sm" href="<?php echo e(route('bets.show', $bet)); ?>">View</a></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <aside>
                <div class="card side-card">
                    <div class="card__head"><h3>Wallet</h3></div>
                    <div class="card__body">
                        <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;font-weight:700">Available balance</div>
                        <div class="text-lime fw-800 mt-8" style="font-size:1.9rem;letter-spacing:-.03em" data-balance><?php echo e(money($user->balance)); ?></div>
                        <div class="flex-between mt-14">
                            <span class="text-muted" style="font-size:.78rem">In play (exposure)</span>
                            <strong style="font-size:.85rem" data-exposure><?php echo e(money($user->exposure)); ?></strong>
                        </div>
                        <div class="flex-between mt-8">
                            <span class="text-muted" style="font-size:.78rem">Lifetime staked</span>
                            <strong style="font-size:.85rem"><?php echo e(money($totalStaked)); ?></strong>
                        </div>
                        <div class="flex gap-8 mt-14">
                            <a class="btn btn--primary btn--sm w-full" href="<?php echo e(route('wallet.index')); ?>">Deposit</a>
                            <a class="btn btn--outline btn--sm w-full" href="<?php echo e(route('wallet.index')); ?>#withdraw">Withdraw</a>
                        </div>
                    </div>
                </div>

                <div class="card side-card">
                    <div class="card__head">
                        <h3>Wallet activity</h3>
                        <a class="link-more" href="<?php echo e(route('wallet.index')); ?>">All</a>
                    </div>
                    <div class="card__body">
                        <?php $__empty_1 = true; $__currentLoopData = $recentTransactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="tx-mini">
                                <div class="tx-mini__ico"><?php echo e($transaction->type_icon); ?></div>
                                <div class="tx-mini__main">
                                    <div class="tx-mini__title"><?php echo e($transaction->type_label); ?></div>
                                    <div class="tx-mini__date"><?php echo e($transaction->created_at->format('d M, h:i A')); ?></div>
                                </div>
                                <div class="tx-mini__amount <?php echo e($transaction->amount >= 0 ? 'text-green' : 'text-red'); ?>">
                                    <?php echo e($transaction->amount >= 0 ? '+' : ''); ?><?php echo e(money($transaction->amount)); ?>

                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="empty" style="padding:20px 8px">
                                <strong>No activity yet</strong>
                                <p>Deposits and bets will appear here.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card side-card" style="background:linear-gradient(150deg,rgba(201,255,69,.13),rgba(20,33,56,.9));border-color:rgba(201,255,69,.2)">
                    <div class="card__body">
                        <div style="font-size:1.5rem;margin-bottom:8px">🛡️</div>
                        <h3 style="font-size:.95rem">Play it safe</h3>
                        <p class="text-muted" style="font-size:.78rem;margin-bottom:0">Set a budget before you bet. Never chase losses. 18+ only.</p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/dashboard.blade.php ENDPATH**/ ?>