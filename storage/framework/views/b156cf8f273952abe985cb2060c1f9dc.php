<?php
    $admin = auth()->user();
    $level = request()->route('level');
    $detailsOpen = request()->routeIs('admin.hierarchy.*');
    $sportsOpen = request()->routeIs('admin.sports.*') || request()->routeIs('admin.matches.*') || request()->routeIs('admin.bets.*');
    $ledgerOpen = request()->routeIs('admin.ledger.*');
    $cashOpen = request()->routeIs('admin.cash.*');
    $reportsOpen = request()->routeIs('admin.secure-code.*');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="theme-color" content="<?php echo e($siteSettings['panel_primary'] ?? '#1e6b1e'); ?>">
    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?> · <?php echo e($siteSettings['site_name']); ?> Admin</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 64 64%22><rect width=%2264%22 height=%2264%22 rx=%2210%22 fill=%22%231e6b1e%22/><text x=%2232%22 y=%2244%22 text-anchor=%22middle%22 font-family=%22Arial%22 font-weight=%22900%22 font-size=%2236%22 fill=%22%23ffffff%22>B</text></svg>">
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>?v=20261115">
    <link rel="stylesheet" href="<?php echo e(asset('css/admin-classic.css')); ?>?v=20261115">
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
            <a class="brand" href="<?php echo e(route('admin.dashboard')); ?>">
                <span class="brand__mark">B</span>
                <span class="brand__text">Bet<span>Zone</span></span>
            </a>
            <button class="icon-btn admin-sidebar__close" id="admin-sidebar-close" type="button" aria-label="Close menu">×</button>
        </div>

        <nav class="admin-nav">
            <a class="admin-nav__link <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('admin.dashboard')); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="8" height="8" rx="1"/><rect x="13" y="3" width="8" height="5" rx="1"/><rect x="13" y="11" width="8" height="10" rx="1"/><rect x="3" y="14" width="8" height="7" rx="1"/></svg>
                Dashboard
            </a>

            <details class="admin-group" <?php echo e($detailsOpen ? 'open' : ''); ?>>
                <summary class="admin-group__summary">
                    <span class="admin-nav__label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a8 8 0 0116 0v1"/></svg>Admin Details</span>
                    <span class="admin-group__chevron">⌄</span>
                </summary>
                <?php $__currentLoopData = \App\Support\Hierarchy::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a class="admin-nav__sublink <?php echo e(request()->routeIs('admin.hierarchy.*') && $level === $key ? 'active' : ''); ?>"
                       href="<?php echo e(route('admin.hierarchy.index', $key)); ?>"><?php echo e(strtoupper($item['label'])); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </details>

            <details class="admin-group" <?php echo e($sportsOpen ? 'open' : ''); ?>>
                <summary class="admin-group__summary">
                    <span class="admin-nav__label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 3a15 15 0 010 18M3 12h18"/></svg>Sports Betting</span>
                    <span class="admin-group__chevron">⌄</span>
                </summary>
                <a class="admin-nav__sublink <?php echo e(request()->routeIs('admin.cricket.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.cricket.index')); ?>">CRICKET</a>
                <a class="admin-nav__sublink <?php echo e(request()->routeIs('admin.sports.inplay') ? 'active' : ''); ?>" href="<?php echo e(route('admin.sports.inplay')); ?>">INPLAY GAMES</a>
                <a class="admin-nav__sublink <?php echo e(request()->routeIs('admin.sports.completed') ? 'active' : ''); ?>" href="<?php echo e(route('admin.sports.completed')); ?>">COMPLETED GAMES</a>
                <a class="admin-nav__sublink <?php echo e(request()->routeIs('admin.sports.current-bets') ? 'active' : ''); ?>" href="<?php echo e(route('admin.sports.current-bets')); ?>">CURRENT BETS</a>
                <a class="admin-nav__sublink <?php echo e(request()->routeIs('admin.drafts.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.drafts.index')); ?>">MARKET DRAFTS</a>
            </details>

            <details class="admin-group" <?php echo e($ledgerOpen ? 'open' : ''); ?>>
                <summary class="admin-group__summary">
                    <span class="admin-nav__label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>Ledger</span>
                    <span class="admin-group__chevron">⌄</span>
                </summary>
                <a class="admin-nav__sublink <?php echo e(request()->routeIs('admin.ledger.profit-loss') ? 'active' : ''); ?>" href="<?php echo e(route('admin.ledger.profit-loss')); ?>">PROFIT/LOSS</a>
                <a class="admin-nav__sublink <?php echo e(request()->routeIs('admin.ledger.show') && $level === 'my' ? 'active' : ''); ?>" href="<?php echo e(route('admin.ledger.show', 'my')); ?>">MY LEDGER</a>
                <?php $__currentLoopData = ['mini_master' => 'MINI LEDGER', 'master_master' => 'MASTER LEDGER', 'super_master' => 'SUPER LEDGER', 'agent_master' => 'AGENT LEDGER', 'client_master' => 'CLIENT LEDGER']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a class="admin-nav__sublink <?php echo e(request()->routeIs('admin.ledger.show') && $level === $key ? 'active' : ''); ?>" href="<?php echo e(route('admin.ledger.show', $key)); ?>"><?php echo e($label); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </details>

            <details class="admin-group" <?php echo e($cashOpen ? 'open' : ''); ?>>
                <summary class="admin-group__summary">
                    <span class="admin-nav__label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18"/></svg>Cash Transaction</span>
                    <span class="admin-group__chevron">⌄</span>
                </summary>
                <?php $__currentLoopData = ['mini_master' => 'MINI MASTER', 'master_master' => 'MASTER MASTER', 'super_master' => 'SUPER MASTER', 'agent_master' => 'AGENT MASTER', 'client_master' => 'CLIENT MASTER']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a class="admin-nav__sublink <?php echo e(request()->routeIs('admin.cash.*') && $level === $key ? 'active' : ''); ?>" href="<?php echo e(route('admin.cash.index', $key)); ?>"><?php echo e($label); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </details>

            <a class="admin-nav__link <?php echo e(request()->routeIs('admin.comm-report.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.comm-report.index')); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>
                Comm. Report
            </a>

            <details class="admin-group" <?php echo e($reportsOpen ? 'open' : ''); ?>>
                <summary class="admin-group__summary">
                    <span class="admin-nav__label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2h9l5 5v15H6z"/><path d="M14 2v6h6M9 13h6M9 17h6"/></svg>Reports</span>
                    <span class="admin-group__chevron">⌄</span>
                </summary>
                <a class="admin-nav__sublink <?php echo e(request()->routeIs('admin.secure-code.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.secure-code.index')); ?>">SECURE CODE REPORT</a>
            </details>

            <a class="admin-nav__link <?php echo e(request()->routeIs('admin.settings.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.settings.index')); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 00.3 1.9l.1.1a2 2 0 11-2.8 2.8l-.1-.1a1.7 1.7 0 00-1.9-.3 1.7 1.7 0 00-1 1.5V21a2 2 0 11-4 0v-.1a1.7 1.7 0 00-1-1.6 1.7 1.7 0 00-1.9.3l-.1.1a2 2 0 11-2.8-2.8l.1-.1a1.7 1.7 0 00.3-1.9 1.7 1.7 0 00-1.5-1H3a2 2 0 110-4h.1a1.7 1.7 0 001.6-1 1.7 1.7 0 00-.3-1.9l-.1-.1a2 2 0 112.8-2.8l.1.1a1.7 1.7 0 001.9.3H9a1.7 1.7 0 001-1.5V3a2 2 0 114 0v.1a1.7 1.7 0 001 1.5 1.7 1.7 0 001.9-.3l.1-.1a2 2 0 112.8 2.8l-.1.1a1.7 1.7 0 00-.3 1.9V9a1.7 1.7 0 001.5 1H21a2 2 0 110 4h-.1a1.7 1.7 0 00-1.5 1z"/></svg>
                Setting
            </a>
        </nav>
    </aside>

    <div class="admin-overlay" id="admin-overlay"></div>

    <div class="admin-main">
        <header class="admin-topbar">
            <button class="icon-btn admin-sidebar-toggle" id="admin-sidebar-toggle" type="button" aria-label="Open menu">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
            </button>

            <div class="admin-topbar__spacer"></div>

            <div class="user-menu" data-user-menu>
                <button class="user-menu__trigger" type="button" data-user-menu-trigger>
                    <span><?php echo e($admin->name); ?> (<?php echo e($admin->agent_code ?: $admin->username); ?>)</span>
                    <span class="chev">▾</span>
                </button>
                <div class="user-menu__panel">
                    <div class="user-menu__head">
                        <strong><?php echo e($admin->name); ?></strong>
                        <span><?php echo e($admin->role_label); ?> · <?php echo e($admin->username); ?></span>
                    </div>
                    <a href="<?php echo e(route('admin.settings.index')); ?>">Setting</a>
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
window.BETZONE = { authenticated: true, canBet: false, isAdmin: true, csrf: <?php echo json_encode(csrf_token(), 15, 512) ?> };
</script>
<script src="<?php echo e(asset('js/app.js')); ?>?v=20261115" defer></script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/layouts/admin.blade.php ENDPATH**/ ?>