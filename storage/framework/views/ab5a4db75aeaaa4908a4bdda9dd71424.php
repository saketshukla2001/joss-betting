<?php $__env->startSection('title', $sport ? $sport->name.' betting' : 'Sportsbook'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo e(route('home')); ?>">Home</a>
            <span class="sep">/</span>
            <span><?php echo e($sport ? $sport->name : 'Sportsbook'); ?></span>
        </div>

        <div class="page-head">
            <div>
                <h1><?php echo e($sport ? $sport->icon.' '.$sport->name : 'Sportsbook'); ?></h1>
                <p>
                    <?php if(request('status') === 'live' && setting_enabled('cricket_feed_enabled', true) && setting_enabled('cricket_board_enabled', true) && (! $sport || $sport->slug === 'cricket')): ?>
                        Live cricket scores below · odds lock at placement · singles only
                    <?php else: ?>
                        <?php echo e($matches->total()); ?> events available · odds lock at placement · singles only
                    <?php endif; ?>
                </p>
            </div>
            <div class="chip-row">
                <a class="chip <?php echo e(!request('status') ? 'active' : ''); ?>" href="<?php echo e($sport ? route('sports.show', $sport) : route('sports.index')); ?>">All</a>
                <a class="chip <?php echo e(request('status') === 'live' ? 'active' : ''); ?>" href="<?php echo e($sport ? route('sports.show', [$sport, 'status' => 'live']) : route('sports.index', ['status' => 'live'])); ?>">Live</a>
                <a class="chip <?php echo e(request('status') === 'upcoming' ? 'active' : ''); ?>" href="<?php echo e($sport ? route('sports.show', [$sport, 'status' => 'upcoming']) : route('sports.index', ['status' => 'upcoming'])); ?>">Upcoming</a>
            </div>
        </div>

        <form class="card filter-bar mb-20" method="GET" action="<?php echo e(url()->current()); ?>">
            <input class="input search" type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search teams or leagues…" aria-label="Search matches">
            <select class="select" name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                <option value="live" <?php if(request('status') === 'live'): echo 'selected'; endif; ?>>Live</option>
                <option value="upcoming" <?php if(request('status') === 'upcoming'): echo 'selected'; endif; ?>>Upcoming</option>
            </select>
            <button class="btn btn--primary" type="submit">Filter</button>
            <?php if(request()->hasAny(['q', 'status'])): ?>
                <a class="btn btn--ghost" href="<?php echo e($sport ? route('sports.show', $sport) : route('sports.index')); ?>">Reset</a>
            <?php endif; ?>
        </form>

        <div class="chip-row mb-20">
            <a class="chip <?php echo e(!$sport ? 'active' : ''); ?>" href="<?php echo e(route('sports.index', request()->only(['q', 'status']))); ?>">All sports</a>
            <?php $__currentLoopData = $sports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sportOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a class="chip <?php echo e($sport && $sport->id === $sportOption->id ? 'active' : ''); ?>" href="<?php echo e(route('sports.show', [$sportOption, ...request()->only(['q', 'status'])])); ?>">
                    <?php echo e($sportOption->icon); ?> <?php echo e($sportOption->name); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <?php if(request('status') === 'live' && setting_enabled('cricket_feed_enabled', true) && setting_enabled('cricket_board_enabled', true) && (! $sport || $sport->slug === 'cricket')): ?>
            <div class="mb-20">
                <?php echo $__env->make('partials.cricket-section', ['mode' => 'live'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        <?php endif; ?>

        <?php if($matches->isEmpty() && ! (request('status') === 'live' && setting_enabled('cricket_feed_enabled', true) && setting_enabled('cricket_board_enabled', true) && (! $sport || $sport->slug === 'cricket'))): ?>
            <div class="card empty">
                <div class="empty__ico">🔍</div>
                <strong>No events match that filter</strong>
                <p>Try another sport or clear your search.</p>
                <a class="btn btn--primary btn--sm mt-14" href="<?php echo e(route('sports.index')); ?>">See all events</a>
            </div>
        <?php else: ?>
            <div class="match-grid">
                <?php $__currentLoopData = $matches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $match): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php echo $__env->make('partials.match-card', ['match' => $match], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <?php echo e($matches->links('partials.pagination')); ?>

        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/sports/index.blade.php ENDPATH**/ ?>