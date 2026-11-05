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
        fputcsv($out, ['phone', 'name', 'orders_count', 'last_order_at', 'total_spent']);
        foreach ($rows as $row) {
            fputcsv($out, [
                $row['phone'],
                $row['name'] ?? '',
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
        $sql = "SELECT
                    customer_phone AS phone,
                    MAX(NULLIF(customer_name, '')) AS name,
                    COUNT(*) AS orders_count,
                    MAX(created_at) AS last_order_at,
                    COALESCE(SUM(CASE WHEN status != 'cancelled' THEN total ELSE 0 END), 0) AS total_spent
                FROM orders
                WHERE customer_phone IS NOT NULL AND TRIM(customer_phone) != ''
                GROUP BY customer_phone
                ORDER BY last_order_at DESC";
        return Database::connection()->query($sql)->fetchAll();
    }
}
