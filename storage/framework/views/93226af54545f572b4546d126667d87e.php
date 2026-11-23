<?php $siteName = $siteSettings['site_name'] ?? 'BetZone'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="theme-color" content="#070b12">
    <title><?php echo $__env->yieldContent('title', 'Sign in'); ?> · <?php echo e($siteName); ?></title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 64 64%22><rect width=%2264%22 height=%2264%22 rx=%2216%22 fill=%22%23c9ff45%22/><text x=%2232%22 y=%2244%22 text-anchor=%22middle%22 font-family=%22Arial%22 font-weight=%22900%22 font-size=%2236%22 fill=%22%23132000%22>B</text></svg>">
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>?v=20261113">
</head>
<body>
<div class="auth-shell">
    <aside class="auth-brand">
        <a class="brand" href="<?php echo e(route('home')); ?>">
            <span class="brand__mark">B</span>
            <span class="brand__text">Bet<span>Zone</span></span>
        </a>

        <div class="auth-brand__hero">
            <span class="eyebrow">Sportsbook · Est. 2026</span>
            <h1 class="mt-14">Your game.<br>Your odds.<br><em>Your payout.</em></h1>
            <p>Live markets across football, cricket, basketball, tennis and hockey. Transparent odds, instant wallet settlement and a panel built for every screen.</p>

            <div class="auth-trust mt-20">
                <div>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l8 4v6c0 5-3.4 8.4-8 10-4.6-1.6-8-5-8-10V6z"/><path d="M9 12l2 2 4-4"/></svg>
                    Secure sessions and hashed passwords
                </div>
                <div>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h8l-1 8 10-12h-8z"/></svg>
                    Live odds with automatic wallet settlement
                </div>
                <div>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/></svg>
                    UPI, bank and USDT deposit requests
                </div>
            </div>
        </div>

        <div class="auth-foot">
            18+ only · Demo platform for evaluation · Sports markets only, no casino or matka.
        </div>
    </aside>

    <main class="auth-main">
        <div class="auth-card">
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </main>
</div>

<div class="toast-stack" id="toast-stack">
    <?php if(session('success')): ?>
        <div class="toast toast--success" data-flash-toast>
            <span class="toast__ico">✓</span><span><?php echo e(session('success')); ?></span>
            <button type="button" aria-label="Dismiss">×</button>
        </div>
    <?php endif; ?>
</div>

<script>
window.BETZONE = {
    authenticated: <?php echo json_encode(auth()->check(), 15, 512) ?>,
    canBet: false,
    isAdmin: false,
    csrf: <?php echo json_encode(csrf_token(), 15, 512) ?>,
    loginUrl: <?php echo json_encode(route('login'), 15, 512) ?>,
    currency: <?php echo json_encode($siteSettings['currency'], 15, 512) ?>,
    minStake: 100,
    maxStake: 100000
};
</script>
<script src="<?php echo e(asset('js/app.js')); ?>?v=20261113" defer></script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/layouts/auth.blade.php ENDPATH**/ ?>