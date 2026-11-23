<?php $__env->startSection('title', 'Search Client'); ?>

<?php $__env->startSection('content'); ?>
    <div class="classic-form-header">
        <span>SEARCH CLIENT</span>
        <a class="classic-back-btn" href="<?php echo e(route('admin.settings.index')); ?>">Back</a>
    </div>

    <div class="client-search-panel">
        <form method="GET" action="<?php echo e(route('admin.settings.search-client')); ?>" class="client-search-form">
            <input class="client-search-input" type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search Code">
            <button class="client-search-btn" type="submit">Search</button>
        </form>

        <?php if(request()->filled('q')): ?>
            <div class="table-wrap" style="margin-top:18px">
                <table class="data-table">
                    <thead><tr><th>Name</th><th>Username</th><th>Code</th><th>Reference</th><th>Agent</th><th>Coins</th><th>Bets</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($client->name); ?></td>
                                <td><?php echo e($client->username); ?></td>
                                <td class="mono"><?php echo e($client->agent_code); ?></td>
                                <td><?php echo e($client->reference ?? '—'); ?></td>
                                <td><?php echo e($client->parent?->username ?? '—'); ?></td>
                                <td class="mono"><?php echo e(number_format($client->balance, 0)); ?></td>
                                <td><?php echo e($client->bets_count); ?></td>
                                <td><a class="classic-view-btn" href="<?php echo e(route('admin.hierarchy.show', ['client_master', $client])); ?>">View</a></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="8"><div class="empty"><strong>No client found for this code</strong></div></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php echo e($clients->links('partials.pagination')); ?>

        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/settings/search-client.blade.php ENDPATH**/ ?>