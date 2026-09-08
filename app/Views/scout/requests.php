<div class="wrap shell">
    <?php require APP_ROOT . '/app/Views/layouts/sidenav_scout.php'; ?>

    <div class="shell__main">
        <?php require APP_ROOT . '/app/Views/layouts/flash.php'; ?>

        <div class="page-head">
            <div>
                <span class="eyebrow">Scout desk</span>
                <h1>My requests</h1>
                <p>Edit or withdraw anything the editors have not looked at yet.</p>
            </div>
            <a class="btn btn--primary" href="<?= e(url('?page=scout/request_form')) ?>">
                File a dispatch
            </a>
        </div>

        <nav class="tabs" aria-label="Filter by status">
            <?php foreach ($tabs as $value => $label): ?>
                <a class="<?= $status === $value ? 'is-active' : '' ?>"
                   href="<?= e(url('?page=scout/requests' . ($value ? '&status=' . $value : ''))) ?>">
                    <?= e($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="panel">
            <?php if ($requests): ?>
                <div class="panel__body panel__body--flush table-scroll">
                    <table class="table" id="requestTable">
                        <thead>
                            <tr>
                                <th>Request</th>
                                <th>Place</th>
                                <th>Genre</th>
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
                                            <?= e($data['country']) ?>
                                            <?php if ($request['original_post_id']): ?>
                                                &middot;
                                                <span class="tag tag--sea ml-text">Change request</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td class="cell-tight"><?= e($data['genre']) ?></td>

                                    <td class="cell-tight">
                                        <?= meter_html($data['cost_level'] ?: 'medium') ?>
                                    </td>

                                    <td class="cell-tight"><?= e(time_ago($request['requested_at'])) ?></td>

                                    <td class="cell-tight">
                                        <span class="tag tag--<?= e($request['status']) ?>">
                                            <?= e($request['status']) ?>
                                        </span>
                                        <?php if ($request['status'] === 'rejected' && $request['admin_note']): ?>
                                            <div class="cell-person__meta cell-note">
                                                <?= e($request['admin_note']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td class="cell-actions">
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <a class="btn btn--sm btn--ghost"
                                               href="<?= e(url('?page=scout/request_form&id=' . (int) $request['id'])) ?>">
                                                Edit
                                            </a>
                                            <button class="btn btn--sm btn--danger" type="button"
                                                    data-withdraw
                                                    data-request-id="<?= (int) $request['id'] ?>"
                                                    data-title="<?= e($data['title']) ?>">
                                                Withdraw
                                            </button>
                                        <?php elseif ($request['status'] === 'approved'): ?>
                                            <span class="muted text-sm">Published</span>
                                        <?php else: ?>
                                            <a class="btn btn--sm btn--ghost"
                                               href="<?= e(url('?page=scout/request_form')) ?>">
                                                File again
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty empty--flush">
                    <h3><?= $status ? 'Nothing with that status' : 'Nothing filed yet' ?></h3>
                    <p>
                        <?= $status
                            ? 'Try another tab, or file something new.'
                            : 'Write up a place you know and send it to the editors.' ?>
                    </p>
                    <a class="btn btn--primary" href="<?= e(url('?page=scout/request_form')) ?>">
                        File a dispatch
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


