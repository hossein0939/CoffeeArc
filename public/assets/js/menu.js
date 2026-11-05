(() => {
  const state = {
    cart: JSON.parse(localStorage.getItem('ca_cart') || '[]'),
    product: null,
    sizeId: null,
    addonIds: [],
    qty: 1,
  };

  const currency = document.body.dataset.currency || 'تومان';
  const money = (n) => {
    if (n === null || n === undefined || Number(n) <= 0) return '';
    return new Intl.NumberFormat('fa-IR').format(n) + ' ' + currency;
  };
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];

  const cartBar = $('#cartBar');
  const cartCount = $('#cartCount');
  const cartTotal = $('#cartTotal');
  const drawer = $('#cartDrawer');
  const drawerBg = $('#drawerBg');
  const modalBg = $('#modalBg');
  const modal = $('#modal');
  const customization = document.body.dataset.customization === '1';
  const ordering = document.body.dataset.ordering === '1';
  const apiBase = document.body.dataset.apiBase || '';
  const tAdd = document.body.dataset.i18nAdd || 'Add';
  const tSize = document.body.dataset.i18nSize || 'Size';
  const tAddons = document.body.dataset.i18nAddons || 'Add-ons';
  const tClose = document.body.dataset.i18nClose || 'Close';

  function save() {
    localStorage.setItem('ca_cart', JSON.stringify(state.cart));
    renderCart();
  }

  function cartSum() {
    return state.cart.reduce((s, i) => s + (i.unit_price || 0) * i.quantity, 0);
  }

  function renderCart() {
    const count = state.cart.reduce((s, i) => s + i.quantity, 0);
    if (cartBar) {
      cartBar.classList.toggle('show', count > 0 && ordering);
      if (cartCount) cartCount.textContent = count;
      if (cartTotal) {
        const sum = cartSum();
        cartTotal.textContent = sum > 0 ? money(sum) : '—';
      }
    }
    const list = $('#cartList');
    if (!list) return;
    if (!state.cart.length) {
      list.innerHTML = '<p class="muted">—</p>';
      return;
    }
    list.innerHTML = state.cart.map((item, idx) => `
      <div class="cart-line">
        <div>
          <strong>${item.product_name}</strong>
          <div class="muted" style="font-size:.8rem">
            ${item.size_name ? item.size_name + ' · ' : ''}${item.quantity}×
            ${(item.addons || []).map(a => a.name).join('، ')}
          </div>
        </div>
        <div style="text-align:left">
          <div>${item.unit_price > 0 ? money(item.unit_price * item.quantity) : '&nbsp;'}</div>
          <button class="btn btn-sm btn-ghost" data-remove="${idx}">×</button>
        </div>
      </div>
    `).join('');
    $$('[data-remove]', list).forEach(btn => {
      btn.addEventListener('click', () => {
        state.cart.splice(+btn.dataset.remove, 1);
        save();
      });
    });
  }

  function openDrawer() {
    drawerBg.classList.add('show');
    drawer.classList.add('open');
  }
  function closeDrawer() {
    drawerBg.classList.remove('show');
    drawer.classList.remove('open');
  }

  function calcModalPrice() {
    if (!state.product) return 0;
    if (!state.product.has_price && !(state.product.base_price > 0)) {
      let extra = 0;
      if (state.sizeId) {
        const size = state.product.sizes.find(s => s.id === state.sizeId);
        if (size) extra += size.price_modifier;
      }
      state.addonIds.forEach(id => {
        const a = state.product.addons.find(x => x.id === id);
        if (a) extra += a.price;
      });
      return extra * state.qty;
    }
    let p = state.product.base_price || 0;
    if (state.sizeId) {
      const size = state.product.sizes.find(s => s.id === state.sizeId);
      if (size) p += size.price_modifier;
    }
    state.addonIds.forEach(id => {
      const a = state.product.addons.find(x => x.id === id);
      if (a) p += a.price;
    });
    return p * state.qty;
  }

  function renderModal() {
    const p = state.product;
    if (!p) return;
    let sizesHtml = '';
    if (customization && p.sizes.length) {
      sizesHtml = `<div class="muted">${tSize}</div><div class="option-row" id="sizeRow">
        ${p.sizes.map(s => `<button type="button" class="option-chip ${state.sizeId === s.id ? 'active' : ''}" data-size="${s.id}">
          ${s.name}${s.price_modifier ? ' (+' + money(s.price_modifier) + ')' : ''}
        </button>`).join('')}
      </div>`;
    }
    let addonsHtml = '';
    if (customization && p.addons.length) {
      addonsHtml = `<div class="muted">${tAddons}</div><div class="option-row" id="addonRow">
        ${p.addons.map(a => `<button type="button" class="option-chip ${state.addonIds.includes(a.id) ? 'active' : ''}" data-addon="${a.id}">
          ${a.name} (+${money(a.price)})
        </button>`).join('')}
      </div>`;
    }
    const total = calcModalPrice();
    const btnLabel = total > 0 ? `${tAdd} · ${money(total)}` : tAdd;
    modal.innerHTML = `
      <img src="${p.image}" alt="" style="width:100%;border-radius:12px;aspect-ratio:1.6;object-fit:cover;margin-bottom:.75rem">
      <h2>${p.name}</h2>
      ${p.description ? `<p class="muted">${p.description}</p>` : ''}
      ${sizesHtml}${addonsHtml}
      <div class="qty-row">
        <button type="button" id="qtyMinus">−</button>
        <strong id="qtyVal">${state.qty}</strong>
        <button type="button" id="qtyPlus">+</button>
      </div>
      <button class="btn btn-primary btn-block" id="addToCart">${btnLabel}</button>
      <button class="btn btn-ghost btn-block" style="margin-top:.5rem" id="closeModal">${tClose}</button>
    `;
    $$('[data-size]', modal).forEach(b => b.addEventListener('click', () => {
      state.sizeId = +b.dataset.size; renderModal();
    }));
    $$('[data-addon]', modal).forEach(b => b.addEventListener('click', () => {
      const id = +b.dataset.addon;
      if (state.addonIds.includes(id)) state.addonIds = state.addonIds.filter(x => x !== id);
      else state.addonIds.push(id);
      renderModal();
    }));
    $('#qtyMinus').onclick = () => { state.qty = Math.max(1, state.qty - 1); renderModal(); };
    $('#qtyPlus').onclick = () => { state.qty += 1; renderModal(); };
    $('#closeModal').onclick = closeModal;
    $('#addToCart').onclick = () => {
      const lineTotal = calcModalPrice();
      const unit = lineTotal / state.qty;
      const size = p.sizes.find(s => s.id === state.sizeId);
      const addons = p.addons.filter(a => state.addonIds.includes(a.id)).map(a => ({ name: a.name, price: a.price }));
      state.cart.push({
        product_id: p.id,
        product_name: p.name,
        size_id: state.sizeId,
        size_name: size ? size.name : null,
        addon_ids: [...state.addonIds],
        addons,
        unit_price: unit,
        quantity: state.qty,
      });
      save();
      closeModal();
      openDrawer();
    };
  }

  function openModal() {
    modalBg.classList.add('show');
    renderModal();
  }
  function closeModal() {
    modalBg.classList.remove('show');
    state.product = null;
  }

  async function openProduct(id) {
    if (!ordering) {
      // still allow viewing details
    }
    const res = await fetch(`${apiBase}/api/product/${id}`);
    const data = await res.json();
    if (!data.ok) return alert(data.error || 'Error');
    if (!ordering) {
      state.product = data.product;
      state.sizeId = data.product.sizes[0]?.id || null;
      state.addonIds = [];
      state.qty = 1;
      openModal();
      // hide add if not ordering - simplify: just show info
      return;
    }
    if (!customization || (!data.product.sizes.length && !data.product.addons.length)) {
      const p = data.product;
      state.cart.push({
        product_id: p.id,
        product_name: p.name,
        size_id: null,
        size_name: null,
        addon_ids: [],
        addons: [],
        unit_price: p.has_price ? (p.base_price || 0) : 0,
        quantity: 1,
      });
      save();
      openDrawer();
      return;
    }
    state.product = data.product;
    state.sizeId = data.product.sizes[0]?.id || null;
    state.addonIds = [];
    state.qty = 1;
    openModal();
  }

  $$('[data-product]').forEach(el => {
    el.addEventListener('click', () => openProduct(+el.dataset.product));
  });

  $$('.cat-chip').forEach(chip => {
    chip.addEventListener('click', () => {
      $$('.cat-chip').forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      const id = chip.dataset.cat;
      $$('.cat-section').forEach(sec => {
        sec.style.display = (!id || sec.dataset.cat === id) ? '' : 'none';
      });
    });
  });

  cartBar?.addEventListener('click', openDrawer);
  drawerBg?.addEventListener('click', closeDrawer);
  $('#closeDrawer')?.addEventListener('click', closeDrawer);
  modalBg?.addEventListener('click', (e) => { if (e.target === modalBg) closeModal(); });

  async function placeInstant() {
    const btn = $('#goCheckout');
    if (btn) { btn.disabled = true; btn.textContent = '...'; }
    const fd = new FormData();
    fd.set('_csrf', document.body.dataset.csrf || '');
    fd.set('instant', '1');
    fd.set('pay_method', 'cafe');
    if (document.body.dataset.tableId) {
      fd.set('table_id', document.body.dataset.tableId);
    }
    fd.set('cart', JSON.stringify(state.cart.map(i => ({
      product_id: i.product_id,
      size_id: i.size_id,
      addon_ids: i.addon_ids || [],
      quantity: i.quantity,
    }))));
    try {
      const res = await fetch(document.body.dataset.placeUrl, {
        method: 'POST',
        body: fd,
        headers: { Accept: 'application/json' },
      });
      const data = await res.json();
      if (!data.ok) {
        if (data.need_checkout) {
          localStorage.setItem('ca_cart', JSON.stringify(state.cart));
          location.href = document.body.dataset.checkoutUrl;
          return;
        }
        alert(data.error || 'Error');
        if (btn) { btn.disabled = false; btn.textContent = btn.dataset.label || 'OK'; }
        return;
      }
      localStorage.removeItem('ca_cart');
      location.href = data.redirect;
    } catch (e) {
      alert('Network error');
      if (btn) { btn.disabled = false; }
    }
  }

  $('#goCheckout')?.addEventListener('click', () => {
    if (!state.cart.length) return;
    localStorage.setItem('ca_cart', JSON.stringify(state.cart));

    const needPhone = document.body.dataset.needPhone === '1';
    const onlinePay = document.body.dataset.onlinePay === '1';
    const tableRequired = document.body.dataset.tableRequired === '1';
    const tableId = document.body.dataset.tableId || '';

    // Shortest path: no phone + no online payment (+ table known if required)
    if (!needPhone && !onlinePay && (!tableRequired || tableId)) {
      placeInstant();
      return;
    }

    location.href = document.body.dataset.checkoutUrl;
  });

  const goBtn = $('#goCheckout');
  if (goBtn) goBtn.dataset.label = goBtn.textContent;

  renderCart();
})();
