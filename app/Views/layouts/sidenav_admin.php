<?php
// TravelVista - admin side navigation. Expects $user (the signed-in admin).
$navUserCounts    = User::counts();
$navRequestCounts = PostRequest::counts();
?>
<aside class="sidenav">
    <div class="sidenav__head">
        <div class="sidenav__role">Admin</div>
        <div class="sidenav__name"><?= e($user['name']) ?></div>
    </div>

    <nav class="sidenav__list" aria-label="Admin">
        <a class="sidenav__link <?= nav_active('?page=admin/dashboard') ? 'is-active' : '' ?>"
           href="<?= e(url('?page=admin/dashboard')) ?>">Overview</a>

        <a class="sidenav__link <?= nav_active('?page=admin/users') ? 'is-active' : '' ?>"
           href="<?= e(url('?page=admin/users')) ?>">
            Users
            <?php if ($navUserCounts['unverified'] > 0): ?>
                <span class="sidenav__badge sidenav__badge--alert"><?= (int) $navUserCounts['unverified'] ?></span>
            <?php endif; ?>
        </a>

        <a class="sidenav__link <?= nav_active('?page=admin/requests') || nav_active('?page=admin/review') ? 'is-active' : '' ?>"
           href="<?= e(url('?page=admin/requests')) ?>">
            Post requests
            <?php if ($navRequestCounts['pending'] > 0): ?>
                <span class="sidenav__badge sidenav__badge--alert"><?= (int) $navRequestCounts['pending'] ?></span>
            <?php endif; ?>
        </a>

        <a class="sidenav__link <?= nav_active('?page=admin/posts') || nav_active('?page=admin/post_edit') ? 'is-active' : '' ?>"
           href="<?= e(url('?page=admin/posts')) ?>">Posts</a>

        <a class="sidenav__link <?= nav_active('?page=admin/comments') ? 'is-active' : '' ?>"
           href="<?= e(url('?page=admin/comments')) ?>">Comments</a>

        <a class="sidenav__link <?= nav_active('?page=admin/resets') ? 'is-active' : '' ?>"
           href="<?= e(url('?page=admin/resets')) ?>">
            Password Resets
            <?php 
            $pendingResets = Database::value("SELECT COUNT(*) FROM reset_requests WHERE status = 'pending'");
            if ($pendingResets > 0): 
            ?>
                <span class="sidenav__badge sidenav__badge--alert"><?= (int) $pendingResets ?></span>
            <?php endif; ?>
        </a>

        <a class="sidenav__link" href="<?= e(url('?page=browse')) ?>">Browse the archive</a>
    </nav>
</aside>


