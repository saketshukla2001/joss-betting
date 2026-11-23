<?php
    $canBet = auth()->check() && auth()->user()->isClient();
    $initials = function (string $name): string {
        return collect(explode(' ', $name))->filter()->take(2)
            ->map(fn ($word) => strtoupper(substr($word, 0, 1)))->implode('');
    };
    $bettingAllowed = auth()->guest() || $canBet;
?>
<article class="card match-card <?php echo e($match->status === 'live' ? 'match-card--live' : ''); ?>">
    <div class="match-card__top">
        <div class="league">
            <span class="sport-ico"><?php echo e($match->sport?->icon ?? '🏆'); ?></span>
            <span><?php echo e($match->league); ?></span>
        </div>
        <?php if($match->status === 'live'): ?>
            <span class="badge badge--live">LIVE <?php echo e($match->score ? '· '.$match->score : ''); ?></span>
        <?php else: ?>
            <span class="match-card__time"><?php echo e($match->start_time->format('d M · h:i A')); ?></span>
        <?php endif; ?>
    </div>

    <div class="teams">
        <div class="team">
            <div class="team__name">
                <span class="team__crest"><?php echo e($initials($match->home_team)); ?></span>
                <span><?php echo e($match->home_team); ?></span>
            </div>
            <?php if($match->home_score !== null): ?>
                <span class="team__score"><?php echo e($match->home_score); ?></span>
            <?php endif; ?>
        </div>
        <div class="team">
            <div class="team__name">
                <span class="team__crest"><?php echo e($initials($match->away_team)); ?></span>
                <span><?php echo e($match->away_team); ?></span>
            </div>
            <?php if($match->away_score !== null): ?>
                <span class="team__score"><?php echo e($match->away_score); ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="match-card__markets">
        <?php $__empty_1 = true; $__currentLoopData = $match->markets->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $market): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="market-row">
                <div class="market-row__label"><?php echo e($market->name); ?></div>
                <div class="odds-grid <?php echo e($market->selections->count() === 2 ? 'odds-grid--2' : ''); ?>">
                    <?php $__currentLoopData = $market->selections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $selection): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $isDisabled = !$bettingAllowed || $market->status !== 'open' || $selection->status !== 'active'; ?>
                        <button
                            type="button"
                            class="odds-btn"
                            data-selection-id="<?php echo e($selection->id); ?>"
                            data-odds-id="<?php echo e($selection->id); ?>"
                            data-odds="<?php echo e(number_format($selection->odds, 2, '.', '')); ?>"
                            data-selection-name="<?php echo e($selection->name); ?>"
                            data-market-name="<?php echo e($market->name); ?>"
                            data-match-label="<?php echo e($match->label); ?>"
                            data-market-id="<?php echo e($market->id); ?>"
                            data-match-id="<?php echo e($match->id); ?>"
                            <?php if($isDisabled): ?> disabled <?php endif; ?>
                        >
                            <span class="odds-name"><?php echo e($selection->name); ?></span>
                            <span class="odds-val" data-odds-value><?php echo e(odds($selection->odds)); ?></span>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-muted" style="font-size:.78rem;margin:0">Markets opening soon.</p>
        <?php endif; ?>
    </div>

    <div class="match-card__foot">
        <span><?php echo e($match->markets->sum(fn ($m) => $m->selections->count())); ?> selections</span>
        <a class="link-more" href="<?php echo e(route('matches.show', $match)); ?>">All markets →</a>
    </div>
</article>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/partials/match-card.blade.php ENDPATH**/ ?>