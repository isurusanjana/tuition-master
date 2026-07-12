<?php
class Response
{
    public static function redirect(string $path): void
    {
        $url = str_starts_with($path, 'http') ? $path : APP_URL . $path;
        header("Location: $url");
        exit;
    }

    public static function json($data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function back(string $fallback = '/'): void
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? (APP_URL . $fallback);
        header("Location: $ref");
        exit;
    }
}
