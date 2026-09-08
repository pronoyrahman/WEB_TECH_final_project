<?php
// TravelVista - edit a published post (admin). Controller passes $post, $baseCost, $errors, $old.

$formData = !empty($old) ? array_merge($post, $old) : $post;
?>

<div class="wrap shell">
    <?php require APP_ROOT . '/app/Views/layouts/sidenav_admin.php'; ?>

    <div class="shell__main">
        <?php require APP_ROOT . '/app/Views/layouts/flash.php'; ?>

        <div class="page-head">
            <div>
                <span class="eyebrow"><?= e(file_number((int) $post['id'])) ?></span>
                <h1>Edit Published Post</h1>
            </div>
            <a class="btn btn--ghost" href="<?= e(url('?page=admin/posts')) ?>">Back to posts</a>
        </div>

        <div class="panel">
            <div class="panel__body">
                <form action="<?= e(url('?page=admin/post_update')) ?>" method="post" enctype="multipart/form-data" class="form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_post">
                    <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">

                    <div class="field">
                        <label class="field__label" for="title">Title</label>
                        <input class="input" type="text" id="title" name="title"
                               value="<?= e($formData['title']) ?>" required>
                        <?php if (isset($errors['title'])): ?><div class="field__error"><?= e($errors['title']) ?></div><?php endif; ?>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label class="field__label" for="country">Country</label>
                            <input class="input" type="text" id="country" name="country"
                                   value="<?= e($formData['country']) ?>" required>
                            <?php if (isset($errors['country'])): ?><div class="field__error"><?= e($errors['country']) ?></div><?php endif; ?>
                        </div>
                        <div class="field">
                            <label class="field__label" for="genre">Genre</label>
                            <select class="select" id="genre" name="genre" required>
                                <?php foreach (GENRES as $g): ?>
                                    <option value="<?= e($g) ?>" <?= $formData['genre'] === $g ? 'selected' : '' ?>><?= e(ucfirst($g)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label class="field__label" for="cost_level">Cost Level</label>
                            <select class="select" id="cost_level" name="cost_level" required>
                                <?php foreach (COST_LEVELS as $c): ?>
                                    <option value="<?= e($c) ?>" <?= $formData['cost_level'] === $c ? 'selected' : '' ?>><?= e(ucfirst($c)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="field">
                        <label class="field__label" for="base_cost">Base Cost Estimate ($)</label>
                        <input class="input" type="number" id="base_cost" name="base_cost" step="0.01"
                               value="<?= e(isset($baseCost) ? $baseCost : '') ?>"
                               placeholder="Leave blank to use default">
                    </div>

                    <div class="field">
                        <label class="field__label" for="travel_medium_info">Travel Medium (How to get there)</label>
                        <input class="input" type="text" id="travel_medium_info" name="travel_medium_info"
                               value="<?= e($formData['travel_medium_info']) ?>" required>
                    </div>

                    <div class="field">
                        <label class="field__label" for="short_history">Short History / Description</label>
                        <textarea class="textarea" id="short_history" name="short_history" rows="6" required><?= e($formData['short_history']) ?></textarea>
                    </div>

                    <div class="field">
                        <label class="field__label" for="country_representation">What it says about the country</label>
                        <textarea class="textarea" id="country_representation" name="country_representation" rows="3"><?= e($formData['country_representation']) ?></textarea>
                    </div>

                    <div class="field">
                        <label class="field__label" for="image">Cover Image (leave blank to keep current)</label>
                        <?php if (!empty($post['image'])): ?>
                            <div class="current-image">
                                <img src="<?= e(post_cover($post['image'], $post['genre'])) ?>" alt="Current cover">
                            </div>
                        <?php endif; ?>
                        <input class="input" type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                        <?php if (isset($errors['image'])): ?><div class="field__error"><?= e($errors['image']) ?></div><?php endif; ?>
                    </div>

                    <div class="field-row mt-4">
                        <button class="btn btn--primary" type="submit">Save Changes</button>
                        <a class="btn btn--ghost" href="<?= e(url('?page=admin/posts')) ?>">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Danger zone -->
        <div class="panel mt-4">
            <div class="panel__head">
                <h2 class="panel__title">Danger zone</h2>
            </div>
            <div class="panel__body">
                <form method="post" action="<?= e(url('?page=admin/post_update')) ?>"
                      data-confirm="Delete this post permanently?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete_post">
                    <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                    <button class="btn btn--danger" type="submit">Delete this post</button>
                </form>
            </div>
        </div>
    </div>
</div>
