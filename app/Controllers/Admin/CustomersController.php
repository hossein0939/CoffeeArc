<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Config\Database;
use App\Helpers\Auth;

final class CustomersController
{
    public function index(): void
    {
        Auth::requireLogin();
        admin_view('admin/customers', [
            'title' => 'شماره موبایل‌ها',
            'rows' => self::phoneRows(),
            'admin' => Auth::user(),
        ]);
    }

    public function export(): void
    {
        Auth::requireLogin();
        $rows = self::phoneRows();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="customer-phones-' . date('Ymd-His') . '.csv"');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel
        $out = fopen('php://output', 'w');
        fputcsv($out, ['phone', 'orders_count', 'last_order_at', 'total_spent']);
        foreach ($rows as $row) {
            fputcsv($out, [
                $row['phone'],
                $row['orders_count'],
                $row['last_order_at'],
                $row['total_spent'],
            ]);
        }
        fclose($out);
        exit;
    }

    private static function phoneRows(): array
    {
        $sql = "SELECT customer_phone, status, total, created_at
                FROM orders
                WHERE customer_phone IS NOT NULL AND TRIM(customer_phone) != ''
                ORDER BY created_at DESC";
        $raw = Database::connection()->query($sql)->fetchAll();

        $grouped = [];
        foreach ($raw as $row) {
            $phone = normalize_phone((string) $row['customer_phone']);
            if (!$phone) {
                continue;
            }
            if (!isset($grouped[$phone])) {
                $grouped[$phone] = [
                    'phone' => $phone,
                    'orders_count' => 0,
                    'last_order_at' => $row['created_at'],
                    'total_spent' => 0,
                ];
            }
            $grouped[$phone]['orders_count']++;
            if ($row['status'] !== 'cancelled') {
                $grouped[$phone]['total_spent'] += (int) $row['total'];
            }
            if ($row['created_at'] > $grouped[$phone]['last_order_at']) {
                $grouped[$phone]['last_order_at'] = $row['created_at'];
            }
        }

        return array_values($grouped);
    }
}
