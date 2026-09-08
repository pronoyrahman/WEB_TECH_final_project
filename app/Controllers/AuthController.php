<?php

class AuthController extends Controller
{
    public function login()
    {
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            // GET: show the form
            if (is_logged_in()) {
                redirect(home_for_role(isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'user'));
            }

            $errors = take_errors();
            $old    = take_old();

            // registration redirects here with ?email=
            $email = isset($old['email']) ? $old['email'] : (isset($_GET['email']) ? (string) $_GET['email'] : '');
            
            $this->render('auth/login', [
                'pageTitle' => 'Sign in',
                'pageScripts' => ['validation.js'],
                'errors' => $errors,
                'email' => $email
            ]);
            return;
        }

        csrf_guard();

        $email    = strtolower(field($_POST, 'email'));
        $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
        $remember = isset($_POST['remember']);

        $input = ['email' => $email, 'remember' => $remember ? '1' : ''];

        // validate
        $errors = [];

        if ($email === '') {
            $errors['email'] = 'Enter your email address.';
        } elseif (!valid_email($email)) {
            $errors['email'] = 'That does not look like an email address.';
        }

        if ($password === '') {
            $errors['password'] = 'Enter your password.';
        }

        if ($errors) {
            back_with_errors($errors, $input, '?page=login');
        }

        // check credentials
        $user = User::attempt($email, $password);

        if ($user === null) {
            back_with_errors(
                ['password' => 'That email and password do not match an account.'],
                $input,
                '?page=login'
            );
        }

        // sign in
        auth_login($user);

        if ($remember) {
            auth_remember((int) $user['id']);
        }

        if (!$user['is_verified']) {
            redirect('?page=pending');
        }

        flash('success', 'Signed in. Welcome back, ' . strtok($user['name'], ' ') . '.');

        // return to the originally requested page
        $intended = isset($_SESSION['intended']) ? $_SESSION['intended'] : null;
        unset($_SESSION['intended']);

        if (is_string($intended) && str_starts_with($intended, '/') && !str_contains($intended, '//')) {
            header('Location: ' . $intended);
            exit;
        }

        redirect(home_for_role($user['role']));
    }

    public function register()
    {
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            // GET: show the form
            if (is_logged_in()) {
                redirect(home_for_role(isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'user'));
            }

            $errors = take_errors();
            $old    = take_old();

            $this->render('auth/register', [
                'pageTitle' => 'Create an account',
                'pageScripts' => ['validation.js'],
                'errors' => $errors,
                'old' => $old
            ]);
            return;
        }

        csrf_guard();

        // read submission
        $name     = field($_POST, 'name');
        $email    = strtolower(field($_POST, 'email'));
        $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
        $confirm  = isset($_POST['password_confirm']) ? (string) $_POST['password_confirm'] : '';
        $role     = field($_POST, 'role');

        $input  = ['name' => $name, 'email' => $email, 'role' => $role];
        $errors = [];

        // validate
        if ($name === '') {
            $errors['name'] = 'Enter your name.';
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = 'That name is too long.';
        }

        if ($email === '') {
            $errors['email'] = 'Enter an email address.';
        } elseif (!valid_email($email)) {
            $errors['email'] = 'That does not look like an email address.';
        } elseif (User::emailTaken($email)) {
            $errors['email'] = 'An account already uses this address.';
        }

        if ($password === '') {
            $errors['password'] = 'Enter a password.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Use at least 8 characters.';
        }

        if ($password !== $confirm) {
            $errors['password_confirm'] = 'The passwords do not match.';
        }

        if (!in_array($role, ['scout', 'user'], true)) {
            $errors['role'] = 'Choose an account type.';
        }

        if ($errors) {
            back_with_errors($errors, $input, '?page=register');
        }

        // write
        User::create($name, $email, $password, $role, 0);

        flash('success', 'Your account was created! An admin will review it shortly.');
        redirect('?page=login&email=' . urlencode($email));
    }

    public function logout()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_guard();
            auth_logout();
            redirect('?page=login');
        }
        redirect('?page=home');
    }

    public function pending()
    {
        require_login();
        
        $this->render('auth/pending', [
            'pageTitle' => 'Pending Verification'
        ]);
    }

    public function forgotPassword()
    {
        if (is_logged_in()) {
            redirect(home_for_role(isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'user'));
        }

        $this->render('auth/forgot_password', [
            'pageTitle' => 'Forgot Password',
            'errors'    => take_errors(),
            'old'       => take_old()
        ]);
    }

    public function submitResetRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('?page=forgot-password');
        }

        csrf_guard();

        $email = strtolower(field($_POST, 'email'));
        if ($email === '') {
            back_with_errors(['email' => 'Please enter your email address.'], [], '?page=forgot-password');
        }

        $user = User::findByEmail($email);
        if ($user) {
            // don't stack duplicate pending requests
            if (!ResetRequest::hasPending((int) $user['id'])) {
                ResetRequest::create((int) $user['id']);
            }
        }

        // same message whether or not the account exists, to prevent email enumeration
        flash('success', 'If an account exists for that email, a request has been sent to the admin.');
        redirect('?page=login');
    }
}
