<section class="section">
    <div class="wrap wrap--narrow">
        <?php require APP_ROOT . '/app/Views/layouts/flash.php'; ?>

        <div class="notice notice--warn mb-6">
            <div class="notice__body">
                <span class="eyebrow eyebrow--brass">Account status</span>
                <h1 class="mb-2">
                    Your account is pending admin approval
                </h1>
                <p>
                    <?= e($user['name']) ?>, your <strong><?= e($user['role']) ?></strong> account was
                    created on <?= e(pretty_date($user['created_at'])) ?>. An admin has to approve it
                    before the archive opens up. There is nothing else for you to do.
                </p>
            </div>
        </div>

        <div class="panel">
            <div class="panel__head">
                <h3 class="panel__title">While you wait</h3>
                <span class="filenum"><?= e(file_number((int) $user['id'], 'USR')) ?></span>
            </div>
            <div class="panel__body">
                <p class="prose">
                    You can still fill in your profile and set a picture. Once an admin verifies
                    the account, everything below unlocks on your next page load.
                </p>

                <dl class="ledger mt-4">
                    <div class="ledger__row">
                        <dt class="ledger__key">Name</dt>
                        <dd class="ledger__val"><?= e($user['name']) ?></dd>
                    </div>
                    <div class="ledger__row">
                        <dt class="ledger__key">Email</dt>
                        <dd class="ledger__val"><?= e($user['email']) ?></dd>
                    </div>
                    <div class="ledger__row">
                        <dt class="ledger__key">Role</dt>
                        <dd class="ledger__val"><?= e($user['role']) ?></dd>
                    </div>
                    <div class="ledger__row">
                        <dt class="ledger__key">Status</dt>
                        <dd class="ledger__val"><span class="tag tag--pending">Pending</span></dd>
                    </div>
                </dl>

                <div class="row mt-5">
                    <a class="btn btn--primary" href="<?= e(url('?page=profile')) ?>">Fill in your profile</a>
                    <a class="btn btn--ghost" href="<?= e(url('?page=home')) ?>">Back to home</a>
                </div>
            </div>
        </div>
    </div>
</section>


