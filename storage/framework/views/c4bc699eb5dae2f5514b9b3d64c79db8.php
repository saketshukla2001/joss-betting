<?php $__env->startSection('title', 'Agent Wallet'); ?>
<?php $__env->startSection('page-title', 'Wallet'); ?>
<?php $__env->startSection('page-subtitle', 'Commission payouts and withdrawals'); ?>

<?php $__env->startSection('content'); ?>
    <div class="admin-page-head">
        <div>
            <h1>Agent wallet</h1>
            <p>Commission payouts land here. Request a withdrawal when you are ready.</p>
        </div>
    </div>

    <div class="admin-stat-grid">
        <div class="stat stat--lime">
            <div class="stat__label">Wallet balance</div>
            <div class="stat__value"><?php echo e(money($agent->balance)); ?></div>
            <div class="stat__meta">Available to withdraw</div>
        </div>
        <div class="stat">
            <div class="stat__label">Commission balance</div>
            <div class="stat__value"><?php echo e(money($agent->commission_balance)); ?></div>
            <div class="stat__meta">Awaiting admin payout</div>
        </div>
        <div class="stat">
            <div class="stat__label">Pending withdrawals</div>
            <div class="stat__value"><?php echo e(money($summary['pending_withdrawals'])); ?></div>
            <div class="stat__meta">On hold for review</div>
        </div>
        <div class="stat">
            <div class="stat__label">Paid commission</div>
            <div class="stat__value text-green"><?php echo e(money($summary['paid_commission'])); ?></div>
            <div class="stat__meta">Lifetime</div>
        </div>
    </div>

    <div class="admin-grid-2 mt-20">
        <div class="admin-card">
            <div class="admin-card__head"><h3>Request withdrawal</h3><span class="badge badge--warning">Manual review</span></div>
            <div class="admin-card__body">
                <form method="POST" action="<?php echo e(route('agent.wallet.withdraw')); ?>" data-method-form>
                    <?php echo csrf_field(); ?>
                    <div class="field">
                        <label for="amount">Amount</label>
                        <div class="input-group">
                            <span class="prefix"><?php echo e($siteSettings['currency']); ?></span>
                            <input class="input" id="amount" name="amount" type="number" min="<?php echo e($siteSettings['min_withdrawal']); ?>" step="1" value="<?php echo e(old('amount')); ?>" required>
                        </div>
                        <div class="hint">Available <?php echo e(money($agent->balance)); ?> · minimum <?php echo e(money($siteSettings['min_withdrawal'])); ?></div>
                    </div>

                    <div class="field">
                        <span class="label">Payout method</span>
                        <div class="payment-methods">
                            <label class="method-option"><input type="radio" name="method" value="upi" <?php if(old('method', 'upi') === 'upi'): echo 'checked'; endif; ?>><span><em>📱</em>UPI</span></label>
                            <label class="method-option"><input type="radio" name="method" value="bank" <?php if(old('method') === 'bank'): echo 'checked'; endif; ?>><span><em>🏦</em>Bank</span></label>
                            <label class="method-option"><input type="radio" name="method" value="usdt" <?php if(old('method') === 'usdt'): echo 'checked'; endif; ?>><span><em>₿</em>USDT</span></label>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="field">
                            <label for="account_name">Account holder</label>
                            <input class="input" id="account_name" name="account_name" value="<?php echo e(old('account_name', $agent->name)); ?>" required>
                        </div>
                        <div class="field">
                            <label for="account_number">Account number / wallet address</label>
                            <input class="input" id="account_number" name="account_number" value="<?php echo e(old('account_number')); ?>" required>
                        </div>
                    </div>

                    <div class="method-fields" data-method-field="upi">
                        <div class="field"><label for="upi_id">UPI ID</label><input class="input" id="upi_id" name="upi_id" value="<?php echo e(old('upi_id')); ?>" placeholder="name@bank"></div>
                    </div>
                    <div class="method-fields" data-method-field="bank">
                        <div class="field"><label for="ifsc">IFSC</label><input class="input" id="ifsc" name="ifsc" value="<?php echo e(old('ifsc')); ?>" placeholder="HDFC0001234"></div>
                    </div>

                    <?php if($agent->balance < (float) $siteSettings['min_withdrawal']): ?>
                        <div class="alert alert--info" style="margin-bottom:12px">Your wallet is below the minimum withdrawal. The admin must release pending commission first.</div>
                    <?php endif; ?>
                    <button class="btn btn--primary btn--block" type="submit" <?php if($agent->balance < (float) $siteSettings['min_withdrawal']): echo 'disabled'; endif; ?>>Request withdrawal</button>
                    <p class="hint mt-8">The amount is held immediately and refunded if the admin rejects the request.</p>
                </form>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card__head"><h3>Withdrawal history</h3></div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>ID</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $withdrawals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $withdrawal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>#<?php echo e($withdrawal->id); ?></td>
                                <td class="mono"><?php echo e(money($withdrawal->amount)); ?></td>
                                <td><?php echo e($withdrawal->method_label); ?></td>
                                <td>
                                    <span class="badge badge--<?php echo e($withdrawal->status_class); ?>"><?php echo e(ucfirst($withdrawal->status)); ?></span>
                                    <?php if($withdrawal->admin_note): ?><span class="sub"><?php echo e($withdrawal->admin_note); ?></span><?php endif; ?>
                                </td>
                                <td><span class="sub" style="margin:0"><?php echo e($withdrawal->created_at->format('d M, H:i')); ?></span></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="5"><div class="empty">No withdrawals yet.</div></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="admin-card mt-20">
        <div class="admin-card__head"><h3>Wallet ledger</h3></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Type</th><th>Description</th><th>Amount</th><th>Balance after</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($transaction->type_label); ?></td>
                            <td><?php echo e($transaction->description); ?></td>
                            <td class="mono <?php echo e($transaction->amount >= 0 ? 'text-green' : 'text-red'); ?>"><?php echo e($transaction->amount >= 0 ? '+' : ''); ?><?php echo e(money($transaction->amount)); ?></td>
                            <td class="mono"><?php echo e(money($transaction->balance_after)); ?></td>
                            <td><span class="badge badge--<?php echo e($transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger')); ?>"><?php echo e($transaction->status_label); ?></span></td>
                            <td><span class="sub" style="margin:0"><?php echo e($transaction->created_at->format('d M, H:i')); ?></span></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6"><div class="empty">No wallet transactions yet. Commission payouts will appear here.</div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($transactions->links('partials.pagination')); ?>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.agent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/agent/wallet.blade.php ENDPATH**/ ?>