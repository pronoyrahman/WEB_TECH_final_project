<div class="auth">

    <section class="auth__pitch">
        <span class="eyebrow eyebrow--light">Joining TravelVista</span>
        <h2>Pick the job you came to do.</h2>
        <p>
            Everyone reads the archive. What changes is what else you can do with it, and you
            can only hold one role at a time.
        </p>

        <ul class="auth__points">
            <li>
                <b>Traveller</b>
                <span>Search the archive, keep a wishlist, estimate trip costs, leave notes.</span>
            </li>
            <li>
                <b>Scout</b>
                <span>File dispatches about places you know and ask to amend them later.</span>
            </li>
            <li>
                <b>Admin</b>
                <span>Approve accounts, publish dispatches, and moderate the comments.</span>
            </li>
            <li>
                <b>One more thing</b>
                <span>New accounts start unverified. An admin approves them before the archive opens.</span>
            </li>
        </ul>
    </section>

    <section class="auth__form">
        <div class="auth__form-inner">
            <?php require APP_ROOT . '/app/Views/layouts/flash.php'; ?>

            <h1>Create an account</h1>
            <p>It takes a minute. Approval usually takes a little longer.</p>

            <form class="form" method="post"
                  action="<?= e(url('?page=register')) ?>" data-validate>
                <?= csrf_field() ?>

                <div class="field">
                    <label class="field__label" for="name">Your name <span class="req">*</span></label>
                    <input class="input <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                           type="text" id="name" name="name"
                           value="<?= e(isset($old['name']) ? $old['name'] : '') ?>"
                           autocomplete="name" autofocus
                           data-rules="required" data-min="2" data-max="100" data-label="Your name">
                    <p class="field__error" data-error-for="name"><?= e(isset($errors['name']) ? $errors['name'] : '') ?></p>
                </div>

                <div class="field">
                    <label class="field__label" for="email">Email address <span class="req">*</span></label>
                    <input class="input <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                           type="email" id="email" name="email"
                           value="<?= e(isset($old['email']) ? $old['email'] : '') ?>"
                           autocomplete="email"
                           data-rules="required email" data-label="Email address" data-check-email>
                    <p class="field__error" data-error-for="email"><?= e(isset($errors['email']) ? $errors['email'] : '') ?></p>
                </div>

                <div class="field">
                    <label class="field__label" for="password">Password <span class="req">*</span></label>
                    <input class="input <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                           type="password" id="password" name="password"
                           autocomplete="new-password"
                           data-rules="required min:8" data-label="Password">
                    <p class="field__hint">Make it at least 8 characters.</p>
                    <p class="field__error" data-error-for="password"><?= e(isset($errors['password']) ? $errors['password'] : '') ?></p>
                </div>

                <fieldset class="field pt-2">
                    <legend class="field__label mb-3">What are you here to do? <span class="req">*</span></legend>

                    <div class="radios">
                        <label class="radio <?= $role === 'user' ? 'is-selected' : '' ?>">
                            <input class="sr-only" type="radio" name="role" value="user"
                                   <?= $role === 'user' ? 'checked' : '' ?>>
                            <span class="radio__label">I want to travel</span>
                            <span class="radio__desc">Search the archive and keep a wishlist.</span>
                        </label>

                        <label class="radio <?= $role === 'scout' ? 'is-selected' : '' ?>">
                            <input class="sr-only" type="radio" name="role" value="scout"
                                   <?= $role === 'scout' ? 'checked' : '' ?>>
                            <span class="radio__label">I am a scout</span>
                            <span class="radio__desc">File new dispatches for the editors.</span>
                        </label>

                        <label class="radio <?= $role === 'admin' ? 'is-selected' : '' ?>">
                            <input class="sr-only" type="radio" name="role" value="admin"
                                   <?= $role === 'admin' ? 'checked' : '' ?>>
                            <span class="radio__label">I am an editor</span>
                            <span class="radio__desc">Review accounts and publish drafts.</span>
                        </label>
                    </div>
                    <p class="field__error" data-error-for="role"><?= e(isset($errors['role']) ? $errors['role'] : '') ?></p>
                </fieldset>

                <button class="btn btn--primary btn--block btn--lg" type="submit">Create account</button>
            </form>

            <p class="auth__alt">
                Already have one?
                <a href="<?= e(url('?page=login')) ?>">Sign in instead</a>
            </p>
        </div>
    </section>
</div>


