<div class="wrap browse" id="browsePage" data-can-save="<?= is_traveller() ? '1' : '0' ?>">

    <!-- the filter rail -->
    <form class="filters" id="filterRail" method="get" action="<?= e(url('?page=browse')) ?>">
        <div class="filters__head">
            <span class="eyebrow m-0">Filters</span>
            <button class="btn btn--link" type="button" id="clearFilters"
                    data-clear-filters <?= ($filters['q'] || $filters['country'] || $activeGenres || $activeCosts) ? '' : 'hidden' ?>>
                Clear all
            </button>
        </div>

        <div class="filters__group">
            <span class="eyebrow">Country</span>
            <select class="select" name="country" id="countryFilter">
                <option value="">Anywhere</option>
                <?php foreach ($countries as $row): ?>
                    <option value="<?= e($row['country']) ?>"
                            <?= $filters['country'] === $row['country'] ? 'selected' : '' ?>>
                        <?= e($row['country']) ?> (<?= (int) $row['total'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filters__group">
            <span class="eyebrow">Genre</span>
            <?php foreach (GENRES as $genre): ?>
                <label class="choice">
                    <input type="checkbox" name="genre" value="<?= e($genre) ?>"
                           <?= in_array($genre, $activeGenres, true) ? 'checked' : '' ?>>
                    <span><?= e(ucfirst($genre)) ?></span>
                    <span class="count"><?= (int) $genreCounts[$genre] ?></span>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="filters__group">
            <span class="eyebrow">Cost level</span>
            <?php foreach (['low', 'medium', 'high'] as $level): ?>
                <label class="choice">
                    <input type="checkbox" name="cost" value="<?= e($level) ?>"
                           <?= in_array($level, $activeCosts, true) ? 'checked' : '' ?>>
                    <span><?= e(ucfirst($level)) ?></span>
                    <span class="count"><?= e(money((float) cost_base($level))) ?></span>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="filters__group">
            <!-- no-JS fallback; browse.js takes over otherwise -->
            <noscript>
                <button class="btn btn--primary btn--block btn--sm" type="submit">Apply filters</button>
            </noscript>
            <p class="field__hint m-0">
                Base costs are what a week for one traveller usually runs to.
            </p>
        </div>
    </form>

    <!-- results -->
    <div class="shell__main">
        <?php require APP_ROOT . '/app/Views/layouts/flash.php'; ?>

        <div class="page-head">
            <div>
                <span class="eyebrow">The archive</span>
                <h1>Browse dispatches</h1>
            </div>
        </div>

        <div class="searchbar">
            <div class="searchbar__field">
                <svg class="searchbar__icon" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>
                </svg>
                <label class="sr-only" for="searchInput">Search the archive</label>
                <input class="input" type="search" id="searchInput" name="q"
                       value="<?= e($filters['q']) ?>"
                       placeholder="Search by place, country or story"
                       autocomplete="off" form="filterRail">
                <span class="searchbar__spin" aria-hidden="true"></span>
            </div>

            <label class="sr-only" for="sortSelect">Sort</label>
            <select class="select w-md" id="sortSelect" name="sort" form="filterRail">
                <?php
                $sorts = [
                    'newest'    => 'Newest first',
                    'oldest'    => 'Oldest first',
                    'title'     => 'By title',
                    'country'   => 'By country',
                    'cost-low'  => 'Cheapest first',
                    'cost-high' => 'Dearest first',
                ];
                ?>
                <?php foreach ($sorts as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $filters['sort'] === $value ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="results__head">
            <span class="results__count" id="resultCount">
                <b><?= count($posts) ?></b> <?= count($posts) === 1 ? 'dispatch' : 'dispatches' ?>
            </span>
            <div class="chips" id="activeChips" hidden></div>
        </div>

        <div class="grid" id="results">
            <?php if ($posts): ?>
                <?php foreach ($posts as $index => $card): ?>
                    <?php
                    $cardIndex = $index;
                    $cardSaved = in_array((int) $card['id'], $savedIds, true);
                    require APP_ROOT . '/app/Views/layouts/dispatch_card.php';
                    ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty">
                    <h3>No matches</h3>
                    <p>
                        <?php if ($filters['q'] !== ''): ?>
                            Nothing in the archive matches &ldquo;<?= e($filters['q']) ?>&rdquo;.
                        <?php else: ?>
                            No dispatch matches those filters yet.
                        <?php endif; ?>
                        Try a broader search, or clear the filters to see everything.
                    </p>
                    <button class="btn btn--ghost" type="button" data-clear-filters>Clear filters</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


