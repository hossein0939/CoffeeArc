(() => {
  function enableSortable(list) {
    if (!list) return;
    let dragEl = null;

    list.querySelectorAll('.sort-item').forEach((item) => {
      item.setAttribute('draggable', 'true');
      const handle = item.querySelector('.drag-handle');
      if (handle) {
        handle.addEventListener('mousedown', () => { item.dataset.canDrag = '1'; });
        handle.addEventListener('mouseup', () => { item.dataset.canDrag = '0'; });
        handle.addEventListener('mouseleave', () => { item.dataset.canDrag = '0'; });
      } else {
        item.dataset.canDrag = '1';
      }

      item.addEventListener('dragstart', (e) => {
        if (handle && item.dataset.canDrag !== '1') {
          e.preventDefault();
          return;
        }
        dragEl = item;
        item.classList.add('sortable-ghost');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', item.dataset.id || '');
      });
      item.addEventListener('dragend', () => {
        item.classList.remove('sortable-ghost');
        dragEl = null;
        list.querySelectorAll('.sort-item').forEach((i) => i.classList.remove('drag-over'));
        if (typeof list._onSortEnd === 'function') list._onSortEnd();
      });
      item.addEventListener('dragover', (e) => {
        e.preventDefault();
        const target = e.currentTarget;
        if (!dragEl || dragEl === target) return;
        const rect = target.getBoundingClientRect();
        const before = (e.clientY - rect.top) < rect.height / 2;
        list.insertBefore(dragEl, before ? target : target.nextSibling);
      });
    });
  }

  window.CoffeeArcSortable = {
    create(el, opts = {}) {
      enableSortable(el);
      el._onSortEnd = opts.onEnd || null;
    }
  };
})();
