<?php $__env->startSection('title', 'Create account'); ?>

<?php $__env->startSection('content'); ?>
    <h2>Join <?php echo e($siteSettings['site_name']); ?></h2>
    <p>Open a free account in seconds. New players receive a <?php echo e(money($siteSettings['signup_bonus'])); ?> welcome bonus.</p>

    <div class="card">
        <?php echo $__env->make('partials.errors', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <form method="POST" action="<?php echo e(route('register')); ?>">
            <?php echo csrf_field(); ?>

            <div class="form-grid">
                <div class="field">
                    <label for="name">Full name</label>
                    <input class="input" id="name" name="name" type="text" value="<?php echo e(old('name')); ?>" placeholder="Your full name" required autofocus autocomplete="name">
                </div>
                <div class="field">
                    <label for="username">Username</label>
                    <input class="input" id="username" name="username" type="text" value="<?php echo e(old('username')); ?>" placeholder="4–20 characters" required autocomplete="username" pattern="[A-Za-z0-9_\-]+">
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input class="input" id="email" name="email" type="email" value="<?php echo e(old('email')); ?>" placeholder="you@example.com" required autocomplete="email">
                </div>
                <div class="field">
                    <label for="phone">Phone <span class="text-muted">(optional)</span></label>
                    <input class="input" id="phone" name="phone" type="text" value="<?php echo e(old('phone')); ?>" placeholder="+91 98765 43210" autocomplete="tel">
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input class="input" id="password" name="password" type="password" placeholder="Min 8 chars, letters + numbers" required autocomplete="new-password">
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirm password</label>
                    <input class="input" id="password_confirmation" name="password_confirmation" type="password" placeholder="Repeat password" required autocomplete="new-password">
                </div>
            </div>

            <label class="check mt-8">
                <input type="checkbox" name="terms" value="1" <?php echo e(old('terms') ? 'checked' : ''); ?>>
                <span>I am 18+ and I agree to the betting rules, fair play policy and responsible gaming guidelines.</span>
            </label>

            <button class="btn btn--primary btn--block btn--lg mt-20" type="submit">Create my account</button>
        </form>

        <div class="auth-switch">Already have an account? <a href="<?php echo e(route('login')); ?>">Sign in</a></div>
    </div>

    <p class="text-center mt-14"><a class="text-muted" href="<?php echo e(route('home')); ?>" style="font-size:.78rem">← Back to sportsbook</a></p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/auth/register.blade.php ENDPATH**/ ?>