(() => {
  document.querySelectorAll('.qr-local[data-qr]').forEach((el) => {
    const text = el.getAttribute('data-qr');
    if (!text || typeof QRCode === 'undefined') return;
    el.innerHTML = '';
    // qrcodejs API
    new QRCode(el, {
      text,
      width: 120,
      height: 120,
      correctLevel: QRCode.CorrectLevel.M,
    });
  });
})();
