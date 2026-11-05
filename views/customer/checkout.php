<?php
/** @var string $mode phone_and_pay|phone_only|pay_only|confirm */
?>
<header class="cust-header">
  <div class="container cust-header-inner">
    <div class="brand">
      <div class="brand-name"><?= e(__('checkout')) ?></div>
      <div class="brand-sub"><a href="<?= e(app_url('m')) ?>"><?= e(__('back_to_menu')) ?></a></div>
    </div>
  </div>
</header>

<main class="container" style="padding:1.25rem 0 3rem;max-width:520px">
  <form id="checkoutForm" class="card" style="padding:1.15rem" method="post" action="<?= e(app_url('checkout')) ?>">
    <?= csrf_field() ?>

    <?php if (!empty($needTable)): ?>
      <div class="form-group">
        <label><?= e(__('table')) ?> *</label>
        <select class="input" name="table_id" required>
          <option value=""><?= e(__('select_table')) ?></option>
          <?php foreach ($tables as $t): ?>
            <option value="<?= (int)$t['id'] ?>">
              <?= e(__('table')) ?> <?= e($t['table_number']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php elseif (!empty($selectedTableId)): ?>
      <input type="hidden" name="table_id" value="<?= (int)$selectedTableId ?>">
      <p class="muted" style="margin-top:0"><?= e(__('table')) ?>:
        <?php
          $tn = '';
          foreach ($tables as $t) {
              if ((int)$t['id'] === (int)$selectedTableId) { $tn = $t['table_number']; break; }
          }
          echo e($tn);
        ?>
      </p>
    <?php endif; ?>

    <?php if ($mode === 'phone_only' || $mode === 'phone_and_pay'): ?>
      <div class="form-group">
        <label><?= e(__('phone')) ?> *</label>
        <input class="input" name="customer_phone" required inputmode="tel" placeholder="09xxxxxxxxx" autofocus>
      </div>
    <?php endif; ?>

    <?php if ($mode === 'pay_only' || $mode === 'phone_and_pay'): ?>
      <div class="form-group">
        <label><?= e(__('pay_method')) ?></label>
        <div class="pay-options">
          <?php if ($onlinePayment): ?>
            <label class="pay-option">
              <input type="radio" name="pay_method" value="online" checked>
              <span><?= e(__('pay_online')) ?></span>
            </label>
          <?php endif; ?>
          <?php if ($payAtCafe): ?>
            <label class="pay-option">
              <input type="radio" name="pay_method" value="cafe" <?= !$onlinePayment ? 'checked' : '' ?>>
              <span><?= e(__('pay_cafe')) ?></span>
            </label>
          <?php endif; ?>
        </div>
      </div>
    <?php elseif ($onlinePayment && !$payAtCafe): ?>
      <input type="hidden" name="pay_method" value="online">
    <?php else: ?>
      <input type="hidden" name="pay_method" value="cafe">
    <?php endif; ?>

    <button class="btn btn-primary btn-block" id="submitOrder" type="submit"><?= e(__('place_order')) ?></button>
  </form>
</main>
<script src="<?= e(asset('js/checkout.js')) ?>"></script>
