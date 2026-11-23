<?php
    $canBet = auth()->check() && auth()->user()->isClient();
    $isAdmin = auth()->check() && auth()->user()->isAdmin();
    $isAgent = auth()->check() && auth()->user()->isMasterPanelUser();
    $user = auth()->user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="theme-color" content="#070b12">
    <title><?php echo $__env->yieldContent('title', $siteSettings['site_name']); ?> · <?php echo e($siteSettings['tagline']); ?></title>
    <meta name="description" content="<?php echo $__env->yieldContent('meta_description', $siteSettings['site_name'].' — '.$siteSettings['tagline']); ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 64 64%22><rect width=%2264%22 height=%2264%22 rx=%2216%22 fill=%22%23c9ff45%22/><text x=%2232%22 y=%2244%22 text-anchor=%22middle%22 font-family=%22Arial%22 font-weight=%22900%22 font-size=%2236%22 fill=%22%23132000%22>B</text></svg>">
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>?v=20261122">
    <?php echo $__env->yieldPushContent('head'); ?>
</head>
<body>
<div class="app-shell">
    <header class="topbar">
        <div class="container topbar__inner">
            <a class="brand" href="<?php echo e(route('home')); ?>" aria-label="<?php echo e($siteSettings['site_name']); ?> home">
                <span class="brand__mark">B</span>
                <span class="brand__text">Bet<span>Zone</span></span>
            </a>

            <nav class="nav" aria-label="Main navigation">
                <a class="nav__link <?php echo e(request()->routeIs('home') ? 'active' : ''); ?>" href="<?php echo e(route('home')); ?>">Home</a>
                <a class="nav__link <?php echo e(request()->routeIs('sports.*') && !request('status') ? 'active' : ''); ?>" href="<?php echo e(route('sports.index')); ?>">Sports</a>
                <a class="nav__link <?php echo e(request('status') === 'live' ? 'active' : ''); ?>" href="<?php echo e(route('sports.index', ['status' => 'live'])); ?>">
                    Live <span class="dot"></span>
                </a>
                <a class="nav__link <?php echo e(request()->routeIs('cricket.*') ? 'active' : ''); ?>" href="<?php echo e(route('cricket.index')); ?>">Cricket</a>
                <?php if($canBet): ?>
                    <a class="nav__link <?php echo e(request()->routeIs('bets.*') ? 'active' : ''); ?>" href="<?php echo e(route('bets.index')); ?>">My Bets</a>
                    <a class="nav__link <?php echo e(request()->routeIs('wallet.*') ? 'active' : ''); ?>" href="<?php echo e(route('wallet.index')); ?>">Wallet</a>
                <?php endif; ?>
            </nav>

            <div class="topbar__actions">
                <?php if($canBet): ?>
                    <a class="balance-chip" href="<?php echo e(route('wallet.index')); ?>" title="Available balance">
                        <span class="wallet-ico">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h18v12H3z"/><path d="M3 7l3-3h12l3 3"/><circle cx="16.5" cy="13" r="1.3" fill="currentColor" stroke="none"/></svg>
                        </span>
                        <span class="bal" data-balance><?php echo e(money($user->balance)); ?></span>
                        <small>Balance</small>
                    </a>
                    <div class="user-menu" data-user-menu>
                        <button class="user-menu__trigger" type="button" data-user-menu-trigger aria-label="Account menu">
                            <span class="avatar"><?php echo e($user->initials); ?></span>
                            <span class="chev">▼</span>
                        </button>
                        <div class="user-menu__panel">
                            <div class="user-menu__head">
                                <strong><?php echo e($user->name); ?></strong>
                                <span><?php echo e('@'.$user->username); ?></span>
                            </div>
                            <a href="<?php echo e(route('dashboard')); ?>">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                                Dashboard
                            </a>
                            <a href="<?php echo e(route('bets.index')); ?>">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                                My bets
                            </a>
                            <a href="<?php echo e(route('account.index')); ?>">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a7 7 0 0114 0v1"/></svg>
                                Account settings
                            </a>
                            <form method="POST" action="<?php echo e(route('logout')); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                <?php elseif($isAdmin): ?>
                    <a class="btn btn--primary btn--sm" href="<?php echo e(route('admin.dashboard')); ?>">Admin panel</a>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button class="btn btn--ghost btn--sm" type="submit">Sign out</button>
                    </form>
                <?php elseif($isAgent): ?>
                    <a class="btn btn--primary btn--sm" href="<?php echo e(route('agent.dashboard')); ?>"><?php echo e(auth()->user()->isAgent() ? 'Agent panel' : 'Master panel'); ?></a>
                    <span class="avatar"><?php echo e($user->initials); ?></span>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button class="btn btn--ghost btn--sm" type="submit">Sign out</button>
                    </form>
                <?php else: ?>
                    <a class="btn btn--ghost btn--sm" href="<?php echo e(route('login')); ?>">Sign in</a>
                    <a class="btn btn--primary btn--sm" href="<?php echo e(route('register')); ?>">Join now</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <?php if(!empty($siteSettings['announcement'])): ?>
        <div class="announce">
            <div class="container announce__inner">
                <span class="announce__tag">Update</span>
                <span class="announce__text"><?php echo e($siteSettings['announcement']); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <main class="page">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer__grid">
                <div>
                    <a class="brand" href="<?php echo e(route('home')); ?>">
                        <span class="brand__mark">B</span>
                        <span class="brand__text">Bet<span>Zone</span></span>
                    </a>
                    <p class="mt-14"><?php echo e($siteSettings['tagline']); ?>. Built as a fully working Laravel + MySQL sportsbook demo — no casino, no matka, just sports markets.</p>
                    <div class="responsible">18+ · PLAY RESPONSIBLY</div>
                </div>
                <div>
                    <h4>Betting</h4>
                    <a href="<?php echo e(route('sports.index')); ?>">All sports</a>
                    <a href="<?php echo e(route('sports.index', ['status' => 'live'])); ?>">Live betting</a>
                    <a href="<?php echo e(route('cricket.index')); ?>">Live cricket</a>
                    <a href="<?php echo e(route('sports.index', ['status' => 'upcoming'])); ?>">Upcoming</a>
                    <?php if($canBet): ?><a href="<?php echo e(route('bets.index')); ?>">My bets</a><?php endif; ?>
                </div>
                <div>
                    <h4>Account</h4>
                    <?php if($canBet): ?>
                        <a href="<?php echo e(route('dashboard')); ?>">Dashboard</a>
                        <a href="<?php echo e(route('wallet.index')); ?>">Wallet</a>
                        <a href="<?php echo e(route('account.index')); ?>">Profile</a>
                    <?php else: ?>
                        <a href="<?php echo e(route('login')); ?>">Sign in</a>
                        <a href="<?php echo e(route('register')); ?>">Create account</a>
                    <?php endif; ?>
                </div>
                <div>
                    <h4>Support</h4>
                    <a href="mailto:<?php echo e($siteSettings['support_email']); ?>"><?php echo e($siteSettings['support_email']); ?></a>
                    <a href="#">Betting rules</a>
                    <a href="#">Responsible gaming</a>
                </div>
            </div>
            <div class="footer__bottom">
                <span>© <?php echo e(date('Y')); ?> <?php echo e($siteSettings['site_name']); ?>. Demo application for evaluation and learning.</span>
                <span>Sportsbook only · Casino and matka excluded by design.</span>
            </div>
        </div>
    </footer>

    <?php if($canBet): ?>
        <nav class="mobile-nav" aria-label="Mobile navigation">
            <div class="mobile-nav__inner">
                <a class="<?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('dashboard')); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5L12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/></svg>
                    Home
                </a>
                <a class="<?php echo e(request()->routeIs('sports.*') ? 'active' : ''); ?>" href="<?php echo e(route('sports.index')); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 3a15 15 0 010 18M3 12h18"/></svg>
                    Sports
                </a>
                <a class="<?php echo e(request()->routeIs('bets.*') ? 'active' : ''); ?>" href="<?php echo e(route('bets.index')); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                    Bets
                </a>
                <a class="<?php echo e(request()->routeIs('wallet.*') ? 'active' : ''); ?>" href="<?php echo e(route('wallet.index')); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h18v12H3z"/><path d="M3 7l3-3h12l3 3"/></svg>
                    Wallet
                </a>
            </div>
        </nav>

        <aside id="bet-slip" class="bet-slip" aria-hidden="true" aria-label="Bet slip">
            <div class="bet-slip__head">
                <strong>Bet <span>Slip</span></strong>
                <button class="slip-close" type="button" data-slip-close aria-label="Close bet slip">×</button>
            </div>
            <div class="bet-slip__body">
                <div id="slip-empty" class="empty">
                    <div class="empty__ico">🎯</div>
                    <strong>Your slip is empty</strong>
                    <p>Tap any odds to add a selection.</p>
                </div>
                <div id="slip-body" hidden>
                    <div class="slip-selection">
                        <div class="slip-selection__match" id="slip-match"></div>
                        <div class="slip-selection__name" id="slip-selection-name"></div>
                        <div class="slip-selection__market" id="slip-market-name"></div>
                        <div class="slip-selection__row">
                            <span class="text-muted" style="font-size:.72rem">Odds</span>
                            <strong class="slip-selection__odds" id="slip-odds">0.00</strong>
                        </div>
                    </div>

                    <div class="slip-stake">
                        <div class="slip-stake__label">
                            <span>Stake</span>
                            <span>Min <?php echo e(money($siteSettings['min_stake'], false)); ?> · Max <?php echo e(money($siteSettings['max_stake'], false)); ?></span>
                        </div>
                        <div class="input-group">
                            <span class="prefix"><?php echo e($siteSettings['currency']); ?></span>
                            <input class="input" type="number" id="slip-stake" min="<?php echo e($siteSettings['min_stake']); ?>" max="<?php echo e($siteSettings['max_stake']); ?>" step="1" inputmode="numeric" value="<?php echo e($siteSettings['min_stake']); ?>">
                        </div>
                        <div class="quick-stakes">
                            <button type="button" data-quick-stake="100">100</button>
                            <button type="button" data-quick-stake="500">500</button>
                            <button type="button" data-quick-stake="1000">1K</button>
                            <button type="button" data-quick-stake="5000">5K</button>
                        </div>
                    </div>

                    <div class="slip-error" id="slip-error" role="alert"></div>

                    <div class="slip-return">
                        <span>Potential return</span>
                        <strong id="slip-return">₹0.00</strong>
                    </div>
                    <button class="btn btn--primary btn--block btn--lg" type="button" id="slip-place">Place bet</button>
                    <p class="slip-note">Odds are locked when you place the bet. 18+ only.</p>
                </div>
            </div>
        </aside>
        <div class="slip-overlay" id="slip-overlay"></div>
        <button class="slip-fab hidden" id="slip-fab" type="button" aria-label="Open bet slip">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 3h12l2 6-8 12L4 9z"/><path d="M4 9h16"/></svg>
            Bet Slip
            <span class="count" data-slip-count>0</span>
        </button>
    <?php endif; ?>

    <div class="toast-stack" id="toast-stack">
        <?php if(session('success')): ?>
            <div class="toast toast--success" data-flash-toast>
                <span class="toast__ico">✓</span>
                <span><?php echo e(session('success')); ?></span>
                <button type="button" aria-label="Dismiss">×</button>
            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="toast toast--error" data-flash-toast>
                <span class="toast__ico">!</span>
                <span><?php echo e(session('error')); ?></span>
                <button type="button" aria-label="Dismiss">×</button>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
window.BETZONE = {
    authenticated: <?php echo json_encode(auth()->check(), 15, 512) ?>,
    canBet: <?php echo json_encode($canBet, 15, 512) ?>,
    isAdmin: <?php echo json_encode($isAdmin, 15, 512) ?>,
    isAgent: <?php echo json_encode($isAgent, 15, 512) ?>,
    csrf: <?php echo json_encode(csrf_token(), 15, 512) ?>,
    betUrl: <?php echo json_encode(route('bets.store'), 15, 512) ?>,
    loginUrl: <?php echo json_encode(route('login'), 15, 512) ?>,
    oddsUrl: <?php echo json_encode(route('api.odds'), 15, 512) ?>,
    currency: <?php echo json_encode($siteSettings['currency'], 15, 512) ?>,
    minStake: <?php echo json_encode((float) $siteSettings['min_stake'], 15, 512) ?>,
    maxStake: <?php echo json_encode((float) $siteSettings['max_stake'], 15, 512) ?>
};
</script>
<script src="<?php echo e(asset('js/app.js')); ?>?v=20261122" defer></script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/layouts/app.blade.php ENDPATH**/ ?>