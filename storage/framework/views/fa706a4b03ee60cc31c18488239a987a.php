<?php $__env->startSection('title', $match->label); ?>

<?php $__env->startSection('content'); ?>
    <div class="live-page">
        <div class="live-head">
            <span><?php echo e(strtoupper($match->home_team)); ?> V <?php echo e(strtoupper($match->away_team)); ?></span>
            <div class="live-head-icons">
                <button type="button" onclick="window.location.reload()" title="Refresh">⟳</button>
                <button type="button" onclick="window.print()" title="Print">▤</button>
            </div>
        </div>

        <div class="live-tabs">
            <button type="button" class="live-tab active" data-live-tab="my">My Book</button>
            <button type="button" class="live-tab" data-live-tab="tii">Tii Book</button>
        </div>

        <div class="live-panel" data-live-panel="my">
            <div class="live-team-table">
                <table>
                    <thead><tr><th></th><th>LAGAI</th><th>KHAI</th></tr></thead>
                    <tbody>
                        <?php $__currentLoopData = $teamRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="live-team-name"><?php echo e(strtoupper($row['team'])); ?></td>
                                <td class="live-lagai"><?php echo e($row['lagai']); ?></td>
                                <td class="live-khai"><?php echo e($row['khai']); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="live-section-head fancy-head">
                <span>FANCY (COMM.)</span><span>NOT</span><span>YES</span>
            </div>
            <div class="live-fancy-list">
                <?php $__empty_1 = true; $__currentLoopData = $fancyRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php if($row['suspended']): ?>
                        <div class="live-fancy-row suspended">
                            <div class="live-fancy-name"><?php echo e(strtoupper($row['name'])); ?><small>SESSION LIMIT : <?php echo e(number_format($row['limit'])); ?></small></div>
                            <div class="live-fancy-suspended">SUSPENDED</div>
                        </div>
                    <?php else: ?>
                        <div class="live-fancy-row">
                            <div class="live-fancy-name">
                                <?php echo e(strtoupper($row['name'])); ?>

                                <small>SESSION LIMIT : <?php echo e(number_format($row['limit'])); ?></small>
                            </div>
                            <div class="live-fancy-badge"><?php echo e($row['badge'] ? 'B' : ''); ?></div>
                            <div class="live-fancy-not"><?php echo e($row['not']); ?></div>
                            <div class="live-fancy-yes"><?php echo e($row['yes']); ?></div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="live-empty">NO FANCY MARKETS</div>
                <?php endif; ?>
            </div>

            <div class="live-section-head clients-head"><span>CLIENTS SESSION PL</span></div>
            <div class="table-wrap">
                <table class="live-table live-clients-table">
                    <thead>
                        <tr>
                            <th>TOTAL</th><th>SESSION AMT.</th><th>TOTAL</th><th>SESSION COMM</th>
                            <th>TOTAL COMM</th><th>TOTAL AMOUNT</th><th>MY SHARE</th><th>NET PL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $clientSessionRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($row['user']?->username); ?> (<?php echo e($row['user']?->agent_code); ?>)</td>
                                <td><?php echo e(number_format($row['session_amt'], 2)); ?></td>
                                <td><?php echo e(number_format($row['total'], 2)); ?></td>
                                <td class="text-red"><?php echo e(number_format($row['session_comm'], 2)); ?></td>
                                <td class="text-red"><?php echo e(number_format($row['total_comm'], 2)); ?></td>
                                <td><?php echo e(number_format($row['total_amount'], 2)); ?></td>
                                <td class="text-green"><?php echo e(number_format($row['my_share'], 2)); ?></td>
                                <td class="text-green"><?php echo e(number_format($row['net_pl'], 2)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td>TOTAL</td><td>0.00</td><td>0.00</td><td class="text-red">0.00</td>
                                <td class="text-red">0.00</td><td>0.00</td><td class="text-green">0.00</td><td class="text-green">0.00</td>
                            </tr>
                        <?php endif; ?>
                        <?php if($clientSessionRows->isNotEmpty()): ?>
                            <tr class="live-total-row">
                                <td>TOTAL</td>
                                <td><?php echo e(number_format($clientTotals['session_amt'], 2)); ?></td>
                                <td><?php echo e(number_format($clientTotals['total'], 2)); ?></td>
                                <td class="text-red"><?php echo e(number_format($clientTotals['session_comm'], 2)); ?></td>
                                <td class="text-red"><?php echo e(number_format($clientTotals['total_comm'], 2)); ?></td>
                                <td><?php echo e(number_format($clientTotals['total_amount'], 2)); ?></td>
                                <td class="text-green"><?php echo e(number_format($clientTotals['my_share'], 2)); ?></td>
                                <td class="text-green"><?php echo e(number_format($clientTotals['net_pl'], 2)); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="live-section-head match-stats-head">
                <span>MATCH STATS</span>
                <button type="button" class="live-small-btn" onclick="document.getElementById('live-match-stats').scrollIntoView({behavior:'smooth'})">MATCH BETS</button>
            </div>
            <div class="table-wrap" id="live-match-stats">
                <table class="live-table">
                    <thead><tr><th>PLACE TIME</th><th>USERNAME</th><th>RUNNER NAME</th><th>BET TYPE</th><th>BET PRICE</th><th>BET VALUE</th><th>BET AMOUNT</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $bets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e(strtoupper($bet->created_at->format('d-M-Y h:i A'))); ?></td>
                                <td><?php echo e($bet->user?->username); ?></td>
                                <td><?php echo e($bet->selection_name); ?></td>
                                <td><?php echo e($bet->market_name); ?></td>
                                <td><?php echo e(odds($bet->odds)); ?></td>
                                <td><?php echo e(number_format($bet->potential_return, 2)); ?></td>
                                <td><?php echo e(number_format($bet->stake, 2)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="7"><div class="live-empty">NO DATA</div></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="live-section-head completed-head">
                <span>COMPLETED SESSIONS</span>
                <button type="button" class="live-small-btn" onclick="window.location.reload()">Refresh</button>
            </div>
            <div class="table-wrap">
                <table class="live-table">
                    <thead><tr><th>S.NO</th><th>RUNNER NAME</th><th>WINNER</th><th>COMM+</th><th>COMM-</th><th>PL</th><th>ACTION</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $completedSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($session['no']); ?></td>
                                <td><?php echo e($session['name']); ?></td>
                                <td><?php echo e($session['winner']); ?></td>
                                <td class="text-green"><?php echo e(number_format($session['comm_plus'], 2)); ?></td>
                                <td class="text-red"><?php echo e(number_format($session['comm_minus'], 2)); ?></td>
                                <td class="<?php echo e($session['pl'] < 0 ? 'text-red' : 'text-green'); ?>"><?php echo e(number_format($session['pl'], 2)); ?></td>
                                <td class="live-actions">
                                    <a href="<?php echo e(route('admin.sports.match-pl', $match)); ?>">Total Pl</a>
                                    <a href="<?php echo e(route('admin.sports.match-bets', [$match, 'session-all'])); ?>">Bets</a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="7"><div class="live-empty">NO DATA</div></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="live-panel" data-live-panel="tii" hidden>
            <div class="live-empty" style="padding:60px">NO DATA</div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    document.querySelectorAll('[data-live-tab]').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('[data-live-tab]').forEach(function (item) { item.classList.remove('active'); });
            document.querySelectorAll('[data-live-panel]').forEach(function (panel) { panel.hidden = true; });
            tab.classList.add('active');
            var panel = document.querySelector('[data-live-panel="' + tab.dataset.liveTab + '"]');
            if (panel) panel.hidden = false;
        });
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/classic/match-view.blade.php ENDPATH**/ ?>