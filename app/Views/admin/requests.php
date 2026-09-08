<div class="wrap shell">
    <?php require APP_ROOT . '/app/Views/layouts/sidenav_admin.php'; ?>

    <div class="shell__main">
        <?php require APP_ROOT . '/app/Views/layouts/flash.php'; ?>

        <div class="page-head">
            <div>
                <span class="eyebrow">Admin</span>
                <h1>Post requests</h1>
                <p>Read a submission in full before publishing it, or publish straight from here.</p>
            </div>
        </div>

        <nav class="tabs" aria-label="Filter by status">
            <?php foreach ($tabs as $value => $label): ?>
                <a class="<?= $status === $value ? 'is-active' : '' ?>"
                   href="<?= e(url('?page=admin/requests&status=' . $value)) ?>">
                    <?= e($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="panel">
            <?php if ($requests): ?>
                <div class="panel__body panel__body--flush table-scroll">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Request</th>
                                <th>Place</th>
                                <th>Scout</th>
                                <th>Cost</th>
                                <th>Filed</th>
                                <th>Status</th>
                                <th class="cell-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                                <?php $data = $request['data']; ?>
                                <tr>
                                    <td class="cell-tight">
                                        <span class="filenum"><?= e(file_number((int) $request['id'], 'REQ')) ?></span>
                                    </td>

                                    <td>
                                        <div class="cell-person__name"><?= e($data['title']) ?></div>
                                        <div class="cell-person__meta">
                                            <?= e($data['country']) ?> &middot; <?= e($data['genre']) ?>
                                            <?php if ($request['original_post_id']): ?>
                                                <span class="tag tag--sea ml-text">Change request</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="cell-person__name"><?= e($request['scout_name']) ?></div>
                                        <div class="cell-person__meta"><?= e($request['scout_email']) ?></div>
                                    </td>

                                    <td class="cell-tight"><?= meter_html($data['cost_level'] ?: 'medium') ?></td>
                                    <td class="cell-tight"><?= e(time_ago($request['requested_at'])) ?></td>

                                    <td class="cell-tight">
                                        <span class="tag tag--<?= e($request['status']) ?>" data-request-status>
                                            <?= e($request['status']) ?>
                                        </span>
                                    </td>

                                    <td class="cell-actions">
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <a class="btn btn--sm btn--ghost"
                                               href="<?= e(url('?page=admin/review&id=' . (int) $request['id'])) ?>">
                                                Review
                                            </a>
                                            <button class="btn btn--sm btn--go" type="button"
                                                    data-approve-request
                                                    data-request-id="<?= (int) $request['id'] ?>"
                                                    data-title="<?= e($data['title']) ?>">
                                                Publish
                                            </button>
                                        <?php elseif ($request['status'] === 'approved'): ?>
                                            <a class="btn btn--sm btn--ghost"
                                               href="<?= e(url('?page=admin/review&id=' . (int) $request['id'])) ?>">
                                                View
                                            </a>
                                        <?php else: ?>
                                            <a class="btn btn--sm btn--ghost"
                                               href="<?= e(url('?page=admin/review&id=' . (int) $request['id'])) ?>">
                                                Reconsider
                                            </a>
                                            <form method="post" class="form-inline"
                                                  action="<?= e(url('?page=admin/review_action')) ?>"
                                                  data-confirm="Delete this request for good?">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="delete_request">
                                                <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                                                <button class="btn btn--sm btn--danger" type="submit">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty empty--flush">
                    <h3><?= $status === 'pending' ? 'The queue is clear' : 'Nothing here' ?></h3>
                    <p>
                        <?= $status === 'pending'
                            ? 'Nothing is waiting for a decision. New submissions land here.'
                            : 'No request has that status yet.' ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>



