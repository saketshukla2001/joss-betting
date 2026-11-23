<?php $__env->startSection('title', 'Wallet'); ?>

<?php $__env->startSection('content'); ?>
    <?php $user = auth()->user(); ?>
    <div class="container">
        <div class="page-head">
            <div>
                <span class="eyebrow">Cashier</span>
                <h1 class="mt-8">Wallet</h1>
                <p>Deposit requests, withdrawals and a full ledger of every movement.</p>
            </div>
            <a class="btn btn--outline" href="<?php echo e(route('bets.index')); ?>">My bets</a>
        </div>

        <?php echo $__env->make('partials.errors', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="stat-grid mb-20">
            <div class="stat stat--lime">
                <div class="stat__label">Available balance</div>
                <div class="stat__value" data-balance><?php echo e(money($user->balance)); ?></div>
                <div class="stat__meta">Ready to withdraw or bet</div>
            </div>
            <div class="stat">
                <div class="stat__label">Exposure</div>
                <div class="stat__value" data-exposure><?php echo e(money($user->exposure)); ?></div>
                <div class="stat__meta">Locked in open tickets</div>
            </div>
            <div class="stat">
                <div class="stat__label">Lifetime deposits</div>
                <div class="stat__value"><?php echo e(money($summary['deposits'])); ?></div>
                <div class="stat__meta">Approved only</div>
            </div>
            <div class="stat">
                <div class="stat__label">Winnings received</div>
                <div class="stat__value"><?php echo e(money($summary['winnings'])); ?></div>
                <div class="stat__meta"><?php echo e($summary['pending_withdrawals'] > 0 ? money($summary['pending_withdrawals']).' withdrawal on hold' : 'No withdrawal on hold'); ?></div>
            </div>
        </div>

        <div class="wallet-grid mb-20">
            <div class="card">
                <div class="card__head">
                    <div>
                        <h3>Add funds</h3>
                        <p>Submit a deposit request for admin approval</p>
                    </div>
                    <span class="badge badge--info">Manual review</span>
                </div>
                <div class="card__body">
                    <form method="POST" action="<?php echo e(route('wallet.deposit')); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="field">
                            <label for="deposit-amount">Amount</label>
                            <div class="input-group">
                                <span class="prefix"><?php echo e($siteSettings['currency']); ?></span>
                                <input class="input" id="deposit-amount" name="amount" type="number" min="<?php echo e($siteSettings['min_deposit']); ?>" step="1" placeholder="<?php echo e($siteSettings['min_deposit']); ?>" value="<?php echo e(old('amount')); ?>" required>
                            </div>
                            <div class="hint">Minimum deposit <?php echo e(money($siteSettings['min_deposit'])); ?>. Funds appear after approval.</div>
                        </div>

                        <div class="field">
                            <span class="label">Payment method</span>
                            <div class="payment-methods">
                                <label class="method-option">
                                    <input type="radio" name="method" value="upi" <?php if(old('method', 'upi') === 'upi'): echo 'checked'; endif; ?>>
                                    <span><em>📱</em>UPI</span>
                                </label>
                                <label class="method-option">
                                    <input type="radio" name="method" value="bank" <?php if(old('method') === 'bank'): echo 'checked'; endif; ?>>
                                    <span><em>🏦</em>Bank</span>
                                </label>
                                <label class="method-option">
                                    <input type="radio" name="method" value="usdt" <?php if(old('method') === 'usdt'): echo 'checked'; endif; ?>>
                                    <span><em>₿</em>USDT</span>
                                </label>
                            </div>
                        </div>

                        <div class="field">
                            <label for="deposit-ref">Transaction reference / UTR</label>
                            <input class="input" id="deposit-ref" name="transaction_ref" type="text" value="<?php echo e(old('transaction_ref')); ?>" placeholder="e.g. UPI-8899221100" required>
                            <div class="hint">Use the UTR, bank reference or USDT transaction hash.</div>
                        </div>

                        <button class="btn btn--primary btn--block" type="submit">Submit deposit request</button>
                    </form>
                </div>
            </div>

            <div class="card" id="withdraw">
                <div class="card__head">
                    <div>
                        <h3>Withdraw winnings</h3>
                        <p>Funds are held until an admin approves</p>
                    </div>
                    <span class="badge badge--warning">On hold</span>
                </div>
                <div class="card__body">
                    <form method="POST" action="<?php echo e(route('wallet.withdraw')); ?>" data-method-form>
                        <?php echo csrf_field(); ?>
                        <div class="field">
                            <label for="withdraw-amount">Amount</label>
                            <div class="input-group">
                                <span class="prefix"><?php echo e($siteSettings['currency']); ?></span>
                                <input class="input" id="withdraw-amount" name="amount" type="number" min="<?php echo e($siteSettings['min_withdrawal']); ?>" step="1" placeholder="<?php echo e($siteSettings['min_withdrawal']); ?>" value="<?php echo e(old('amount')); ?>" required>
                            </div>
                            <div class="hint">Available <?php echo e(money($user->balance)); ?> · minimum <?php echo e(money($siteSettings['min_withdrawal'])); ?></div>
                        </div>

                        <div class="field">
                            <span class="label">Payout method</span>
                            <div class="payment-methods">
                                <label class="method-option">
                                    <input type="radio" name="method" value="upi" <?php if(old('method', 'upi') === 'upi'): echo 'checked'; endif; ?>>
                                    <span><em>📱</em>UPI</span>
                                </label>
                                <label class="method-option">
                                    <input type="radio" name="method" value="bank" <?php if(old('method') === 'bank'): echo 'checked'; endif; ?>>
                                    <span><em>🏦</em>Bank</span>
                                </label>
                                <label class="method-option">
                                    <input type="radio" name="method" value="usdt" <?php if(old('method') === 'usdt'): echo 'checked'; endif; ?>>
                                    <span><em>₿</em>USDT</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="field">
                                <label for="account-name">Account holder name</label>
                                <input class="input" id="account-name" name="account_name" type="text" value="<?php echo e(old('account_name', $user->name)); ?>" required>
                            </div>
                            <div class="field">
                                <label for="account-number">Account number / wallet address</label>
                                <input class="input" id="account-number" name="account_number" type="text" value="<?php echo e(old('account_number')); ?>" placeholder="Bank A/C or USDT address" required>
                            </div>
                        </div>

                        <div class="method-fields" data-method-field="upi">
                            <div class="field">
                                <label for="upi-id">UPI ID</label>
                                <input class="input" id="upi-id" name="upi_id" type="text" value="<?php echo e(old('upi_id')); ?>" placeholder="name@bank">
                            </div>
                        </div>

                        <div class="method-fields" data-method-field="bank">
                            <div class="field">
                                <label for="ifsc">IFSC code</label>
                                <input class="input" id="ifsc" name="ifsc" type="text" value="<?php echo e(old('ifsc')); ?>" placeholder="HDFC0001234">
                            </div>
                        </div>

                        <button class="btn btn--primary btn--block" type="submit">Request withdrawal</button>
                        <p class="hint mt-8">The amount is deducted from your available balance immediately and held until review. Rejected requests are refunded.</p>
                    </form>
                </div>
            </div>
        </div>

        <div class="wallet-grid mb-20">
            <div class="card">
                <div class="card__head"><h3>Deposit requests</h3><span class="badge badge--muted"><?php echo e($deposits->count()); ?> latest</span></div>
                <?php if($deposits->isEmpty()): ?>
                    <div class="empty" style="padding:24px"><strong>No deposit requests</strong><p>Submit one above to get started.</p></div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="data-table table--stack">
                            <thead><tr><th>ID</th><th>Amount</th><th>Method</th><th>Reference</th><th>Status</th><th>Date</th></tr></thead>
                            <tbody>
                                <?php $__currentLoopData = $deposits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deposit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td data-label="ID">#<?php echo e($deposit->id); ?></td>
                                        <td data-label="Amount" class="mono"><?php echo e(money($deposit->amount)); ?></td>
                                        <td data-label="Method"><?php echo e($deposit->method_label); ?></td>
                                        <td data-label="Reference"><span class="ticket-code"><?php echo e($deposit->transaction_ref); ?></span></td>
                                        <td data-label="Status"><span class="badge badge--<?php echo e($deposit->status_class); ?>"><?php echo e(ucfirst($deposit->status)); ?></span></td>
                                        <td data-label="Date"><span class="sub" style="margin:0"><?php echo e($deposit->created_at->format('d M, h:i A')); ?></span></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card__head"><h3>Withdrawal requests</h3><span class="badge badge--muted"><?php echo e($withdrawals->count()); ?> latest</span></div>
                <?php if($withdrawals->isEmpty()): ?>
                    <div class="empty" style="padding:24px"><strong>No withdrawals yet</strong><p>Your payout requests will appear here.</p></div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="data-table table--stack">
                            <thead><tr><th>ID</th><th>Amount</th><th>Method</th><th>Account</th><th>Status</th><th>Date</th></tr></thead>
                            <tbody>
                                <?php $__currentLoopData = $withdrawals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $withdrawal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td data-label="ID">#<?php echo e($withdrawal->id); ?></td>
                                        <td data-label="Amount" class="mono"><?php echo e(money($withdrawal->amount)); ?></td>
                                        <td data-label="Method"><?php echo e($withdrawal->method_label); ?></td>
                                        <td data-label="Account"><?php echo e($withdrawal->account_name); ?><span class="sub"><?php echo e($withdrawal->upi_id ?: $withdrawal->account_number); ?></span></td>
                                        <td data-label="Status">
                                            <span class="badge badge--<?php echo e($withdrawal->status_class); ?>"><?php echo e(ucfirst($withdrawal->status)); ?></span>
                                            <?php if($withdrawal->admin_note): ?><span class="sub"><?php echo e($withdrawal->admin_note); ?></span><?php endif; ?>
                                        </td>
                                        <td data-label="Date"><span class="sub" style="margin:0"><?php echo e($withdrawal->created_at->format('d M, h:i A')); ?></span></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card__head">
                <div>
                    <h3>Transaction ledger</h3>
                    <p>Running balance after every movement</p>
                </div>
            </div>
            <?php if($transactions->isEmpty()): ?>
                <div class="empty"><strong>No transactions yet</strong><p>Deposits, bets and payouts will appear here.</p></div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="data-table table--stack">
                        <thead>
                            <tr><th>Type</th><th>Description</th><th>Amount</th><th>Balance after</th><th>Status</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td data-label="Type">
                                        <span class="flex-center">
                                            <span class="tx-mini__ico" style="width:26px;height:26px;font-size:.75rem"><?php echo e($transaction->type_icon); ?></span>
                                            <span class="strong"><?php echo e($transaction->type_label); ?></span>
                                        </span>
                                    </td>
                                    <td data-label="Description"><?php echo e($transaction->description); ?></td>
                                    <td data-label="Amount" class="mono <?php echo e($transaction->amount >= 0 ? 'text-green' : 'text-red'); ?>">
                                        <?php echo e($transaction->amount >= 0 ? '+' : ''); ?><?php echo e(money($transaction->amount)); ?>

                                    </td>
                                    <td data-label="Balance after" class="mono"><?php echo e(money($transaction->balance_after)); ?></td>
                                    <td data-label="Status"><span class="badge badge--<?php echo e($transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger')); ?>"><?php echo e($transaction->status_label); ?></span></td>
                                    <td data-label="Date"><span class="sub" style="margin:0"><?php echo e($transaction->created_at->format('d M, h:i A')); ?></span></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <?php echo e($transactions->links('partials.pagination')); ?>

            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/wallet/index.blade.php ENDPATH**/ ?>