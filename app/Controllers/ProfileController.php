<?php

class ProfileController extends Controller
{
    public function index()
    {
        $user = require_login();

        $errors = take_errors();
        $old    = take_old();

        $this->render('profile/index', [
            'pageTitle' => 'Your Profile',
            'pageScripts' => ['validation.js'],
            'errors' => $errors,
            'old' => $old,
            'user' => $user
        ]);
    }

    public function updateDetails()
    {
        $user = require_login();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('?page=profile');
        }
        
        csrf_guard();

        $name  = field($_POST, 'name');
        $email = strtolower(field($_POST, 'email'));

        $input  = ['name' => $name, 'email' => $email];
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
        } elseif ($email !== $user['email'] && User::emailTaken($email)) {
            $errors['email'] = 'An account already uses this address.';
        }

        // file upload
        $picture = isset($_FILES['profile_picture']) ? $_FILES['profile_picture'] : [];
        $imageResult = save_image($picture, 'avatars');

        if (!$imageResult['ok']) {
            $errors['profile_picture'] = $imageResult['error'];
        }

        if ($errors) {
            $_SESSION['profile_form_open'] = true;
            back_with_errors($errors, $input, '?page=profile');
        }

        // write
        $filename = $imageResult['filename'];

        if ($filename !== null && !empty($user['profile_picture'])) {
            delete_image($user['profile_picture'], 'avatars');
        }

        User::updateProfile((int) $user['id'], $name, $email);
        if ($filename !== null) {
            User::updatePicture((int) $user['id'], $filename);
        }

        flash('success', 'Profile updated.');
        redirect('?page=profile');
    }

    public function updatePassword()
    {
        $user = require_login();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('?page=profile');
        }
        
        csrf_guard();

        $current = isset($_POST['current_password']) ? (string) $_POST['current_password'] : '';
        $new     = isset($_POST['new_password']) ? (string) $_POST['new_password'] : '';
        $confirm = isset($_POST['new_password_confirm']) ? (string) $_POST['new_password_confirm'] : '';

        $errors = [];

        // validate
        if ($current === '') {
            $errors['current_password'] = 'Enter your current password.';
        } elseif (!password_verify($current, $user['password_hash'])) {
            $errors['current_password'] = 'That is not your current password.';
        }

        if (strlen($new) < 8) {
            $errors['new_password'] = 'Use at least 8 characters.';
        }

        if ($new !== $confirm) {
            $errors['new_password_confirm'] = 'The passwords do not match.';
        }

        if ($errors) {
            $_SESSION['password_form_open'] = true;
            back_with_errors($errors, [], '?page=profile');
        }

        // write
        User::updatePassword((int) $user['id'], $new);

        flash('success', 'Password updated.');
        redirect('?page=profile');
    }
}
