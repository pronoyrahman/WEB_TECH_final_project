<?php
// TravelVista - forgot password view. Handled by AuthController.
?>
<div class="auth">

    <section class="auth__pitch">
        <span class="eyebrow eyebrow--light">TravelVista</span>
        <h2>The archive is closed to the street.</h2>
        <p>
            Dispatches are written by scouts and checked by editors, so reading them means
            having an account. It also means your wishlist and your notes are waiting where
            you left them.
        </p>

        <ul class="auth__points">
            <li>
                <b>Wishlist</b>
                <span>Places you saved, with what a trip to each would probably cost.</span>
            </li>
            <li>
                <b>Search</b>
                <span>Filter the whole archive by country, genre and cost as you type.</span>
            </li>
            <li>
                <b>Notes</b>
                <span>What you told the next traveller, and what they told you.</span>
            </li>
        </ul>
    </section>

    <section class="auth__form">
        <div class="auth__form-inner">
            <?php require APP_ROOT . '/app/Views/layouts/flash.php'; ?>

            <h1>Reset your password</h1>
            <p>Enter your email address and we'll send a request to the administrators.</p>

            <form class="form" action="<?= e(url('?page=forgot-password-submit')) ?>" method="POST" data-validate>
                <?= csrf_field() ?>

                <div class="field">
                    <label class="field__label" for="email">Email address</label>
                    <input class="input <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                           type="email" id="email" name="email"
                           value="<?= e(isset($old['email']) ? $old['email'] : '') ?>"
                           autocomplete="email" autofocus
                           data-rules="required email" data-label="Email address">
                    <p class="field__error" data-error-for="email"><?= e(isset($errors['email']) ? $errors['email'] : '') ?></p>
                </div>

                <div class="field mt-4">
                    <button type="submit" class="btn btn--primary btn--block btn--lg">Request Reset</button>
                </div>
                
                <p class="auth__alt">
                    <a href="<?= e(url('?page=login')) ?>">Back to Sign in</a>
                </p>
            </form>
        </div>
    </section>
</div>
