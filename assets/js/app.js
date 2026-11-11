document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.modal').forEach((modal) => {
    modal.addEventListener('show.bs.modal', () => {
      if (modal.parentElement !== document.body) document.body.append(modal);
    });
  });

  const sidebar = document.getElementById('sidebar');
  const toggle = document.getElementById('sidebarToggle');
  toggle?.addEventListener('click', () => sidebar?.classList.toggle('show'));
  document.addEventListener('click', (event) => {
    if (window.innerWidth < 992 && sidebar?.classList.contains('show') && !sidebar.contains(event.target) && !toggle?.contains(event.target)) sidebar.classList.remove('show');
  });

  document.querySelectorAll('[data-toggle-password]').forEach((button) => {
    button.addEventListener('click', () => {
      const input = document.getElementById(button.dataset.togglePassword);
      if (!input) return;
      input.type = input.type === 'password' ? 'text' : 'password';
      button.innerHTML = input.type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
    });
  });

  document.querySelectorAll('.app-alert').forEach((alert) => setTimeout(() => {
    if (window.bootstrap && alert.isConnected) bootstrap.Alert.getOrCreateInstance(alert).close();
  }, 5500));

  // Dashboard charts
  if (window.WM_DASHBOARD && window.Chart) {
    const d = window.WM_DASHBOARD;
    const salesCanvas = document.getElementById('salesChart');
    if (salesCanvas) new Chart(salesCanvas, {
      type: 'line', data: { labels: d.labels, datasets: [{ label: 'Penjualan', data: d.sales, borderColor: '#198754', backgroundColor: 'rgba(25,135,84,.12)', fill: true, tension: .38, borderWidth: 2.5, pointRadius: 3, pointHoverRadius: 5 }] },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' Rp ' + Number(ctx.raw).toLocaleString('id-ID') } } }, scales: { y: { beginAtZero: true, grid: { color: '#edf0ee' }, ticks: { callback: value => value >= 1000000 ? 'Rp ' + (value/1000000).toFixed(1) + 'jt' : 'Rp ' + (value/1000).toFixed(0) + 'rb' } }, x: { grid: { display: false } } } }
    });
    const categoryCanvas = document.getElementById('categoryChart');
    if (categoryCanvas) {
      const palette = ['#198754','#f3a847','#5578d8','#45a6a0','#9c77ca','#e47d7d','#7c9293'];
      new Chart(categoryCanvas, { type: 'doughnut', data: { labels: d.categories, datasets: [{ data: d.counts, backgroundColor: palette, borderWidth: 0, hoverOffset: 5 }] }, options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { display: false } } } });
      const legend = document.getElementById('categoryLegend');
      if (legend) legend.innerHTML = d.categories.map((name,i) => `<span><i style="background:${palette[i%palette.length]}"></i>${escapeHtml(name)} <b>${Number(d.counts[i]||0)}</b></span>`).join('');
    }
  }

  // POS cart
  const productGrid = document.getElementById('posProducts');
  if (productGrid) {
    const cart = new Map();
    const search = document.getElementById('posSearch');
    const category = document.getElementById('posCategory');
    const cartItems = document.getElementById('cartItems');
    const cartCount = document.getElementById('cartCount');
    const subtotalEl = document.getElementById('cartSubtotal');
    const totalEl = document.getElementById('cartTotal');
    const cartJson = document.getElementById('cartJson');
    const checkoutButton = document.getElementById('checkoutButton');
    const formatMoney = value => 'Rp ' + Number(value).toLocaleString('id-ID');

    const filterProducts = () => {
      const term = (search?.value || '').toLowerCase().trim();
      const cat = category?.value || 'all';
      productGrid.querySelectorAll('.pos-product-card').forEach(card => {
        const matchesText = (card.dataset.search || '').includes(term);
        const matchesCategory = cat === 'all' || card.dataset.category === cat;
        card.hidden = !(matchesText && matchesCategory);
      });
    };
    search?.addEventListener('input', filterProducts);
    category?.addEventListener('change', filterProducts);

    const renderCart = () => {
      let count = 0, total = 0;
      const rows = [];
      cart.forEach((item,id) => { count += item.qty; total += item.qty * item.price; rows.push({id:Number(id),qty:item.qty}); });
      cartCount.textContent = count;
      subtotalEl.textContent = formatMoney(total);
      totalEl.textContent = formatMoney(total);
      cartJson.value = JSON.stringify(rows);
      checkoutButton.disabled = count === 0;
      if (!cart.size) {
        cartItems.innerHTML = '<div class="cart-empty"><i class="bi bi-basket"></i><b>Keranjang masih kosong</b><span>Pilih produk di sebelah kiri untuk memulai transaksi.</span></div>';
        return;
      }
      cartItems.innerHTML = Array.from(cart.entries()).map(([id,item]) => `
        <div class="cart-item-row">
          <div class="cart-item-icon"><i class="bi bi-box"></i></div>
          <div class="cart-item-info"><b>${escapeHtml(item.name)}</b><small>${formatMoney(item.price)} / ${escapeHtml(item.unit)}</small><div class="qty-control"><button type="button" data-cart-action="minus" data-id="${id}" aria-label="Kurangi">−</button><span>${item.qty}</span><button type="button" data-cart-action="plus" data-id="${id}" aria-label="Tambah">+</button></div></div>
          <div class="cart-item-total"><b>${formatMoney(item.price * item.qty)}</b><button type="button" class="remove-cart" data-cart-action="remove" data-id="${id}" aria-label="Hapus item"><i class="bi bi-x-lg"></i></button></div>
        </div>`).join('');
    };

    productGrid.addEventListener('click', event => {
      const card = event.target.closest('.pos-product-card');
      if (!card) return;
      const id = card.dataset.productId;
      const stock = Number(card.dataset.stock || 0);
      const existing = cart.get(id);
      if (existing && existing.qty >= stock) { window.alert('Jumlah di keranjang sudah mencapai stok tersedia.'); return; }
      if (existing) existing.qty++;
      else cart.set(id, { name: card.querySelector('.pos-product-name')?.textContent || '', price: Number(card.dataset.price), qty: 1, stock, unit: card.dataset.unit || 'pcs' });
      renderCart();
      card.classList.add('added'); setTimeout(() => card.classList.remove('added'), 350);
    });
    cartItems.addEventListener('click', event => {
      const button = event.target.closest('[data-cart-action]'); if (!button) return;
      const id = button.dataset.id, item = cart.get(id); if (!item) return;
      const action = button.dataset.cartAction;
      if (action === 'plus' && item.qty < item.stock) item.qty++;
      else if (action === 'plus') window.alert('Stok tidak mencukupi.');
      else if (action === 'minus') { item.qty--; if (item.qty <= 0) cart.delete(id); }
      else if (action === 'remove') cart.delete(id);
      renderCart();
    });
    document.getElementById('clearCart')?.addEventListener('click', () => { cart.clear(); renderCart(); });
    document.getElementById('checkoutForm')?.addEventListener('submit', event => {
      if (!cart.size) { event.preventDefault(); return; }
      const method = document.getElementById('paymentMethod')?.value;
      const cash = Number(document.getElementById('cashAmount')?.value || 0);
      let subtotal = 0; cart.forEach(i => subtotal += i.price * i.qty);
      if (method === 'tunai' && cash < subtotal) { event.preventDefault(); window.alert('Jumlah uang yang diterima masih kurang.'); }
      else if (!window.confirm('Proses transaksi sebesar ' + formatMoney(subtotal) + '?')) event.preventDefault();
    });
    renderCart();
  }

  const paymentMethod = document.getElementById('paymentMethod');
  const cashField = document.getElementById('cashField');
  const paymentHint = document.getElementById('paymentHint');
  const customerType = document.getElementById('customerType');
  const memberCustomerField = document.getElementById('memberCustomerField');
  const memberCustomer = document.getElementById('memberCustomer');
  const updateCustomerType = () => {
    const isMember = customerType?.value === 'member';
    if (memberCustomerField) memberCustomerField.hidden = !isMember;
    if (memberCustomer) {
      memberCustomer.disabled = !isMember;
      memberCustomer.required = isMember;
    }
  };
  customerType?.addEventListener('change', updateCustomerType);
  updateCustomerType();
  const updatePayment = () => {
    if (!paymentMethod) return;
    const cash = paymentMethod.value === 'tunai';
    if (cashField) cashField.hidden = !cash;
    if (!cash && customerType && customerType.value !== 'member') {
      customerType.value = 'member';
      updateCustomerType();
    }
    if (paymentHint) paymentHint.innerHTML = cash ? '<i class="bi bi-info-circle me-1"></i>Masukkan uang yang diterima dari pelanggan.' : '<i class="bi bi-info-circle me-1"></i>Saldo pelanggan akan dipotong secara simulasi. Pastikan pelanggan terpilih dan saldo mencukupi.';
  };
  paymentMethod?.addEventListener('change', updatePayment); updatePayment();

  document.querySelectorAll('[data-product-image-preview]').forEach((preview) => {
    const form = preview.closest('form');
    const fileInput = form?.querySelector('[data-image-file-input]');
    const urlInput = form?.querySelector('[data-image-url-input]') || form?.querySelector('input[name="gambar"]');
    let previewUrl = null;
    const showPreview = (src) => {
      preview.replaceChildren();
      if (!src) {
        const icon = document.createElement('i');
        icon.className = 'bi bi-image';
        preview.append(icon);
        return;
      }
      const image = document.createElement('img');
      image.alt = 'Preview gambar produk';
      image.src = src;
      image.onerror = () => showPreview('');
      preview.append(image);
    };
    fileInput?.addEventListener('change', () => {
      if (previewUrl) URL.revokeObjectURL(previewUrl);
      previewUrl = fileInput.files?.[0] ? URL.createObjectURL(fileInput.files[0]) : null;
      if (previewUrl) showPreview(previewUrl);
      else showPreview(urlInput?.value.trim() || '');
    });
    urlInput?.addEventListener('input', () => {
      if (fileInput?.files?.length) fileInput.value = '';
      if (previewUrl) URL.revokeObjectURL(previewUrl);
      previewUrl = null;
      showPreview(urlInput.value.trim());
    });
  });

  // Add multiple stock receipt rows
  const addStockLine = document.getElementById('addStockLine');
  addStockLine?.addEventListener('click', () => {
    const container = document.getElementById('stockLines');
    const first = container?.querySelector('.stock-line-row');
    if (!container || !first) return;
    const clone = first.cloneNode(true);
    clone.querySelectorAll('select').forEach(el => el.value = '');
    clone.querySelectorAll('input').forEach(el => el.value = el.name === 'jumlah[]' ? '1' : '0');
    const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'btn btn-sm btn-light text-danger mt-2 remove-stock-line'; remove.innerHTML = '<i class="bi bi-x-circle me-1"></i>Hapus baris';
    clone.appendChild(remove); container.appendChild(clone);
  });
  document.getElementById('stockLines')?.addEventListener('click', e => {
    if (e.target.closest('.remove-stock-line')) e.target.closest('.stock-line-row')?.remove();
  });
});

function escapeHtml(value) {
  return String(value).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
}
