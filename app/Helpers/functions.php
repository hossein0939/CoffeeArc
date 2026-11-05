<?php

declare(strict_types=1);

function base_path(string $path = ''): string
{
    $root = dirname(__DIR__, 2);
    return $path === '' ? $root : $root . DIRECTORY_SEPARATOR . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
}

function public_path(string $path = ''): string
{
    return base_path('public' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
}

function view_path(string $path = ''): string
{
    return base_path('views' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function is_post(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function request_method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function input(string $key, mixed $default = null): mixed
{
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token = null): bool
{
    $token = $token ?? ($_POST['_csrf'] ?? '');
    return is_string($token) && isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
}

function require_csrf(): void
{
    if (!verify_csrf()) {
        http_response_code(419);
        exit('Invalid CSRF token');
    }
}

function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }
    $msg = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $msg;
}

function money(?int $amount): string
{
    if ($amount === null) {
        return '';
    }
    $suffix = \App\Helpers\Lang::locale() === 'en' ? ' Toman' : ' تومان';
    return number_format($amount) . $suffix;
}

function has_price(mixed $price): bool
{
    return $price !== null && $price !== '' && (int) $price > 0;
}

/** Normalize Iranian phone numbers for storage / unique listing. */
function normalize_phone(?string $phone): ?string
{
    if ($phone === null) {
        return null;
    }
    $digits = preg_replace('/\D+/', '', $phone) ?? '';
    if ($digits === '') {
        return null;
    }
    if (str_starts_with($digits, '0098')) {
        $digits = substr($digits, 4);
    } elseif (str_starts_with($digits, '98') && strlen($digits) >= 12) {
        $digits = substr($digits, 2);
    }
    if (strlen($digits) === 10 && str_starts_with($digits, '9')) {
        $digits = '0' . $digits;
    }
    return $digits !== '' ? $digits : null;
}

function __(string $key, ?string $default = null): string
{
    return \App\Helpers\Lang::get($key, $default);
}

function lang_pick(?string $fa, ?string $en): string
{
    return \App\Helpers\Lang::pick($fa, $en);
}

function product_image_url(array $product, ?array $category = null): string
{
    if (!empty($product['image_path'])) {
        return upload_url($product['image_path']);
    }
    $catImage = $category['image_path'] ?? $product['category_image'] ?? null;
    if ($catImage) {
        return upload_url((string) $catImage);
    }
    return upload_url(null);
}

function app_url(string $path = ''): string
{
    $base = rtrim(env('APP_URL', ''), '/');
    if ($base === '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        // If using public/index.php, base is dirname of script
        $dir = str_replace('\\', '/', dirname($script));
        if (str_ends_with($dir, '/public')) {
            $dir = substr($dir, 0, -7);
        }
        if ($dir === '/' || $dir === '\\' || $dir === '.') {
            $dir = '';
        }
        $base = $scheme . '://' . $host . $dir;
    }
    $path = '/' . ltrim($path, '/');
    if ($path === '/') {
        return $base ?: '/';
    }
    return $base . $path;
}

function asset(string $path): string
{
    return app_url('assets/' . ltrim($path, '/'));
}

function upload_url(?string $path): string
{
    if (!$path) {
        return asset('img/placeholder.svg');
    }
    return app_url('uploads/' . ltrim($path, '/'));
}

function view(string $name, array $data = [], ?string $layout = 'layouts/main'): void
{
    extract($data, EXTR_SKIP);
    $contentView = view_path($name . '.php');
    if (!is_file($contentView)) {
        http_response_code(500);
        echo 'View not found: ' . e($name);
        exit;
    }
    ob_start();
    require $contentView;
    $content = ob_get_clean();
    if ($layout) {
        require view_path($layout . '.php');
    } else {
        echo $content;
    }
}

function admin_view(string $name, array $data = []): void
{
    view($name, $data, 'layouts/admin');
}

function customer_view(string $name, array $data = []): void
{
    view($name, $data, 'layouts/customer');
}

function generate_order_code(): string
{
    return 'CA' . date('ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
}

function generate_token(int $bytes = 16): string
{
    return bin2hex(random_bytes($bytes));
}

function status_label(string $status): string
{
    return match ($status) {
        'new' => 'جدید',
        'preparing' => 'در حال آماده‌سازی',
        'ready' => 'آماده',
        'delivered' => 'تحویل شده',
        'cancelled' => 'لغو شده',
        'free' => 'آزاد',
        'occupied' => 'اشغال',
        'reserved' => 'رزرو',
        'unpaid' => 'پرداخت نشده',
        'pending' => 'در انتظار پرداخت',
        'paid' => 'پرداخت شده',
        'failed' => 'ناموفق',
        'online' => 'آنلاین',
        'cafe' => 'در کافه',
        default => $status,
    };
}

function status_class(string $status): string
{
    return match ($status) {
        'new', 'pending' => 'badge-warn',
        'preparing' => 'badge-info',
        'ready', 'paid', 'free' => 'badge-ok',
        'delivered' => 'badge-muted',
        'cancelled', 'failed', 'occupied' => 'badge-danger',
        'reserved' => 'badge-blue',
        default => 'badge-muted',
    };
}
