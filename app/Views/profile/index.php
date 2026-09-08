<?php
// TravelVista - profile. Handled by ProfileController.

$avatar = avatar_url(isset($user['profile_picture']) ? $user['profile_picture'] : null);
?>

<div class="wrap shell">
    
    <aside class="sidenav profile-card">
        <div class="profile-card__head">
            <img src="<?= e($avatar) ?>" alt="Avatar" class="profile-card__avatar">
            <h3 class="profile-card__name"><?= e($user['name']) ?></h3>
            <span class="tag tag--<?= e($user['role']) ?>"><?= e(ucfirst($user['role'])) ?></span>
        </div>
    </aside>

    <div class="shell__main">
        <?php require APP_ROOT . '/app/Views/layouts/flash.php'; ?>

        <div class="page-head">
            <div>
                <span class="eyebrow">Settings</span>
                <h1>My Profile</h1>
            </div>
        </div>

        <div class="panel">
            <div class="panel__head">
                <h2 class="panel__title">Update Details</h2>
            </div>
            <div class="panel__body">
                <form action="<?= url('?page=profile/update') ?>" method="post" enctype="multipart/form-data" class="form stack">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_profile">

                    <div class="field">
                        <label class="field__label" for="name">Name</label>
                        <input class="input" type="text" id="name" name="name" 
                               value="<?= e(isset($old['name']) ? $old['name'] : $user['name']) ?>" required>
                        <?php if (isset($errors['name'])): ?><div class="field__error"><?= e($errors['name']) ?></div><?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="field__label" for="email">Email</label>
                        <input class="input" type="email" id="email" name="email" 
                               value="<?= e(isset($old['email']) ? $old['email'] : $user['email']) ?>" required>
                        <?php if (isset($errors['email'])): ?><div class="field__error"><?= e($errors['email']) ?></div><?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="field__label" for="profile_picture">Profile Picture</label>
                        <input class="input" type="file" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png,image/webp">
                        <?php if (isset($errors['profile_picture'])): ?><div class="field__error"><?= e($errors['profile_picture']) ?></div><?php endif; ?>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn--primary">Save Profile</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="panel mt-6">
            <div class="panel__head">
                <h2 class="panel__title">Change Password</h2>
            </div>
            <div class="panel__body">
                <form action="<?= url('?page=profile/password') ?>" method="post" class="form stack">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="change_password">

                    <div class="field">
                        <label class="field__label" for="current_password">Current Password</label>
                        <input class="input" type="password" id="current_password" name="current_password" required>
                        <?php if (isset($errors['current_password'])): ?><div class="field__error"><?= e($errors['current_password']) ?></div><?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="field__label" for="new_password">New Password</label>
                        <input class="input" type="password" id="new_password" name="new_password" required>
                        <?php if (isset($errors['new_password'])): ?><div class="field__error"><?= e($errors['new_password']) ?></div><?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="field__label" for="new_password_confirm">Confirm New Password</label>
                        <input class="input" type="password" id="new_password_confirm" name="new_password_confirm" required>
                        <?php if (isset($errors['new_password_confirm'])): ?><div class="field__error"><?= e($errors['new_password_confirm']) ?></div><?php endif; ?>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn--warning">Update Password</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
