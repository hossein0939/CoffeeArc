<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

final class Order
{
    public static function create(array $order, array $items): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO orders (
                    order_code, table_id, source, status, pay_method, pay_status,
                    customer_name, customer_phone, notes, discount_code, discount_amount,
                    subtotal, total
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $order['order_code'],
                $order['table_id'] ?? null,
                $order['source'] ?? 'customer',
                $order['status'] ?? 'new',
                $order['pay_method'],
                $order['pay_status'] ?? 'unpaid',
                $order['customer_name'] ?? null,
                $order['customer_phone'] ?? null,
                $order['notes'] ?? null,
                $order['discount_code'] ?? null,
                (int) ($order['discount_amount'] ?? 0),
                (int) $order['subtotal'],
                (int) $order['total'],
            ]);
            $orderId = (int) $pdo->lastInsertId();

            $itemStmt = $pdo->prepare(
                'INSERT INTO order_items (
                    order_id, product_id, product_name, size_name, addons_json,
                    unit_price, quantity, line_total, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            foreach ($items as $item) {
                $itemStmt->execute([
                    $orderId,
                    $item['product_id'] ?? null,
                    $item['product_name'],
                    $item['size_name'] ?? null,
                    isset($item['addons']) ? json_encode($item['addons'], JSON_UNESCAPED_UNICODE) : null,
                    (int) $item['unit_price'],
                    (int) $item['quantity'],
                    (int) $item['line_total'],
                    $item['notes'] ?? null,
                ]);
            }

            if (!empty($order['table_id'])) {
                CafeTable::setStatus((int) $order['table_id'], 'occupied');
            }

            $pdo->commit();
            return $orderId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT o.*, t.table_number
             FROM orders o
             LEFT JOIN cafe_tables t ON t.id = o.table_id
             WHERE o.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByCode(string $code): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT o.*, t.table_number
             FROM orders o
             LEFT JOIN cafe_tables t ON t.id = o.table_id
             WHERE o.order_code = ?'
        );
        $stmt->execute([$code]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByAuthority(string $authority): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM orders WHERE zarinpal_authority = ? LIMIT 1');
        $stmt->execute([$authority]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function items(int $orderId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id');
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public static function list(?string $status = null, int $limit = 100): array
    {
        $sql = 'SELECT o.*, t.table_number
                FROM orders o
                LEFT JOIN cafe_tables t ON t.id = o.table_id';
        $params = [];
        if ($status && $status !== 'all') {
            $sql .= ' WHERE o.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY o.id DESC LIMIT ' . (int) $limit;
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function updateStatus(int $id, string $status): void
    {
        $stmt = Database::connection()->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);

        if (in_array($status, ['delivered', 'cancelled'], true)) {
            $order = self::find($id);
            if ($order && $order['table_id']) {
                $active = Database::connection()->prepare(
                    "SELECT COUNT(*) FROM orders
                     WHERE table_id = ? AND status IN ('new','preparing','ready') AND id != ?"
                );
                $active->execute([(int) $order['table_id'], $id]);
                if ((int) $active->fetchColumn() === 0) {
                    CafeTable::free((int) $order['table_id']);
                }
            }
        }
    }

    public static function markPaid(int $id, ?string $ref = null): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE orders SET pay_status = ?, zarinpal_ref = COALESCE(?, zarinpal_ref) WHERE id = ?'
        );
        $stmt->execute(['paid', $ref, $id]);
    }

    public static function setPaymentPending(int $id, string $authority): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE orders SET pay_status = ?, zarinpal_authority = ? WHERE id = ?'
        );
        $stmt->execute(['pending', $authority, $id]);
    }

    public static function markFailed(int $id): void
    {
        $stmt = Database::connection()->prepare('UPDATE orders SET pay_status = ? WHERE id = ?');
        $stmt->execute(['failed', $id]);
    }

    public static function stats(): array
    {
        $pdo = Database::connection();
        $today = $pdo->query(
            "SELECT COUNT(*) AS cnt, COALESCE(SUM(total),0) AS sum_total
             FROM orders
             WHERE DATE(created_at) = CURDATE() AND status != 'cancelled' AND pay_status IN ('paid','unpaid')"
        )->fetch();
        $week = $pdo->query(
            "SELECT COUNT(*) AS cnt, COALESCE(SUM(total),0) AS sum_total
             FROM orders
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
               AND status != 'cancelled'"
        )->fetch();
        $paidToday = $pdo->query(
            "SELECT COALESCE(SUM(total),0) FROM orders
             WHERE DATE(created_at) = CURDATE() AND pay_status = 'paid'"
        )->fetchColumn();

        return [
            'today_count' => (int) ($today['cnt'] ?? 0),
            'today_sales' => (int) ($today['sum_total'] ?? 0),
            'week_count' => (int) ($week['cnt'] ?? 0),
            'week_sales' => (int) ($week['sum_total'] ?? 0),
            'today_paid' => (int) $paidToday,
        ];
    }

    public static function recentOrdersCountByIp(string $ip, int $minutes = 2): int
    {
        // Soft rate-limit via session primarily; keep helper for future
        return 0;
    }
}
