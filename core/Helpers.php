<?php
/**
 * Global helper functions.
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return APP_URL . '/' . ltrim($path, '/');
}

function route(string $name, array $params = []): string
{
    global $router;
    return $router instanceof Router ? $router->url($name, $params) : url('/');
}

function asset(string $path): string
{
    return APP_URL . '/assets/' . ltrim($path, '/');
}

function old(string $key, $default = '')
{
    $old = Session::get('_old', []);
    return e($old[$key] ?? $default);
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . Session::csrfToken() . '">';
}

function flash_message(string $key): string
{
    return Session::flash($key) ?? '';
}

function format_date(?string $date, string $format = 'd M Y'): string
{
    if (!$date) return '';
    return date($format, strtotime($date));
}

function setting(string $key, $default = null)
{
    static $cache = [];
    $centerId = Auth::centerId();
    $cacheKey = ($centerId ?? 'global') . ':' . $key;
    if (isset($cache[$cacheKey])) return $cache[$cacheKey];

    $row = Database::fetchOne(
        "SELECT setting_value FROM settings WHERE setting_key = :k AND tuition_center_id " . ($centerId ? "= :c" : "IS NULL"),
        $centerId ? ['k' => $key, 'c' => $centerId] : ['k' => $key]
    );
    if (!$row && $centerId) {
        // fallback to global default
        $row = Database::fetchOne("SELECT setting_value FROM settings WHERE setting_key = :k AND tuition_center_id IS NULL", ['k' => $key]);
    }
    return $cache[$cacheKey] = ($row['setting_value'] ?? $default);
}

function active_menu(string $route): string
{
    $current = $_SERVER['REQUEST_URI'] ?? '';
    $mod = explode('.', $route)[0] ?? '';
    return str_contains($current, "/$mod") ? 'active' : '';
}

function log_activity(string $action, string $moduleKey = '', string $description = ''): void
{
    Database::query(
        "INSERT INTO activity_logs (tuition_center_id, user_id, action, module_key, description, ip_address, created_at)
         VALUES (:c, :u, :a, :m, :d, :ip, NOW())",
        [
            'c' => Auth::centerId(),
            'u' => Auth::id(),
            'a' => $action,
            'm' => $moduleKey,
            'd' => $description,
            'ip' => Request::ip(),
        ]
    );
}

function redirect_with_error(string $path, string $message): void
{
    Session::flash('error', $message);
    Response::redirect($path);
}

function redirect_with_success(string $path, string $message): void
{
    Session::flash('success', $message);
    Response::redirect($path);
}
