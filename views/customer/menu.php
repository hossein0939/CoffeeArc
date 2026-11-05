<?php
use App\Models\Setting;
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
