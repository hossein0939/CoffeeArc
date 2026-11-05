<?php if (!empty($flash_ok)): ?><div class="alert alert-ok"><?= e($flash_ok) ?></div><?php endif; ?>

<div class="grid-2">
  <div class="card" style="padding:1rem">
    <h3 style="margin-top:0">تخفیف جدید</h3>
    <form method="post" action="<?= e(app_url('admin/discounts')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="0">
      <div class="form-group"><label>کد</label><input class="input" name="code" required placeholder="WELCOME10"></div>
      <div class="form-group">
        <label>نوع</label>
        <select class="input" name="type">
          <option value="percent">درصدی</option>
          <option value="fixed">مبلغ ثابت (تومان)</option>
        </select>
      </div>
      <div class="form-group"><label>مقدار</label><input class="input" type="number" name="value" required min="1"></div>
      <label style="display:flex;gap:.5rem;margin-bottom:1rem"><input type="checkbox" name="is_active" value="1" checked> فعال</label>
      <button class="btn btn-primary" type="submit">ذخیره</button>
    </form>
  </div>
  <div class="card table-wrap" style="padding:1rem">
    <table class="data">
      <thead><tr><th>کد</th><th>نوع</th><th>مقدار</th><th>وضعیت</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($discounts as $d): ?>
        <tr>
          <td><?= e($d['code']) ?></td>
          <td><?= $d['type'] === 'percent' ? 'درصد' : 'ثابت' ?></td>
          <td><?= (int)$d['value'] ?><?= $d['type'] === 'percent' ? '%' : ' تومان' ?></td>
          <td><?= (int)$d['is_active'] ? 'فعال' : 'غیرفعال' ?></td>
          <td>
            <form method="post" action="<?= e(app_url('admin/discounts/delete')) ?>" onsubmit="return confirm('حذف؟')">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
              <button class="btn btn-sm btn-danger" type="submit">حذف</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
