<section class="section section--tight">
    <div class="wrap">
        <?php require APP_ROOT . '/app/Views/layouts/flash.php'; ?>

        <div class="detail__cover">
            <img src="<?= e(post_cover($post['image'], $post['genre'])) ?>"
                 alt="<?= e($post['title']) ?>" width="800" height="350">
            <div class="detail__cover-tags">
                <span class="tag"><?= e($post['genre']) ?></span>
                <span class="tag"><?= e($post['country']) ?></span>
                <span class="tag"><?= e(file_number($postId)) ?></span>
            </div>
        </div>

        <div class="detail">

            <!-- the record -->
            <article>
                <span class="eyebrow">Dispatch <?= e(file_number($postId)) ?></span>
                <h1><?= e($post['title']) ?></h1>

                <div class="detail__meta">
                    <span>
                        Filed by <strong><?= e(isset($post['scout_name']) ? $post['scout_name'] : 'TravelVista') ?></strong>
                    </span>
                    <span aria-hidden="true">&middot;</span>
                    <span><?= e(pretty_date($post['created_at'])) ?></span>
                    <?php if ($post['updated_at'] !== $post['created_at']): ?>
                        <span aria-hidden="true">&middot;</span>
                        <span>revised <?= e(time_ago($post['updated_at'])) ?></span>
                    <?php endif; ?>
                    <span aria-hidden="true">&middot;</span>
                    <span><span id="commentCount"><?= count($comments) ?></span> notes</span>
                </div>

                <div class="detail__section prose">
                    <h2>Short history</h2>
                    <?php foreach (preg_split('/\n\s*\n/', trim($post['short_history'])) as $para): ?>
                        <p><?= nl2br(e(trim($para))) ?></p>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($post['country_representation'])): ?>
                    <div class="detail__section prose">
                        <h2>What it represents about <?= e($post['country']) ?></h2>
                        <?php foreach (preg_split('/\n\s*\n/', trim($post['country_representation'])) as $para): ?>
                            <p><?= nl2br(e(trim($para))) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="detail__section prose">
                    <h2>Getting there</h2>
                    <p><?= nl2br(e($post['travel_medium_info'])) ?></p>
                </div>

                <!-- comments -->
                <div class="detail__section" id="comments">
                    <div class="page-head">
                        <div>
                            <span class="eyebrow">From other travellers</span>
                            <h2>Notes</h2>
                        </div>
                    </div>

                    <?php if ($canSave): ?>
                        <form class="comment-form" id="commentForm" data-post-id="<?= (int) $postId ?>">
                            <?= csrf_field() ?>

                            <div class="field">
                                <label class="field__label" for="commentContent">
                                    Add a note as <?= e($user['name']) ?>
                                </label>
                                <textarea class="textarea" id="commentContent" name="content"
                                          rows="3" maxlength="<?= Comment::MAX_LENGTH ?>"
                                          data-max="<?= Comment::MAX_LENGTH ?>"
                                          placeholder="What should the next traveller know?"></textarea>
                                <div class="field__counter" data-counter-for="content">
                                    0 / <?= Comment::MAX_LENGTH ?>
                                </div>
                                <p class="field__error" data-error-for="content"></p>
                            </div>

                            <div class="row row--end">
                                <button class="btn btn--primary" type="submit">Post note</button>
                            </div>
                        </form>
                    <?php elseif ($user === null): ?>
                        <div class="notice mb-5">
                            <div class="notice__body">
                                <span class="eyebrow">Sign in to join in</span>
                                <p>
                                    Notes are written by travellers.
                                    <a href="<?= e(url('?page=login')) ?>">Sign in</a> or
                                    <a href="<?= e(url('?page=register')) ?>">create an account</a>
                                    to add one.
                                </p>
                            </div>
                        </div>
                    <?php elseif ($user['role'] !== 'user'): ?>
                        <div class="notice mb-5">
                            <div class="notice__body">
                                <span class="eyebrow">Read only</span>
                                <p>
                                    Notes are written by travellers. Your
                                    <?= e($user['role']) ?> account can read them but not add to them.
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="comments__list" id="commentList">
                        <?php foreach ($comments as $comment): ?>
                            <?php $own = $user !== null && (int) $comment['user_id'] === (int) $user['id']; ?>
                            <article class="comment" data-comment="<?= (int) $comment['id'] ?>">
                                <?php $face = avatar_url($comment['profile_picture']); ?>
                                <?php if ($face): ?>
                                    <img class="avatar" src="<?= e($face) ?>" alt="">
                                <?php else: ?>
                                    <span class="avatar" aria-hidden="true"><?= e(initial($comment['author'])) ?></span>
                                <?php endif; ?>

                                <div>
                                    <div class="comment__head">
                                        <span class="comment__author"><?= e($comment['author']) ?></span>
                                        <?php if ($comment['author_role'] === 'scout'): ?>
                                            <span class="tag tag--sea">Scout</span>
                                        <?php endif; ?>
                                        <span class="comment__when"><?= e(time_ago($comment['created_at'])) ?></span>

                                        <?php if ($own || ($user !== null && $user['role'] === 'admin')): ?>
                                            <button class="btn btn--link comment__del" type="button"
                                                    data-delete-comment
                                                    data-comment-id="<?= (int) $comment['id'] ?>">
                                                Delete
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <p class="comment__body"><?= e($comment['content']) ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <?php if (!$comments): ?>
                        <div class="empty" id="commentsEmpty">
                            <h3>No notes yet</h3>
                            <p>
                                <?= $canSave
                                    ? 'Been here? Tell the next traveller what to expect.'
                                    : 'Nobody has written about this one yet.' ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </article>

            <!-- sidebar: ledger, calculator, save button -->
            <aside class="detail__aside">

                <div class="panel">
                    <div class="panel__head">
                        <h2 class="panel__title">The record</h2>
                        <span class="filenum"><?= e(file_number($postId)) ?></span>
                    </div>
                    <div class="panel__body">
                        <dl class="ledger ledger--flush">
                            <div class="ledger__row">
                                <dt class="ledger__key">Country</dt>
                                <dd class="ledger__val"><?= e($post['country']) ?></dd>
                            </div>
                            <div class="ledger__row">
                                <dt class="ledger__key">Genre</dt>
                                <dd class="ledger__val"><?= e($post['genre']) ?></dd>
                            </div>
                            <div class="ledger__row">
                                <dt class="ledger__key">Medium</dt>
                                <dd class="ledger__val"><?= e(excerpt($post['travel_medium_info'], 24)) ?></dd>
                            </div>
                            <div class="ledger__row">
                                <dt class="ledger__key">Cost</dt>
                                <dd class="ledger__val">
                                    <?= meter_html($post['cost_level']) ?>
                                    <span class="ml-text"><?= e(ucfirst($post['cost_level'])) ?></span>
                                </dd>
                            </div>
                            <div class="ledger__row">
                                <dt class="ledger__key">Filed</dt>
                                <dd class="ledger__val"><?= e(pretty_date($post['created_at'])) ?></dd>
                            </div>
                        </dl>

                        <?php if ($canSave): ?>
                            <button class="btn btn--primary btn--block mt-4<?= $isSaved ? ' is-saved' : '' ?>"
                                    type="button" data-wishlist-toggle
                                    data-post-id="<?= (int) $postId ?>"
                                    aria-pressed="<?= $isSaved ? 'true' : 'false' ?>">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M6 3h12a1 1 0 0 1 1 1v17l-7-4-7 4V4a1 1 0 0 1 1-1z"/>
                                </svg>
                                <span data-wishlist-label><?= $isSaved ? 'Saved to wishlist' : 'Save to wishlist' ?></span>
                            </button>
                        <?php endif; ?>

                        <?php if ($user !== null && $user['role'] === 'scout' && (int) $post['scout_id'] === (int) $user['id']): ?>
                            <a class="btn btn--ghost btn--block"
                               href="<?= e(url('?page=scout/request_form&change=' . $postId)) ?>"
                               class="mt-3">
                                Request a change
                            </a>
                        <?php endif; ?>

                        <?php if ($user !== null && $user['role'] === 'admin'): ?>
                            <a class="btn btn--ghost btn--block"
                               href="<?= e(url('?page=admin/post_edit&id=' . $postId)) ?>"
                               class="mt-3">
                                Edit this post
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- The probable-cost calculator -->
                <div class="panel">
                    <div class="panel__head">
                        <h2 class="panel__title">Probable cost</h2>
                    </div>

                    <div class="panel__body" id="costCalc"
                         data-post-id="<?= (int) $postId ?>"
                         data-base-cost="<?= e((string) $baseCost) ?>"
                         data-symbol="<?= $currency === 'USD' ? '$' : e($currency . ' ') ?>">

                        <div class="calc__base">
                            <span class="eyebrow m-0">Base, one week, one person</span>
                            <span class="calc__base-value"><?= e(money($baseCost, $currency)) ?></span>
                        </div>

                        <div class="calc__controls">
                            <div class="field">
                                <label class="field__label" for="calcTravellers">Travellers</label>
                                <div class="stepper">
                                    <button type="button" data-step="-1" aria-label="One fewer traveller">&minus;</button>
                                    <input type="number" id="calcTravellers" value="2" min="1" max="10" step="1"
                                           inputmode="numeric" aria-label="Number of travellers">
                                    <button type="button" data-step="1" aria-label="One more traveller">+</button>
                                </div>
                            </div>

                            <div class="field">
                                <label class="field__label" for="calcDays">Days</label>
                                <div class="stepper">
                                    <button type="button" data-step="-1" aria-label="One fewer day">&minus;</button>
                                    <input type="number" id="calcDays" value="7" min="1" max="60" step="1"
                                           inputmode="numeric" aria-label="Number of days">
                                    <button type="button" data-step="1" aria-label="One more day">+</button>
                                </div>
                            </div>
                        </div>

                        <div class="calc__total">
                            <span class="calc__total-value" id="calcTotal" aria-live="polite">&mdash;</span>
                            <span class="calc__total-label">Estimated trip total</span>
                        </div>

                        <div class="calc__split">
                            <span>Per person <b id="calcPerPerson">&mdash;</b></span>
                            <span>Per day <b id="calcPerDay">&mdash;</b></span>
                        </div>

                        <p class="calc__note" id="calcNote">
                            An estimate, not a quote. Each traveller after the first counts as 85%.
                        </p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>




