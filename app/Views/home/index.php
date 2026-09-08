<?php if ($user === null): ?>
    <!-- Guest: the landing page -->

    <section class="hero">
        <div class="wrap hero__inner">
            <div>
                <span class="script script--light">Explore &middot; Discover &middot; Experience</span>

                <h1 class="hero__title">
                    See the world through<br>
                    <em>someone who went first</em>
                </h1>

                <p class="hero__lede">
                    TravelVista is a guide written by scouts who actually made the trip. Every
                    destination is checked by an editor before it is published, so what you read
                    is what you will find when you get there.
                </p>

                <div class="hero__cta">
                    <a class="btn btn--primary btn--lg" href="<?= e(url('?page=register')) ?>">
                        Start your journey
                    </a>
                    <a class="btn btn--ghost-light btn--lg" href="<?= e(url('?page=login')) ?>">
                        I already have an account
                    </a>
                </div>
            </div>

            <div class="herostats">
                <div class="herostat">
                    <span class="herostat__num"><?= number_format($stats['approved']) ?></span>
                    <span class="herostat__label">Destinations</span>
                </div>
                <div class="herostat">
                    <span class="herostat__num"><?= number_format($stats['countries']) ?></span>
                    <span class="herostat__label">Countries</span>
                </div>
                <div class="herostat">
                    <span class="herostat__num"><?= number_format($stats['scouts']) ?></span>
                    <span class="herostat__label">Scouts filing</span>
                </div>
                <div class="herostat">
                    <span class="herostat__num">100<span class="herostat__unit">%</span></span>
                    <span class="herostat__label">Editor checked</span>
                </div>
            </div>
        </div>
    </section>

    <div class="wrap">
        <?php require APP_ROOT . '/app/Views/layouts/flash.php'; ?>
    </div>

    <!-- the numbers again, as a band over the brand gradient -->
    <section class="section">
        <div class="wrap">
            <div class="statband">
                <div class="statband__grid">
                    <div>
                        <span class="statband__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 21s7-6.3 7-11a7 7 0 1 0-14 0c0 4.7 7 11 7 11z"/>
                                <circle cx="12" cy="10" r="2.5"/>
                            </svg>
                        </span>
                        <span class="statband__num"><?= number_format($stats['approved']) ?></span>
                        <span class="statband__label">Destinations published</span>
                    </div>

                    <div>
                        <span class="statband__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M3 12h18M12 3c2.5 3 2.5 15 0 18M12 3c-2.5 3-2.5 15 0 18"/>
                            </svg>
                        </span>
                        <span class="statband__num"><?= number_format($stats['countries']) ?></span>
                        <span class="statband__label">Countries covered</span>
                    </div>

                    <div>
                        <span class="statband__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M2 13l20-8-8 20-2.5-8z"/>
                            </svg>
                        </span>
                        <span class="statband__num"><?= number_format($stats['scouts']) ?></span>
                        <span class="statband__label">Scouts filing reports</span>
                    </div>

                    <div>
                        <span class="statband__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                        </span>
                        <span class="statband__num"><?= number_format(Comment::countAll()) ?></span>
                        <span class="statband__label">Traveller notes</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section section--pad-b">
        <div class="wrap">
            <div class="section-head">
                <span class="script">How it works</span>
                <h2>Three jobs, one archive</h2>
                <p>Pick the one that matches why you are here. You hold one role at a time.</p>
            </div>

            <div class="roles">
                <div class="role">
                    <span class="role__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M2 13l20-8-8 20-2.5-8z"/>
                        </svg>
                    </span>
                    <h3>Scout</h3>
                    <p>
                        Write up a place you know first-hand: its history, what it means to the
                        country, the going rate and how you get there. File it for review, and
                        ask to amend it later when things change.
                    </p>
                </div>

                <div class="role">
                    <span class="role__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 11l2 2 4-4"/>
                            <path d="M20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h9"/>
                        </svg>
                    </span>
                    <h3>Admin</h3>
                    <p>
                        Approve new accounts, read every submission before it goes live, edit
                        what needs editing and keep the comments civil. Nothing reaches the
                        archive unread.
                    </p>
                </div>

                <div class="role">
                    <span class="role__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 3h12a1 1 0 0 1 1 1v17l-7-4-7 4V4a1 1 0 0 1 1-1z"/>
                        </svg>
                    </span>
                    <h3>Traveller</h3>
                    <p>
                        Search by country, budget and kind of trip, save places to a wishlist,
                        work out what a trip would actually cost, and leave notes for whoever
                        goes next.
                    </p>
                </div>
            </div>
        </div>
    </section>

<?php elseif (!$user['is_verified']): ?>
    <!-- Signed in, but the account is not approved yet -->

    <section class="section">
        <div class="wrap wrap--narrow">
            <?php require APP_ROOT . '/app/Views/layouts/flash.php'; ?>

            <div class="notice notice--warn mb-6">
                <div class="notice__body">
                    <span class="eyebrow eyebrow--warm">Account status</span>
                    <h2 class="mb-2">Your account is pending admin approval</h2>
                    <p>
                        You are signed in as <strong><?= e($user['name']) ?></strong>, registered as a
                        <strong><?= e($user['role']) ?></strong>. An admin has to approve the account
                        before the archive opens up. Nothing else is needed from you.
                    </p>
                </div>
            </div>

            <div class="panel">
                <div class="panel__head">
                    <h3 class="panel__title">What you will be able to do</h3>
                </div>
                <div class="panel__body">
                    <ul class="stack plain-list">
                        <li class="row row--top">
                            <span class="tag tag--sea">Browse</span>
                            <span>Read every published destination and search the whole archive.</span>
                        </li>
                        <?php if ($user['role'] === 'user'): ?>
                            <li class="row row--top">
                                <span class="tag tag--sea">Save</span>
                                <span>Keep a wishlist of places and see what the trips would cost.</span>
                            </li>
                            <li class="row row--top">
                                <span class="tag tag--sea">Comment</span>
                                <span>Leave notes on a destination for the next traveller.</span>
                            </li>
                        <?php elseif ($user['role'] === 'scout'): ?>
                            <li class="row row--top">
                                <span class="tag tag--sea">File</span>
                                <span>Submit destinations you know for the editors to review.</span>
                            </li>
                        <?php else: ?>
                            <li class="row row--top">
                                <span class="tag tag--sea">Moderate</span>
                                <span>Verify accounts, publish destinations and manage comments.</span>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <div class="row mt-5">
                        <a class="btn btn--primary" href="<?= e(url('?page=profile')) ?>">Update your profile</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php else: ?>
    <!-- Verified: greeting and the latest destinations -->

    <?php
    $latest      = Post::latestApproved(6);
    $savedIds    = $user['role'] === 'user' ? Wishlist::postIds((int) $user['id']) : [];
    $genreCounts = Post::genreCounts();
    ?>

    <section class="section section--tight">
        <div class="wrap">
            <?php require APP_ROOT . '/app/Views/layouts/flash.php'; ?>

            <div class="welcome">
                <div>
                    <span class="script script--light" id="localDate"></span>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            document.getElementById('localDate').textContent = new Date().toLocaleDateString(undefined, {
                                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
                            });
                        });
                    </script>
                    <h1>Good to see you, <?= e(strtok($user['name'], ' ')) ?>.</h1>
                    <p>
                        <?= number_format($stats['approved']) ?> destinations across
                        <?= number_format($stats['countries']) ?> countries are open to you.
                    </p>
                </div>

                <div class="welcome__actions">
                    <a class="btn btn--primary" href="<?= e(url('?page=browse')) ?>">Browse the archive</a>
                    <?php if ($user['role'] === 'user'): ?>
                        <a class="btn btn--ghost-light" href="<?= e(url('?page=wishlist')) ?>">Your wishlist</a>
                    <?php elseif ($user['role'] === 'scout'): ?>
                        <a class="btn btn--ghost-light" href="<?= e(url('?page=scout/request_form')) ?>">File a destination</a>
                    <?php else: ?>
                        <a class="btn btn--ghost-light" href="<?= e(url('?page=admin/dashboard')) ?>">Dashboard</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="section-head">
                <span class="script">Wonderful places for you</span>
                <h2>Browse by the kind of trip</h2>
            </div>

            <div class="cats mb-6">
                <?php foreach (GENRES as $genre): ?>
                    <a class="cat" href="<?= e(url('?page=browse&genre=' . urlencode($genre))) ?>">
                        <div class="cat__art">
                            <img src="<?= e(asset('images/genre/' . $genre . '.jpg')) ?>"
                                 alt="" loading="lazy" width="800" height="350">
                        </div>
                        <span class="cat__name"><?= e($genre) ?></span>
                        <span class="cat__count">
                            <?= (int) $genreCounts[$genre] ?>
                            <?= $genreCounts[$genre] === 1 ? 'place' : 'places' ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="page-head">
                <div>
                    <span class="script">Fresh from the field</span>
                    <h2 class="m-0">Most recently published</h2>
                </div>
                <a class="btn btn--ghost" href="<?= e(url('?page=browse')) ?>">
                    See all <?= (int) $stats['approved'] ?>
                </a>
            </div>

            <?php if ($latest): ?>
                <div class="grid">
                    <?php foreach ($latest as $index => $card): ?>
                        <?php
                        $cardIndex = $index;
                        $cardSaved = in_array((int) $card['id'], $savedIds, true);
                        require APP_ROOT . '/app/Views/layouts/dispatch_card.php';
                        ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty">
                    <h3>The archive is empty</h3>
                    <p>
                        No destination has been published yet. Once a scout files one and an editor
                        approves it, it will appear here.
                    </p>
                    <?php if ($user['role'] === 'scout'): ?>
                        <a class="btn btn--primary" href="<?= e(url('?page=scout/request_form')) ?>">
                            File the first one
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>


