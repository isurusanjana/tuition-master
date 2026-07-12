<?php
class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // In CLI (e.g. PHPUnit, scripts) headers_sent() is always true and a real
            // session cannot be started; $_SESSION still works fine as plain array storage
            // for the lifetime of the process, which is all tests need.
            @session_name(SESSION_NAME);
            @session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax',
            ]);
            if (!isset($_SESSION) || !is_array($_SESSION)) {
                $_SESSION = [];
            }
        }
    }

    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    // public static function flash(string $key, string $message = null)
    // {
    //     if ($message !== null) {
    //         $_SESSION['_flash'][$key] = $message;
    //         return null;
    //     }
    //     $msg = $_SESSION['_flash'][$key] ?? null;
    //     unset($_SESSION['_flash'][$key]);
    //     return $msg;
    // }

    public static function flash(string $key, ?string $message = null)
    {
        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return null;
        }
        $msg = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $msg;
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }
    }

    public static function csrfToken(): string
    {
        if (!isset($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function verifyCsrf(?string $token): bool
    {
        return $token !== null && isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
    }
}
