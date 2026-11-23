<?php $__env->startSection('title', 'Setting'); ?>

<?php $__env->startSection('content'); ?>
    <div class="classic-tiles">
        <a class="classic-tile" href="<?php echo e(route('admin.settings.statement')); ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 3h14v18H5z"/><path d="M8 7h8M8 11h8M8 15h5"/></svg>
            Statement
        </a>
        <a class="classic-tile" href="<?php echo e(route('admin.settings.account-operations')); ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a8 8 0 0116 0v1"/></svg>
            Account Operations
        </a>
        <a class="classic-tile" href="<?php echo e(route('admin.ledger.profit-loss')); ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>
            Profit &amp; Loss
        </a>
        <a class="classic-tile" href="<?php echo e(route('admin.settings.casino-profit-loss')); ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18M8 14h.01M12 14h.01M16 14h.01"/></svg>
            Casino Profit Loss
        </a>
        <a class="classic-tile" href="<?php echo e(route('admin.settings.search-client')); ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.5 15.5L21 21"/></svg>
            Search Client
        </a>
        <a class="classic-tile" href="<?php echo e(route('admin.settings.appearance')); ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><circle cx="8" cy="10" r="1.2" fill="currentColor"/><circle cx="12" cy="7" r="1.2" fill="currentColor"/><circle cx="16" cy="10" r="1.2" fill="currentColor"/><path d="M12 21a3 3 0 010-6 3 3 0 000-6"/></svg>
            Panel Color
        </a>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/settings/index.blade.php ENDPATH**/ ?>