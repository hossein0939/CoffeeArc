(() => {
  const state = { cart: [], product: null, sizeId: null, addonIds: [], qty: 1 };
  const customization = document.body.dataset.customization === '1';
  const apiBase = document.body.dataset.apiBase || '';
  const money = (n) => new Intl.NumberFormat('fa-IR').format(n) + ' تومان';
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];

  function render() {
    const list = $('#posCartList');
    const total = state.cart.reduce((s, i) => s + i.unit_price * i.quantity, 0);
    $('#posTotal').textContent = money(total);
    if (!state.cart.length) {
      list.innerHTML = '<p class="muted">آیتمی اضافه نشده</p>';
      return;
    }
    list.innerHTML = state.cart.map((item, idx) => `
      <div class="cart-line">
        <div><strong>${item.product_name}</strong> × ${item.quantity}</div>
        <div>
          ${money(item.unit_price * item.quantity)}
          <button class="btn btn-sm btn-ghost" data-rm="${idx}">×</button>
        </div>
      </div>`).join('');
    $$('[data-rm]').forEach(b => b.onclick = () => { state.cart.splice(+b.dataset.rm, 1); render(); });
  }

  async function addProduct(id) {
    const res = await fetch(`${apiBase}/api/product/${id}`);
    const data = await res.json();
    if (!data.ok) return alert(data.error || 'خطا');
    const p = data.product;
    if (!customization || (!p.sizes.length && !p.addons.length)) {
      state.cart.push({
        product_id: p.id, product_name: p.name, size_id: null, size_name: null,
        addon_ids: [], addons: [], unit_price: p.base_price, quantity: 1,
      });
      render();
      return;
    }
    // simple prompt flow for POS speed
    let sizeId = p.sizes[0]?.id || null;
    if (p.sizes.length > 1) {
      const names = p.sizes.map((s, i) => `${i + 1}) ${s.name}`).join('\n');
      const pick = prompt('سایز را انتخاب کنید:\n' + names, '1');
      const idx = Math.max(1, parseInt(pick || '1', 10)) - 1;
      sizeId = p.sizes[idx]?.id || sizeId;
    }
    const size = p.sizes.find(s => s.id === sizeId);
    let unit = p.base_price + (size?.price_modifier || 0);
    const addonIds = [];
    const addons = [];
    // skip multi-addon prompts for one-click feel; user can use customer menu for complex
    state.cart.push({
      product_id: p.id, product_name: p.name, size_id: sizeId, size_name: size?.name || null,
      addon_ids: addonIds, addons, unit_price: unit, quantity: 1,
    });
    render();
  }

  $$('#posGrid [data-product]').forEach(el => el.addEventListener('click', () => addProduct(+el.dataset.product)));

  $$('#posCats .cat-chip').forEach(chip => {
    chip.addEventListener('click', () => {
      $$('#posCats .cat-chip').forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      const cat = chip.dataset.cat || '';
      $$('#posGrid [data-product]').forEach(card => {
        card.hidden = cat !== '' && card.dataset.cat !== cat;
      });
    });
  });

  $('#posSubmit')?.addEventListener('click', async () => {
    if (!state.cart.length) return alert('سبد خالی است');
    const fd = new FormData();
    fd.set('_csrf', document.body.dataset.csrf);
    fd.set('cart', JSON.stringify(state.cart.map(i => ({
      product_id: i.product_id, size_id: i.size_id, addon_ids: i.addon_ids || [], quantity: i.quantity,
    }))));
    fd.set('table_id', $('#posTable')?.value || '');
    fd.set('pay_method', 'cafe');
    fd.set('mark_paid', $('#markPaid')?.checked ? '1' : '0');
    fd.set('customer_phone', $('#posPhone')?.value || '');
    fd.set('discount_code', $('#posDiscount')?.value || '');
    const res = await fetch(document.body.dataset.posUrl, { method: 'POST', body: fd, headers: { Accept: 'application/json' } });
    const data = await res.json();
    if (!data.ok) return alert(data.error || 'خطا');
    location.href = data.redirect || document.body.dataset.adminUrl;
  });

  render();
})();
