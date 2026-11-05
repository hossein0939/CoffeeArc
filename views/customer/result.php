<?php $pay = input('pay'); ?>
<main class="container" style="padding:2rem 0;max-width:560px">
  <div class="card" style="padding:1.5rem;text-align:center">
    <?php if ($pay === 'ok' || $order['pay_status'] === 'paid'): ?>
      <div class="badge badge-ok" style="margin-bottom:1rem">پرداخت موفق</div>
    <?php elseif ($pay === 'failed' || $order['pay_status'] === 'failed'): ?>
      <div class="badge badge-danger" style="margin-bottom:1rem">پرداخت ناموفق</div>
    <?php elseif ($order['pay_status'] === 'unpaid'): ?>
      <div class="badge badge-warn" style="margin-bottom:1rem">پرداخت در کافه</div>
    <?php else: ?>
      <div class="badge badge-info" style="margin-bottom:1rem">سفارش ثبت شد</div>
    <?php endif; ?>

    <h1 style="margin:.5rem 0">کد سفارش</h1>
    <div style="font-size:1.6rem;font-weight:800;letter-spacing:.05em;color:var(--accent)"><?= e($order['order_code']) ?></div>
    <p class="muted">میز: <?= e($order['table_number'] ?? '—') ?> · <?= e(status_label($order['status'])) ?></p>
    <p style="font-weight:700;font-size:1.15rem"><?= e(money((int)$order['total'])) ?></p>

    <div style="text-align:right;margin-top:1.25rem">
      <?php foreach ($items as $item): ?>
        <div class="cart-line">
          <div>
            <strong><?= e($item['product_name']) ?></strong> × <?= (int)$item['quantity'] ?>
            <?php if ($item['size_name']): ?><div class="muted" style="font-size:.8rem"><?= e($item['size_name']) ?></div><?php endif; ?>
          </div>
          <div><?= e(money((int)$item['line_total'])) ?></div>
        </div>
      <?php endforeach; ?>
    </div>

    <a class="btn btn-primary" style="margin-top:1.25rem" href="<?= e(app_url('m')) ?>">بازگشت به منو</a>
  </div>
</main>
