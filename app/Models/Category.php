<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;

final class Category
{
    public static function all(bool $onlyActive = false): array
    {
        $sql = 'SELECT * FROM categories';
        if ($onlyActive) {
            $sql .= ' WHERE is_active = 1';
        }
        $sql .= ' ORDER BY sort_order ASC, id ASC';
        return Database::connection()->query($sql)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO categories (name, name_en, image_path, sort_order, is_active) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['name'],
            $data['name_en'] ?? null,
            $data['image_path'] ?? null,
            (int) ($data['sort_order'] ?? 0),
            (int) ($data['is_active'] ?? 1),
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $sql = 'UPDATE categories SET name = ?, name_en = ?, sort_order = ?, is_active = ?';
        $params = [
            $data['name'],
            $data['name_en'] ?? null,
            (int) ($data['sort_order'] ?? 0),
            (int) ($data['is_active'] ?? 1),
        ];
        if (array_key_exists('image_path', $data) && $data['image_path'] !== null) {
            $sql .= ', image_path = ?';
            $params[] = $data['image_path'];
        }
        $sql .= ' WHERE id = ?';
        $params[] = $id;
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
    }

    public static function reorder(array $orderedIds): void
    {
        $stmt = Database::connection()->prepare('UPDATE categories SET sort_order = ? WHERE id = ?');
        foreach (array_values($orderedIds) as $i => $id) {
            $stmt->execute([$i + 1, (int) $id]);
        }
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$id]);
    }
}
