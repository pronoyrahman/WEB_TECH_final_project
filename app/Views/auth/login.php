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

            <h1>Sign in</h1>
            <p>Use the address you registered with.</p>

            <form class="form" method="post"
                  action="<?= e(url('?page=login')) ?>" data-validate>
                <?= csrf_field() ?>

                <div class="field">
                    <label class="field__label" for="email">Email address</label>
                    <input class="input <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                           type="email" id="email" name="email"
                           value="<?= e($email) ?>"
                           autocomplete="email" autofocus
                           data-rules="required email" data-label="Email address">
                    <p class="field__error" data-error-for="email"><?= e(isset($errors['email']) ? $errors['email'] : '') ?></p>
                </div>

                <div class="field">
                    <label class="field__label" for="password">Password</label>
                    <input class="input <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                           type="password" id="password" name="password"
                           autocomplete="current-password"
                           data-rules="required" data-label="Password">
                    <p class="field__error" data-error-for="password"><?= e(isset($errors['password']) ? $errors['password'] : '') ?></p>
                </div>

                <div class="auth__forgot">
                    <a href="<?= e(url('?page=forgot-password')) ?>" class="text-sm">Forgot your password?</a>
                </div>

                <label class="choice">
                    <input type="checkbox" name="remember" value="1"
                           <?= !empty($old['remember']) ? 'checked' : '' ?>>
                    <span>Keep me signed in on this device for 30 days</span>
                </label>

                <button class="btn btn--primary btn--block btn--lg" type="submit">Sign in</button>
            </form>

            <p class="auth__alt">
                No account yet?
                <a href="<?= e(url('?page=register')) ?>">Create one</a>
            </p>
        </div>
    </section>
</div>


