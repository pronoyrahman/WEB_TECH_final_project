<?php
// TravelVista - admin password resets view. Handled by AdminController.
?>

<div class="wrap shell">
    <?php require APP_ROOT . '/app/Views/layouts/sidenav_admin.php'; ?>

    <div class="shell__main">
        <?php require APP_ROOT . '/app/Views/layouts/flash.php'; ?>

        <div class="page-head">
            <div>
                <span class="eyebrow">Admin</span>
                <h1>Password Resets</h1>
                <p>Approve requests and send temporary passwords to users.</p>
            </div>
        </div>

            <div class="box mt-4">
                <?php if (empty($requests)): ?>
                    <div class="empty-state">
                        <span class="empty-state__icon">inbox</span>
                        <h3>No pending requests</h3>
                        <p>When a user forgets their password, their request will appear here.</p>
                    </div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $req): ?>
                                    <tr id="reset-row-<?= (int) $req['id'] ?>">
                                        <td><?= e(date('M j, Y - g:i A', strtotime($req['created_at']))) ?></td>
                                        <td><strong><?= e($req['name']) ?></strong></td>
                                        <td><?= e($req['email']) ?></td>
                                        <td class="table__actions">
                                            <button type="button" class="btn btn--sm btn--primary js-approve-reset" 
                                                    data-id="<?= (int) $req['id'] ?>"
                                                    data-email="<?= e($req['email']) ?>">
                                                Approve Reset
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
