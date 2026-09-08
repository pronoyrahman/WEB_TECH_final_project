<?php
// TravelVista - bootstrap. Required by index.php before any routing. Starts the
// session, loads config, helpers, the database class and models, and restores a
// remember-me login from its cookie.

declare(strict_types=1);

// errors: log everything, show nothing to the visitor
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// configuration and helpers
require_once __DIR__ . '/config.php';
require_once APP_ROOT . '/core/Helpers.php';
require_once APP_ROOT . '/core/Upload.php';

// session
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'path'     => BASE_URL === '' ? '/' : BASE_URL . '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

// database and models
require_once APP_ROOT . '/core/Database.php';
require_once APP_ROOT . '/app/Models/User.php';
require_once APP_ROOT . '/app/Models/Post.php';
require_once APP_ROOT . '/app/Models/PostRequest.php';
require_once APP_ROOT . '/app/Models/Wishlist.php';
require_once APP_ROOT . '/app/Models/Comment.php';
require_once APP_ROOT . '/app/Models/CostEstimate.php';
require_once APP_ROOT . '/app/Models/ResetRequest.php';

// authentication
require_once APP_ROOT . '/core/Auth.php';
auth_restore_from_cookie();

// baseline response headers
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: same-origin');
}
