<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;

final class CafeTable
{
    public static function all(): array
    {
        return Database::connection()
            ->query('SELECT * FROM cafe_tables ORDER BY CAST(table_number AS UNSIGNED), table_number')
            ->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM cafe_tables WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByNumber(string $number): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM cafe_tables WHERE table_number = ?');
        $stmt->execute([$number]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByToken(string $token): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM cafe_tables WHERE qr_token = ?');
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $token = $data['qr_token'] ?? generate_token(12);
        $stmt = Database::connection()->prepare(
            'INSERT INTO cafe_tables (table_number, seats, status, qr_token) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['table_number'],
            (int) ($data['seats'] ?? 2),
            $data['status'] ?? 'free',
            $token,
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE cafe_tables SET table_number = ?, seats = ?, status = ? WHERE id = ?'
        );
        $stmt->execute([
            $data['table_number'],
            (int) ($data['seats'] ?? 2),
            $data['status'] ?? 'free',
            $id,
        ]);
    }

    public static function setStatus(int $id, string $status): void
    {
        $occupiedAt = $status === 'occupied' ? date('Y-m-d H:i:s') : null;
        $stmt = Database::connection()->prepare(
            'UPDATE cafe_tables SET status = ?, occupied_at = ? WHERE id = ?'
        );
        $stmt->execute([$status, $occupiedAt, $id]);
    }

    public static function free(int $id): void
    {
        self::setStatus($id, 'free');
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM cafe_tables WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function menuUrl(array $table): string
    {
        return app_url('m?t=' . urlencode($table['table_number']) . '&token=' . urlencode($table['qr_token']));
    }
}
