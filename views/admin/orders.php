<?php if ($stats): ?>
  <div class="stats-row">
    <div class="card stat-card"><div class="label">سفارش امروز</div><div class="value"><?= (int)$stats['today_count'] ?></div></div>
    <div class="card stat-card"><div class="label">فروش امروز</div><div class="value" style="font-size:1rem"><?= e(money((int)$stats['today_sales'])) ?></div></div>
    <div class="card stat-card"><div class="label">سفارش ۷ روز</div><div class="value"><?= (int)$stats['week_count'] ?></div></div>
    <div class="card stat-card"><div class="label">پرداخت‌شده امروز</div><div class="value" style="font-size:1rem"><?= e(money((int)$stats['today_paid'])) ?></div></div>
  </div>
<?php endif; ?>

<div class="filters">
  <?php foreach (['all' => 'همه', 'new' => 'جدید', 'preparing' => 'آماده‌سازی', 'ready' => 'آماده', 'delivered' => 'تحویل', 'cancelled' => 'لغو'] as $k => $label): ?>
    <a class="btn btn-sm <?= $status === $k ? 'btn-primary' : 'btn-ghost' ?>" href="<?= e(app_url('admin?status=' . $k)) ?>"><?= e($label) ?></a>
  <?php endforeach; ?>
</div>

<div class="card table-wrap">
  <table class="data">
    <thead>
      <tr>
        <th>کد</th>
        <th>میز</th>
        <th>مبلغ</th>
        <th>پرداخت</th>
        <th>وضعیت</th>
        <th>عملیات یک‌کلیکه</th>
      </tr>
    </thead>
    <tbody>
    <?php if (!$orders): ?>
      <tr><td colspan="6" class="muted">سفارشی نیست.</td></tr>
    <?php endif; ?>
    <?php foreach ($orders as $o): ?>
      <tr>
        <td><a href="<?= e(app_url('admin/orders/' . $o['id'])) ?>"><?= e($o['order_code']) ?></a>
          <div class="muted" style="font-size:.75rem"><?= e($o['created_at']) ?></div>
        </td>
        <td><?= e($o['table_number'] ?? '—') ?></td>
        <td><?= e(money((int)$o['total'])) ?></td>
        <td>
          <span class="badge <?= e(status_class($o['pay_status'])) ?>"><?= e(status_label($o['pay_status'])) ?></span>
          <div class="muted" style="font-size:.75rem"><?= e(status_label($o['pay_method'])) ?></div>
        </td>
        <td><span class="badge <?= e(status_class($o['status'])) ?>"><?= e(status_label($o['status'])) ?></span></td>
        <td style="white-space:nowrap">
          <?php
          $next = match ($o['status']) {
              'new' => ['preparing', 'آماده‌سازی'],
              'preparing' => ['ready', 'آماده'],
              'ready' => ['delivered', 'تحویل'],
              default => null,
          };
          ?>
          <?php if ($next): ?>
            <form style="display:inline" method="post" action="<?= e(app_url('admin/orders/' . $o['id'] . '/status')) ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="status" value="<?= e($next[0]) ?>">
              <button class="btn btn-sm btn-primary" type="submit"><?= e($next[1]) ?></button>
            </form>
          <?php endif; ?>
          <?php if ($o['pay_status'] !== 'paid' && $o['status'] !== 'cancelled'): ?>
            <form style="display:inline" method="post" action="<?= e(app_url('admin/orders/' . $o['id'] . '/paid')) ?>">
              <?= csrf_field() ?>
              <button class="btn btn-sm btn-ok" type="submit">پرداخت شد</button>
            </form>
          <?php endif; ?>
          <?php if (!in_array($o['status'], ['delivered', 'cancelled'], true)): ?>
            <form style="display:inline" method="post" action="<?= e(app_url('admin/orders/' . $o['id'] . '/status')) ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="status" value="cancelled">
              <button class="btn btn-sm btn-danger" type="submit">لغو</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<script>
  // auto refresh live orders every 20s
  setTimeout(() => location.reload(), 20000);
</script>
