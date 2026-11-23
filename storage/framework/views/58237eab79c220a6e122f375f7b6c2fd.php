<?php $__env->startSection('title', $config['details_title']); ?>

<?php $__env->startSection('content'); ?>
    <div class="classic-form-header">
        <span><?php echo e($config['details_title']); ?></span>
        <a class="classic-back-btn classic-back-btn--light" href="<?php echo e(route('admin.dashboard')); ?>">Back</a>
    </div>

    <div class="hierarchy-panel">
        <div class="hierarchy-toolbar">
            <span class="hierarchy-toolbar__spacer"></span>
            <a class="hierarchy-create" href="<?php echo e(route('admin.hierarchy.create', $level)); ?>">+ Create</a>
            <span class="hierarchy-toolbar__spacer"></span>
            <div class="hierarchy-toolbar__right">
                <a class="hierarchy-update" href="<?php echo e(route('admin.hierarchy.limits', $level)); ?>">+ Update</a>
                <button class="hierarchy-delete-selected" type="button" id="hierarchy-delete-btn">Delete Selected</button>
            </div>
        </div>

        <form class="hierarchy-filters" method="GET" action="<?php echo e(route('admin.hierarchy.index', $level)); ?>" id="hierarchy-filters" hidden>
            <input class="input" type="search" name="code" value="<?php echo e(request('code')); ?>" placeholder="Search Code">
            <input class="input" type="search" name="name" value="<?php echo e(request('name')); ?>" placeholder="Search Name">
            <select class="select" name="status">
                <option value="">All statuses</option>
                <?php $__currentLoopData = ['active', 'inactive', 'blocked']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status); ?>" <?php if(request('status') === $status): echo 'selected'; endif; ?>><?php echo e(strtoupper($status)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button class="btn btn--primary btn--sm" type="submit">Filter</button>
            <a class="btn btn--ghost btn--sm" href="<?php echo e(route('admin.hierarchy.index', $level)); ?>">Reset</a>
        </form>

        <form id="bulk-delete-form" method="POST" action="<?php echo e(route('admin.hierarchy.bulk-delete', $level)); ?>" data-confirm="Delete selected accounts? Accounts with downline or bets will be skipped.">
            <?php echo csrf_field(); ?>
        </form>

        <div class="table-wrap hierarchy-table-wrap">
            <table class="data-table hierarchy-table">
                <thead>
                    <tr>
                        <th rowspan="2"><input type="checkbox" id="hierarchy-select-all" aria-label="Select all"></th>
                        <th rowspan="2">CODE <button class="col-search" type="button" data-toggle-filters>⌕</button></th>
                        <th rowspan="2">NAME <button class="col-search" type="button" data-toggle-filters>⌕</button></th>
                        <th rowspan="2"><?php echo e($config['parent_label']); ?></th>
                        <th rowspan="2">D.O.J</th>
                        <th rowspan="2">PASSWORD</th>
                        <?php if($level !== 'client_master'): ?><th rowspan="2">SHARE</th><?php endif; ?>
                        <th colspan="3">COMM %</th>
                        <?php if($level === 'client_master'): ?><th rowspan="2">EXPO</th><?php endif; ?>
                        <th rowspan="2">CHIPS</th>
                        <th rowspan="2">STATUS</th>
                    </tr>
                    <tr>
                        <th>TYPE</th><th>MATCH</th><th>SESSION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $popupId = 'hierarchy-popup-'.$user->id; ?>
                        <tr>
                            <td>
                                <div class="row-tools">
                                    <button class="share-toggle partnership-toggle" type="button" data-partnership="<?php echo e(json_encode($partnerships[$user->id])); ?>" aria-label="Partnership details"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="6" r="2.5"/><circle cx="18" cy="18" r="2.5"/><path d="M8.3 10.8l7.4-3.6M8.3 13.2l7.4 3.6"/></svg></button>
                                    <button class="hierarchy-menu-toggle" type="button" data-popup-toggle="<?php echo e($popupId); ?>" aria-label="Actions">⋮</button>
                                    <input class="row-checkbox" type="checkbox" value="<?php echo e($user->id); ?>" aria-label="Select <?php echo e($user->username); ?>">
                                </div>
                            </td>
                            <td class="mono"><?php echo e($user->agent_code); ?></td>
                            <td>
                                <a class="hierarchy-name" href="<?php echo e(route('admin.hierarchy.show', [$level, $user])); ?>">
                                    <span class="eye-icon">◉</span><?php echo e($user->username); ?>

                                </a>
                            </td>
                            <td><?php echo e($user->parent ? $user->parent->agent_code.' ('.$user->parent->name.')' : '—'); ?></td>
                            <td class="nowrap"><?php echo e(strtoupper($user->created_at->format('d-M-Y'))); ?></td>
                            <td>******</td>
                            <?php if($level !== 'client_master'): ?>
                                <td><?php echo e(rtrim(rtrim(number_format($user->share_percent, 2), '0'), '.')); ?> %</td>
                            <?php endif; ?>
                            <td><?php echo e($user->commission_type === 'net_loss' ? 'NCO' : 'BBB'); ?></td>
                            <td><?php echo e(number_format($user->commission_rate, 0)); ?></td>
                            <td><?php echo e(number_format($user->session_commission_percent, 0)); ?></td>
                            <?php if($level === 'client_master'): ?>
                                <td class="text-red mono"><?php echo e(number_format($user->exposure, 2)); ?></td>
                            <?php endif; ?>
                            <td class="mono"><?php echo e(number_format($user->balance, 2)); ?></td>
                            <td>
                                <span class="badge <?php echo e($user->status === 'active' ? 'badge--success' : ($user->status === 'inactive' ? 'badge--danger' : 'badge--warning')); ?>">
                                    <?php echo e(strtoupper($user->status)); ?>

                                </span>
                            </td>
                        </tr>

                        <tr class="popup-row"><td colspan="12"><div class="row-popup" id="<?php echo e($popupId); ?>">
                            <a href="<?php echo e(route('admin.hierarchy.cash', [$level, $user, 'direction' => 'deposit'])); ?>">DEPOSIT</a>
                            <a href="<?php echo e(route('admin.hierarchy.cash', [$level, $user, 'direction' => 'withdraw'])); ?>">WITHDRAW</a>
                            <a href="<?php echo e(route('admin.hierarchy.login-report', [$level, $user])); ?>">LOGIN REPORT</a>
                            <a href="<?php echo e(route('admin.settings.account-operations')); ?>">ACCOUNT OPERATIONS</a>
                            <form method="POST" action="<?php echo e(route('admin.hierarchy.inactive', [$level, $user])); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit">INACTIVE</button>
                            </form>
                            <form method="POST" action="<?php echo e(route('admin.hierarchy.block', [$level, $user])); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit">BLOCK ACTIONS</button>
                            </form>
                            <a href="<?php echo e(route('admin.hierarchy.edit', [$level, $user])); ?>">EDIT</a>
                            <?php if($level !== 'client_master'): ?>
                                <a href="<?php echo e(route('admin.access.edit', $user)); ?>">PANEL ACCESS</a>
                            <?php endif; ?>
                            <a href="<?php echo e(route('admin.hierarchy.statement', [$level, $user])); ?>">STATEMENT</a>
                            <form method="POST" action="<?php echo e(route('admin.hierarchy.reset-password', [$level, $user])); ?>" data-confirm="Reset password for <?php echo e($user->username); ?>?">
                                <?php echo csrf_field(); ?>
                                <button type="submit">RESET PASSWORD</button>
                            </form>
                            <?php if($level === 'agent_master'): ?>
                                <a href="<?php echo e(route('admin.comm-report.index', ['agent' => $user->username])); ?>">AGENT COMMISSION REPORT</a>
                            <?php endif; ?>
                            <form method="POST" action="<?php echo e(route('admin.hierarchy.destroy', [$level, $user])); ?>" data-confirm="Delete account <?php echo e($user->username); ?>? This is permanent.">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="danger">DELETE</button>
                            </form>
                        </div></td></tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="12"><div class="no-data"><span>NO DATA</span></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php echo e($users->links('partials.pagination')); ?>

    </div>

    <div class="partnership-overlay" id="partnership-overlay">
        <div class="partnership-dialog" role="dialog" aria-modal="true">
            <div class="partnership-header">
                <span id="partnership-title">PARTNERSHIP DETAILS</span>
                <button type="button" class="partnership-x" data-close-partnership aria-label="Close">×</button>
            </div>
            <div class="partnership-body" id="partnership-body"></div>
            <div class="partnership-footer">
                <button type="button" class="partnership-close" data-close-partnership>Close</button>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    var popups = Array.prototype.slice.call(document.querySelectorAll('.row-popup'));

    function closePopups() {
        popups.forEach(function (popup) { popup.classList.remove('open'); });
    }

    document.querySelectorAll('[data-popup-toggle]').forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.stopPropagation();
            var popup = document.getElementById(button.dataset.popupToggle);
            var open = popup.classList.contains('open');
            closePopups();
            if (open) return;
            var rect = button.getBoundingClientRect();
            popup.style.left = Math.min(rect.left, window.innerWidth - 230) + 'px';
            popup.style.top = Math.min(rect.bottom + 4, window.innerHeight - 360) + 'px';
            popup.classList.add('open');
        });
    });

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.row-popup') && !event.target.closest('[data-popup-toggle]')) closePopups();
    });
    document.addEventListener('scroll', closePopups, true);

    var all = document.getElementById('hierarchy-select-all');
    var checkboxes = Array.prototype.slice.call(document.querySelectorAll('.row-checkbox'));
    if (all) {
        all.addEventListener('change', function () {
            checkboxes.forEach(function (box) { box.checked = all.checked; });
        });
    }

    var deleteButton = document.getElementById('hierarchy-delete-btn');
    var deleteForm = document.getElementById('bulk-delete-form');
    if (deleteButton && deleteForm) {
        deleteButton.addEventListener('click', function () {
            var ids = checkboxes.filter(function (box) { return box.checked; }).map(function (box) { return box.value; });
            if (!ids.length) { alert('Select at least one account to delete.'); return; }
            if (!confirm('Delete selected accounts? Accounts with downline or bets will be skipped.')) return;
            ids.forEach(function (id) {
                var input = document.createElement('input');
                input.type = 'hidden'; input.name = 'ids[]'; input.value = id;
                deleteForm.appendChild(input);
            });
            deleteForm.submit();
        });
    }

    var partnershipOverlay = document.getElementById('partnership-overlay');
    var partnershipBody = document.getElementById('partnership-body');
    var partnershipTitle = document.getElementById('partnership-title');
    var shareLevels = [
        ['admin_master', 'Admin Master'], ['mini_master', 'Mini Master'],
        ['master_master', 'Master Master'], ['super_master', 'Super Master'], ['agent_master', 'Agent Master']
    ];
    var commissionLevels = shareLevels.concat([['client_master', 'Client Master']]);

    function shareTable(values) {
        return '<table class="partnership-table"><thead><tr>' + shareLevels.map(function (l) { return '<th>' + l[1] + '</th>'; }).join('') +
            '</tr></thead><tbody><tr>' + shareLevels.map(function (l) { return '<td>' + Number(values[l[0]] || 0) + '</td>'; }).join('') + '</tr></tbody></table>';
    }

    function commissionTable(values) {
        return '<table class="partnership-table"><thead><tr>' + commissionLevels.map(function (l) { return '<th>' + l[1] + '</th>'; }).join('') +
            '</tr></thead><tbody><tr>' + commissionLevels.map(function (l) { return '<td>' + Number(values[l[0]] || 0) + '</td>'; }).join('') + '</tr></tbody></table>';
    }

    function renderPartnership(data) {
        var sections = [
            ['MATCH SHARE', shareTable(data.match_share)],
            ['MATCH COMMISSION', commissionTable(data.match_commission)],
            ['SESSION COMMISSION', commissionTable(data.session_commission)],
            ['CASINO SHARE', shareTable(data.casino_share)],
            ['CASINO COMMISSION', commissionTable(data.casino_commission)],
            ['MATKA SHARE', shareTable(data.matka_share)],
            ['MATKA COMMISSION', commissionTable(data.matka_commission)]
        ];
        partnershipTitle.textContent = data.title;
        partnershipBody.innerHTML = sections.map(function (section) {
            return '<div class="partnership-section"><h4>' + section[0] + '</h4>' + section[1] + '</div>';
        }).join('');
    }

    document.querySelectorAll('.partnership-toggle').forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.stopPropagation();
            renderPartnership(JSON.parse(button.dataset.partnership));
            partnershipOverlay.classList.add('open');
            closePopups();
        });
    });

    document.querySelectorAll('[data-close-partnership]').forEach(function (button) {
        button.addEventListener('click', function () { partnershipOverlay.classList.remove('open'); });
    });

    partnershipOverlay.addEventListener('click', function (event) {
        if (event.target === partnershipOverlay) partnershipOverlay.classList.remove('open');
    });

    var filters = document.getElementById('hierarchy-filters');
    document.querySelectorAll('[data-toggle-filters]').forEach(function (button) {
        button.addEventListener('click', function () {
            filters.hidden = !filters.hidden;
        });
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/hierarchy/index.blade.php ENDPATH**/ ?>