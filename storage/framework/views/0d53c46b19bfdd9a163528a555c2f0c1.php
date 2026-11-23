<?php
    $agent = auth()->user();
    $agentPendingCount = $agent->agentCommissions()->where('status', 'pending')->count();
    $nextLevel = \App\Support\Hierarchy::nextLevelFor($agent->role);
    $nextConfig = $nextLevel ? \App\Support\Hierarchy::get($nextLevel) : null;
    $canNetwork = $agent->hasPanelModule('network');
    $canCreate = $agent->hasPanelModule('create');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="theme-color" content="<?php echo e($siteSettings['panel_primary'] ?? '#1e6b1e'); ?>">
    <title><?php echo $__env->yieldContent('title', 'Panel'); ?> · <?php echo e($siteSettings['site_name']); ?></title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg viewBox='0 0 64 64' xmlns='http://www.w3.org/2000/svg'%3E%3Crect width='64' height='64' rx='10' fill='%231e6b1e'/%3E%3Ctext x='32' y='44' text-anchor='middle' font-family='Arial' font-weight='900' font-size='36' fill='%23fff'%3EB%3C/text%3E%3C/svg%3E">
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>?v=20261118">
    <link rel="stylesheet" href="<?php echo e(asset('css/admin-classic.css')); ?>?v=20261118">
    <?php
        $theme = function (string $key, string $fallback) use ($siteSettings) {
            $value = $siteSettings[$key] ?? '';
            return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? $value : $fallback;
        };
    ?>
    <style>
        :root {
            --panel-primary: <?php echo e($theme('panel_primary', '#1e6b1e')); ?>;
            --panel-primary-dark: <?php echo e($theme('panel_primary_dark', '#155315')); ?>;
            --panel-primary-mid: <?php echo e($theme('panel_primary_mid', '#2a7a2a')); ?>;
            --panel-accent: <?php echo e($theme('panel_accent', '#ffb74d')); ?>;
            --panel-soft: <?php echo e($theme('panel_soft', '#eef5ee')); ?>;
        }
    </style>
    <?php echo $__env->yieldPushContent('head'); ?>
</head>
<body class="admin-body admin-classic">
<div class="admin-shell">
    <aside class="admin-sidebar" id="admin-sidebar">
        <div class="admin-sidebar__brand">
            <a class="brand" href="<?php echo e(route('agent.dashboard')); ?>">
                <span class="brand__mark">B</span>
                <span class="brand__text">Bet<span>Zone</span></span>
            </a>
            <button class="icon-btn admin-sidebar__close" id="admin-sidebar-close" type="button" aria-label="Close menu">×</button>
        </div>

        <div style="padding:8px 16px 12px;border-bottom:1px solid rgba(255,255,255,.12)">
            <span class="badge badge--success"><?php echo e($agent->role_label); ?></span>
            <span style="display:block;color:#cfe3cf;font-size:.68rem;margin-top:5px"><?php echo e($agent->agent_code); ?> · <?php echo e(number_format($agent->balance, 2)); ?> coins</span>
        </div>

        <nav class="admin-nav">
            <a class="admin-nav__link <?php echo e(request()->routeIs('agent.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('agent.dashboard')); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="8" height="8" rx="1"/><rect x="13" y="3" width="8" height="5" rx="1"/><rect x="13" y="11" width="8" height="10" rx="1"/><rect x="3" y="14" width="8" height="7" rx="1"/></svg>
                Dashboard
            </a>

            <?php if($canNetwork && $nextConfig): ?>
                <a class="admin-nav__link <?php echo e(request()->routeIs('agent.hierarchy.*') ? 'active' : ''); ?>" href="<?php echo e(route('agent.hierarchy.index')); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="5" r="2.5"/><circle cx="5" cy="19" r="2.5"/><circle cx="19" cy="19" r="2.5"/><path d="M12 7.5v4M5 16.5v-2a2 2 0 012-2h10a2 2 0 012 2v2"/></svg>
                    My Network
                </a>
            <?php endif; ?>

            <?php if($agent->hasPanelModule('matches')): ?>
                <a class="admin-nav__link <?php echo e(request()->routeIs('agent.matches.*') ? 'active' : ''); ?>" href="<?php echo e(route('agent.matches.index')); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 3a15 15 0 010 18M3 12h18"/></svg>
                    Matches
                </a>
            <?php endif; ?>

            <?php if($agent->hasPanelModule('mybets')): ?>
                <a class="admin-nav__link <?php echo e(request()->routeIs('agent.my-bets') ? 'active' : ''); ?>" href="<?php echo e(route('agent.my-bets')); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                    My Bets
                </a>
            <?php endif; ?>

            <?php if($agent->hasPanelModule('players')): ?>
                <a class="admin-nav__link <?php echo e(request()->routeIs('agent.players.*') ? 'active' : ''); ?>" href="<?php echo e(route('agent.players.index')); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="8" r="4"/><path d="M2 21v-1a7 7 0 0114 0v1"/><path d="M17 4a4 4 0 010 8M22 21v-1a6 6 0 00-4-5.6"/></svg>
                    My Players
                </a>
            <?php endif; ?>

            <?php if($agent->hasPanelModule('bets')): ?>
                <a class="admin-nav__link <?php echo e(request()->routeIs('agent.bets.*') ? 'active' : ''); ?>" href="<?php echo e(route('agent.bets.index')); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>
                    Downline Bets
                </a>
            <?php endif; ?>

            <?php if($agent->hasPanelModule('commissions')): ?>
                <a class="admin-nav__link <?php echo e(request()->routeIs('agent.commissions.*') ? 'active' : ''); ?>" href="<?php echo e(route('agent.commissions.index')); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M8 12h8M12 8v8"/></svg>
                    Commission
                    <?php if($agentPendingCount > 0): ?>
                        <span class="admin-nav__badge"><?php echo e($agentPendingCount); ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>

            <div class="admin-nav__label">Account</div>

            <?php if($agent->hasPanelModule('wallet')): ?>
                <a class="admin-nav__link <?php echo e(request()->routeIs('agent.wallet.*') ? 'active' : ''); ?>" href="<?php echo e(route('agent.wallet.index')); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h18v12H3z"/><path d="M3 7l3-3h12l3 3"/><circle cx="16.5" cy="13" r="1.3" fill="currentColor" stroke="none"/></svg>
                    Wallet
                </a>
            <?php endif; ?>

            <?php if($agent->hasPanelModule('account')): ?>
                <a class="admin-nav__link <?php echo e(request()->routeIs('agent.account.*') ? 'active' : ''); ?>" href="<?php echo e(route('agent.account.index')); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a7 7 0 0114 0v1"/></svg>
                    My Profile
                </a>
            <?php endif; ?>

            <a class="admin-nav__link" href="<?php echo e(route('home')); ?>" target="_blank" rel="noopener">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><path d="M15 3h6v6M10 14L21 3"/></svg>
                View storefront
            </a>
        </nav>
    </aside>

    <div class="admin-overlay" id="admin-overlay"></div>

    <div class="admin-main">
        <header class="admin-topbar">
            <button class="icon-btn admin-sidebar-toggle" id="admin-sidebar-toggle" type="button" aria-label="Open menu">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
            </button>

            <div class="admin-topbar__title">
                <?php echo $__env->yieldContent('page-title', 'Panel'); ?>
                <span><?php echo $__env->yieldContent('page-subtitle', $agent->role_label.' · '.$agent->agent_code); ?></span>
            </div>

            <div class="admin-topbar__spacer"></div>

            <?php if($agent->hasPanelModule('commissions')): ?>
                <span class="badge badge--success hide-xs"><?php echo e(number_format($agent->commission_balance, 2)); ?> pending</span>
            <?php endif; ?>

            <?php if($nextConfig && $canNetwork && $canCreate): ?>
                <a class="btn btn--primary btn--sm" href="<?php echo e(route('agent.hierarchy.create')); ?>">+ <?php echo e($nextConfig['label']); ?></a>
            <?php endif; ?>

            <div class="user-menu" data-user-menu>
                <button class="user-menu__trigger" type="button" data-user-menu-trigger>
                    <span><?php echo e($agent->name); ?></span>
                    <span class="chev">▾</span>
                </button>
                <div class="user-menu__panel">
                    <div class="user-menu__head">
                        <strong><?php echo e($agent->name); ?></strong>
                        <span><?php echo e($agent->role_label); ?> · <?php echo e($agent->username); ?></span>
                    </div>
                    <?php if($agent->hasPanelModule('account')): ?>
                        <a href="<?php echo e(route('agent.account.index')); ?>">My Profile</a>
                    <?php endif; ?>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit">Logout</button>
                    </form>
                </div>
            </div>
        </header>

        <div class="admin-content">
            <?php if(session('success')): ?>
                <div class="alert alert--success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <div class="alert alert--error"><?php echo e(session('error')); ?></div>
            <?php endif; ?>
            <?php if($errors->any()): ?>
                <div class="alert alert--error">
                    <strong>Please fix:</strong>
                    <ul>
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </div>
</div>

<div class="toast-stack" id="toast-stack"></div>
<script>
window.BETZONE = { authenticated: true, canBet: true, isAdmin: false, csrf: <?php echo json_encode(csrf_token(), 15, 512) ?> };
</script>
<script src="<?php echo e(asset('js/app.js')); ?>?v=20261118" defer></script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/layouts/agent.blade.php ENDPATH**/ ?>