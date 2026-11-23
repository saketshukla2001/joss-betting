<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('code', '500'); ?> · <?php echo e($siteSettings['site_name'] ?? 'BetZone'); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>?v=20261113">
</head>
<body>
    <div style="min-height:100vh;display:grid;place-items:center;padding:24px;background:radial-gradient(ellipse 60% 60% at 50% 0%,rgba(201,255,69,.09),transparent 65%),var(--bg)">
        <div style="max-width:460px;text-align:center">
            <a class="brand" href="<?php echo e(url('/')); ?>" style="justify-content:center;margin-bottom:28px">
                <span class="brand__mark">B</span>
                <span class="brand__text">Bet<span>Zone</span></span>
            </a>
            <div style="font-size:5rem;font-weight:900;letter-spacing:-.08em;color:var(--lime);line-height:1"><?php echo $__env->yieldContent('code', '500'); ?></div>
            <h1 style="font-size:1.5rem;margin-top:14px"><?php echo $__env->yieldContent('title', 'Play interrupted'); ?></h1>
            <p class="text-muted" style="font-size:.9rem"><?php echo $__env->yieldContent('message', 'Something went wrong.'); ?></p>
            <div class="flex-center" style="justify-content:center;margin-top:22px">
                <a class="btn btn--primary" href="<?php echo e(url('/')); ?>">Back to sportsbook</a>
                <a class="btn btn--outline" href="javascript:history.back()">Go back</a>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/layouts/error.blade.php ENDPATH**/ ?>