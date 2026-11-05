<div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1rem">
  <p class="muted" style="margin:0">شماره‌هایی که مشتریان هنگام سفارش ثبت کرده‌اند. برای پیامک یا باشگاه مشتریان قابل خروجی گرفتن است.</p>
  <a class="btn btn-primary btn-sm" href="<?= e(app_url('admin/customers/export')) ?>">خروجی CSV</a>
</div>

<div class="card table-wrap">
  <table class="data">
    <thead>
      <tr>
        <th>موبایل</th>
        <th>نام</th>
        <th>تعداد سفارش</th>
        <th>آخرین سفارش</th>
        <th>جمع خرید</th>
      </tr>
    </thead>
    <tbody>
    <?php if (!$rows): ?>
      <tr><td colspan="5" class="muted">هنوز شماره‌ای ثبت نشده.</td></tr>
    <?php endif; ?>
    <?php foreach ($rows as $row): ?>
      <tr>
        <td dir="ltr" style="text-align:left"><?= e($row['phone']) ?></td>
        <td><?= e($row['name'] ?: '—') ?></td>
        <td><?= (int)$row['orders_count'] ?></td>
        <td><?= e($row['last_order_at']) ?></td>
        <td><?= e(money((int)$row['total_spent'])) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
