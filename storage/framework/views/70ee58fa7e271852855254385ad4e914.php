<?php $__env->startSection('title', 'Cricket settings'); ?>
<?php $__env->startSection('page-title', 'Cricket settings'); ?>
<?php $__env->startSection('page-subtitle', 'Feed, discovery and live visibility'); ?>

<?php $__env->startSection('content'); ?>
    <div class="admin-page-head">
        <div>
            <h1>Cricket configuration</h1>
            <p><?php echo e($configured); ?> configured fallback match ID(s).</p>
        </div>
        <div class="actions-row">
            <a class="btn btn--ghost btn--sm" href="<?php echo e(route('admin.cricket.index')); ?>">← Dashboard</a>
            <a class="btn btn--ghost btn--sm" href="<?php echo e(route('admin.settings.platform')); ?>">Platform settings</a>
        </div>
    </div>

    <form method="POST" action="<?php echo e(route('admin.cricket.settings.update')); ?>">
        <?php echo csrf_field(); ?>

        <div class="admin-card">
            <div class="admin-card__head"><h3>Feed &amp; visibility</h3></div>
            <div class="admin-card__body">
                <div class="toggle-row">
                    <div>
                        <strong style="font-size:.84rem">Cricket feed enabled</strong>
                        <p class="text-muted" style="font-size:.75rem;margin:2px 0 0">Master switch for the public cricket section and board.</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="cricket_feed_enabled" value="1" <?php if($values['cricket_feed_enabled'] === '1'): echo 'checked'; endif; ?>>
                        <span></span>
                    </label>
                </div>

                <div class="toggle-row mt-14">
                    <div>
                        <strong style="font-size:.84rem">Live board enabled</strong>
                        <p class="text-muted" style="font-size:.75rem;margin:2px 0 0">Shows the Cricbuzz board on the public live surfaces.</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="cricket_board_enabled" value="1" <?php if($values['cricket_board_enabled'] === '1'): echo 'checked'; endif; ?>>
                        <span></span>
                    </label>
                </div>

                <div class="toggle-row mt-14">
                    <div>
                        <strong style="font-size:.84rem">Discovery mode</strong>
                        <p class="text-muted" style="font-size:.75rem;margin:2px 0 0">Use Cricbuzz live/upcoming/recent discovery. Turn off to use the configured fallback IDs only.</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="cricket_discovery_mode" value="1" <?php if($values['cricket_discovery_mode'] === '1'): echo 'checked'; endif; ?>>
                        <span></span>
                    </label>
                </div>

                <div class="toggle-row mt-14">
                    <div>
                        <strong style="font-size:.84rem">Legacy OpenCricket dashboard feed</strong>
                        <p class="text-muted" style="font-size:.75rem;margin:2px 0 0">Keep disabled unless the legacy key is restored. It is unrelated to Cricbuzz.</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="legacy_opencricket_enabled" value="1" <?php if($values['legacy_opencricket_enabled'] === '1'): echo 'checked'; endif; ?>>
                        <span></span>
                    </label>
                </div>
            </div>
        </div>

        <div class="admin-card mt-20">
            <div class="admin-card__head"><h3>Data &amp; refresh</h3></div>
            <div class="admin-card__body">
                <div class="form-grid">
                    <div class="field">
                        <label for="live_only_sport">Live surfaces sport filter</label>
                        <select class="select" id="live_only_sport" name="live_only_sport">
                            <option value="cricket" <?php if($values['live_only_sport'] === 'cricket'): echo 'selected'; endif; ?>>Cricket only</option>
                            <option value="all" <?php if($values['live_only_sport'] === 'all'): echo 'selected'; endif; ?>>All sports</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="live_stale_hours">Hide DB live matches older than (hours)</label>
                        <input class="input" id="live_stale_hours" name="live_stale_hours" type="number" min="0" max="168"
                               value="<?php echo e(old('live_stale_hours', $values['live_stale_hours'])); ?>">
                    </div>

                    <div class="field">
                        <label for="cricket_stale_minutes">Feed stale threshold (minutes)</label>
                        <input class="input" id="cricket_stale_minutes" name="cricket_stale_minutes" type="number" min="1" max="240"
                               value="<?php echo e(old('cricket_stale_minutes', $values['cricket_stale_minutes'])); ?>">
                    </div>

                    <div class="field">
                        <label for="cricket_detail_limit">Max live matches with detailed scores</label>
                        <input class="input" id="cricket_detail_limit" name="cricket_detail_limit" type="number" min="0" max="20"
                               value="<?php echo e(old('cricket_detail_limit', $values['cricket_detail_limit'])); ?>">
                    </div>

                    <div class="field">
                        <label for="cricket_discovery_ttl">Discovery cache (seconds)</label>
                        <input class="input" id="cricket_discovery_ttl" name="cricket_discovery_ttl" type="number" min="30" max="3600"
                               value="<?php echo e(old('cricket_discovery_ttl', $values['cricket_discovery_ttl'])); ?>">
                    </div>
                </div>

                <div class="field mt-14">
                    <label for="cricket_fallback_ids">Fallback configured match IDs</label>
                    <textarea class="textarea" id="cricket_fallback_ids" name="cricket_fallback_ids" rows="5"
                              placeholder="165061|UP vs Jharkhand&#10;165080|Odisha vs Goa"><?php echo e(old('cricket_fallback_ids', $values['cricket_fallback_ids'])); ?></textarea>
                    <p class="hint">One per line: <code>id|label</code> (or <code>id</code>). Used when discovery is disabled or returns nothing.</p>
                </div>
            </div>
        </div>

        <div class="form-actions mt-20">
            <button class="btn btn--primary" type="submit">Save cricket settings</button>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/cricket/settings.blade.php ENDPATH**/ ?>