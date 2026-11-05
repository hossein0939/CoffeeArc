<?php
use App\Models\Setting;
$cafePhone = trim((string) Setting::get('cafe_phone', ''));
$cafeAddress = trim((string) Setting::get('cafe_address', ''));
$phoneDigits = normalize_phone($cafePhone);
?>
<header class="cust-header">
  <div class="container cust-header-inner">
    <div class="brand">
      <div class="brand-name"><?= e(lang_pick(Setting::get('cafe_name', 'کافه آرک'), Setting::get('cafe_name_en', 'Coffee Arc'))) ?></div>
      <div class="brand-sub"><?= e(lang_pick(Setting::get('cafe_name_en', 'Coffee Arc'), Setting::get('cafe_name', 'کافه آرک'))) ?></div>
    </div>
    <div class="header-actions">
      <div class="muted" style="font-size:.9rem">
        <?php if ($selectedTable): ?>
          <?= e(__('table')) ?> <?= e($selectedTable['table_number']) ?>
        <?php else: ?>
          <?= e(__('brand_sub')) ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php if ($cafePhone !== '' || $cafeAddress !== ''): ?>
  <div class="container menu-contact">
    <?php if ($cafePhone !== ''): ?>
      <a class="menu-contact-phone" href="tel:<?= e($phoneDigits ?: preg_replace('/\s+/', '', $cafePhone)) ?>" dir="ltr">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1.1-.3 1.2.4 2.5.6 3.8.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.6 21 3 13.4 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.6.6 3.8.1.4 0 .8-.3 1.1L6.6 10.8z" fill="currentColor"/></svg>
        <span><?= e($cafePhone) ?></span>
      </a>
    <?php endif; ?>
    <?php if ($cafeAddress !== ''): ?>
      <div class="menu-contact-address">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7zm0 9.5c-1.4 0-2.5-1.1-2.5-2.5S10.6 6.5 12 6.5s2.5 1.1 2.5 2.5S13.4 11.5 12 11.5z" fill="currentColor"/></svg>
        <span><?= e($cafeAddress) ?></span>
      </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</header>

<main class="container" style="padding-top:.5rem">
  <?php if (!$orderingEnabled): ?>
    <div class="alert alert-ok" style="margin-top:1rem"><?= e(__('menu_only')) ?></div>
  <?php endif; ?>

  <div class="cats">
    <button type="button" class="cat-chip active" data-cat=""><?= e(__('all')) ?></button>
    <?php foreach ($categories as $cat): ?>
      <button type="button" class="cat-chip" data-cat="<?= (int)$cat['id'] ?>"><?= e(lang_pick($cat['name'], $cat['name_en'] ?? null)) ?></button>
    <?php endforeach; ?>
  </div>

  <?php foreach ($categories as $cat): ?>
    <?php $items = $grouped[(int)$cat['id']] ?? []; if (!$items) continue; ?>
    <section class="cat-section" data-cat="<?= (int)$cat['id'] ?>" style="margin-bottom:1.75rem">
      <h2 class="cat-title"><?= e(lang_pick($cat['name'], $cat['name_en'] ?? null)) ?></h2>
      <div class="product-grid">
        <?php foreach ($items as $p): ?>
          <?php
            $priced = has_price($p['base_price'] ?? null);
            $desc = lang_pick($p['description'] ?? null, $p['description_en'] ?? null);
          ?>
          <article class="product-card" data-product="<?= (int)$p['id'] ?>">
            <img class="thumb" src="<?= e(product_image_url($p)) ?>" alt="<?= e(lang_pick($p['name'], $p['name_en'] ?? null)) ?>" loading="lazy">
            <div class="body">
              <h3><?= e(lang_pick($p['name'], $p['name_en'] ?? null)) ?></h3>
              <?php if ($desc): ?><p class="item-desc"><?= e($desc) ?></p><?php endif; ?>
              <div class="price-row">
                <span class="price<?= $priced ? '' : ' price-empty' ?>"><?= $priced ? e(money((int)$p['base_price'])) : '&nbsp;' ?></span>
                <?php if ($orderingEnabled): ?><button type="button" class="add-btn" aria-label="+">+</button><?php endif; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>
</main>

<div class="cart-bar" id="cartBar">
  <span><span id="cartCount">0</span> <?= e(__('items')) ?></span>
  <span id="cartTotal">—</span>
</div>

<div class="drawer-backdrop" id="drawerBg"></div>
<div class="cart-drawer" id="cartDrawer">
  <div style="display:flex;justify-content:space-between;align-items:center">
    <h3 style="margin:0"><?= e(__('cart')) ?></h3>
    <button class="btn btn-sm btn-ghost" id="closeDrawer"><?= e(__('close')) ?></button>
  </div>
  <div id="cartList" style="margin:1rem 0"></div>
  <?php if ($orderingEnabled): ?>
    <button class="btn btn-primary btn-block" id="goCheckout"><?= e(__('place_order')) ?></button>
  <?php endif; ?>
</div>

<div class="modal-backdrop" id="modalBg"><div class="modal" id="modal"></div></div>

<script src="<?= e(asset('js/menu.js')) ?>"></script>
