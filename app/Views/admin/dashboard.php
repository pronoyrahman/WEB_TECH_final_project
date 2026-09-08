<div class="wrap shell">
    <?php require APP_ROOT . '/app/Views/layouts/sidenav_admin.php'; ?>

    <div class="shell__main">
        <?php require APP_ROOT . '/app/Views/layouts/flash.php'; ?>

        <div class="page-head">
            <div>
                <span class="eyebrow">Admin</span>
                <h1>What needs you</h1>
                <p>Accounts to approve, dispatches to read, and the state of the archive.</p>
            </div>
        </div>

        <!-- What is waiting -->
        <div class="stats mb-6">
            <div class="stat <?= $requestCounts['pending'] ? 'stat--accent' : '' ?>">
                <span class="stat__num" data-count="pending"><?= (int) $requestCounts['pending'] ?></span>
                <span class="stat__label">Requests to review</span>
            </div>
            <div class="stat <?= $userCounts['unverified'] ? 'stat--alert' : '' ?>">
                <span class="stat__num" data-count="unverified"><?= (int) $userCounts['unverified'] ?></span>
                <span class="stat__label">Accounts to verify</span>
            </div>
            <div class="stat">
                <span class="stat__num"><?= (int) $postStats['approved'] ?></span>
                <span class="stat__label">Published posts</span>
            </div>
            <div class="stat">
                <span class="stat__num" data-count="comments"><?= (int) $commentTotal ?></span>
                <span class="stat__label">Comments</span>
            </div>
        </div>

        <!-- Who is here -->
        <div class="panel">
            <div class="panel__head">
                <h2 class="panel__title">Who is registered</h2>
                <a class="btn btn--link" href="<?= e(url('?page=admin/users')) ?>">Manage users</a>
            </div>
            <div class="panel__body">
                <div class="stats">
                    <div class="stat">
                        <span class="stat__num"><?= (int) $userCounts['user'] ?></span>
                        <span class="stat__label">Travellers</span>
                    </div>
                    <div class="stat">
                        <span class="stat__num"><?= (int) $userCounts['scout'] ?></span>
                        <span class="stat__label">Scouts</span>
                    </div>
                    <div class="stat">
                        <span class="stat__num"><?= (int) $userCounts['admin'] ?></span>
                        <span class="stat__label">Admins</span>
                    </div>
                    <div class="stat">
                        <span class="stat__num"><?= (int) $postStats['countries'] ?></span>
                        <span class="stat__label">Countries covered</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- The review queue -->
        <div class="panel">
            <div class="panel__head">
                <h2 class="panel__title">Waiting to be read</h2>
                <a class="btn btn--link" href="<?= e(url('?page=admin/requests')) ?>">Open the queue</a>
            </div>

            <?php if ($queue): ?>
                <div class="panel__body panel__body--flush table-scroll">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Request</th>
                                <th>Place</th>
                                <th>Scout</th>
                                <th>Filed</th>
                                <th class="cell-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($queue as $request): ?>
                                <tr>
                                    <td class="cell-tight">
                                        <span class="filenum"><?= e(file_number((int) $request['id'], 'REQ')) ?></span>
                                    </td>
                                    <td>
                                        <div class="cell-person__name"><?= e($request['data']['title']) ?></div>
                                        <div class="cell-person__meta">
                                            <?= e($request['data']['country']) ?>
                                            <?php if ($request['original_post_id']): ?>
                                                &middot; <span class="tag tag--sea">Change request</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?= e($request['scout_name']) ?></td>
                                    <td class="cell-tight"><?= e(time_ago($request['requested_at'])) ?></td>
                                    <td class="cell-actions">
                                        <a class="btn btn--sm btn--primary"
                                           href="<?= e(url('?page=admin/review&id=' . (int) $request['id'])) ?>">
                                            Review
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty empty--flush">
                    <h3>The queue is clear</h3>
                    <p>Nothing is waiting for a decision. New submissions land here.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Accounts waiting on approval -->
        <?php if ($unverified): ?>
            <div class="panel">
                <div class="panel__head">
                    <h2 class="panel__title">Accounts waiting on you</h2>
                    <a class="btn btn--link" href="<?= e(url('?page=admin/users&verified=0')) ?>">See all</a>
                </div>

                <div class="panel__body panel__body--flush table-scroll">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Person</th>
                                <th>Role</th>
                                <th>Registered</th>
                                <th class="cell-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($unverified as $person): ?>
                                <tr>
                                    <td>
                                        <div class="cell-person">
                                            <?php $face = avatar_url($person['profile_picture']); ?>
                                            <?php if ($face): ?>
                                                <img class="avatar avatar--sm" src="<?= e($face) ?>" alt="">
                                            <?php else: ?>
                                                <span class="avatar avatar--sm" aria-hidden="true"><?= e(initial($person['name'])) ?></span>
                                            <?php endif; ?>
                                            <div>
                                                <div class="cell-person__name"><?= e($person['name']) ?></div>
                                                <div class="cell-person__meta"><?= e($person['email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="cell-tight"><span class="tag"><?= e($person['role']) ?></span></td>
                                    <td class="cell-tight"><?= e(time_ago($person['created_at'])) ?></td>
                                    <td class="cell-actions">
                                        <button class="btn btn--sm btn--go" type="button"
                                                data-verify-toggle
                                                data-remove-on-verify
                                                data-user-id="<?= (int) $person['id'] ?>"
                                                data-verified="0">
                                            Verify
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>


