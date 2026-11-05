<div
  id="posRoot"
  data-customization="<?= $customization ? '1' : '0' ?>"
  data-api-base="<?= e(rtrim(app_url(''), '/')) ?>"
  data-csrf="<?= e(csrf_token()) ?>"
  data-pos-url="<?= e(app_url('admin/pos')) ?>"
  data-admin-url="<?= e(app_url('admin')) ?>"
>
<div class="pos-layout">
  <div>
    <div class="product-grid">
      <?php foreach ($categories as $cat): foreach ($grouped[(int)$cat['id']] ?? [] as $p): ?>
        <article class="product-card" data-product="<?= (int)$p['id'] ?>">
          <img class="thumb" src="<?= e(upload_url($p['image_path'])) ?>" alt="">
          <div class="body">
            <h3><?= e($p['name']) ?></h3>
            <div class="en"><?= e($cat['name']) ?></div>
            <div class="price-row">
              <span class="price"><?= e(money((int)$p['base_price'])) ?></span>
              <button type="button" class="add-btn">+</button>
            </div>
          </div>
        </article>
      <?php endforeach; endforeach; ?>
    </div>
  </div>
  <aside class="card pos-cart">
    <h3 style="margin-top:0">سفارش جاری</h3>
    <div class="form-group">
      <label>میز</label>
      <select class="input" id="posTable">
        <option value="">بدون میز</option>
        <?php foreach ($tables as $t): ?>
          <option value="<?= (int)$t['id'] ?>">میز <?= e($t['table_number']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>نام مشتری</label>
      <input class="input" id="posName">
    </div>
    <?php if ($discounts): ?>
      <div class="form-group">
        <label>کد تخفیف</label>
        <input class="input" id="posDiscount">
      </div>
    <?php endif; ?>
    <label style="display:flex;gap:.5rem;align-items:center;margin-bottom:1rem">
      <input type="checkbox" id="markPaid"> پرداخت نقدی انجام شد
    </label>
    <div id="posCartList"></div>
    <div style="display:flex;justify-content:space-between;font-weight:800;margin:1rem 0">
      <span>جمع</span><span id="posTotal">۰</span>
    </div>
    <button class="btn btn-primary btn-block" id="posSubmit" type="button">تکمیل سفارش</button>
  </aside>
</div>
</div>
<script>
  // bridge dataset from #posRoot to document.body for pos.js
  (function () {
    const root = document.getElementById('posRoot');
    ['customization','apiBase','csrf','posUrl','adminUrl'].forEach((k) => {
      const attr = 'data-' + k.replace(/[A-Z]/g, m => '-' + m.toLowerCase());
      // already kebab in HTML
    });
    document.body.dataset.customization = root.dataset.customization;
    document.body.dataset.apiBase = root.dataset.apiBase;
    document.body.dataset.csrf = root.dataset.csrf;
    document.body.dataset.posUrl = root.dataset.posUrl;
    document.body.dataset.adminUrl = root.dataset.adminUrl;
  })();
</script>
<script src="<?= e(asset('js/pos.js')) ?>"></script>
