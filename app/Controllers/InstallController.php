<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;

final class InstallController
{
    public function index(): void
    {
        if (is_file(base_path('.env')) && $this->alreadyInstalled()) {
            redirect(app_url('admin/login'));
        }
        view('install/index', [
            'title' => 'نصب کافه آرک',
            'error' => flash('error'),
            'success' => flash('success'),
        ], 'layouts/install');
    }

    public function run(): void
    {
        require_csrf();

        $host = trim((string) input('db_host', 'localhost'));
        $port = trim((string) input('db_port', '3306'));
        $name = trim((string) input('db_name', 'coffeearc'));
        $user = trim((string) input('db_user', 'root'));
        $pass = (string) input('db_pass', '');
        $appUrl = rtrim(trim((string) input('app_url', '')), '/');
        $adminUser = trim((string) input('admin_user', 'admin'));
        $adminPass = (string) input('admin_pass', '');
        $cafeName = trim((string) input('cafe_name', 'کافه آرک'));

        if ($adminPass === '' || strlen($adminPass) < 4) {
            flash('error', 'رمز ادمین حداقل ۴ کاراکتر باشد.');
            redirect(app_url('install'));
        }

        try {
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$name}`");

            $schema = file_get_contents(base_path('sql/schema.sql'));
            $pdo->exec($schema);
            $seed = file_get_contents(base_path('sql/seed.sql'));
            $pdo->exec($seed);
            \App\Services\MenuSeeder::run($pdo);

            $this->writeEnv([
                'APP_NAME' => $cafeName,
                'APP_URL' => $appUrl,
                'APP_DEBUG' => 'false',
                'DB_HOST' => $host,
                'DB_PORT' => $port,
                'DB_NAME' => $name,
                'DB_USER' => $user,
                'DB_PASS' => $pass,
                'ZARINPAL_MERCHANT' => 'TEST',
                'ZARINPAL_SANDBOX' => 'true',
            ]);

            load_env(base_path('.env'));

            // Reconnect with db name via env
            $pdo = new PDO(
                "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
                $user,
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // Ensure unique QR tokens
            $tables = $pdo->query('SELECT id FROM cafe_tables')->fetchAll(PDO::FETCH_COLUMN);
            $upd = $pdo->prepare('UPDATE cafe_tables SET qr_token = ? WHERE id = ?');
            foreach ($tables as $tid) {
                $upd->execute([bin2hex(random_bytes(12)), $tid]);
            }

            $hash = password_hash($adminPass, PASSWORD_DEFAULT);
            $pdo->prepare('INSERT INTO admins (username, password_hash, display_name) VALUES (?, ?, ?)')
                ->execute([$adminUser, $hash, 'مدیر سیستم']);

            $set = $pdo->prepare(
                'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
            );
            $sysLang = (string) input('system_lang', 'fa');
            if (!in_array($sysLang, ['fa', 'en'], true)) {
                $sysLang = 'fa';
            }

            $set->execute(['cafe_name', $cafeName]);
            $set->execute(['zarinpal_merchant', 'TEST']);
            $set->execute(['zarinpal_sandbox', '1']);
            $set->execute(['system_lang', $sysLang]);
            $set->execute(['default_lang', $sysLang]);
            $set->execute(['installed', '1']);

            if (!is_dir(base_path('storage'))) {
                mkdir(base_path('storage'), 0755, true);
            }
            file_put_contents(base_path('storage/installed.lock'), date('c'));

            flash('success', 'نصب با موفقیت انجام شد. وارد شوید.');
            redirect(app_url('admin/login'));
        } catch (\Throwable $e) {
            flash('error', 'خطا در نصب: ' . $e->getMessage());
            redirect(app_url('install'));
        }
    }

    private function alreadyInstalled(): bool
    {
        if (is_file(base_path('storage/installed.lock'))) {
            return true;
        }
        try {
            return \App\Config\App::isInstalled();
        } catch (\Throwable) {
            return false;
        }
    }

    private function writeEnv(array $data): void
    {
        $lines = [];
        foreach ($data as $k => $v) {
            $lines[] = $k . '=' . $v;
        }
        file_put_contents(base_path('.env'), implode(PHP_EOL, $lines) . PHP_EOL);
    }
}
