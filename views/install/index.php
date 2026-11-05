<div class="card install-box">
  <h1 style="margin-top:0">نصب کافه آرک</h1>
  <p class="muted">اطلاعات دیتابیس MySQL و مدیر را وارد کنید. این صفحه فقط یک‌بار اجرا می‌شود.</p>
  <?php if (!empty($error)): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <?php if (!empty($success)): ?><div class="alert alert-ok"><?= e($success) ?></div><?php endif; ?>
  <form method="post" action="<?= e(app_url('install')) ?>">
    <?= csrf_field() ?>
    <div class="grid-2">
      <div class="form-group">
        <label>نام کافه</label>
        <input class="input" name="cafe_name" value="کافه آرک" required>
      </div>
      <div class="form-group">
        <label>آدرس سایت (APP_URL)</label>
        <input class="input" name="app_url" value="<?= e(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')) ?>" required>
      </div>
      <div class="form-group">
        <label>DB Host</label>
        <input class="input" name="db_host" value="localhost" required>
      </div>
      <div class="form-group">
        <label>DB Port</label>
        <input class="input" name="db_port" value="3306" required>
      </div>
      <div class="form-group">
        <label>نام دیتابیس</label>
        <input class="input" name="db_name" value="coffeearc" required>
      </div>
      <div class="form-group">
        <label>کاربر دیتابیس</label>
        <input class="input" name="db_user" value="root" required>
      </div>
      <div class="form-group">
        <label>رمز دیتابیس</label>
        <input class="input" type="password" name="db_pass" value="">
      </div>
      <div class="form-group">
        <label>نام کاربری ادمین</label>
        <input class="input" name="admin_user" value="admin" required>
      </div>
      <div class="form-group">
        <label>رمز ادمین</label>
        <input class="input" type="password" name="admin_pass" required>
      </div>
      <div class="form-group">
        <label>زبان سیستم</label>
        <select class="input" name="system_lang">
          <option value="fa" selected>فارسی</option>
          <option value="en">English</option>
        </select>
      </div>
    </div>
    <button class="btn btn-primary btn-block" type="submit">نصب و راه‌اندازی</button>
  </form>
</div>
