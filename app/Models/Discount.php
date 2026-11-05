<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;

final class Discount
{
    public static function all(): array
    {
        return Database::connection()->query('SELECT * FROM discounts ORDER BY id DESC')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM discounts WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByCode(string $code): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM discounts WHERE code = ? AND is_active = 1 LIMIT 1'
        );
        $stmt->execute([strtoupper(trim($code))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function calcAmount(array $discount, int $subtotal): int
    {
        if ($discount['type'] === 'percent') {
            return (int) floor($subtotal * ((int) $discount['value']) / 100);
        }
        return min((int) $discount['value'], $subtotal);
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO discounts (code, type, value, is_active) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            strtoupper(trim($data['code'])),
            $data['type'],
            (int) $data['value'],
            (int) ($data['is_active'] ?? 1),
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE discounts SET code = ?, type = ?, value = ?, is_active = ? WHERE id = ?'
        );
        $stmt->execute([
            strtoupper(trim($data['code'])),
            $data['type'],
            (int) $data['value'],
            (int) ($data['is_active'] ?? 1),
            $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM discounts WHERE id = ?');
        $stmt->execute([$id]);
    }
}
