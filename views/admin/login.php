<div class="card login-box">
  <h1 style="margin-top:0">ورود مدیر</h1>
  <p class="muted">پنل مدیریت کافه آرک</p>
  <?php if (!empty($error)): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="<?= e(app_url('admin/login')) ?>">
    <?= csrf_field() ?>
    <div class="form-group">
      <label>نام کاربری</label>
      <input class="input" name="username" required autofocus>
    </div>
    <div class="form-group">
      <label>رمز عبور</label>
      <input class="input" type="password" name="password" required>
    </div>
    <button class="btn btn-primary btn-block" type="submit">ورود</button>
  </form>
</div>
