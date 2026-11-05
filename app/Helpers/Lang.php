<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Setting;

final class Lang
{
    private static string $locale = 'fa';
    private static array $lines = [];

    public static function boot(): void
    {
        $lang = 'fa';
        try {
            if (is_file(base_path('.env'))) {
                $fromDb = Setting::get('system_lang', Setting::get('default_lang', 'fa'));
                if (is_string($fromDb) && $fromDb !== '') {
                    $lang = $fromDb;
                }
            }
        } catch (\Throwable) {
            $lang = 'fa';
        }

        if (!in_array($lang, ['fa', 'en'], true)) {
            $lang = 'fa';
        }

        self::$locale = $lang;
        $file = base_path('lang/' . self::$locale . '.php');
        self::$lines = is_file($file) ? (require $file) : [];
    }

    public static function locale(): string
    {
        return self::$locale;
    }

    public static function isRtl(): bool
    {
        return self::$locale === 'fa';
    }

    public static function get(string $key, ?string $default = null): string
    {
        return (string) (self::$lines[$key] ?? $default ?? $key);
    }

    public static function pick(?string $fa, ?string $en): string
    {
        if (self::$locale === 'en') {
            return trim((string) ($en ?: $fa)) ?: (string) $fa;
        }
        return trim((string) ($fa ?: $en)) ?: (string) $en;
    }
}
