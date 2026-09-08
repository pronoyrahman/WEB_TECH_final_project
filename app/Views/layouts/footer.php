</main>

<footer class="footer">
    <div class="wrap">
        <div class="footer__grid">
            <div>
                <!-- white wordmark on the dark footer ground -->
                <div class="brand brand--footer mb-3">
                    <img src="<?= e(asset('images/brand/logo_white.png')) ?>"
                         alt="<?= e(APP_NAME) ?>" loading="lazy">
                </div>
                <p class="footer__blurb">
                    Explore, discover, experience. Every destination here was visited, written
                    up and filed by a scout, then checked by an editor before it was published.
                    Nothing is sponsored and nothing is generated.
                </p>
            </div>

            <div>
                <h4>Explore</h4>
                <div class="footer__links">
                    <a href="<?= e(url('?page=home')) ?>">Home</a>
                    <?php if ($verified): ?>
                        <a href="<?= e(url('?page=browse')) ?>">Browse destinations</a>
                    <?php else: ?>
                        <a href="<?= e(url('?page=register')) ?>">Create an account</a>
                    <?php endif; ?>
                    <?php if (is_traveller()): ?>
                        <a href="<?= e(url('?page=wishlist')) ?>">Your wishlist</a>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <h4>Account</h4>
                <div class="footer__links">
                    <?php if (is_logged_in()): ?>
                        <a href="<?= e(url('?page=profile')) ?>">Profile</a>
                        <?php if (is_role('scout')): ?>
                            <a href="<?= e(url('?page=scout/requests')) ?>">Your requests</a>
                        <?php endif; ?>
                        <?php if (is_role('admin')): ?>
                            <a href="<?= e(url('?page=admin/dashboard')) ?>">Dashboard</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="<?= e(url('?page=login')) ?>">Sign in</a>
                        <a href="<?= e(url('?page=register')) ?>">Create account</a>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <h4>Roles</h4>
                <div class="footer__links">
                    <span>Traveller &mdash; browse and save</span>
                    <span>Scout &mdash; file dispatches</span>
                    <span>Admin &mdash; review and publish</span>
                </div>
            </div>
        </div>

        <div class="footer__base footer__base--center">
            <span>All Rights Reserved by <?= e(APP_NAME) ?> &mdash; Web Technologies, Group 11</span>
        </div>
    </div>
</footer>

<script src="<?= e(asset('js/app.js')) ?>"></script>
<?php $scripts = isset($pageScripts) ? $pageScripts : []; ?>
<?php foreach ($scripts as $script): ?>
    <script src="<?= e(asset('js/' . $script)) ?>"></script>
<?php endforeach; ?>
</body>
</html>
