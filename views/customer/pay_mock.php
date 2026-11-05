<main class="container" style="padding:2rem 0;max-width:480px">
  <div class="card" style="padding:1.5rem">
    <h1 style="margin-top:0">درگاه آزمایشی</h1>
    <p class="muted">Merchant روی حالت TEST است. این صفحه جایگزین زرین‌پال برای تست محلی است.</p>
    <p>مبلغ: <strong><?= e(money((int)$amount)) ?></strong></p>
    <p class="muted" style="font-size:.85rem">Authority: <?= e($authority) ?></p>
    <form method="post" action="<?= e(app_url('pay/mock')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="Authority" value="<?= e($authority) ?>">
      <button class="btn btn-primary btn-block" name="result" value="ok" type="submit">پرداخت موفق</button>
      <button class="btn btn-danger btn-block" style="margin-top:.5rem" name="result" value="fail" type="submit">پرداخت ناموفق</button>
    </form>
  </div>
</main>
