<?php

declare(strict_types=1);

/**
 * End-to-end smoke tests against running server + DB.
 * Usage: php scripts/smoke_test.php
 */

$base = 'http://127.0.0.1:8080';
$failed = 0;

function req(string $method, string $url, array $opts = []): array
{
    $ch = curl_init($url);
    $headers = $opts['headers'] ?? [];
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_COOKIEJAR => sys_get_temp_dir() . '/ca_cookies.txt',
        CURLOPT_COOKIEFILE => sys_get_temp_dir() . '/ca_cookies.txt',
    ]);
    if (!empty($opts['post'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $opts['post']);
    }
    if ($headers) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    $raw = curl_exec($ch);
    if ($raw === false) {
        return ['code' => 0, 'body' => curl_error($ch), 'headers' => ''];
    }
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $hs = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return [
        'code' => $code,
        'headers' => substr($raw, 0, $hs),
        'body' => substr($raw, $hs),
    ];
}

function assert_true(bool $cond, string $msg): void
{
    global $failed;
    if ($cond) {
        echo "[OK] {$msg}\n";
    } else {
        echo "[FAIL] {$msg}\n";
        $failed++;
    }
}

@unlink(sys_get_temp_dir() . '/ca_cookies.txt');

// Menu page
$r = req('GET', $base . '/m');
assert_true($r['code'] === 200, 'GET /m returns 200');
assert_true(str_contains($r['body'], 'صبحانه') || str_contains($r['body'], 'آرک') || str_contains($r['body'], 'Arc'), 'Menu shows real categories/brand');

// Product API — first product
require_once dirname(__DIR__) . '/app/bootstrap.php';
$firstId = (int) App\Config\Database::connection()->query('SELECT id FROM products WHERE deleted_at IS NULL ORDER BY id LIMIT 1')->fetchColumn();
$r = req('GET', $base . '/api/product/' . $firstId);
assert_true($r['code'] === 200, 'GET product API 200');
$data = json_decode($r['body'], true);
assert_true(($data['ok'] ?? false) === true, 'Product API ok');
assert_true(!empty($data['product']['name']), 'Product has name');
assert_true(array_key_exists('has_price', $data['product']), 'has_price flag present');

// Admin login page
$r = req('GET', $base . '/admin/login');
assert_true($r['code'] === 200, 'Admin login page');
preg_match('/name="_csrf" value="([^"]+)"/', $r['body'], $m);
$csrf = $m[1] ?? '';
assert_true($csrf !== '', 'CSRF token present');

// Login
$r = req('POST', $base . '/admin/login', [
    'post' => http_build_query([
        '_csrf' => $csrf,
        'username' => 'admin',
        'password' => 'admin123',
    ]),
]);
assert_true(in_array($r['code'], [302, 303], true) || str_contains($r['headers'], 'Location:'), 'Admin login redirects');

$r = req('GET', $base . '/admin');
assert_true($r['code'] === 200, 'Admin orders page');
assert_true(str_contains($r['body'], 'سفارش'), 'Orders page content');

$r = req('GET', $base . '/admin/menu');
assert_true($r['code'] === 200, 'Admin menu page');

$r = req('GET', $base . '/admin/tables');
assert_true($r['code'] === 200, 'Admin tables page');

$r = req('GET', $base . '/admin/pos');
assert_true($r['code'] === 200, 'Admin POS page');

$r = req('GET', $base . '/admin/settings');
assert_true($r['code'] === 200, 'Admin settings page');
assert_true(str_contains($r['body'], 'feature_online_ordering') || str_contains($r['body'], 'ثبت سفارش آنلاین'), 'Feature toggles visible');

// Place cafe order via checkout
$r = req('GET', $base . '/checkout');
assert_true($r['code'] === 200, 'Checkout page');
preg_match('/name="_csrf" value="([^"]+)"/', $r['body'], $m2);
$csrf2 = $m2[1] ?? $csrf;

$tableId = (int) App\Config\Database::connection()->query('SELECT id FROM cafe_tables ORDER BY id LIMIT 1')->fetchColumn();
// Set a temporary price so payment/order totals work in smoke tests
App\Config\Database::connection()->exec('UPDATE products SET base_price = 100000 WHERE id = ' . (int) $firstId);

$r = req('POST', $base . '/checkout', [
    'headers' => ['Accept: application/json'],
    'post' => [
        '_csrf' => $csrf2,
        'cart' => json_encode([
            ['product_id' => $firstId, 'quantity' => 2, 'addon_ids' => [], 'size_id' => null],
        ]),
        'table_id' => $tableId,
        'pay_method' => 'cafe',
        'customer_name' => 'تست',
        'customer_phone' => '09120000000',
        'notes' => 'smoke test',
    ],
]);
$order = json_decode($r['body'], true);
assert_true(($order['ok'] ?? false) === true, 'Place cafe order ok: ' . ($order['error'] ?? $r['body']));
assert_true(!empty($order['order_code']), 'Order code returned');

if (!empty($order['order_code'])) {
    $r = req('GET', $base . '/order/' . $order['order_code']);
    assert_true($r['code'] === 200, 'Order result page');
    assert_true(str_contains($r['body'], $order['order_code']), 'Result shows code');
}

// Online payment mock flow (wait past rate-limit)
sleep(6);
$r = req('GET', $base . '/checkout');
preg_match('/name="_csrf" value="([^"]+)"/', $r['body'], $m3);
$csrf3 = $m3[1] ?? $csrf2;
$r = req('POST', $base . '/checkout', [
    'headers' => ['Accept: application/json'],
    'post' => [
        '_csrf' => $csrf3,
        'cart' => json_encode([
            ['product_id' => $firstId, 'quantity' => 1, 'size_id' => null, 'addon_ids' => []],
        ]),
        'table_id' => $tableId,
        'pay_method' => 'online',
        'customer_name' => 'آنلاین',
        'customer_phone' => '09121111111',
    ],
]);
$pay = json_decode($r['body'], true);
assert_true(($pay['ok'] ?? false) === true, 'Online order request ok: ' . ($pay['error'] ?? ''));
assert_true(isset($pay['redirect']) && str_contains($pay['redirect'], 'pay/mock'), 'Redirects to mock gateway');

if (!empty($pay['redirect'])) {
    $r = req('GET', $pay['redirect']);
    assert_true($r['code'] === 200, 'Mock pay page');
    preg_match('/name="_csrf" value="([^"]+)"/', $r['body'], $m4);
    preg_match('/name="Authority" value="([^"]+)"/', $r['body'], $ma);
    $r = req('POST', $base . '/pay/mock', [
        'post' => [
            '_csrf' => $m4[1] ?? '',
            'Authority' => $ma[1] ?? '',
            'result' => 'ok',
        ],
    ]);
    assert_true(in_array($r['code'], [302, 303], true) || str_contains($r['headers'], 'Location:'), 'Mock pay redirects to callback');
    if (preg_match('/Location:\s*(.+)/i', $r['headers'], $loc)) {
        $cb = trim($loc[1]);
        if (!str_starts_with($cb, 'http')) {
            $cb = $base . '/' . ltrim($cb, '/');
        }
        // follow redirects manually a couple times
        for ($i = 0; $i < 3; $i++) {
            $r = req('GET', $cb);
            if (preg_match('/Location:\s*(.+)/i', $r['headers'], $loc2)) {
                $cb = trim($loc2[1]);
                if (!str_starts_with($cb, 'http')) {
                    $cb = $base . '/' . ltrim($cb, '/');
                }
                continue;
            }
            break;
        }
        assert_true($r['code'] === 200 && (str_contains($r['body'], 'پرداخت موفق') || str_contains($r['body'], 'پرداخت')), 'Payment success page');
    }
}

// POS order
$r = req('GET', $base . '/admin/pos');
preg_match('/data-csrf="([^"]+)"/', $r['body'], $mc);
$r = req('POST', $base . '/admin/pos', [
    'headers' => ['Accept: application/json'],
    'post' => [
        '_csrf' => $mc[1] ?? $csrf,
        'cart' => json_encode([['product_id' => $firstId, 'quantity' => 1, 'addon_ids' => []]]),
        'table_id' => $tableId,
        'pay_method' => 'cafe',
        'mark_paid' => '1',
        'customer_phone' => '09123334444',
    ],
]);
$pos = json_decode($r['body'], true);
assert_true(($pos['ok'] ?? false) === true, 'POS order ok: ' . ($pos['error'] ?? $r['body']));

// Restore null prices for demo menu
App\Config\Database::connection()->exec('UPDATE products SET base_price = NULL');

// System language is admin-controlled (no public switcher)
$r = req('GET', $base . '/m');
assert_true($r['code'] === 200 && !str_contains($r['body'], 'lang-switch') && !str_contains($r['body'], '?lang='), 'No public language switcher');

// Instant checkout path when phone+online disabled
App\Models\Setting::set('customer_phone', '0');
App\Models\Setting::set('online_payment', '0');
App\Models\Setting::set('pay_at_cafe', '1');
App\Config\Database::connection()->exec('UPDATE products SET base_price = 50000 WHERE id = ' . (int) $firstId);
sleep(3);
$r = req('POST', $base . '/checkout', [
    'headers' => ['Accept: application/json'],
    'post' => [
        '_csrf' => $csrf,
        'instant' => '1',
        'pay_method' => 'cafe',
        'table_id' => $tableId,
        'cart' => json_encode([['product_id' => $firstId, 'quantity' => 1, 'addon_ids' => []]]),
    ],
]);
// May need fresh csrf from a page
if (!($json = json_decode($r['body'], true)) || empty($json['ok'])) {
    $r = req('GET', $base . '/m');
    preg_match('/data-csrf="([^"]+)"/', $r['body'], $mc2);
    $r = req('POST', $base . '/checkout', [
        'headers' => ['Accept: application/json'],
        'post' => [
            '_csrf' => $mc2[1] ?? $csrf,
            'instant' => '1',
            'pay_method' => 'cafe',
            'table_id' => $tableId,
            'cart' => json_encode([['product_id' => $firstId, 'quantity' => 1, 'addon_ids' => []]]),
        ],
    ]);
    $json = json_decode($r['body'], true);
}
assert_true(($json['ok'] ?? false) === true, 'Instant cafe order ok: ' . ($json['error'] ?? $r['body']));
App\Config\Database::connection()->exec('UPDATE products SET base_price = NULL');
App\Models\Setting::set('customer_phone', '1');
App\Models\Setting::set('online_payment', '1');

echo "\nDone. Failures: {$failed}\n";
exit($failed > 0 ? 1 : 0);
