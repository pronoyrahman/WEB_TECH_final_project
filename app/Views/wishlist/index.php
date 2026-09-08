<section class="section section--tight">
    <div class="wrap">
        <?php require APP_ROOT . '/app/Views/layouts/flash.php'; ?>

        <div class="page-head">
            <div>
                <span class="eyebrow">Saved for later</span>
                <h1>Your wishlist</h1>
                <p><span id="savedCount"><?= count($saved) ?></span> places kept back for a trip you have not booked yet.</p>
            </div>
            <a class="btn btn--ghost" href="<?= e(url('?page=browse')) ?>">Find more</a>
        </div>

        <div id="wishlistBody">
            <?php if ($saved): ?>
                <div class="wishtotal">
                    <div>
                        <span class="eyebrow">If you did every one of them</span>
                        <div class="wishtotal__value" id="wishlistTotal"><?= e(money($total)) ?></div>
                    </div>
                    <p class="field__hint w-lg m-0">
                        Base costs added up: one week, one traveller, each place. Open a dispatch
                        to work out what your own trip would run to.
                    </p>
                </div>

                <div class="stack" id="savedList">
                    <?php foreach ($saved as $item): ?>
                        <article class="saved" data-saved-post="<?= (int) $item['id'] ?>">
                            <a class="saved__thumb" href="<?= e(url('?page=post&id=' . (int) $item['id'])) ?>"
                               tabindex="-1" aria-hidden="true">
                                <img src="<?= e(post_cover($item['image'], $item['genre'])) ?>"
                                     alt="" loading="lazy" width="800" height="350">
                            </a>

                            <div>
                                <h2 class="saved__title">
                                    <a href="<?= e(url('?page=post&id=' . (int) $item['id'])) ?>">
                                        <?= e($item['title']) ?>
                                    </a>
                                </h2>

                                <div class="saved__facts">
                                    <span class="tag"><?= e($item['country']) ?></span>
                                    <span class="tag"><?= e($item['genre']) ?></span>
                                    <span class="row row--tight">
                                        <?= meter_html($item['cost_level']) ?>
                                        <span class="data text-sm">
                                            <?= e(money((float) (isset($item['base_cost']) ? $item['base_cost'] : cost_base($item['cost_level'])))) ?>
                                        </span>
                                    </span>
                                    <span class="filenum">Saved <?= e(time_ago($item['added_at'])) ?></span>
                                </div>
                            </div>

                            <div class="saved__actions">
                                <a class="btn btn--sm btn--ghost"
                                   href="<?= e(url('?page=post&id=' . (int) $item['id'])) ?>">Open</a>

                                <button class="btn btn--sm btn--danger is-saved"
                                        type="button" data-wishlist-toggle
                                        data-post-id="<?= (int) $item['id'] ?>"
                                        aria-pressed="true">
                                    Remove
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty">
                    <h3>Nothing saved yet</h3>
                    <p>
                        When a dispatch looks like somewhere you would actually go, save it here.
                        The wishlist keeps a running total of what the trips would cost.
                    </p>
                    <a class="btn btn--primary" href="<?= e(url('?page=browse')) ?>">Browse the archive</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>


