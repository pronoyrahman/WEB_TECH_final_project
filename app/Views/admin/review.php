<div class="wrap shell">
    <?php require APP_ROOT . '/app/Views/layouts/sidenav_admin.php'; ?>

    <div class="shell__main">
        <?php require APP_ROOT . '/app/Views/layouts/flash.php'; ?>

        <div class="page-head">
            <div>
                <span class="eyebrow">
                    <?= e(file_number($requestId, 'REQ')) ?>
                    <?= $isChange ? ' &mdash; change to ' . e(file_number((int) $original['id'])) : '' ?>
                </span>
                <h1><?= e($data['title']) ?></h1>
                <p>
                    Filed by <strong><?= e($request['scout_name']) ?></strong>
                    <?= e(time_ago($request['requested_at'])) ?>
                    &middot; <span class="tag tag--<?= e($request['status']) ?>"><?= e($request['status']) ?></span>
                </p>
            </div>
            <a class="btn btn--ghost" href="<?= e(url('?page=admin/requests')) ?>">Back to the queue</a>
        </div>

        <div class="review">

            <!-- the submission -->
            <div>
                <?php if ($isChange): ?>
                    <div class="panel">
                        <div class="panel__head">
                            <h2 class="panel__title">What would change</h2>
                            <span class="filenum">published &rarr; proposed</span>
                        </div>
                        <div class="panel__body">
                            <?php foreach ($compared as $field => $label): ?>
                                <?php
                                $was = isset($original[$field]) ? trim((string) $original[$field]) : '';
                                $now = isset($data[$field]) ? trim((string) $data[$field]) : '';
                                $same = $was === $now;
                                ?>
                                <div class="diff__row <?= $same ? 'is-same' : '' ?>">
                                    <div>
                                        <span class="eyebrow"><?= e($label) ?> &mdash; published</span>
                                        <div class="diff__side diff__side--was">
                                            <?= $was === '' ? '<span class="muted">empty</span>' : nl2br(e($was)) ?>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="eyebrow"><?= $same ? 'unchanged' : 'proposed' ?></span>
                                        <div class="diff__side diff__side--now">
                                            <?= $now === '' ? '<span class="muted">empty</span>' : nl2br(e($now)) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="panel">
                        <div class="panel__head">
                            <h2 class="panel__title">The submission</h2>
                        </div>
                        <div class="panel__body">
                            <div class="review__field">
                                <span class="eyebrow">Short history</span>
                                <p><?= nl2br(e(isset($data['short_history']) ? $data['short_history'] : '')) ?></p>
                            </div>

                            <?php if (isset($data['country_representation']) && trim((string) $data['country_representation']) !== ''): ?>
                                <div class="review__field">
                                    <span class="eyebrow">What it represents about <?= e(isset($data['country']) ? $data['country'] : '') ?></span>
                                    <p><?= nl2br(e($data['country_representation'])) ?></p>
                                </div>
                            <?php endif; ?>

                            <div class="review__field">
                                <span class="eyebrow">Getting there</span>
                                <p><?= nl2br(e(isset($data['travel_medium_info']) ? $data['travel_medium_info'] : '')) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- the record and the decision -->
            <aside class="stack">

                <div class="panel">
                    <div class="panel__head">
                        <h2 class="panel__title">Cover</h2>
                    </div>
                    <div class="panel__body">
                        <img src="<?= e(post_cover(isset($data['image']) ? $data['image'] : null, isset($data['genre']) ? $data['genre'] : 'city')) ?>"
                             alt="" class="review__cover"
                             width="800" height="350">
                        <p class="field__hint mt-2">
                            <?= !empty($data['image'])
                                ? 'Uploaded by the scout.'
                                : 'No upload - the archive would use the genre illustration.' ?>
                        </p>

                        <dl class="ledger mt-4">
                            <div class="ledger__row">
                                <dt class="ledger__key">Country</dt>
                                <dd class="ledger__val"><?= e(isset($data['country']) ? $data['country'] : '') ?></dd>
                            </div>
                            <div class="ledger__row">
                                <dt class="ledger__key">Genre</dt>
                                <dd class="ledger__val"><?= e(isset($data['genre']) ? $data['genre'] : '') ?></dd>
                            </div>
                            <div class="ledger__row">
                                <dt class="ledger__key">Cost</dt>
                                <dd class="ledger__val">
                                    <?= meter_html(isset($data['cost_level']) && $data['cost_level'] ? $data['cost_level'] : 'medium') ?>
                                    <span class="ml-text"><?= e(isset($data['cost_level']) ? $data['cost_level'] : '') ?></span>
                                </dd>
                            </div>
                            <div class="ledger__row">
                                <dt class="ledger__key">Base cost</dt>
                                <dd class="ledger__val">
                                    <?= isset($data['base_cost']) && $data['base_cost'] !== ''
                                        ? e(money((float) $data['base_cost']))
                                        : e(money((float) cost_base(isset($data['cost_level']) && $data['cost_level'] ? $data['cost_level'] : 'medium'))) . ' (from level)' ?>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <?php if ($request['status'] === 'pending'): ?>
                    <div class="panel">
                        <div class="panel__head">
                            <h2 class="panel__title">Decide</h2>
                        </div>
                        <div class="panel__body stack">
                            <form method="post" action="<?= e(url('?page=admin/review_action')) ?>"
                                  data-confirm="Publish this to the archive?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="approve_request">
                                <input type="hidden" name="request_id" value="<?= (int) $requestId ?>">
                                <button class="btn btn--primary btn--block" type="submit">
                                    <?= $isChange ? 'Apply the changes' : 'Publish to the archive' ?>
                                </button>
                            </form>

                            <hr class="my-2">

                            <form class="form" method="post"
                                  action="<?= e(url('?page=admin/review_action')) ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="reject_request">
                                <input type="hidden" name="request_id" value="<?= (int) $requestId ?>">

                                <div class="field">
                                    <label class="field__label" for="admin_note">Why send it back?</label>
                                    <textarea class="textarea" id="admin_note" name="admin_note"
                                              rows="3" maxlength="255"
                                              placeholder="The scout sees this next to the request."></textarea>
                                </div>

                                <button class="btn btn--danger btn--block" type="submit">Send it back</button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="panel">
                        <div class="panel__head">
                            <h2 class="panel__title">Already decided</h2>
                        </div>
                        <div class="panel__body">
                            <p class="muted">
                                This request was marked
                                <strong><?= e($request['status']) ?></strong>.
                            </p>

                            <?php if (!empty($request['admin_note'])): ?>
                                <div class="notice mt-3">
                                    <div class="notice__body">
                                        <span class="eyebrow">Note to the scout</span>
                                        <p><?= e($request['admin_note']) ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <form method="post" class="mt-4"
                                  action="<?= e(url('?page=admin/review_action')) ?>"
                                  data-confirm="Delete this request for good?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete_request">
                                <input type="hidden" name="request_id" value="<?= (int) $requestId ?>">
                                <button class="btn btn--danger btn--block" type="submit">Delete the request</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</div>


