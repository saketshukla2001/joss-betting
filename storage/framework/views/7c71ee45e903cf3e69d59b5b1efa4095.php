<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Cricbuzz Live Scores · Test Screen</title>
    <style>
        :root {
            --bg: #070b16;
            --panel: #0f1626;
            --panel-2: #131c30;
            --line: rgba(255, 255, 255, .08);
            --text: #eaf0fb;
            --muted: #8a97ad;
            --lime: #c9ff45;
            --green: #36d399;
            --red: #ff6b6b;
            --blue: #5aa9ff;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(ellipse 60% 45% at 15% 0%, rgba(90, 169, 255, .16), transparent 60%),
                radial-gradient(ellipse 55% 40% at 85% 5%, rgba(201, 255, 69, .10), transparent 60%),
                var(--bg);
            color: var(--text);
            font-family: ui-sans-serif, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .wrap { max-width: 1180px; margin: 0 auto; padding: 28px 20px 48px; }
        header { display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end; justify-content: space-between; }
        .eyebrow { font-size: .68rem; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; color: var(--lime); }
        h1 { margin: 6px 0 4px; font-size: clamp(1.5rem, 3.4vw, 2.15rem); letter-spacing: -.03em; }
        .sub { margin: 0; color: var(--muted); font-size: .82rem; }
        .actions { display: flex; gap: 8px; }
        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 9px 15px; border-radius: 10px; font-size: .8rem; font-weight: 700;
            text-decoration: none; border: 1px solid var(--line); color: var(--text);
            background: rgba(255, 255, 255, .04); transition: .15s ease;
        }
        .btn:hover { background: rgba(255, 255, 255, .09); }
        .btn--lime { background: var(--lime); color: #0a1305; border-color: transparent; }
        .btn--lime:hover { background: #d8ff70; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin: 22px 0 24px; }
        .stat { background: linear-gradient(160deg, rgba(255,255,255,.05), rgba(255,255,255,.02)); border: 1px solid var(--line); border-radius: 12px; padding: 12px 14px; }
        .stat b { display: block; font-size: 1.35rem; letter-spacing: -.02em; }
        .stat span { font-size: .68rem; color: var(--muted); text-transform: uppercase; letter-spacing: .09em; font-weight: 700; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(330px, 1fr)); gap: 14px; }
        .card { background: linear-gradient(170deg, var(--panel-2), var(--panel)); border: 1px solid var(--line); border-radius: 16px; overflow: hidden; }
        .card__top { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 15px; border-bottom: 1px solid var(--line); }
        .card__title { font-size: .82rem; font-weight: 800; letter-spacing: .01em; }
        .badge { font-size: .62rem; font-weight: 900; letter-spacing: .1em; text-transform: uppercase; padding: 5px 9px; border-radius: 999px; white-space: nowrap; }
        .badge--live { background: rgba(54, 211, 153, .13); color: var(--green); border: 1px solid rgba(54, 211, 153, .3); }
        .badge--result { background: rgba(138, 151, 173, .14); color: #b9c6da; border: 1px solid rgba(138, 151, 173, .28); }
        .badge--error { background: rgba(255, 107, 107, .12); color: var(--red); border: 1px solid rgba(255, 107, 107, .3); }
        .badge--live::before { content: ""; display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: var(--green); margin-right: 6px; vertical-align: 1px; animation: pulse 1.6s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .25; } }
        .card__body { padding: 14px 15px; }
        .row { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; padding: 5px 0; }
        .team { font-size: .86rem; font-weight: 800; color: #dbe6f7; }
        .score { font-variant-numeric: tabular-nums; font-size: .95rem; font-weight: 800; }
        .overs { font-size: .7rem; color: var(--muted); font-weight: 600; margin-left: 4px; }
        .status { margin: 10px 0 0; font-size: .76rem; line-height: 1.4; color: #b9c6da; }
        .meta { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 10px; font-size: .7rem; color: var(--muted); }
        .meta b { color: var(--text); font-weight: 800; }
        .card__foot { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 15px; border-top: 1px solid var(--line); font-size: .7rem; color: var(--muted); }
        .card__foot a { color: var(--blue); text-decoration: none; font-weight: 800; }
        .card__foot a:hover { text-decoration: underline; }
        .error { font-size: .78rem; line-height: 1.5; color: #ffb3b3; }
        .note { margin-top: 22px; font-size: .72rem; color: var(--muted); line-height: 1.6; }
        .note code { color: #cfe0ff; }
    </style>
</head>
<body>
<div class="wrap">
    <header>
        <div>
            <div class="eyebrow">Read-only API test screen</div>
            <h1>Cricbuzz live scores</h1>
            <p class="sub">
                <?php echo e(count($matches)); ?> fixed test matches · <?php echo e($host ?: 'cricbuzz-cricket.p.rapidapi.com'); ?>

                · generated <?php echo e($generatedAt->format('d M Y, h:i:s A')); ?> IST
            </p>
        </div>
        <div class="actions">
            <a class="btn btn--lime" href="<?php echo e(route('cricbuzz-test.results', ['refresh' => 1])); ?>">Refresh</a>
            <a class="btn" href="<?php echo e(route('cricbuzz-test.index')); ?>">Full test page</a>
        </div>
    </header>

    <div class="stats">
        <div class="stat"><b><?php echo e($liveCount); ?></b><span>Live now</span></div>
        <div class="stat"><b><?php echo e($finishedCount); ?></b><span>Results</span></div>
        <div class="stat"><b><?php echo e(count($matches)); ?></b><span>Matches</span></div>
        <div class="stat"><b><?php echo e($refresh ? 'Fresh' : 'Cached'); ?></b><span>Data source</span></div>
    </div>

    <div class="grid">
        <?php $__currentLoopData = $matches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $live = $row['live'];
                $result = $row['result'];
                $badgeClass = ! $result['success'] ? 'badge--error' : ($row['finished'] ? 'badge--result' : 'badge--live');
                $badgeText = ! $result['success'] ? 'Error' : ($row['finished'] ? 'Result' : 'Live');
            ?>

            <article class="card">
                <div class="card__top">
                    <span class="card__title"><?php echo e($row['label']); ?></span>
                    <span class="badge <?php echo e($badgeClass); ?>"><?php echo e($badgeText); ?></span>
                </div>

                <div class="card__body">
                    <?php if(! $result['success']): ?>
                        <p class="error" style="margin:0">
                            <?php echo e($result['error']); ?>

                            <?php if($result['status']): ?> <span style="color:var(--muted)">(HTTP <?php echo e($result['status']); ?>)</span><?php endif; ?>
                        </p>
                    <?php elseif($live && ! empty($live['innings'])): ?>
                        <?php $__currentLoopData = $live['innings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="row">
                                <span class="team"><?php echo e($inning['team']); ?></span>
                                <span class="score">
                                    <?php echo e($inning['runs'] ?? '—'); ?><?php if($inning['wickets'] !== null): ?>/<?php echo e($inning['wickets']); ?><?php endif; ?>
                                    <?php if($inning['overs'] !== null): ?><span class="overs"><?php echo e($inning['overs']); ?> ov</span><?php endif; ?>
                                </span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <p class="status"><?php echo e($live['status'] ?: '—'); ?></p>

                        <div class="meta">
                            <?php if($live['crr'] !== null): ?><span>CRR <b><?php echo e($live['crr']); ?></b></span><?php endif; ?>
                            <?php if($live['rrr'] !== null): ?><span>RRR <b><?php echo e($live['rrr']); ?></b></span><?php endif; ?>
                            <?php if($live['target'] !== null): ?><span>Target <b><?php echo e($live['target']); ?></b></span><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="status" style="margin:0"><?php echo e($live['status'] ?? 'Waiting for score…'); ?></p>
                    <?php endif; ?>
                </div>

                <div class="card__foot">
                    <span>
                        <?php echo e($result['cached'] ? 'cached' : 'fresh'); ?>

                        <?php if($result['fetched_at']): ?>
                            · <?php echo e(\Illuminate\Support\Carbon::parse($result['fetched_at'])->timezone(config('app.timezone'))->format('h:i:s A')); ?>

                        <?php endif; ?>
                    </span>
                    <a href="<?php echo e(route('cricbuzz-test.show', $row['id'])); ?>">Scorecard →</a>
                </div>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <p class="note">
        Fixed match IDs: <code>165061</code>, <code>165080</code>, <code>177178</code>,
        <code>175840</code>, <code>175240</code>, <code>161285</code>.
        Data from Cricbuzz via RapidAPI, cached briefly to respect the per-second rate limit.
        This screen is read-only and has no connection to betting, wallets or odds.
    </p>
</div>
</body>
</html>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/joss_beting/resources/views/cricbuzz-test/results.blade.php ENDPATH**/ ?>