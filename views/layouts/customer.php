<?php
use App\Helpers\Lang;
use App\Models\Setting;
$dir = Lang::isRtl() ? 'rtl' : 'ltr';
$lang = Lang::locale();
$cafeFa = Setting::get('cafe_name', 'کافه آرک');
$cafeEn = Setting::get('cafe_name_en', 'Coffee Arc');
$needPhone = Setting::enabled('customer_phone');
$phoneRequired = Setting::enabled('customer_phone_required');
$onlinePay = Setting::enabled('online_payment');
$payCafe = Setting::enabled('pay_at_cafe');
$tableId = (int) ($_SESSION['selected_table_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>" dir="<?= e($dir) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? lang_pick($cafeFa, $cafeEn)) ?></title>
  <link rel="stylesheet" href="<?= e(asset('css/fonts.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body
  class="lang-<?= e($lang) ?>"
  data-ordering="<?= !empty($orderingEnabled) ? '1' : '0' ?>"
  data-customization="<?= !empty($customization) ? '1' : '0' ?>"
  data-api-base="<?= e(rtrim(app_url(''), '/')) ?>"
  data-checkout-url="<?= e(app_url('checkout')) ?>"
  data-menu-url="<?= e(app_url('m')) ?>"
  data-currency="<?= e($lang === 'en' ? 'Toman' : 'تومان') ?>"
  data-i18n-add="<?= e(__('add_to_order')) ?>"
  data-i18n-size="<?= e(__('size')) ?>"
  data-i18n-addons="<?= e(__('addons')) ?>"
  data-i18n-close="<?= e(__('close')) ?>"
  data-need-phone="<?= $needPhone ? '1' : '0' ?>"
  data-phone-required="<?= $phoneRequired ? '1' : '0' ?>"
  data-online-pay="<?= $onlinePay ? '1' : '0' ?>"
  data-pay-cafe="<?= $payCafe ? '1' : '0' ?>"
  data-table-id="<?= $tableId ?>"
  data-table-required="<?= Setting::enabled('table_required') ? '1' : '0' ?>"
  data-csrf="<?= e(csrf_token()) ?>"
  data-place-url="<?= e(app_url('checkout')) ?>"
>
<?= $content ?>
</body>
</html>
