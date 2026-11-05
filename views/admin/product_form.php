<?php
$p = $product;
$sizes = $p['sizes'] ?? [['name' => '', 'price_modifier' => 0]];
$addons = $p['addons'] ?? [['name' => '', 'price' => 0]];
if (!$sizes) $sizes = [['name' => '', 'price_modifier' => 0]];
if (!$addons) $addons = [['name' => '', 'price' => 0]];
$priceVal = has_price($p['base_price'] ?? null) ? (int)$p['base_price'] : '';
?>
<div class="card" style="padding:1.25rem;max-width:780px">
  <form method="post" action="<?= e(app_url('admin/menu/product')) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)($p['id'] ?? 0) ?>">
    <div class="form-group">
      <label>دسته</label>
      <select class="input" name="category_id" required>
        <?php foreach ($categories as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= ((int)($p['category_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?> / <?= e((string)$c['name_en']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="grid-2">
      <div class="form-group"><label>نام فارسی</label><input class="input" name="name" value="<?= e($p['name'] ?? '') ?>" required></div>
      <div class="form-group"><label>نام انگلیسی</label><input class="input" name="name_en" value="<?= e($p['name_en'] ?? '') ?>"></div>
    </div>
    <div class="form-group"><label>محتوا / ترکیبات (فارسی)</label><textarea class="input" name="description" rows="3" placeholder="مثلاً نان تازه + پنیر + خیار"><?= e($p['description'] ?? '') ?></textarea></div>
    <div class="form-group"><label>محتوا / ترکیبات (انگلیسی)</label><textarea class="input" name="description_en" rows="3"><?= e($p['description_en'] ?? '') ?></textarea></div>
    <div class="grid-2">
      <div class="form-group">
        <label>قیمت پایه (تومان) — خالی = بدون قیمت</label>
        <input class="input" type="number" name="base_price" value="<?= e((string)$priceVal) ?>" min="0" placeholder="خالی بگذارید اگر قیمت ندارد">
      </div>
      <div class="form-group"><label>ترتیب</label><input class="input" type="number" name="sort_order" value="<?= (int)($p['sort_order'] ?? 0) ?>"></div>
    </div>
    <div class="form-group">
      <label>تصویر محصول (اختیاری — در غیر این صورت تصویر دسته استفاده می‌شود)</label>
      <input class="input" type="file" name="image" accept="image/*">
      <?php if (!empty($p['image_path'])): ?>
        <img src="<?= e(upload_url($p['image_path'])) ?>" alt="" style="width:120px;margin-top:.5rem;border-radius:8px">
      <?php endif; ?>
    </div>
    <label style="display:flex;gap:.5rem;margin-bottom:1rem"><input type="checkbox" name="is_available" value="1" <?= !isset($p['is_available']) || (int)$p['is_available'] ? 'checked' : '' ?>> موجود</label>

    <h3>سایزها</h3>
    <div id="sizes">
      <?php foreach ($sizes as $s): ?>
        <div class="grid-2" style="margin-bottom:.5rem">
          <input class="input" name="size_name[]" placeholder="نام سایز" value="<?= e($s['name'] ?? '') ?>">
          <input class="input" type="number" name="size_mod[]" placeholder="مابه‌التفاوت قیمت" value="<?= (int)($s['price_modifier'] ?? 0) ?>">
        </div>
      <?php endforeach; ?>
    </div>
    <button type="button" class="btn btn-sm btn-ghost" onclick="document.getElementById('sizes').insertAdjacentHTML('beforeend',`<div class='grid-2' style='margin-bottom:.5rem'><input class='input' name='size_name[]' placeholder='نام سایز'><input class='input' type='number' name='size_mod[]' value='0'></div>`)">+ سایز</button>

    <h3>افزودنی‌ها</h3>
    <div id="addons">
      <?php foreach ($addons as $a): ?>
        <div class="grid-2" style="margin-bottom:.5rem">
          <input class="input" name="addon_name[]" placeholder="نام افزودنی" value="<?= e($a['name'] ?? '') ?>">
          <input class="input" type="number" name="addon_price[]" placeholder="قیمت" value="<?= (int)($a['price'] ?? 0) ?>">
        </div>
      <?php endforeach; ?>
    </div>
    <button type="button" class="btn btn-sm btn-ghost" onclick="document.getElementById('addons').insertAdjacentHTML('beforeend',`<div class='grid-2' style='margin-bottom:.5rem'><input class='input' name='addon_name[]' placeholder='نام افزودنی'><input class='input' type='number' name='addon_price[]' value='0'></div>`)">+ افزودنی</button>

    <div style="margin-top:1.25rem;display:flex;gap:.5rem">
      <button class="btn btn-primary" type="submit">ذخیره</button>
      <a class="btn btn-ghost" href="<?= e(app_url('admin/menu')) ?>">انصراف</a>
    </div>
  </form>
</div>
