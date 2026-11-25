<?php $__env->startSection('title', 'AI cricket markets'); ?>
<?php $__env->startSection('page-title', 'AI cricket markets'); ?>
<?php $__env->startSection('page-subtitle', 'Suggestions are validated here. Settlement still uses the existing bet service.'); ?>

<?php $__env->startSection('content'); ?>
    <div class="admin-page-head">
        <div>
            <h1>Live cricket markets</h1>
            <p>Questions come from the cached Cricbuzz board. Yes and No are normal selections. This server has no WebSocket process, so the sportsbook polls <code>/api/cricket-markets</code> the same way it already polls odds.</p>
        </div>
        <div class="actions-row">
            <form method="POST" action="<?php echo e(route('admin.cricket.markets.run')); ?>">
                <?php echo csrf_field(); ?>
                <button class="btn btn--primary btn--sm" type="submit">Run one pass</button>
            </form>
            <a class="btn btn--ghost btn--sm" href="<?php echo e(route('admin.cricket.demo')); ?>">Live demo</a>
            <a class="btn btn--ghost btn--sm" href="<?php echo e(route('admin.cricket.index')); ?>">Cricket feed</a>
        </div>
    </div>

    <form class="admin-card mb-20" method="POST" action="<?php echo e(route('admin.cricket.markets.settings')); ?>">
        <?php echo csrf_field(); ?>
        <div class="admin-card__head"><h3>Generation</h3></div>
        <div class="admin-card__body">
            <div class="form-grid">
                <label class="check"><input type="checkbox" name="enabled" value="1" <?php if(setting_enabled('cricket_ai_markets_enabled', false)): echo 'checked'; endif; ?>> <span>Generate markets for live matches</span></label>
                <label class="check"><input type="checkbox" name="approval_required" value="1" <?php if(setting_enabled('cricket_ai_approval_required', true)): echo 'checked'; endif; ?>> <span>Hold questions until an admin approves them</span></label>
                <label class="check"><input type="checkbox" name="use_model" value="1" <?php if(setting_enabled('cricket_ai_use_model', false)): echo 'checked'; endif; ?> <?php if(! $configured): echo 'disabled'; endif; ?>> <span>Ask the configured model for suggestions<?php echo e($configured ? '' : ' (no API key set)'); ?></span></label>
            </div>
            <div class="form-grid mt-14">
                <div class="field">
                    <label for="generation_seconds">Minimum seconds between new questions</label>
                    <input class="input" id="generation_seconds" name="generation_seconds" type="number" min="30" max="3600" value="<?php echo e(setting('cricket_ai_generation_seconds', 60)); ?>">
                </div>
                <div class="field">
                    <label for="yes_odds">Yes odds</label>
                    <input class="input" id="yes_odds" name="yes_odds" type="number" min="1.01" max="20" step="0.01" value="<?php echo e(setting('cricket_ai_yes_odds', '1.85')); ?>">
                </div>
                <div class="field">
                    <label for="no_odds">No odds</label>
                    <input class="input" id="no_odds" name="no_odds" type="number" min="1.01" max="20" step="0.01" value="<?php echo e(setting('cricket_ai_no_odds', '1.85')); ?>">
                </div>
            </div>
            <div class="mt-14">
                <?php $__currentLoopData = $catalog; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="check" style="display:flex;margin-bottom:8px">
                        <input type="checkbox" name="templates[]" value="<?php echo e($key); ?>" <?php if(in_array($key, $enabledTemplates, true)): echo 'checked'; endif; ?> <?php if(! $template['available']): echo 'disabled'; endif; ?>>
                        <span><?php echo e($template['label']); ?><?php if(! $template['available']): ?> — <?php echo e($template['reason']); ?><?php endif; ?></span>
                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <button class="btn btn--primary btn--sm mt-14" type="submit">Save settings</button>
        </div>
    </form>

    <div class="admin-card mb-20">
        <div class="admin-card__head"><h3>Providers</h3></div>
        <div class="admin-card__body">
            <p>Cricbuzz <?php echo e(($providers['cricbuzz']['configured'] ?? false) ? 'is configured' : 'is not configured'); ?>. Rate-limit responses recorded: <?php echo e($providers['cricbuzz']['rate_limits'] ?? 0); ?>. Last failure: <?php echo e($providers['cricbuzz']['last_failure']['status'] ?? 'none'); ?>.</p>
            <p>Sportmonks <?php echo e(($providers['sportmonks']['configured'] ?? false) ? 'is configured' : 'is not configured'); ?>. Its current client supplies innings totals, not a ball-by-ball feed, unless a fixture payload includes a ball list.</p>
            <p>Six and next-ball markets stay off until a real ball list is present. Demo matches are labelled LIVE DEMO and cannot take real-money bets.</p>
        </div>
    </div>

    <div class="admin-card mb-20">
        <div class="admin-card__head">
            <h3>Live matches</h3>
            <span><?php echo e(count($live)); ?><?php if($generatedAt): ?> · cached <?php echo e($generatedAt); ?><?php endif; ?></span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Match</th><th>Score</th><th>Status</th><th>Generation</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $live; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <strong><?php echo e($row['label']); ?></strong>
                                <div class="text-muted"><?php echo e($row['team1']); ?> vs <?php echo e($row['team2']); ?></div>
                                <div class="text-muted"><?php echo e($row['provider'] === 'demo' ? 'LIVE DEMO' : 'REAL'); ?> · <?php echo e($row['provider']); ?> · <?php echo e($row['play']); ?> · <?php echo e($row['ball_feed'] ? 'ball feed' : 'no ball feed'); ?></div>
                                <div class="text-muted">Provider <?php echo e($row['id']); ?> · innings <?php echo e($row['innings'] ?: '—'); ?></div>
                                <?php if($row['error']): ?>
                                    <div class="text-muted"><?php echo e($row['error']); ?></div>
                                <?php endif; ?>
                                <?php $__empty_2 = true; $__currentLoopData = $row['questions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                    <div class="text-muted"><?php echo e($question['status']); ?>: <?php echo e($question['question']); ?><?php if($question['target_over']): ?> · over <?php echo e($question['target_over']); ?><?php endif; ?></div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                    <div class="text-muted">No questions for this match</div>
                                <?php endif; ?>
                                <?php $__currentLoopData = $row['markets']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $market): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="text-muted">Market <?php echo e($market['status'] ?? '—'); ?> / <?php echo e($market['phase']); ?>: <?php echo e($market['name']); ?></div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </td>
                            <td>
                                <?php if($row['runs'] !== null && $row['wickets'] !== null && $row['overs']): ?>
                                    <?php echo e($row['batting_team']); ?> <?php echo e($row['runs']); ?>/<?php echo e($row['wickets']); ?> (<?php echo e($row['overs']); ?>)
                                    <?php if($row['stale']): ?><div class="text-muted">Cached score is stale</div><?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Score not in the cached detail yet</span>
                                    <?php if($row['error']): ?><div class="text-muted"><?php echo e($row['error']); ?></div><?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($row['status'] !== '' ? $row['status'] : 'Live'); ?></td>
                            <td>
                                <form method="POST" action="<?php echo e(route('admin.cricket.markets.toggle', $row['id'])); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button class="btn btn--ghost btn--sm" type="submit"><?php echo e($row['enabled'] ? 'On for this match' : 'Off for this match'); ?></button>
                                </form>
                                <?php if (! ($generationEnabled)): ?>
                                    <div class="text-muted">Global generation is off</div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4">
                                <div class="empty">
                                    <strong>No live Cricbuzz matches in the board cache</strong>
                                    <?php $__currentLoopData = $boardErrors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(!empty($error['message'])): ?>
                                            <div class="text-muted"><?php echo e($error['message']); ?></div>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card mb-20">
        <div class="admin-card__head"><h3>Waiting questions</h3></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Question</th><th>Source</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <strong><?php echo e($candidate->question); ?></strong>
                                <div class="text-muted"><?php echo e($candidate->template); ?> · over <?php echo e($candidate->parameters['target_over'] ?? '—'); ?> · match <?php echo e($candidate->provider_match_id); ?></div>
                            </td>
                            <td><?php echo e($candidate->source); ?></td>
                            <td><?php echo e($candidate->status); ?><?php if($candidate->rejection_reason): ?><div class="text-muted"><?php echo e($candidate->rejection_reason); ?></div><?php endif; ?></td>
                            <td>
                                <?php if($candidate->status === 'pending'): ?>
                                    <form method="POST" action="<?php echo e(route('admin.cricket.markets.approve', $candidate)); ?>" style="display:inline"><?php echo csrf_field(); ?><button class="btn btn--primary btn--sm" type="submit">Approve</button></form>
                                    <form method="POST" action="<?php echo e(route('admin.cricket.markets.reject', $candidate)); ?>" style="display:inline"><?php echo csrf_field(); ?><button class="btn btn--ghost btn--sm" type="submit">Reject</button></form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4"><div class="empty"><strong>No questions yet</strong></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card mb-20">
        <div class="admin-card__head"><h3>Markets</h3></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Question</th><th>Yes / No</th><th>Market</th><th>Phase</th><th></th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $rules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $yes = $rule->market?->selections->firstWhere('result_key', 'yes');
                            $no = $rule->market?->selections->firstWhere('result_key', 'no');
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo e($rule->market?->name); ?></strong>
                                <div class="text-muted"><?php echo e($rule->source); ?> · <?php echo e($rule->template); ?> · innings <?php echo e($rule->innings); ?> · over <?php echo e($rule->target_over); ?></div>
                            </td>
                            <td class="mono"><?php echo e($yes ? number_format($yes->odds, 2) : '—'); ?> / <?php echo e($no ? number_format($no->odds, 2) : '—'); ?></td>
                            <td><?php echo e($rule->market?->status); ?></td>
                            <td><?php echo e($rule->phase); ?></td>
                            <td>
                                <?php if($rule->market?->status === 'open'): ?>
                                    <form method="POST" action="<?php echo e(route('admin.cricket.markets.suspend', $rule)); ?>" style="display:inline"><?php echo csrf_field(); ?><button class="btn btn--ghost btn--sm" type="submit">Suspend</button></form>
                                <?php endif; ?>
                                <?php if($rule->phase === 'open' && $rule->market?->status === 'suspended'): ?>
                                    <form method="POST" action="<?php echo e(route('admin.cricket.markets.reopen', $rule)); ?>" style="display:inline"><?php echo csrf_field(); ?><button class="btn btn--ghost btn--sm" type="submit">Reopen</button></form>
                                <?php endif; ?>
                                <?php if(! in_array($rule->phase, ['settled', 'void'], true)): ?>
                                    <form method="POST" action="<?php echo e(route('admin.cricket.markets.void', $rule)); ?>" style="display:inline" data-confirm="Void this market and refund open bets?"><?php echo csrf_field(); ?><button class="btn btn--danger btn--sm" type="submit">Void</button></form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5"><div class="empty"><strong>No generated markets</strong></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card__head"><h3>Lifecycle log</h3></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>When</th><th>Event</th><th>Message</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e(optional($event->created_at)->format('d M H:i:s')); ?></td>
                            <td><?php echo e($event->event); ?></td>
                            <td><?php echo e($event->message); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="3"><div class="empty"><strong>No events yet</strong></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/admin/cricket/markets.blade.php ENDPATH**/ ?>