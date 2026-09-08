<?php
// TravelVista - file or edit a post request. Handled by ScoutController::requestForm().

// formData falls back through: old input, existing request, original post, defaults
$formData = [
    'title' => '', 'country' => '', 'genre' => 'city', 'cost_level' => 'medium',
    'base_cost' => '', 'travel_medium_info' => '', 'short_history' => '',
    'country_representation' => '', 'image' => ''
];

if ($request) {
    $formData = array_merge($formData, $request['data']);
} elseif ($originalPost) {
    $formData = array_merge($formData, $originalPost);
}

if (!empty($old)) {
    $formData = array_merge($formData, $old);
}
?>

<div class="wrap shell">
    <?php require APP_ROOT . '/app/Views/layouts/sidenav_scout.php'; ?>

    <div class="shell__main mt-4">
        
        <div class="page-head">
            <div>
                <span class="eyebrow"><?= $originalPost ? 'Change Request' : 'Dispatch' ?></span>
                <h1><?= e($pageTitle) ?></h1>
            </div>
            <a class="btn btn--ghost" href="<?= e(url('?page=scout/dashboard')) ?>">Cancel</a>
        </div>

        <?php require APP_ROOT . '/app/Views/layouts/flash.php'; ?>

        <div class="panel">
            <div class="panel__body">
                <form action="<?= url('?page=scout/submit_request') ?>" method="post" enctype="multipart/form-data" class="form stack">
                    <?= csrf_field() ?>
                    <?php if ($id > 0): ?>
                        <input type="hidden" name="id" value="<?= $id ?>">
                    <?php endif; ?>
                    <?php if ($originalPostId): ?>
                        <input type="hidden" name="original_post_id" value="<?= $originalPostId ?>">
                    <?php endif; ?>

                    <div class="field">
                        <label class="field__label" for="title">Title</label>
                        <input class="input" type="text" id="title" name="title" value="<?= e($formData['title']) ?>" required>
                        <?php if (isset($errors['title'])): ?><div class="field__error"><?= e($errors['title']) ?></div><?php endif; ?>
                    </div>
                    
                    <div class="field-row">
                        <div class="field">
                            <label class="field__label" for="country">Country</label>
                            <input class="input" type="text" id="country" name="country" value="<?= e($formData['country']) ?>" required>
                            <?php if (isset($errors['country'])): ?><div class="field__error"><?= e($errors['country']) ?></div><?php endif; ?>
                        </div>
                        <div class="field">
                            <label class="field__label" for="genre">Genre</label>
                            <select class="input" id="genre" name="genre" required>
                                <?php foreach (GENRES as $g): ?>
                                    <option value="<?= e($g) ?>" <?= ($formData['genre'] === $g) ? 'selected' : '' ?>><?= e(ucfirst($g)) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['genre'])): ?><div class="field__error"><?= e($errors['genre']) ?></div><?php endif; ?>
                        </div>
                        <div class="field">
                            <label class="field__label" for="cost_level">Cost Level</label>
                            <select class="input" id="cost_level" name="cost_level" required>
                                <?php foreach (COST_LEVELS as $c): ?>
                                    <option value="<?= e($c) ?>" <?= ($formData['cost_level'] === $c) ? 'selected' : '' ?>><?= e(ucfirst($c)) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['cost_level'])): ?><div class="field__error"><?= e($errors['cost_level']) ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label class="field__label" for="base_cost">Base Cost Estimate ($)</label>
                            <input class="input" type="number" id="base_cost" name="base_cost" step="0.01" value="<?= e($formData['base_cost']) ?>" placeholder="Leave blank to use default">
                            <?php if (isset($errors['base_cost'])): ?><div class="field__error"><?= e($errors['base_cost']) ?></div><?php endif; ?>
                        </div>

                        <div class="field">
                            <label class="field__label" for="travel_medium_info">Travel Medium</label>
                            <input class="input" type="text" id="travel_medium_info" name="travel_medium_info" value="<?= e($formData['travel_medium_info']) ?>" required>
                            <?php if (isset($errors['travel_medium_info'])): ?><div class="field__error"><?= e($errors['travel_medium_info']) ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="field">
                        <label class="field__label" for="short_history">Short History / Description</label>
                        <textarea class="input" id="short_history" name="short_history" rows="6" required><?= e($formData['short_history']) ?></textarea>
                        <?php if (isset($errors['short_history'])): ?><div class="field__error"><?= e($errors['short_history']) ?></div><?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="field__label" for="country_representation">What it says about the country</label>
                        <textarea class="input" id="country_representation" name="country_representation" rows="3"><?= e($formData['country_representation']) ?></textarea>
                        <?php if (isset($errors['country_representation'])): ?><div class="field__error"><?= e($errors['country_representation']) ?></div><?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="field__label" for="image">Cover Image (leave blank to keep current)</label>
                        <?php if (!empty($formData['image'])): ?>
                            <div class="current-image">
                                <img src="<?= e(post_cover($formData['image'], isset($formData['genre']) ? $formData['genre'] : 'city')) ?>" alt="Current cover">
                            </div>
                        <?php endif; ?>
                        <input class="input" type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                        <?php if (isset($errors['image'])): ?><div class="field__error"><?= e($errors['image']) ?></div><?php endif; ?>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn--primary"><?= $id > 0 ? 'Update Request' : 'Submit Request' ?></button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
