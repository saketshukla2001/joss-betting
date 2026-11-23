<?php $__env->startSection('title', 'Casino Profit Loss'); ?>

<?php $__env->startSection('content'); ?>
    <div class="classic-form-header">
        <span>CASINO PROFIT LOSS</span>
        <a class="classic-back-btn" href="<?php echo e(route('admin.settings.index')); ?>">Back</a>
    </div>

    <div class="admin-stat-grid" style="margin-top:0">
        <div class="stat"><div class="stat__label">Casino stake</div><div class="stat__value">0</div></div>
        <div class="stat"><div class="stat__label">Casino payout</div><div class="stat__value">0</div></div>
        <div class="stat"><div class="stat__label">Casino profit</div><div class="stat__value">0</div></div>
        <div class="stat"><div class="stat__label">Casino commission</div><div class="stat__value">0</div></div>
    </div>

    <div class="classic-card">
        <div class="classic-card__head"><span>CASINO REPORT</span></div>
        <div class="empty" style="padding:40px 20px">
            <strong>Casino module is not enabled</strong>
            <p>This build is sportsbook-only, so there is no casino betting data to report.</p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/settings/casino-profit-loss.blade.php ENDPATH**/ ?>