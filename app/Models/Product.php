<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;

final class Product
{
    public static function allActive(?int $categoryId = null): array
    {
        $sql = 'SELECT p.*, c.name AS category_name, c.name_en AS category_name_en, c.image_path AS category_image
                FROM products p
                JOIN categories c ON c.id = p.category_id
                WHERE p.deleted_at IS NULL AND p.is_available = 1 AND c.is_active = 1';
        $params = [];
        if ($categoryId) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }
        $sql .= ' ORDER BY c.sort_order, p.sort_order, p.id';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function allAdmin(?int $categoryId = null): array
    {
        $sql = 'SELECT p.*, c.name AS category_name, c.name_en AS category_name_en, c.image_path AS category_image
                FROM products p
                JOIN categories c ON c.id = p.category_id
                WHERE p.deleted_at IS NULL';
        $params = [];
        if ($categoryId) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }
        $sql .= ' ORDER BY c.sort_order, p.sort_order, p.id';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id, bool $includeDeleted = false): ?array
    {
        $sql = 'SELECT * FROM products WHERE id = ?';
        if (!$includeDeleted) {
            $sql .= ' AND deleted_at IS NULL';
        }
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function withOptions(int $id): ?array
    {
        $product = self::find($id);
        if (!$product) {
            return null;
        }
        $product['sizes'] = self::sizes($id);
        $product['addons'] = self::addons($id);
        return $product;
    }

    public static function sizes(int $productId, bool $onlyActive = true): array
    {
        $sql = 'SELECT * FROM product_sizes WHERE product_id = ?';
        if ($onlyActive) {
            $sql .= ' AND is_active = 1';
        }
        $sql .= ' ORDER BY sort_order, id';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public static function addons(int $productId, bool $onlyActive = true): array
    {
        $sql = 'SELECT * FROM product_addons WHERE product_id = ?';
        if ($onlyActive) {
            $sql .= ' AND is_active = 1';
        }
        $sql .= ' ORDER BY sort_order, id';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $price = self::normalizePrice($data['base_price'] ?? null);
        $stmt = Database::connection()->prepare(
            'INSERT INTO products (category_id, name, name_en, description, description_en, base_price, image_path, is_available, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            (int) $data['category_id'],
            $data['name'],
            $data['name_en'] ?? null,
            $data['description'] ?? null,
            $data['description_en'] ?? null,
            $price,
            $data['image_path'] ?? null,
            (int) ($data['is_available'] ?? 1),
            (int) ($data['sort_order'] ?? 0),
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $price = self::normalizePrice($data['base_price'] ?? null);
        $stmt = Database::connection()->prepare(
            'UPDATE products SET category_id = ?, name = ?, name_en = ?, description = ?, description_en = ?, base_price = ?,
             image_path = COALESCE(?, image_path), is_available = ?, sort_order = ? WHERE id = ?'
        );
        $stmt->execute([
            (int) $data['category_id'],
            $data['name'],
            $data['name_en'] ?? null,
            $data['description'] ?? null,
            $data['description_en'] ?? null,
            $price,
            $data['image_path'] ?? null,
            (int) ($data['is_available'] ?? 1),
            (int) ($data['sort_order'] ?? 0),
            $id,
        ]);
    }

    public static function reorder(array $orderedIds): void
    {
        $stmt = Database::connection()->prepare('UPDATE products SET sort_order = ? WHERE id = ?');
        foreach (array_values($orderedIds) as $i => $id) {
            $stmt->execute([$i + 1, (int) $id]);
        }
    }

    private static function normalizePrice(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        $n = (int) $value;
        return $n > 0 ? $n : null;
    }

    public static function softDelete(int $id): void
    {
        $stmt = Database::connection()->prepare('UPDATE products SET deleted_at = NOW(), is_available = 0 WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function setAvailability(int $id, bool $available): void
    {
        $stmt = Database::connection()->prepare('UPDATE products SET is_available = ? WHERE id = ?');
        $stmt->execute([$available ? 1 : 0, $id]);
    }

    public static function replaceSizes(int $productId, array $sizes): void
    {
        $pdo = Database::connection();
        $pdo->prepare('DELETE FROM product_sizes WHERE product_id = ?')->execute([$productId]);
        $stmt = $pdo->prepare(
            'INSERT INTO product_sizes (product_id, name, price_modifier, sort_order, is_active) VALUES (?, ?, ?, ?, 1)'
        );
        foreach ($sizes as $i => $size) {
            if (trim((string) ($size['name'] ?? '')) === '') {
                continue;
            }
            $stmt->execute([
                $productId,
                trim($size['name']),
                (int) ($size['price_modifier'] ?? 0),
                $i + 1,
            ]);
        }
    }

    public static function replaceAddons(int $productId, array $addons): void
    {
        $pdo = Database::connection();
        $pdo->prepare('DELETE FROM product_addons WHERE product_id = ?')->execute([$productId]);
        $stmt = $pdo->prepare(
            'INSERT INTO product_addons (product_id, name, price, sort_order, is_active) VALUES (?, ?, ?, ?, 1)'
        );
        foreach ($addons as $i => $addon) {
            if (trim((string) ($addon['name'] ?? '')) === '') {
                continue;
            }
            $stmt->execute([
                $productId,
                trim($addon['name']),
                (int) ($addon['price'] ?? 0),
                $i + 1,
            ]);
        }
    }
}
