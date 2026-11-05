<?php

declare(strict_types=1);

namespace App\Helpers;

final class Uploader
{
    public static function image(array $file, string $subdir = 'products'): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('خطا در آپلود فایل.');
        }
        if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
            throw new \RuntimeException('حداکثر حجم تصویر ۲ مگابایت است.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];
        if (!isset($allowed[$mime])) {
            throw new \RuntimeException('فرمت تصویر مجاز نیست.');
        }

        $dir = public_path('uploads/' . $subdir);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = $subdir . '/' . date('Ymd') . '_' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
        $dest = public_path('uploads/' . $name);
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new \RuntimeException('ذخیره تصویر ناموفق بود.');
        }
        return $name;
    }
}
