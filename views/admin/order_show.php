<div class="card" style="padding:1.25rem;max-width:720px">
  <p><strong>کد:</strong> <?= e($order['order_code']) ?></p>
  <p><strong>میز:</strong> <?= e($order['table_number'] ?? '—') ?></p>
  <p><strong>وضعیت:</strong> <span class="badge <?= e(status_class($order['status'])) ?>"><?= e(status_label($order['status'])) ?></span></p>
  <p><strong>پرداخت:</strong> <?= e(status_label($order['pay_status'])) ?> / <?= e(status_label($order['pay_method'])) ?></p>
  <p><strong>مشتری:</strong> <?= e($order['customer_name'] ?: '—') ?> · <?= e($order['customer_phone'] ?: '—') ?></p>
  <?php if ($order['notes']): ?><p><strong>توضیحات:</strong> <?= e($order['notes']) ?></p><?php endif; ?>
  <?php if ($order['zarinpal_ref']): ?><p><strong>کد پیگیری:</strong> <?= e($order['zarinpal_ref']) ?></p><?php endif; ?>

  <hr style="border-color:var(--border)">
  <?php foreach ($items as $item): ?>
    <div class="cart-line">
      <div>
        <strong><?= e($item['product_name']) ?></strong> × <?= (int)$item['quantity'] ?>
        <?php if ($item['size_name']): ?><div class="muted"><?= e($item['size_name']) ?></div><?php endif; ?>
        <?php
          $addons = $item['addons_json'] ? json_decode($item['addons_json'], true) : [];
          if ($addons):
        ?>
          <div class="muted" style="font-size:.8rem"><?= e(implode('، ', array_column($addons, 'name'))) ?></div>
        <?php endif; ?>
      </div>
      <div><?= e(money((int)$item['line_total'])) ?></div>
    </div>
  <?php endforeach; ?>
  <?php if ((int)$order['discount_amount'] > 0): ?>
    <p class="muted">تخفیف (<?= e($order['discount_code']) ?>): −<?= e(money((int)$order['discount_amount'])) ?></p>
  <?php endif; ?>
  <p style="font-size:1.2rem;font-weight:800">جمع: <?= e(money((int)$order['total'])) ?></p>
  <a class="btn btn-ghost" href="<?= e(app_url('admin')) ?>">بازگشت</a>
</div>
