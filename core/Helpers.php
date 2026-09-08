<?php
// TravelVista - helper functions used across controllers and views.

// Output escaping

// Escape a value for output inside HTML.
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Read a string field from an array such as $_POST or $_GET.
function field(array $input, string $key, string $default = ''): string
{
    return isset($input[$key]) && is_scalar($input[$key]) ? (string)$input[$key] : $default;
}

// Shorten text to $limit characters on a word boundary.
function excerpt(string $text, int $limit = 160): string
{
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags($text)));
    if (mb_strlen($text) <= $limit) {
        return $text;
    }
    $cut = mb_substr($text, 0, $limit);
    $space = mb_strrpos($cut, ' ');
    return rtrim($space ? mb_substr($cut, 0, $space) : $cut, ' ,.;:') . '...';
}

// URLs

// Build an absolute URL path from a project-relative path.
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

// Asset URL with a cache-busting mtime stamp.
function asset(string $path): string
{
    $publicPath = 'public/' . ltrim($path, '/');
    $file = APP_ROOT . '/' . $publicPath;
    $stamp = is_file($file) ? filemtime($file) : 1;
    return url($publicPath) . '?v=' . $stamp;
}

// Redirect and stop.
function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function nav_active(string $needle): bool
{
    $page = isset($_GET['page']) ? (string)$_GET['page'] : 'home';
    if ($needle === '?page=home' && $page === 'home') return true;
    return str_ends_with('?page=' . $page, $needle);
}

// Security

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_guard(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = isset($_POST['csrf_token']) ? (string)$_POST['csrf_token'] : '';
        if (!hash_equals(csrf_token(), $token)) {
            http_response_code(403);
            exit('CSRF token validation failed.');
        }
    }
}

// Formatting

function money($amount, string $currency = 'USD'): string
{
    $prefix = $currency === 'USD' ? '$' : $currency . ' ';
    return $prefix . number_format((float)$amount);
}

// Media

function post_cover(?string $image, string $genre): string
{
    if ($image && is_file(UPLOAD_DIR . '/posts/' . $image)) {
        return url('public/uploads/posts/' . rawurlencode($image));
    }
    $genre = in_array($genre, GENRES, true) ? $genre : 'city';
    return asset('images/genre/' . $genre . '.svg');
}

function avatar_url(?string $picture): ?string
{
    if ($picture && is_file(UPLOAD_DIR . '/avatars/' . $picture)) {
        return url('public/uploads/avatars/' . rawurlencode($picture));
    }
    return null;
}

// Validation and session helpers

function valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function flash(string $type, string $message): void
{
    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $flashes = isset($_SESSION['flash']) ? $_SESSION['flash'] : [];
    unset($_SESSION['flash']);
    return is_array($flashes) ? $flashes : [];
}

function back_with_errors(array $errors, array $input, string $fallback = ''): void
{
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $input;
    if (!empty($_SERVER['HTTP_REFERER'])) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        redirect($fallback);
    }
    exit;
}

function take_errors(): array
{
    $errors = isset($_SESSION['errors']) ? $_SESSION['errors'] : [];
    unset($_SESSION['errors']);
    return is_array($errors) ? $errors : [];
}

function take_old(): array
{
    $old = isset($_SESSION['old']) ? $_SESSION['old'] : [];
    unset($_SESSION['old']);
    return is_array($old) ? $old : [];
}

function home_for_role(string $role): string
{
    if ($role === 'admin') {
        return '?page=admin/dashboard';
    }
    if ($role === 'scout') {
        return '?page=scout/dashboard';
    }
    return '?page=home';
}

function initial(string $name): string
{
    return mb_strtoupper(mb_substr(trim($name), 0, 1));
}

function cost_base(string $level): float
{
    return isset(COST_BASE[$level]) ? (float) COST_BASE[$level] : 0.0;
}

function pretty_date(?string $dateStr, string $format = 'M j, Y'): string
{
    if (!$dateStr) return '';
    $dt = strtotime($dateStr);
    return $dt ? date($format, $dt) : '';
}

function time_ago(string $datetime): string
{
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    
    return date('M j, Y', $time);
}

function is_traveller(): bool
{
    return is_logged_in() && user_role() === 'user';
}

function meter_html(string $level): string
{
    $levels = ['low' => 1, 'medium' => 2, 'high' => 3];
    $active = isset($levels[$level]) ? $levels[$level] : 0;
    
    $html = '<span class="meter" aria-label="Cost: ' . e(ucfirst($level)) . '">';
    for ($i = 1; $i <= 3; $i++) {
        $class = $i <= $active ? 'meter__seg is-on' : 'meter__seg';
        $html .= '<span class="' . $class . '"></span>';
    }
    $html .= '</span>';
    return $html;
}

function file_number(int $id, string $prefix = 'FILE'): string
{
    return $prefix . ' ' . str_pad((string)$id, 3, '0', STR_PAD_LEFT);
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

// JSON API helpers

function json_error(string $message, int $status = 400, array $extra = []): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    $payload = array_merge(['error' => $message], $extra);
    echo json_encode($payload);
    exit;
}

function json_out(array $data = [], int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function api_require_method(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        json_error('Method Not Allowed', 405);
    }
}

// Same checks as require_role(), but responds with JSON.
function api_require_role(string $role = ''): array
{
    if (!is_logged_in()) {
        json_error('Unauthorized', 401);
    }

    $user = current_user();
    if ($user === null) {
        json_error('Unauthorized', 401);
    }

    if ($role !== '' && user_role() !== $role) {
        json_error('Forbidden', 403);
    }

    if ((int) $user['is_verified'] !== 1) {
        json_error('An admin has not verified your account yet.', 403);
    }

    return $user;
}

function json_input(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function api_csrf_guard(array $input): void
{
    $token = isset($input['csrf_token']) ? (string)$input['csrf_token'] : '';
    if (!hash_equals(csrf_token(), $token)) {
        json_error('CSRF token validation failed.', 403);
    }
}

function is_role(string $role): bool
{
    return is_logged_in() && user_role() === $role;
}

function require_verified(): void
{
    $user = require_login();
    if ((int) $user['is_verified'] !== 1) {
        redirect('?page=pending');
    }
}

// Shape a post row into the JSON structure app.js (TV.renderCard) expects.
function post_to_json(array $post, array $savedIds): array
{
    $costLabels = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'];
    $segments = ['low' => 1, 'medium' => 2, 'high' => 3];
    $cost = isset($post['cost_level']) ? $post['cost_level'] : 'medium';

    return [
        'id'          => (int) $post['id'],
        'saved'       => in_array((int) $post['id'], $savedIds, true),
        'url'         => url('?page=post&id=' . $post['id']),
        'cover'       => post_cover(isset($post['image']) ? $post['image'] : null, isset($post['genre']) ? $post['genre'] : 'city'),
        'genre'       => $post['genre'],
        'title'       => $post['title'],
        'snippet'     => isset($post['short_history']) ? $post['short_history'] : '',
        'country'     => $post['country'],
        'medium'      => isset($post['travel_medium_info']) ? $post['travel_medium_info'] : '',
        'segments'    => isset($segments[$cost]) ? $segments[$cost] : 2,
        'cost_label'  => isset($costLabels[$cost]) ? $costLabels[$cost] : 'Medium',
        'scout_name'  => isset($post['scout_name']) ? $post['scout_name'] : 'Unknown',
        'file_number' => 'FILE-' . str_pad((string) $post['id'], 4, '0', STR_PAD_LEFT)
    ];
}
