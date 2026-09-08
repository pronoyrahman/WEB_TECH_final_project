<div class="wrap shell">
    <?php require APP_ROOT . '/app/Views/layouts/sidenav_admin.php'; ?>

    <div class="shell__main">
        <?php require APP_ROOT . '/app/Views/layouts/flash.php'; ?>

        <div class="page-head">
            <div>
                <span class="eyebrow">Admin</span>
                <h1>Users</h1>
                <p>
                    <?= (int) $counts['total'] ?> accounts,
                    <span data-count="unverified"><?= (int) $counts['unverified'] ?></span> waiting on approval.
                </p>
            </div>
            <button class="btn btn--primary" type="button" id="addUserToggle"
                    aria-expanded="<?= $formOpen ? 'true' : 'false' ?>" aria-controls="addUserPanel">
                <?= $formOpen ? 'Cancel' : 'Add a user' ?>
            </button>
        </div>

        <!-- add a user by hand -->
        <div class="panel" id="addUserPanel" <?= $formOpen ? '' : 'hidden' ?>>
            <div class="panel__head">
                <h2 class="panel__title">Add a user</h2>
            </div>
            <div class="panel__body">
                <form class="form" method="post"
                      action="<?= e(url('?page=admin/user_action')) ?>" data-validate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="create">

                    <div class="form__row">
                        <div class="field">
                            <label class="field__label" for="new_name">Name <span class="req">*</span></label>
                            <input class="input <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                   type="text" id="new_name" name="name"
                                   value="<?= e(isset($old['name']) ? $old['name'] : '') ?>"
                                   data-rules="required" data-max="100" data-label="Name">
                            <p class="field__error" data-error-for="name"><?= e(isset($errors['name']) ? $errors['name'] : '') ?></p>
                        </div>

                        <div class="field">
                            <label class="field__label" for="new_email">Email <span class="req">*</span></label>
                            <input class="input <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                   type="email" id="new_email" name="email"
                                   value="<?= e(isset($old['email']) ? $old['email'] : '') ?>"
                                   data-rules="required email" data-label="Email" data-check-email>
                            <p class="field__error" data-error-for="email"><?= e(isset($errors['email']) ? $errors['email'] : '') ?></p>
                        </div>
                    </div>

                    <div class="form__row">
                        <div class="field">
                            <label class="field__label" for="new_password">Password</label>
                            <input class="input <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                   type="password" id="new_password" name="password"
                                   data-rules="min:8" data-label="Password">
                            <p class="field__hint">Must be at least 8 characters long.</p>
                            <p class="field__error" data-error-for="password"><?= e(isset($errors['password']) ? $errors['password'] : '') ?></p>
                        </div>

                        <div class="field">
                            <label class="field__label" for="new_role">Role</label>
                            <select class="select <?= isset($errors['role']) ? 'is-invalid' : '' ?>"
                                    id="new_role" name="role">
                                <?php foreach (['user' => 'User', 'scout' => 'Scout', 'admin' => 'Admin'] as $val => $label): ?>
                                    <option value="<?= e($val) ?>" <?= (isset($old['role']) ? $old['role'] : '') === $val ? 'selected' : '' ?>>
                                        <?= e($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="field__error" data-error-for="role"><?= e(isset($errors['role']) ? $errors['role'] : '') ?></p>
                        </div>
                    </div>

                    <div class="field">
                        <label class="checkbox">
                            <input type="checkbox" name="is_verified" value="1" <?= isset($old['is_verified']) ? 'checked' : '' ?>>
                            Mark as verified (skip approval)
                        </label>
                    </div>

                    <button class="btn btn--primary" type="submit">Create account</button>
                </form>
            </div>
        </div>

        <!-- search and filters -->
        <div class="panel panel--flat pt-0">
            <form class="table-filters" method="get" action="<?= e(url('?page=admin/users')) ?>">
                <div class="field">
                    <label class="sr-only" for="q">Search by name or email</label>
                    <input class="input" type="search" id="q" name="q" value="<?= e($term) ?>"
                           placeholder="Search users...">
                </div>

                <div class="field">
                    <label class="sr-only" for="roleF">Role</label>
                    <select class="select" id="roleF" name="role">
                        <option value="">Any role</option>
                        <option value="user" <?= $roleF === 'user' ? 'selected' : '' ?>>Users only</option>
                        <option value="scout" <?= $roleF === 'scout' ? 'selected' : '' ?>>Scouts only</option>
                        <option value="admin" <?= $roleF === 'admin' ? 'selected' : '' ?>>Admins only</option>
                    </select>
                </div>

                <div class="field">
                    <label class="sr-only" for="verified">Status</label>
                    <select class="select" id="verified" name="verified">
                        <option value="">Any status</option>
                        <option value="1" <?= $verified === '1' ? 'selected' : '' ?>>Verified</option>
                        <option value="0" <?= $verified === '0' ? 'selected' : '' ?>>Pending</option>
                    </select>
                </div>

                <button class="btn btn--ghost" type="submit">Filter</button>

                <?php if ($term || $roleF || $verified !== ''): ?>
                    <a class="btn btn--ghost" href="<?= e(url('?page=admin/users')) ?>">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- the user list -->
        <div class="panel">
            <?php if ($people): ?>
                <div class="panel__body panel__body--flush table-scroll">
                    <table class="table table--middle">
                        <thead>
                            <tr>
                                <th>Name &amp; Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Status</th>
                                <th class="cell-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($people as $person): ?>
                                <tr>
                                    <td>
                                        <div class="cell-person">
                                            <div class="cell-person__avatar avatar avatar--sm">
                                                <?php if (!empty($person['profile_picture'])): ?>
                                                    <img src="<?= e(avatar_url($person['profile_picture'])) ?>" alt="">
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div class="cell-person__name">
                                                    <?= e($person['name']) ?>
                                                    <?php if ((int)$person['id'] === (int)$user['id']): ?>
                                                        <span class="muted">(You)</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="cell-person__meta"><?= e($person['email']) ?></div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <?php if ((int)$person['id'] === (int)$user['id']): ?>
                                            <span class="tag"><?= e($person['role']) ?></span>
                                        <?php else: ?>
                                            <form method="post" action="<?= e(url('?page=admin/user_action')) ?>">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="role">
                                                <input type="hidden" name="user_id" value="<?= (int) $person['id'] ?>">
                                                <select class="select select--sm" name="role" aria-label="Change role"
                                                        onchange="this.form.submit()">
                                                    <?php foreach (['user' => 'User', 'scout' => 'Scout', 'admin' => 'Admin'] as $val => $label): ?>
                                                        <option value="<?= e($val) ?>" <?= $person['role'] === $val ? 'selected' : '' ?>>
                                                            <?= e($label) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </form>
                                        <?php endif; ?>
                                    </td>

                                    <td class="cell-tight"><?= e(time_ago($person['created_at'])) ?></td>

                                    <td class="cell-tight">
                                        <label class="toggle" aria-label="Toggle verification">
                                            <input type="checkbox" class="toggle__input"
                                                   data-verify-user="<?= (int) $person['id'] ?>"
                                                   <?= $person['is_verified'] ? 'checked' : '' ?>>
                                            <span class="toggle__fill"></span>
                                        </label>
                                    </td>

                                    <td class="cell-actions">
                                        <form method="post" class="form-inline"
                                              action="<?= e(url('?page=admin/user_action')) ?>"
                                              data-confirm="Delete <?= e($person['name']) ?>? This drops all their posts, requests and comments too.">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?= (int) $person['id'] ?>">
                                            <button class="btn btn--sm btn--danger" type="submit"
                                                    <?= (int)$person['id'] === (int)$user['id'] ? 'disabled' : '' ?>>
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty">
                    <h3>No accounts found</h3>
                    <p>Try clearing your filters or search term.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


