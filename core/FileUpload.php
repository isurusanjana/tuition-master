<?php
/**
 * FileUpload - validated file upload helper for lesson documents/PDF/video and logos.
 */
class FileUpload
{
    public static function upload(array $file, string $subDir, array $allowedExt, int $maxMb = null): ?string
    {
        $maxMb = $maxMb ?? UPLOAD_MAX_MB;
        if ($file['error'] !== UPLOAD_ERR_OK) return null;
        if ($file['size'] > $maxMb * 1024 * 1024) return null;

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true)) return null;

        $dir = UPLOAD_PATH . '/' . trim($subDir, '/');
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $target = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $target)) return null;

        return trim($subDir, '/') . '/' . $filename;
    }

    public static function delete(?string $relativePath): void
    {
        if (!$relativePath) return;
        $full = UPLOAD_PATH . '/' . ltrim($relativePath, '/');
        if (is_file($full)) unlink($full);
    }
}
