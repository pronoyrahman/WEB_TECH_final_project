<div class="wrap shell">
    <?php require APP_ROOT . '/app/Views/layouts/sidenav_scout.php'; ?>

    <div class="shell__main">
        <?php require APP_ROOT . '/app/Views/layouts/flash.php'; ?>

        <div class="page-head">
            <div>
                <span class="eyebrow">Scout desk</span>
                <h1>Published dispatches</h1>
                <p>Live in the archive. To change one, send the editors a change request.</p>
            </div>
            <a class="btn btn--ghost" href="<?= e(url('?page=scout/requests')) ?>">My requests</a>
        </div>

        <div class="panel">
            <?php if ($posts): ?>
                <div class="panel__body panel__body--flush table-scroll">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Record</th>
                                <th>Place</th>
                                <th>Cost</th>
                                <th>Saved</th>
                                <th>Notes</th>
                                <th>Published</th>
                                <th class="cell-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td class="cell-tight">
                                        <span class="filenum"><?= e(file_number((int) $post['id'])) ?></span>
                                    </td>

                                    <td>
                                        <div class="cell-person__name"><?= e($post['title']) ?></div>
                                        <div class="cell-person__meta">
                                            <?= e($post['country']) ?> &middot; <?= e($post['genre']) ?>
                                        </div>
                                    </td>

                                    <td class="cell-tight"><?= meter_html($post['cost_level']) ?></td>
                                    <td class="cell-tight data"><?= (int) $post['saved_count'] ?></td>
                                    <td class="cell-tight data"><?= (int) $post['comment_count'] ?></td>
                                    <td class="cell-tight"><?= e(pretty_date($post['created_at'])) ?></td>

                                    <td class="cell-actions">
                                        <a class="btn btn--sm btn--ghost"
                                           href="<?= e(url('?page=post&id=' . (int) $post['id'])) ?>">View</a>
                                        <a class="btn btn--sm btn--primary"
                                           href="<?= e(url('?page=scout/request_form&change=' . (int) $post['id'])) ?>">
                                            Request a change
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty empty--flush">
                    <h3>Nothing published yet</h3>
                    <p>
                        Once an editor approves one of your requests, the dispatch appears here
                        and in the public archive.
                    </p>
                    <a class="btn btn--primary" href="<?= e(url('?page=scout/request_form')) ?>">
                        File a dispatch
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


