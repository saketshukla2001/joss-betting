<?php $__env->startSection('title', 'Sign in'); ?>

<?php $__env->startSection('content'); ?>
    <h2>Welcome back</h2>
    <p>Sign in to your <?php echo e($siteSettings['site_name']); ?> account to bet, manage your wallet and track results.</p>

    <div class="card">
        <?php echo $__env->make('partials.errors', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <form method="POST" action="<?php echo e(route('login')); ?>">
            <?php echo csrf_field(); ?>

            <div class="field">
                <label for="username">Username or email</label>
                <input class="input" id="username" name="username" type="text" value="<?php echo e(old('username')); ?>" placeholder="e.g. demo" required autofocus autocomplete="username">
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="input-group">
                    <input class="input" id="password" name="password" type="password" placeholder="Your password" required autocomplete="current-password" style="border-radius:9px 0 0 9px">
                    <button class="btn btn--dark" type="button" data-toggle-password="password" style="border-radius:0 9px 9px 0">Show</button>
                </div>
            </div>

            <div class="flex-between mt-8">
                <label class="check">
                    <input type="checkbox" name="remember" value="1" <?php echo e(old('remember') ? 'checked' : ''); ?>>
                    <span>Remember me for 30 days</span>
                </label>
            </div>

            <button class="btn btn--primary btn--block btn--lg mt-20" type="submit">Sign in</button>
        </form>

        <div class="auth-switch">New to <?php echo e($siteSettings['site_name']); ?>? <a href="<?php echo e(route('register')); ?>">Create an account</a></div>
    </div>

    <div class="demo-creds">
        <div class="demo-creds__title">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
            Demo credentials
        </div>
        <div class="demo-row">
            <span>Client · <code>demo</code> / <code>Client@12345</code></span>
            <button type="button" data-fill-username="demo" data-fill-password="Client@12345">Use</button>
        </div>
        <div class="demo-row">
            <span>Admin · <code>admin</code> / <code>Admin@12345</code></span>
            <button type="button" data-fill-username="admin" data-fill-password="Admin@12345">Use</button>
        </div>
        <div class="demo-row">
            <span>Agent · <code>agent1</code> / <code>Agent@12345</code></span>
            <button type="button" data-fill-username="agent1" data-fill-password="Agent@12345">Use</button>
        </div>
        <div class="demo-row">
            <span>Agent · <code>agent2</code> / <code>Agent2@12345</code></span>
            <button type="button" data-fill-username="agent2" data-fill-password="Agent2@12345">Use</button>
        </div>
    </div>

    <p class="text-center mt-14"><a class="text-muted" href="<?php echo e(route('home')); ?>" style="font-size:.78rem">← Back to sportsbook</a></p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/auth/login.blade.php ENDPATH**/ ?>