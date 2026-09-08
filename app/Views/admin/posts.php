<div class="wrap shell">
    <?php require APP_ROOT . '/app/Views/layouts/sidenav_admin.php'; ?>

    <div class="shell__main">
        <?php require APP_ROOT . '/app/Views/layouts/flash.php'; ?>

        <div class="page-head">
            <div>
                <span class="eyebrow">Admin</span>
                <h1>Posts</h1>
                <p>Everything that has been published, and everything that has been pulled.</p>
            </div>
        </div>

        <nav class="tabs" aria-label="Filter by status">
            <?php foreach ($tabs as $value => $label): ?>
                <a class="<?= $status === $value ? 'is-active' : '' ?>"
                   href="<?= e(url('?page=admin/posts' . ($value ? '&status=' . $value : ''))) ?>">
                    <?= e($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="panel">
            <?php if ($posts): ?>
                <div class="panel__body panel__body--flush table-scroll">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Record</th>
                                <th>Place</th>
                                <th>Scout</th>
                                <th>Cost</th>
                                <th>Notes</th>
                                <th>Status</th>
                                <th>Updated</th>
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

                                    <td><?= e(isset($post['scout_name']) ? $post['scout_name'] : 'Removed account') ?></td>
                                    <td class="cell-tight"><?= meter_html($post['cost_level']) ?></td>
                                    <td class="cell-tight data"><?= (int) $post['comment_count'] ?></td>

                                    <td class="cell-tight">
                                        <span class="tag tag--<?= e($post['status']) ?>"><?= e($post['status']) ?></span>
                                    </td>

                                    <td class="cell-tight"><?= e(time_ago($post['updated_at'])) ?></td>

                                    <td class="cell-actions">
                                        <?php if ($post['status'] === 'approved'): ?>
                                            <a class="btn btn--sm btn--ghost"
                                               href="<?= e(url('?page=post&id=' . (int) $post['id'])) ?>">View</a>
                                        <?php endif; ?>

                                        <a class="btn btn--sm btn--ghost"
                                           href="<?= e(url('?page=admin/post_edit&id=' . (int) $post['id'])) ?>">Edit</a>

                                        <?php if ($post['status'] === 'approved'): ?>
                                            <form method="post" class="form-inline"
                                                  action="<?= e(url('?page=admin/post_update')) ?>"
                                                  data-confirm="Hide &quot;<?= e($post['title']) ?>&quot;? It will be removed from Browse but not deleted.">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="hide_post">
                                                <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                                                <button class="btn btn--sm btn--warning" type="submit">Hide</button>
                                            </form>
                                        <?php elseif ($post['status'] === 'hidden'): ?>
                                            <form method="post" class="form-inline"
                                                  action="<?= e(url('?page=admin/post_update')) ?>">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="unhide_post">
                                                <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                                                <button class="btn btn--sm btn--primary" type="submit">Unhide</button>
                                            </form>
                                        <?php endif; ?>

                                        <form method="post" class="form-inline"
                                              action="<?= e(url('?page=admin/post_update')) ?>"
                                              data-confirm="Delete &quot;<?= e($post['title']) ?>&quot;? Its comments and saves go too. This cannot be undone.">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete_post">
                                            <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                                            <button class="btn btn--sm btn--danger" type="submit">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty empty--flush">
                    <h3>Nothing here</h3>
                    <p>
                        No post has that status. Published posts arrive by approving a request
                        in the queue.
                    </p>
                    <a class="btn btn--primary" href="<?= e(url('?page=admin/requests')) ?>">
                        Open the queue
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>



