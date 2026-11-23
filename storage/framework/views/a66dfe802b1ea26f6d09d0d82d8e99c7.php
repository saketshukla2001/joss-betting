<?php $__env->startSection('title', $config['label'].' Transaction'); ?>

<?php $__env->startSection('content'); ?>
    <div class="classic-form-header">
        <span><?php echo e(strtoupper($config['label'])); ?> TRANSACTION</span>
        <a class="classic-back-btn classic-back-btn--light" href="<?php echo e(route('admin.dashboard')); ?>">Back</a>
    </div>

    <div class="cash-form-panel">
        <form method="POST" action="<?php echo e(route('admin.cash.store', $level)); ?>">
            <?php echo csrf_field(); ?>

            <div class="cash-grid">
                <div class="field">
                    <label for="client_id">CLIENT<span class="req">*</span></label>
                    <div class="select-with-icon">
                        <select class="select" id="client_id" name="client_id" required>
                            <option value="">SELECT CLIENT</option>
                            <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($client->id); ?>" <?php if((int) old('client_id') === $client->id): echo 'selected'; endif; ?>>
                                    <?php echo e($client->username); ?> (<?php echo e($client->agent_code); ?>) · <?php echo e(number_format($client->balance, 0)); ?> coins
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <span class="select-search"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.5 15.5L21 21"/></svg></span>
                    </div>
                </div>

                <div class="field">
                    <label for="collection">COLLECTION<span class="req">*</span></label>
                    <input class="input" id="collection" name="collection" value="<?php echo e(old('collection', 'CASH')); ?>" placeholder="CASH" required>
                </div>

                <div class="field">
                    <label for="date">DATE<span class="req">*</span></label>
                    <input class="input" id="date" name="date" type="date" value="<?php echo e(old('date', now()->toDateString())); ?>" required>
                </div>

                <div class="field">
                    <label for="amount">AMOUNT<span class="req">*</span></label>
                    <input class="input" id="amount" name="amount" type="number" min="1" step="1" value="<?php echo e(old('amount')); ?>" required>
                </div>

                <div class="field">
                    <label for="payment_type">PAYMENT TYPE<span class="req">*</span></label>
                    <select class="select" id="payment_type" name="payment_type" required>
                        <option value="">SELECT PAYMENT TYPE</option>
                        <?php $__currentLoopData = ['CASH' => 'CASH', 'ONLINE' => 'ONLINE', 'BANK' => 'BANK', 'UPI' => 'UPI']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>" <?php if(old('payment_type') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="field">
                    <label for="comment">COMMENT<span class="req">*</span></label>
                    <input class="input" id="comment" name="comment" value="<?php echo e(old('comment')); ?>" placeholder="Comment" required>
                </div>
            </div>

            <button class="cash-submit" type="submit">Submit</button>
        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/classic/cash.blade.php ENDPATH**/ ?>