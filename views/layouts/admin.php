<?php
use App\Helpers\Auth;
use App\Helpers\Lang;
use App\Models\Setting;
$admin = $admin ?? Auth::user();
$features = Setting::all();
$path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$dir = Lang::isRtl() ? 'rtl' : 'ltr';
?>
<!DOCTYPE html>
<html lang="<?= e(Lang::locale()) ?>" dir="<?= e($dir) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'پنل') ?> | <?= e(lang_pick(Setting::get('cafe_name', 'کافه آرک'), Setting::get('cafe_name_en', 'Coffee Arc'))) ?></title>
  <link rel="stylesheet" href="<?= e(asset('css/fonts.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body>
<div class="admin-shell">
  <aside class="admin-side">
    <div class="logo"><span>Coffee</span> Arc</div>
    <div class="muted" style="font-size:.85rem"><?= e($admin['display_name'] ?? 'مدیر') ?></div>
    <nav class="admin-nav">
      <a href="<?= e(app_url('admin')) ?>" class="<?= (bool)preg_match('#/admin/?(\?|$)#', $path) || str_contains($path, 'admin/orders') ? 'active' : '' ?>">سفارش‌ها</a>
      <?php if (($features['staff_pos'] ?? '0') === '1'): ?>
        <a href="<?= e(app_url('admin/pos')) ?>" class="<?= str_contains($path, 'admin/pos') ? 'active' : '' ?>">ثبت سفارش</a>
      <?php endif; ?>
      <a href="<?= e(app_url('admin/menu')) ?>" class="<?= str_contains($path, 'admin/menu') ? 'active' : '' ?>">منو</a>
      <?php if (($features['table_board'] ?? '0') === '1'): ?>
        <a href="<?= e(app_url('admin/tables')) ?>" class="<?= str_contains($path, 'admin/tables') ? 'active' : '' ?>">میزها</a>
      <?php endif; ?>
      <?php if (($features['discounts'] ?? '0') === '1'): ?>
        <a href="<?= e(app_url('admin/discounts')) ?>" class="<?= str_contains($path, 'admin/discounts') ? 'active' : '' ?>">تخفیف‌ها</a>
      <?php endif; ?>
      <a href="<?= e(app_url('admin/customers')) ?>" class="<?= str_contains($path, 'admin/customers') ? 'active' : '' ?>">موبایل‌ها</a>
      <a href="<?= e(app_url('admin/settings')) ?>" class="<?= str_contains($path, 'admin/settings') ? 'active' : '' ?>">تنظیمات</a>
      <a href="<?= e(app_url('m')) ?>" target="_blank">مشاهده منو</a>
      <a href="<?= e(app_url('admin/logout')) ?>">خروج</a>
    </nav>
  </aside>
  <main class="admin-main">
    <div class="admin-top">
      <h1 style="margin:0;font-size:1.35rem"><?= e($title ?? '') ?></h1>
    </div>
    <?= $content ?>
  </main>
</div>
</body>
</html>
