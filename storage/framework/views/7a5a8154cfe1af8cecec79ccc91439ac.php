<?php $__env->startSection('title', 'Agent Commission Report'); ?>

<?php $__env->startSection('content'); ?>
    <div class="classic-form-header">
        <span>AGENT COMMISSION REPORT</span>
        <a class="classic-back-btn classic-back-btn--light" href="<?php echo e(route('admin.settings.index')); ?>">Back</a>
    </div>

    <div class="commission-report-panel">
        <form method="GET" action="<?php echo e(route('admin.comm-report.index')); ?>" class="commission-filter">
            <div class="commission-date-group">
                <input type="date" name="start_date" value="<?php echo e(request('start_date')); ?>" placeholder="Start Date">
                <span class="date-arrow">→</span>
                <input type="date" name="end_date" value="<?php echo e(request('end_date')); ?>" placeholder="End Date">
            </div>
            <input class="commission-agent" type="text" name="agent" value="<?php echo e(request('agent')); ?>" placeholder="Agent Username">
            <button class="commission-apply" type="submit">Apply</button>
            <a class="commission-reset" href="<?php echo e(route('admin.comm-report.index')); ?>">↻ Reset History</a>
        </form>

        <div class="table-wrap">
            <table class="data-table commission-table">
                <thead>
                    <tr>
                        <th rowspan="2">DATE</th>
                        <th rowspan="2">USERNAME</th>
                        <th colspan="6">MILA HAI</th>
                        <th colspan="5">DIYA HAI</th>
                    </tr>
                    <tr>
                        <th>M.COMM</th><th>S.COMM</th><th>C.COMM</th><th>MATKA.COMM</th><th>T.COMM</th><th>ACTION</th>
                        <th>M.COMM</th><th>S.COMM</th><th>C.COMM</th><th>MATKA.COMM</th><th>T.COMM</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="nowrap"><?php echo e(strtoupper(\Illuminate\Support\Carbon::parse($row['date'])->format('d-M-Y'))); ?></td>
                            <td><?php echo e($row['username']); ?></td>
                            <td class="mono text-green"><?php echo e(number_format($row['received']['m'], 2)); ?></td>
                            <td class="mono text-green"><?php echo e(number_format($row['received']['s'], 2)); ?></td>
                            <td class="mono text-green"><?php echo e(number_format($row['received']['c'], 2)); ?></td>
                            <td class="mono text-green"><?php echo e(number_format($row['received']['matka'], 2)); ?></td>
                            <td class="mono text-green"><?php echo e(number_format($row['received']['t'], 2)); ?></td>
                            <td><a class="classic-view-btn" href="<?php echo e(route('admin.comm-report.index', ['agent' => $row['username']])); ?>">View</a></td>
                            <td class="mono text-red"><?php echo e(number_format($row['given']['m'], 2)); ?></td>
                            <td class="mono text-red"><?php echo e(number_format($row['given']['s'], 2)); ?></td>
                            <td class="mono text-red"><?php echo e(number_format($row['given']['c'], 2)); ?></td>
                            <td class="mono text-red"><?php echo e(number_format($row['given']['matka'], 2)); ?></td>
                            <td class="mono text-red"><?php echo e(number_format($row['given']['t'], 2)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="13">
                                <div class="no-data">
                                    <svg viewBox="0 0 64 48" fill="none" stroke="#cfcfcf" stroke-width="2"><path d="M4 8h56v32H4z"/><path d="M4 8l28 20L60 8"/></svg>
                                    <span>NO DATA</span>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="strong">TOTAL</td>
                        <td></td>
                        <td class="mono text-green"><?php echo e(number_format($totals['received']['m'], 2)); ?></td>
                        <td class="mono text-green"><?php echo e(number_format($totals['received']['s'], 2)); ?></td>
                        <td class="mono text-green"><?php echo e(number_format($totals['received']['c'], 2)); ?></td>
                        <td class="mono text-green"><?php echo e(number_format($totals['received']['matka'], 2)); ?></td>
                        <td class="mono text-green"><?php echo e(number_format($totals['received']['t'], 2)); ?></td>
                        <td></td>
                        <td class="mono text-red"><?php echo e(number_format($totals['given']['m'], 2)); ?></td>
                        <td class="mono text-red"><?php echo e(number_format($totals['given']['s'], 2)); ?></td>
                        <td class="mono text-red"><?php echo e(number_format($totals['given']['c'], 2)); ?></td>
                        <td class="mono text-red"><?php echo e(number_format($totals['given']['matka'], 2)); ?></td>
                        <td class="mono text-red"><?php echo e(number_format($totals['given']['t'], 2)); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/classic/comm-report.blade.php ENDPATH**/ ?>