<?php if (!empty($flash_ok)): ?><div class="alert alert-ok"><?= e($flash_ok) ?></div><?php endif; ?>
<?php if (!empty($flash_err)): ?><div class="alert alert-error"><?= e($flash_err) ?></div><?php endif; ?>

<p class="muted" style="margin-top:0">برای تغییر ترتیب، دسته‌ها یا محصولات را بکشید و رها کنید. تصویر دسته فقط به‌عنوان تصویر پیش‌فرض محصولات بدون عکس استفاده می‌شود و روی خود دسته در منو نمایش داده نمی‌شود.</p>

<div class="grid-2" style="margin-bottom:1.25rem">
  <div class="card" style="padding:1rem">
    <h3 style="margin-top:0">دسته جدید / ویرایش</h3>
    <form method="post" action="<?= e(app_url('admin/menu/category')) ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="catId" value="0">
      <div class="form-group"><label>نام فارسی</label><input class="input" name="name" id="catName" required></div>
      <div class="form-group"><label>نام انگلیسی</label><input class="input" name="name_en" id="catEn"></div>
      <div class="form-group"><label>تصویر فال‌بک محصولات (اختیاری)</label><input class="input" type="file" name="image" accept="image/*"></div>
      <div class="form-group"><label>ترتیب</label><input class="input" type="number" name="sort_order" id="catSort" value="0"></div>
      <label style="display:flex;gap:.5rem;align-items:center;margin-bottom:1rem">
        <input type="checkbox" name="is_active" id="catActive" value="1" checked> فعال
      </label>
      <button class="btn btn-primary" type="submit">ذخیره دسته</button>
    </form>

    <h4 style="margin:1.25rem 0 .5rem">ترتیب دسته‌ها (درگ کنید)</h4>
    <ul id="catSortable" class="sort-list" data-url="<?= e(app_url('admin/menu/reorder-categories')) ?>">
      <?php foreach ($categories as $c): ?>
        <li class="sort-item" data-id="<?= (int)$c['id'] ?>">
          <span class="drag-handle" title="جابجا کردن">⠿</span>
          <span class="sort-title">
            <?= e($c['name']) ?>
            <?php if ($c['name_en']): ?><small class="muted"> / <?= e($c['name_en']) ?></small><?php endif; ?>
            <?php if (!(int)$c['is_active']): ?><span class="badge badge-muted">غیرفعال</span><?php endif; ?>
            <?php if (!empty($c['image_path'])): ?><span class="badge badge-ok">تصویر</span><?php endif; ?>
          </span>
          <span class="sort-actions">
            <button type="button" class="btn btn-sm btn-ghost" onclick="fillCat(<?= (int)$c['id'] ?>, <?= json_encode($c['name'], JSON_UNESCAPED_UNICODE) ?>, <?= json_encode((string)$c['name_en'], JSON_UNESCAPED_UNICODE) ?>, <?= (int)$c['sort_order'] ?>, <?= (int)$c['is_active'] ?>)">ویرایش</button>
            <form style="display:inline" method="post" action="<?= e(app_url('admin/menu/category/delete')) ?>" onsubmit="return confirm('حذف؟')">
              <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <button class="btn btn-sm btn-danger" type="submit">حذف</button>
            </form>
          </span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <div class="card" style="padding:1rem">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:.5rem;flex-wrap:wrap">
      <h3 style="margin:0">محصولات</h3>
      <a class="btn btn-primary btn-sm" href="<?= e(app_url('admin/menu/product/new')) ?>">محصول جدید</a>
    </div>

    <?php foreach ($categories as $c): $items = $grouped[(int)$c['id']] ?? []; if (!$items) continue; ?>
      <h4 style="margin:1.2rem 0 .4rem"><?= e($c['name']) ?></h4>
      <ul class="sort-list product-sortable" data-url="<?= e(app_url('admin/menu/reorder-products')) ?>">
        <?php foreach ($items as $p): ?>
          <li class="sort-item" data-id="<?= (int)$p['id'] ?>">
            <span class="drag-handle">⠿</span>
            <span class="sort-title">
              <?= e($p['name']) ?>
              <?php if (has_price($p['base_price'] ?? null)): ?>
                <small class="muted"><?= e(money((int)$p['base_price'])) ?></small>
              <?php else: ?>
                <small class="muted">بدون قیمت</small>
              <?php endif; ?>
            </span>
            <span class="sort-actions">
              <form method="post" action="<?= e(app_url('admin/menu/product/toggle')) ?>" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <input type="hidden" name="available" value="<?= (int)$p['is_available'] ? '0' : '1' ?>">
                <button class="btn btn-sm <?= (int)$p['is_available'] ? 'btn-ok' : 'btn-ghost' ?>" type="submit">
                  <?= (int)$p['is_available'] ? 'موجود' : 'ناموجود' ?>
                </button>
              </form>
              <a class="btn btn-sm btn-ghost" href="<?= e(app_url('admin/menu/product/' . $p['id'])) ?>">ویرایش</a>
              <form style="display:inline" method="post" action="<?= e(app_url('admin/menu/product/delete')) ?>" onsubmit="return confirm('حذف؟')">
                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <button class="btn btn-sm btn-danger" type="submit">حذف</button>
              </form>
            </span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endforeach; ?>
  </div>
</div>

<script>
function fillCat(id, name, en, sort, active) {
  document.getElementById('catId').value = id;
  document.getElementById('catName').value = name;
  document.getElementById('catEn').value = en || '';
  document.getElementById('catSort').value = sort;
  document.getElementById('catActive').checked = !!active;
}
</script>
<script src="<?= e(asset('js/sortable-local.js')) ?>"></script>
<script>
(() => {
  const csrf = <?= json_encode($csrf) ?>;
  function bindSortable(el) {
    if (!el || !window.CoffeeArcSortable) return;
    CoffeeArcSortable.create(el, {
      onEnd: async () => {
        const ids = [...el.querySelectorAll('.sort-item')].map(li => li.dataset.id);
        const fd = new FormData();
        fd.set('_csrf', csrf);
        fd.set('ids', JSON.stringify(ids));
        await fetch(el.dataset.url, { method: 'POST', body: fd, headers: { Accept: 'application/json' } });
      }
    });
  }
  bindSortable(document.getElementById('catSortable'));
  document.querySelectorAll('.product-sortable').forEach(bindSortable);
})();
</script>
