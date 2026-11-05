<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

final class Setting
{
    private static ?array $cache = null;

    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $stmt = Database::connection()->query('SELECT setting_key, setting_value FROM settings');
        $rows = $stmt->fetchAll();
        $out = [];
        foreach ($rows as $row) {
            $out[$row['setting_key']] = $row['setting_value'];
        }
        self::$cache = $out;
        return $out;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $all = self::all();
        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    public static function set(string $key, string $value): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        $stmt->execute([$key, $value]);
        self::$cache = null;
    }

    public static function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            self::set((string) $key, (string) $value);
        }
    }

    public static function enabled(string $feature): bool
    {
        return self::get($feature, '0') === '1';
    }

    public static function featureKeys(): array
    {
        return [
            'online_ordering' => 'ثبت سفارش آنلاین مشتری',
            'online_payment' => 'پرداخت آنلاین زرین‌پال',
            'pay_at_cafe' => 'پرداخت در کافه',
            'table_required' => 'الزام انتخاب میز',
            'qr_per_table' => 'QR اختصاصی هر میز',
            'item_customization' => 'سایز و افزودنی محصول',
            'order_notes' => 'توضیحات سفارش',
            'customer_phone' => 'دریافت شماره تماس',
            'customer_phone_required' => 'اجباری بودن شماره تماس',
            'discounts' => 'کد تخفیف',
            'staff_pos' => 'ثبت سفارش مسئول (POS)',
            'table_board' => 'وضعیت میزها',
            'simple_stats' => 'آمار ساده فروش',
        ];
    }

    public static function clearCache(): void
    {
        self::$cache = null;
    }
}
