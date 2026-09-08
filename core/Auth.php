<?php
// TravelVista - session, remember-me and role guards.

// Current visitor

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function user_role(): ?string
{
    return isset($_SESSION['user_role']) ? (string) $_SESSION['user_role'] : null;
}

function current_user(): ?array
{
    $id = user_id();
    if (!$id) {
        return null;
    }
    require_once APP_ROOT . '/app/Models/User.php';
    return User::findById($id);
}

// Guards

function require_login(): array
{
    if (!is_logged_in()) {
        // remember the requested page so login can return to it
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($method === 'GET' && !empty($_SERVER['REQUEST_URI'])) {
            $_SESSION['intended'] = (string) $_SERVER['REQUEST_URI'];
        }
        redirect('?page=login');
    }
    return current_user();
}

// Requires the given role. Unverified accounts are sent to the pending page,
// so access is not gated only by hidden nav links.
function require_role(string $role): array
{
    $user = require_login();

    if (user_role() !== $role) {
        http_response_code(403);
        exit('Forbidden: requires ' . e($role) . ' role.');
    }

    if ((int) $user['is_verified'] !== 1) {
        redirect('?page=pending');
    }

    return $user;
}

// Session lifecycle

function auth_login(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['name'];
}

function auth_logout(): void
{
    // also clear the DB token so a copied remember-me cookie stops working
    $id = user_id();
    if ($id) {
        User::setRememberToken($id, null);
    }

    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    setcookie('tv_remember', '', time() - 3600, '/');
    session_destroy();
}

function auth_remember(int $userId): void
{
    $token = bin2hex(random_bytes(32));
    $hash = hash('sha256', $token);

    Database::run("UPDATE users SET remember_token = ? WHERE id = ?", [$hash, $userId]);

    $cookieValue = $userId . ':' . $token;
    setcookie('tv_remember', $cookieValue, [
        'expires' => time() + (30 * 24 * 60 * 60),
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS'])
    ]);
}

function auth_restore_from_cookie(): void
{
    if (is_logged_in()) {
        return;
    }
    
    if (!isset($_COOKIE['tv_remember'])) {
        return;
    }
    
    $parts = explode(':', $_COOKIE['tv_remember'], 2);
    if (count($parts) !== 2) {
        return;
    }
    
    $userId = (int) $parts[0];
    $token = $parts[1];
    
    $user = Database::one("SELECT * FROM users WHERE id = ? LIMIT 1", [$userId]);
    
    if ($user && $user['remember_token'] === hash('sha256', $token)) {
        auth_login($user);
    }
}
