<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$host = 'localhost';
$port = '3306';
$name = 'coffeearc';
$user = 'root';
$pass = '';
$appUrl = 'http://127.0.0.1:8080';
$adminUser = 'admin';
$adminPass = 'admin123';
$cafeName = 'کافه آرک';

$pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
$pdo->exec("DROP DATABASE IF EXISTS `{$name}`");
$pdo->exec("CREATE DATABASE `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `{$name}`");
$pdo->exec(file_get_contents(dirname(__DIR__) . '/sql/schema.sql'));
$pdo->exec(file_get_contents(dirname(__DIR__) . '/sql/seed.sql'));
App\Services\MenuSeeder::run($pdo);

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
$set->execute(['cafe_name', $cafeName]);
$set->execute(['cafe_name_en', 'Coffee Arc']);
$set->execute(['zarinpal_merchant', 'TEST']);
$set->execute(['installed', '1']);

$env = <<<ENV
APP_NAME={$cafeName}
APP_URL={$appUrl}
APP_DEBUG=true
DB_HOST={$host}
DB_PORT={$port}
DB_NAME={$name}
DB_USER={$user}
DB_PASS={$pass}
ZARINPAL_MERCHANT=TEST
ZARINPAL_SANDBOX=true
ENV;
file_put_contents(dirname(__DIR__) . '/.env', $env . PHP_EOL);
file_put_contents(dirname(__DIR__) . '/storage/installed.lock', date('c'));

$count = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$cats = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
echo "Installed OK\nCategories: {$cats}\nProducts: {$count}\nAdmin: {$adminUser} / {$adminPass}\nURL: {$appUrl}\n";
