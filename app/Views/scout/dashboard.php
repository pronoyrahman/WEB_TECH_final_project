<div class="wrap shell">
    <?php require APP_ROOT . '/app/Views/layouts/sidenav_scout.php'; ?>

    <div class="shell__main">
        <?php require APP_ROOT . '/app/Views/layouts/flash.php'; ?>

        <div class="page-head">
            <div>
                <span class="eyebrow">Scout desk</span>
                <h1>Your filing record</h1>
                <p>Everything you have sent the editors, and how it landed.</p>
            </div>
            <a class="btn btn--primary" href="<?= e(url('?page=scout/request_form')) ?>">
                File a dispatch
            </a>
        </div>

        <div class="stats mb-6">
            <div class="stat stat--accent">
                <span class="stat__num" data-count="pending"><?= count($pending) ?></span>
                <span class="stat__label">Awaiting review</span>
            </div>
            <div class="stat">
                <span class="stat__num"><?= count($publishedPosts) ?></span>
                <span class="stat__label">Published</span>
            </div>
            <div class="stat">
                <span class="stat__num"><?= $savesCount ?></span>
                <span class="stat__label">Times saved</span>
            </div>
            <div class="stat">
                <span class="stat__num"><?= $commentsCount ?></span>
                <span class="stat__label">Notes left</span>
            </div>
            <?php if (count($rejected) > 0): ?>
                <div class="stat stat--alert">
                    <span class="stat__num" data-count="rejected"><?= count($rejected) ?></span>
                    <span class="stat__label">Sent back</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="panel">
            <div class="panel__head">
                <h2 class="panel__title">Latest requests</h2>
                <a class="btn btn--link" href="<?= e(url('?page=scout/requests')) ?>">See all</a>
            </div>

            <?php if ($recent): ?>
                <div class="panel__body panel__body--flush table-scroll">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Request</th>
                                <th>Place</th>
                                <th>Cost</th>
                                <th>Filed</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent as $request): ?>
                                <tr>
                                    <td class="cell-tight">
                                        <span class="filenum"><?= e(file_number((int) $request['id'], 'REQ')) ?></span>
                                    </td>
                                    <td>
                                        <div class="cell-person__name"><?= e($request['data']['title']) ?></div>
                                        <div class="cell-person__meta">
                                            <?= e($request['data']['country']) ?>
                                            <?php if ($request['original_post_id']): ?>
                                                &middot; change to <?= e(isset($request['original_title']) ? $request['original_title'] : 'a published post') ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="cell-tight"><?= meter_html($request['data']['cost_level'] ?: 'medium') ?></td>
                                    <td class="cell-tight"><?= e(time_ago($request['requested_at'])) ?></td>
                                    <td class="cell-tight">
                                        <span class="tag tag--<?= e($request['status']) ?>"><?= e($request['status']) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty empty--flush">
                    <h3>Nothing filed yet</h3>
                    <p>
                        Pick a place you know well. Write down what happened there, what it costs
                        and how a traveller gets to it. The editors take it from there.
                    </p>
                    <a class="btn btn--primary" href="<?= e(url('?page=scout/request_form')) ?>">
                        File your first dispatch
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


