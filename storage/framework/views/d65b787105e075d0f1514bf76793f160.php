<?php $__env->startSection('title', $match->label); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $canBet = auth()->check() && auth()->user()->isClient();
        $bettingAllowed = auth()->guest() || $canBet;
        $statusBadge = match ($match->status) {
            'live' => 'badge--live',
            'upcoming' => 'badge--info',
            'settled' => 'badge--muted',
            'cancelled' => 'badge--danger',
            default => 'badge--muted',
        };
    ?>

    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo e(route('home')); ?>">Home</a>
            <span class="sep">/</span>
            <a href="<?php echo e(route('sports.show', $match->sport)); ?>"><?php echo e($match->sport?->name); ?></a>
            <span class="sep">/</span>
            <span><?php echo e($match->home_team); ?> vs <?php echo e($match->away_team); ?></span>
        </div>

        <?php if($match->provider === 'demo'): ?>
            <div class="alert alert--info">LIVE DEMO. This score is simulated. <?php if(auth()->user()?->exemptFromDemoWallet()): ?> This testing account is not charged. <?php else: ?> A bet is taken from your wallet and settled like any other match. <?php endif; ?></div>
        <?php elseif($match->status === 'cancelled'): ?>
            <div class="alert alert--error">This match was cancelled. All open bets on it have been refunded to player wallets.</div>
        <?php elseif($match->status === 'settled'): ?>
            <div class="alert alert--info">This match is settled. Winning bets have been paid and losing bets closed.</div>
        <?php endif; ?>

        <div class="match-layout">
            <div>
                <section class="match-hero">
                    <div class="match-hero__meta">
                        <span class="badge <?php echo e($statusBadge); ?>"><?php echo e($match->status_label); ?></span>
                        <?php if($match->provider === 'demo'): ?>
                            <span class="badge badge--info">LIVE DEMO</span>
                        <?php endif; ?>
                        <span class="text-muted"><?php echo e($match->sport?->icon); ?> <?php echo e($match->league); ?></span>
                        <span class="text-muted">·</span>
                        <span class="text-muted"><?php echo e($match->start_time->format('D, d M Y · h:i A')); ?></span>
                        <?php if($match->result): ?>
                            <span class="badge badge--lime">Result: <?php echo e($match->result_label); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="match-hero__teams">
                        <div class="match-hero__team">
                            <div class="match-hero__crest"><?php echo e(strtoupper(substr($match->home_team, 0, 2))); ?></div>
                            <strong><?php echo e($match->home_team); ?></strong>
                            <span>Home</span>
                        </div>
                        <div class="match-hero__score">
                            <?php if($match->home_score !== null && $match->away_score !== null): ?>
                                <strong><?php echo e($match->home_score); ?> : <?php echo e($match->away_score); ?></strong>
                                <span><?php echo e($match->status === 'live' ? 'Live score' : 'Final score'); ?></span>
                            <?php else: ?>
                                <strong>VS</strong>
                                <span><?php echo e($match->start_time->diffForHumans()); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="match-hero__team">
                            <div class="match-hero__crest"><?php echo e(strtoupper(substr($match->away_team, 0, 2))); ?></div>
                            <strong><?php echo e($match->away_team); ?></strong>
                            <span>Away</span>
                        </div>
                    </div>
                </section>

                <?php if(in_array($match->provider, ['cricbuzz', 'demo'], true)): ?>
                    <?php echo $__env->make('partials.cricket-market-live', ['match' => $match], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endif; ?>

                <div class="section-head">
                    <div>
                        <h2>Markets</h2>
                        <p><?php echo e($match->markets->count()); ?> markets · tap odds to add to your slip</p>
                    </div>
                    <?php if($canBet): ?>
                        <a class="link-more" href="<?php echo e(route('bets.index')); ?>">My bets →</a>
                    <?php endif; ?>
                </div>

                <?php $__empty_1 = true; $__currentLoopData = $match->markets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $market): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $marketBadge = match ($market->status) {
                            'open' => 'badge--success',
                            'suspended' => 'badge--warning',
                            'settled' => 'badge--muted',
                            'void' => 'badge--danger',
                            default => 'badge--muted',
                        };
                    ?>
                    <section class="card match-market">
                        <div class="card__head">
                            <div>
                                <h3><?php echo e($market->name); ?></h3>
                                <?php if($market->auto_settle): ?>
                                    <p>Auto-settles from the match result</p>
                                <?php endif; ?>
                            </div>
                            <span class="badge <?php echo e($marketBadge); ?>" data-market-status="<?php echo e($market->id); ?>"><?php echo e($market->status_label); ?></span>
                        </div>
                        <div class="card__body">
                            <div class="selection-grid <?php echo e($market->selections->count() === 2 ? 'selection-grid--2' : ''); ?>">
                                <?php $__currentLoopData = $market->selections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $selection): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $disabled = !$bettingAllowed || $market->status !== 'open' || $selection->status !== 'active'; ?>
                                    <button
                                        type="button"
                                        class="selection-btn"
                                        data-selection-id="<?php echo e($selection->id); ?>"
                                        data-odds-id="<?php echo e($selection->id); ?>"
                                        data-odds="<?php echo e(number_format($selection->odds, 2, '.', '')); ?>"
                                        data-selection-name="<?php echo e($selection->name); ?>"
                                        data-market-name="<?php echo e($market->name); ?>"
                                        data-match-label="<?php echo e($match->label); ?>"
                                        data-market-id="<?php echo e($market->id); ?>"
                                        data-match-id="<?php echo e($match->id); ?>"
                                        <?php if($disabled): ?> disabled <?php endif; ?>
                                    >
                                        <span class="selection-btn__name">
                                            <?php echo e($selection->name); ?>

                                            <?php if($selection->result === 'win'): ?>
                                                <span class="badge badge--success win-tag">Won</span>
                                            <?php elseif($selection->result === 'void'): ?>
                                                <span class="badge badge--muted win-tag">Void</span>
                                            <?php elseif($selection->status === 'suspended'): ?>
                                                <span class="badge badge--warning win-tag">Suspended</span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="selection-btn__odds" data-odds-value><?php echo e(odds($selection->odds)); ?></span>
                                    </button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </section>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="card empty">
                        <div class="empty__ico">📉</div>
                        <strong>No markets available</strong>
                        <p>Markets for this event have not been opened yet.</p>
                    </div>
                <?php endif; ?>
            </div>

            <aside>
                <div class="card side-card">
                    <div class="card__head"><h3>Match info</h3></div>
                    <div class="card__body">
                        <ul class="info-list">
                            <li><span>Sport</span><strong><?php echo e($match->sport?->icon); ?> <?php echo e($match->sport?->name); ?></strong></li>
                            <li><span>League</span><strong><?php echo e($match->league); ?></strong></li>
                            <li><span>Start</span><strong><?php echo e($match->start_time->format('d M, h:i A')); ?></strong></li>
                            <li><span>Status</span><strong><?php echo e($match->status_label); ?></strong></li>
                            <?php if($match->result): ?>
                                <li><span>Result</span><strong class="text-lime"><?php echo e($match->result_label); ?></strong></li>
                            <?php endif; ?>
                            <li><span>Markets</span><strong><?php echo e($match->markets->count()); ?></strong></li>
                        </ul>
                    </div>
                </div>

                <?php if(auth()->guard()->check()): ?>
                    <?php if($canBet): ?>
                        <div class="card side-card">
                            <div class="card__head"><h3>Your wallet</h3></div>
                            <div class="card__body">
                                <div class="flex-between">
                                    <span class="text-muted" style="font-size:.78rem">Available</span>
                                    <strong class="text-lime" data-balance><?php echo e(money(auth()->user()->balance)); ?></strong>
                                </div>
                                <div class="flex-between mt-8">
                                    <span class="text-muted" style="font-size:.78rem">Exposure</span>
                                    <strong data-exposure><?php echo e(money(auth()->user()->exposure)); ?></strong>
                                </div>
                                <a class="btn btn--outline btn--block mt-14" href="<?php echo e(route('wallet.index')); ?>">Manage wallet</a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="card side-card">
                        <div class="card__head"><h3>Want to bet?</h3></div>
                        <div class="card__body">
                            <p class="text-muted" style="font-size:.82rem">Sign in or create a free account to add this selection to your bet slip.</p>
                            <a class="btn btn--primary btn--block" href="<?php echo e(route('login')); ?>">Sign in</a>
                            <a class="btn btn--outline btn--block mt-8" href="<?php echo e(route('register')); ?>">Create account</a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card side-card">
                    <div class="card__head"><h3>Betting rules</h3></div>
                    <div class="card__body">
                        <ul class="info-list">
                            <li><span>Bet type</span><strong>Single</strong></li>
                            <li><span>Odds</span><strong>Decimal</strong></li>
                            <li><span>Min stake</span><strong><?php echo e(money($siteSettings['min_stake'])); ?></strong></li>
                            <li><span>Max stake</span><strong><?php echo e(money($siteSettings['max_stake'])); ?></strong></li>
                        </ul>
                        <p class="text-muted mt-14" style="font-size:.72rem;margin-bottom:0">
                            <?php if($match->provider === 'demo' && auth()->user()?->exemptFromDemoWallet()): ?>
                                Odds are locked when the ticket is placed. This testing account is settled from the simulated balls and is not charged.
                            <?php elseif($match->provider === 'demo'): ?>
                                Odds are locked when the bet is placed. The stake is taken from your wallet. Settlement credits the full return (stake × odds) or closes a losing ticket.
                            <?php else: ?>
                                Odds are locked when the bet is placed. If a market is voided, stakes are refunded automatically. Settlement credits the full return (stake × odds) to your wallet.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <?php if($related->isNotEmpty()): ?>
                    <div class="card side-card">
                        <div class="card__head"><h3>More <?php echo e($match->sport?->name); ?></h3></div>
                        <div class="card__body">
                            <?php $__currentLoopData = $related; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a class="bet-mini" href="<?php echo e(route('matches.show', $event)); ?>">
                                    <div class="bet-mini__main">
                                        <div class="bet-mini__match"><?php echo e($event->home_team); ?> vs <?php echo e($event->away_team); ?></div>
                                        <div class="bet-mini__sel"><?php echo e($event->league); ?> · <?php echo e($event->start_time->format('d M, h:i A')); ?></div>
                                    </div>
                                    <span class="badge <?php echo e($event->status === 'live' ? 'badge--live' : 'badge--muted'); ?>"><?php echo e($event->status === 'live' ? 'Live' : 'Open'); ?></span>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/matches/show.blade.php ENDPATH**/ ?>