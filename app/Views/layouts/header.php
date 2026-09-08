<?php
// TravelVista - page head and top bar. Views set $pageTitle (and optionally
// $pageDescription) before requiring this. The nav is built from the visitor's
// role and verification state.

$title      = isset($pageTitle) ? $pageTitle : APP_NAME;
$user       = current_user();
$role       = user_role();
$verified   = $user ? (bool) $user['is_verified'] : false;

$savedCount = 0;
if ($user && $role === 'user') {
    $savedCount = Wishlist::count((int) $user['id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> &mdash; <?= e(APP_NAME) ?></title>
    
    <link rel="icon" type="image/png" href="<?= e(asset('images/brand/mark-32.png')) ?>" sizes="32x32">
    <link rel="apple-touch-icon" href="<?= e(asset('images/brand/mark-180.png')) ?>">
    
    <?php if (isset($pageDescription)): ?>
        <meta name="description" content="<?= e($pageDescription) ?>">
    <?php endif; ?>

    <meta name="base-url" content="<?= e(BASE_URL) ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:opsz,wght@14..32,400..600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?= e(asset('css/global.css')) ?>">
</head>
<body class="page-<?= e(str_replace(' ', '-', strtolower($title))) ?>">

<a class="skip-link" href="#main">Skip to content</a>

<header class="topbar" id="topbar">
    <div class="topbar__inner wrap">
        
        <a class="brand" href="<?= e(url('?page=home')) ?>" aria-label="<?= e(APP_NAME) ?> home">
            <img class="brand__logo" src="<?= e(asset('images/brand/logo.png')) ?>" alt="<?= e(APP_NAME) ?>">
        </a>

        <!-- Mobile nav trigger -->
        <button class="topbar__burger" id="navToggle" type="button" aria-expanded="false" aria-controls="topbarNav" aria-label="Toggle navigation">
            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>

        <nav class="topbar__nav" id="topbarNav" aria-label="Main">
            <a class="topbar__link <?= nav_active('?page=home') ? 'is-active' : '' ?>"
               href="<?= e(url('?page=home')) ?>">Home</a>

            <?php if ($verified): ?>
                <a class="topbar__link <?= nav_active('?page=browse') || nav_active('?page=post') ? 'is-active' : '' ?>"
                   href="<?= e(url('?page=browse')) ?>">Browse</a>
            <?php endif; ?>

            <?php if ($role === 'user' && $verified): ?>
                <a class="topbar__link <?= nav_active('?page=wishlist') ? 'is-active' : '' ?>"
                   href="<?= e(url('?page=wishlist')) ?>">
                    Wishlist<span class="topbar__count" id="navWishlistCount"
                                  <?= $savedCount ? '' : 'hidden' ?>><?= (int) $savedCount ?></span>
                </a>
            <?php endif; ?>

            <?php if ($role === 'scout' && $verified): ?>
                <a class="topbar__link <?= str_starts_with(isset($_GET['page']) ? $_GET['page'] : '', 'scout/') ? 'is-active' : '' ?>"
                   href="<?= e(url('?page=scout/dashboard')) ?>">Scout desk</a>
            <?php endif; ?>

            <?php if ($role === 'admin' && $verified): ?>
                <a class="topbar__link <?= str_starts_with(isset($_GET['page']) ? $_GET['page'] : '', 'admin/') ? 'is-active' : '' ?>"
                   href="<?= e(url('?page=admin/dashboard')) ?>">Admin</a>
            <?php endif; ?>
            
            <div class="topbar__actions">
                <?php if ($user === null): ?>
                    <a class="btn btn--ghost btn--sm" href="<?= e(url('?page=login')) ?>">Sign in</a>
                    <a class="btn btn--primary btn--sm" href="<?= e(url('?page=register')) ?>">Create account</a>
                <?php else: ?>
                    <div class="usermenu" id="userMenu">
                        <button class="usermenu__trigger" type="button" id="userMenuBtn"
                                aria-expanded="false" aria-haspopup="true">
                            <?php $avatar = avatar_url($user['profile_picture']); ?>
                            <?php if ($avatar): ?>
                                <img class="avatar" src="<?= e($avatar) ?>" alt="">
                            <?php else: ?>
                                <span class="avatar" aria-hidden="true"><?= e(initial($user['name'])) ?></span>
                            <?php endif; ?>
                            <span><?= e(strtok($user['name'], ' ')) ?></span>
                            <span class="usermenu__caret" aria-hidden="true">&#9662;</span>
                        </button>

                        <div class="usermenu__panel" role="menu">
                            <div class="usermenu__head">
                                <div class="usermenu__name"><?= e($user['name']) ?></div>
                                <div class="usermenu__mail"><?= e($user['email']) ?></div>
                                <div class="mt-2">
                                    <span class="tag tag--sea"><?= e($user['role']) ?></span>
                                    <?php if (!$verified): ?>
                                        <span class="tag tag--pending">Unverified</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <a class="usermenu__item" role="menuitem" href="<?= e(url('?page=profile')) ?>">Profile</a>

                            <?php if ($verified && $role !== 'user'): ?>
                                <a class="usermenu__item" role="menuitem" href="<?= e(url(home_for_role($role))) ?>">
                                    <?= $role === 'admin' ? 'Admin dashboard' : 'Scout desk' ?>
                                </a>
                            <?php endif; ?>

                            <?php if ($verified && $role === 'user'): ?>
                                <a class="usermenu__item" role="menuitem" href="<?= e(url('?page=wishlist')) ?>">
                                    Wishlist <span class="sidenav__badge"><?= (int) $savedCount ?></span>
                                </a>
                            <?php endif; ?>

                            <form method="post" action="<?= e(url('?page=logout')) ?>">
                                <?= csrf_field() ?>
                                <button class="usermenu__item usermenu__item--danger" type="submit" role="menuitem">
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </nav>

    </div>
</header>

<main id="main">
