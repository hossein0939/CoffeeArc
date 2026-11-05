(() => {
  const cart = JSON.parse(localStorage.getItem('ca_cart') || '[]');
  const form = document.getElementById('checkoutForm');
  const btn = document.getElementById('submitOrder');

  if (!cart.length) {
    location.href = document.body.dataset.menuUrl || '/m';
    return;
  }

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!btn) return;
    btn.disabled = true;
    const original = btn.textContent;
    btn.textContent = '...';

    const fd = new FormData(form);
    fd.set('cart', JSON.stringify(cart.map(i => ({
      product_id: i.product_id,
      size_id: i.size_id,
      addon_ids: i.addon_ids || [],
      quantity: i.quantity,
      notes: i.notes || null,
    }))));

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        body: fd,
        headers: { Accept: 'application/json' },
      });
      const data = await res.json();
      if (!data.ok) {
        alert(data.error || 'Error');
        btn.disabled = false;
        btn.textContent = original;
        return;
      }
      localStorage.removeItem('ca_cart');
      location.href = data.redirect;
    } catch (err) {
      alert('Network error');
      btn.disabled = false;
      btn.textContent = original;
    }
  });
})();
