<div class="wrap shell">
    <?php require APP_ROOT . '/app/Views/layouts/sidenav_admin.php'; ?>

    <div class="shell__main">
        <?php require APP_ROOT . '/app/Views/layouts/flash.php'; ?>

        <div class="page-head">
            <div>
                <span class="eyebrow">Admin</span>
                <h1>Comments</h1>
                <p><span data-count="comments"><?= (int) $total ?></span> notes across the archive.</p>
            </div>
        </div>

        <form class="searchbar" method="get" action="<?= e(url('?page=admin/comments')) ?>">
            <div class="searchbar__field">
                <svg class="searchbar__icon" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>
                </svg>
                <label class="sr-only" for="commentSearch">Search comments</label>
                <input class="input" type="search" id="commentSearch" name="q"
                       value="<?= e($term) ?>" placeholder="Search text, author or destination">
            </div>
            <button class="btn btn--ghost" type="submit">Search</button>
            <?php if ($term !== ''): ?>
                <a class="btn btn--link" href="<?= e(url('?page=admin/comments')) ?>">Clear</a>
            <?php endif; ?>
        </form>

        <div class="panel">
            <?php if ($comments): ?>
                <div class="panel__body panel__body--flush table-scroll">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Author</th>
                                <th>On</th>
                                <th>Note</th>
                                <th>Posted</th>
                                <th class="cell-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comments as $comment): ?>
                                <tr>
                                    <td>
                                        <div class="cell-person__name"><?= e($comment['author']) ?></div>
                                        <div class="cell-person__meta">
                                            <?= e($comment['author_email']) ?>
                                            <span class="tag ml-text"><?= e($comment['author_role']) ?></span>
                                        </div>
                                    </td>

                                    <td>
                                        <a href="<?= e(url('?page=post&id=' . (int) $comment['post_id'])) ?>">
                                            <?= e(excerpt($comment['post_title'], 40)) ?>
                                        </a>
                                        <div class="cell-person__meta"><?= e($comment['country']) ?></div>
                                    </td>

                                    <td class="cell-wide">
                                        <span class="u-prewrap"><?= e(excerpt($comment['content'], 180)) ?></span>
                                    </td>

                                    <td class="cell-tight"><?= e(time_ago($comment['created_at'])) ?></td>

                                    <td class="cell-actions">
                                        <button class="btn btn--sm btn--danger" type="button"
                                                data-admin-delete-comment
                                                data-comment-id="<?= (int) $comment['id'] ?>">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty empty--flush">
                    <h3><?= $term !== '' ? 'No comments match' : 'No comments yet' ?></h3>
                    <p>
                        <?= $term !== ''
                            ? 'Try a different search term.'
                            : 'Once travellers start leaving notes, they turn up here.' ?>
                    </p>
                    <?php if ($term !== ''): ?>
                        <a class="btn btn--ghost" href="<?= e(url('?page=admin/comments')) ?>">Show all</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


