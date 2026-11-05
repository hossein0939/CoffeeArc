<?php if (!empty($flash_ok)): ?><div class="alert alert-ok"><?= e($flash_ok) ?></div><?php endif; ?>
<?php if (!empty($flash_err)): ?><div class="alert alert-error"><?= e($flash_err) ?></div><?php endif; ?>

<form method="post" action="<?= e(app_url('admin/settings')) ?>">
  <?= csrf_field() ?>
  <div class="grid-2">
    <div class="card" style="padding:1rem">
      <h3 style="margin-top:0">اطلاعات کافه</h3>
      <div class="form-group"><label>نام</label><input class="input" name="cafe_name" value="<?= e($settings['cafe_name'] ?? '') ?>"></div>
      <div class="form-group"><label>نام انگلیسی</label><input class="input" name="cafe_name_en" value="<?= e($settings['cafe_name_en'] ?? '') ?>"></div>
      <div class="form-group">
        <label>زبان سیستم (مدیر و مشتری)</label>
        <select class="input" name="system_lang">
          <option value="fa" <?= ($settings['system_lang'] ?? 'fa') === 'fa' ? 'selected' : '' ?>>فارسی</option>
          <option value="en" <?= ($settings['system_lang'] ?? '') === 'en' ? 'selected' : '' ?>>English</option>
        </select>
        <p class="muted" style="font-size:.8rem;margin:.35rem 0 0">با تغییر زبان، کل رابط کاربری سیستم عوض می‌شود. سوییچ برای مشتری وجود ندارد.</p>
      </div>
      <div class="form-group"><label>تلفن</label><input class="input" name="cafe_phone" value="<?= e($settings['cafe_phone'] ?? '') ?>" placeholder="۰۹۱۲… — روی منو نمایش و قابل تماس می‌شود" dir="ltr" style="text-align:left"></div>
      <div class="form-group"><label>آدرس</label><input class="input" name="cafe_address" value="<?= e($settings['cafe_address'] ?? '') ?>" placeholder="روی هدر منوی مشتری نمایش داده می‌شود"></div>
      <h3>زرین‌پال</h3>
      <div class="form-group">
        <label>Merchant ID</label>
        <input class="input" name="zarinpal_merchant" value="<?= e($settings['zarinpal_merchant'] ?? '') ?>" placeholder="TEST برای حالت آزمایشی">
        <p class="muted" style="font-size:.8rem;margin:.35rem 0 0">مقدار <code>TEST</code> درگاه آزمایشی داخلی را فعال می‌کند.</p>
      </div>
      <label class="switch-row" style="border:none">
        <span>حالت سندباکس زرین‌پال</span>
        <label class="switch"><input type="checkbox" name="zarinpal_sandbox" value="1" <?= ($settings['zarinpal_sandbox'] ?? '1') === '1' ? 'checked' : '' ?>><span class="slider"></span></label>
      </label>
      <h3>تغییر رمز ادمین</h3>
      <div class="form-group"><label>رمز جدید (اختیاری)</label><input class="input" type="password" name="new_password" autocomplete="new-password"></div>
    </div>

    <div class="card" style="padding:1rem">
      <h3 style="margin-top:0">فعال / غیرفعال کردن قابلیت‌ها</h3>
      <?php foreach ($features as $key => $label): ?>
        <label class="switch-row">
          <span><?= e($label) ?></span>
          <label class="switch">
            <input type="checkbox" name="feature_<?= e($key) ?>" value="1" <?= ($settings[$key] ?? '0') === '1' ? 'checked' : '' ?>>
            <span class="slider"></span>
          </label>
        </label>
      <?php endforeach; ?>
    </div>
  </div>
  <button class="btn btn-primary" style="margin-top:1rem" type="submit">ذخیره تنظیمات</button>
</form>

<?php if (($settings['qr_per_table'] ?? '0') === '1'): ?>
  <div class="card" style="padding:1rem;margin-top:1rem">
    <h3 style="margin-top:0">لینک و QR میزها</h3>
    <div class="tables-grid">
      <?php foreach ($tables as $t): $url = \App\Models\CafeTable::menuUrl($t); ?>
        <div class="table-card free">
          <div class="num"><?= e($t['table_number']) ?></div>
          <div class="qr-box qr-local" data-qr="<?= e($url) ?>" style="margin:.5rem auto"></div>
          <a class="btn btn-sm btn-ghost" href="<?= e($url) ?>" target="_blank">باز کردن</a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <script src="<?= e(asset('vendor/qrcode.min.js')) ?>"></script>
  <script src="<?= e(asset('js/qr-local.js')) ?>"></script>
<?php endif; ?>
