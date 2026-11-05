<?php

declare(strict_types=1);

namespace App\Config;

final class App
{
    public static function boot(): void
    {
        load_env(base_path('.env'));

        date_default_timezone_set('Asia/Tehran');
        mb_internal_encoding('UTF-8');

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax',
            ]);
        }

        if (env('APP_DEBUG', 'false') === 'true') {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
            ini_set('display_errors', '0');
        }

        \App\Helpers\Lang::boot();
    }

    public static function isInstalled(): bool
    {
        if (!is_file(base_path('.env'))) {
            return false;
        }
        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1');
            $stmt->execute(['installed']);
            $row = $stmt->fetch();
            return $row && $row['setting_value'] === '1';
        } catch (\Throwable) {
            return false;
        }
    }
}
