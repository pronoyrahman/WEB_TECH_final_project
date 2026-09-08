<?php
// TravelVista - one destination card.
// Expects: $card (post row), $cardSaved (bool, optional), $cardIndex (int, optional).
// app.js renderCard() must produce the same markup - keep them in sync.

$cardSaved = isset($cardSaved) ? $cardSaved : false;
$cardIndex = isset($cardIndex) ? $cardIndex : 0;
$postUrl   = url('?page=post&id=' . (int) $card['id']);
?>
<article class="dispatch reveal">

    <a class="dispatch__cover" href="<?= e($postUrl) ?>" tabindex="-1" aria-hidden="true">
        <img src="<?= e(post_cover(isset($card['image']) ? $card['image'] : null, $card['genre'])) ?>"
             alt="" loading="lazy" width="800" height="350">
        <span class="tag dispatch__stamp"><?= e($card['genre']) ?></span>
    </a>

    <?php if (is_traveller()): ?>
        <button class="dispatch__save<?= $cardSaved ? ' is-saved' : '' ?>"
                type="button"
                data-wishlist-toggle
                data-post-id="<?= (int) $card['id'] ?>"
                aria-pressed="<?= $cardSaved ? 'true' : 'false' ?>"
                title="<?= $cardSaved ? 'Remove from wishlist' : 'Save to wishlist' ?>">
            <span class="sr-only"><?= $cardSaved ? 'Remove from wishlist' : 'Save to wishlist' ?></span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linejoin="round" aria-hidden="true">
                <path d="M6 3h12a1 1 0 0 1 1 1v17l-7-4-7 4V4a1 1 0 0 1 1-1z"/>
            </svg>
        </button>
    <?php endif; ?>

    <div class="dispatch__body">
        <h3 class="dispatch__title">
            <a href="<?= e($postUrl) ?>"><?= e($card['title']) ?></a>
        </h3>

        <p class="dispatch__snippet"><?= e(excerpt($card['short_history'], 108)) ?></p>

        <dl class="ledger">
            <div class="ledger__row">
                <dt class="ledger__key">Country</dt>
                <dd class="ledger__val"><?= e($card['country']) ?></dd>
            </div>
            <div class="ledger__row">
                <dt class="ledger__key">Medium</dt>
                <dd class="ledger__val"><?= e(excerpt($card['travel_medium_info'], 28)) ?></dd>
            </div>
            <div class="ledger__row">
                <dt class="ledger__key">Cost</dt>
                <dd class="ledger__val">
                    <?= meter_html($card['cost_level']) ?>
                    <span class="ml-text"><?= e(money((float) cost_base($card['cost_level']))) ?></span>
                </dd>
            </div>
        </dl>
    </div>

    <div class="dispatch__foot">
        <span class="dispatch__by">
            Filed by <strong><?= e(isset($card['scout_name']) ? $card['scout_name'] : 'TravelVista') ?></strong>
        </span>
        <span class="filenum"><?= e(file_number((int) $card['id'])) ?></span>
    </div>
</article>
