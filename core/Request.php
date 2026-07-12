<?php
class Request
{
    public static function input(string $key, $default = null)
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    public static function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    public static function only(array $keys): array
    {
        $out = [];
        foreach ($keys as $k) {
            if (isset($_POST[$k])) $out[$k] = is_string($_POST[$k]) ? trim($_POST[$k]) : $_POST[$k];
        }
        return $out;
    }

    public static function file(string $key): ?array
    {
        return (!empty($_FILES[$key]) && $_FILES[$key]['error'] !== UPLOAD_ERR_NO_FILE) ? $_FILES[$key] : null;
    }

    public static function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }

    public static function csrfCheck(): bool
    {
        return Session::verifyCsrf(self::input('_csrf'));
    }

    public static function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
