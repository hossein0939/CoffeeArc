<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

final class MenuSeeder
{
    public static function run(PDO $pdo): void
    {
        $local = base_path('sql/menu_data.local.php');
        $menuFile = is_file($local) ? $local : base_path('sql/menu_data.php');
        $menu = require $menuFile;
        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        $pdo->exec('DELETE FROM product_addons');
        $pdo->exec('DELETE FROM product_sizes');
        $pdo->exec('DELETE FROM order_items');
        $pdo->exec('DELETE FROM orders');
        $pdo->exec('DELETE FROM products');
        $pdo->exec('DELETE FROM categories');
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

        $catStmt = $pdo->prepare(
            'INSERT INTO categories (name, name_en, image_path, sort_order, is_active) VALUES (?, ?, ?, ?, 1)'
        );
        $prodStmt = $pdo->prepare(
            'INSERT INTO products (category_id, name, name_en, description, description_en, base_price, image_path, is_available, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, NULL, 1, ?)'
        );

        foreach ($menu as $ci => $cat) {
            $catStmt->execute([
                $cat['name'],
                $cat['name_en'],
                $cat['image'] ?? null,
                $ci + 1,
            ]);
            $catId = (int) $pdo->lastInsertId();
            foreach ($cat['items'] as $pi => $item) {
                $price = array_key_exists(4, $item) ? $item[4] : null;
                if ($price !== null && (int) $price <= 0) {
                    $price = null;
                }
                $prodStmt->execute([
                    $catId,
                    $item[0],
                    $item[1],
                    $item[2] ?? null,
                    $item[3] ?? null,
                    $price,
                    $pi + 1,
                ]);
            }
        }
    }
}
