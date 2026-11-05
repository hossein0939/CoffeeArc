<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;

final class AdminUser
{
    public static function findByUsername(string $username): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM admins WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM admins WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function verify(string $username, string $password): ?array
    {
        $user = self::findByUsername($username);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }
        return $user;
    }

    public static function updatePassword(int $id, string $password): void
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = Database::connection()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
        $stmt->execute([$hash, $id]);
    }

    public static function create(string $username, string $password, string $displayName = 'مدیر سیستم'): int
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = Database::connection()->prepare(
            'INSERT INTO admins (username, password_hash, display_name) VALUES (?, ?, ?)'
        );
        $stmt->execute([$username, $hash, $displayName]);
        return (int) Database::connection()->lastInsertId();
    }
}
