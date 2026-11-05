<?php if (!empty($flash_ok)): ?><div class="alert alert-ok"><?= e($flash_ok) ?></div><?php endif; ?>

<div class="card" style="padding:1rem;margin-bottom:1rem">
  <h3 style="margin-top:0">افزودن میز</h3>
  <form method="post" action="<?= e(app_url('admin/tables')) ?>" class="grid-2">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="0">
    <div class="form-group"><label>شماره میز</label><input class="input" name="table_number" required></div>
    <div class="form-group"><label>ظرفیت</label><input class="input" type="number" name="seats" value="4" min="1"></div>
    <div class="form-group"><label>وضعیت</label>
      <select class="input" name="status">
        <option value="free">آزاد</option>
        <option value="occupied">اشغال</option>
        <option value="reserved">رزرو</option>
      </select>
    </div>
    <div class="form-group" style="display:flex;align-items:flex-end">
      <button class="btn btn-primary" type="submit">ذخیره</button>
    </div>
  </form>
</div>

<div class="tables-grid">
  <?php foreach ($tables as $t): ?>
    <div class="table-card <?= e($t['status']) ?>">
      <div class="num"><?= e($t['table_number']) ?></div>
      <div class="muted"><?= (int)$t['seats'] ?> نفره · <?= e(status_label($t['status'])) ?></div>
      <?php if ($qrEnabled): ?>
        <?php $url = \App\Models\CafeTable::menuUrl($t); ?>
        <div class="qr-box qr-local" data-qr="<?= e($url) ?>" style="margin:.6rem auto"></div>
        <a class="btn btn-sm btn-ghost" href="<?= e($url) ?>" target="_blank">لینک منو</a>
      <?php endif; ?>
      <div style="margin-top:.6rem;display:flex;flex-wrap:wrap;gap:.25rem;justify-content:center">
        <?php foreach (['free' => 'آزاد', 'occupied' => 'اشغال', 'reserved' => 'رزرو'] as $st => $label): ?>
          <form method="post" action="<?= e(app_url('admin/tables/status')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
            <input type="hidden" name="status" value="<?= e($st) ?>">
            <button class="btn btn-sm btn-ghost" type="submit"><?= e($label) ?></button>
          </form>
        <?php endforeach; ?>
        <form method="post" action="<?= e(app_url('admin/tables/delete')) ?>" onsubmit="return confirm('حذف میز؟')">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
          <button class="btn btn-sm btn-danger" type="submit">حذف</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php if (!empty($qrEnabled)): ?>
<script src="<?= e(asset('vendor/qrcode.min.js')) ?>"></script>
<script src="<?= e(asset('js/qr-local.js')) ?>"></script>
<?php endif; ?>
