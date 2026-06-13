// Theme Manager & Shopping Cart Engine
document.addEventListener('DOMContentLoaded', () => {
  // 1. Dark Mode Toggle
  const themeToggle = document.getElementById('theme-toggle');
  if (themeToggle) {
    const currentTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', currentTheme);
    updateThemeIcon(currentTheme);

    themeToggle.addEventListener('click', () => {
      const theme = document.documentElement.getAttribute('data-theme');
      const newTheme = theme === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', newTheme);
      localStorage.setItem('theme', newTheme);
      updateThemeIcon(newTheme);
    });
  }

  function updateThemeIcon(theme) {
    const icon = themeToggle.querySelector('svg');
    if (icon) {
      if (theme === 'dark') {
        // Moon icon / Sun representation
        icon.innerHTML = '<path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m11.314 11.314l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z"></path>';
      } else {
        // Moon outline
        icon.innerHTML = '<path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>';
      }
    }
  }

  // Header Scroll Effect
  const header = document.querySelector('header');
  window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  });

  // 2. Shopping Cart Engine
  let cart = JSON.parse(localStorage.getItem('salamandre_cart')) || [];
  const cartBtn = document.getElementById('cart-btn');
  const cartDrawer = document.getElementById('cart-drawer');
  const cartOverlay = document.getElementById('cart-overlay');
  const closeCartBtn = document.getElementById('close-cart');
  const cartItemsContainer = document.getElementById('cart-items-container');
  const cartSubtotalEl = document.getElementById('cart-subtotal');
  const cartCountEl = document.querySelector('.cart-count');

  if (cartBtn && cartDrawer && cartOverlay && closeCartBtn) {
    // Open Cart
    cartBtn.addEventListener('click', () => {
      renderCart();
      cartDrawer.classList.add('open');
      cartOverlay.classList.add('active');
    });

    // Close Cart
    const closeCart = () => {
      cartDrawer.classList.remove('open');
      cartOverlay.classList.remove('active');
    };

    closeCartBtn.addEventListener('click', closeCart);
    cartOverlay.addEventListener('click', closeCart);
  }

  // Expose function to add items to cart globally
  window.addToCart = function(product) {
    const existing = cart.find(item => item.id === product.id);
    if (existing) {
      existing.quantity += 1;
    } else {
      cart.push({
        id: product.id,
        title: product.title,
        price: parseFloat(product.price),
        image: product.image,
        quantity: 1
      });
    }
    saveCart();
    updateCartBadge();
    animateCartBadge();
    
    // Automatically open drawer
    if (cartDrawer && cartOverlay) {
      renderCart();
      cartDrawer.classList.add('open');
      cartOverlay.classList.add('active');
    }
  };

  function removeCartItem(id) {
    cart = cart.filter(item => item.id !== id);
    saveCart();
    updateCartBadge();
    renderCart();
  }

  function changeQuantity(id, change) {
    const item = cart.find(i => i.id === id);
    if (item) {
      item.quantity += change;
      if (item.quantity <= 0) {
        removeCartItem(id);
        return;
      }
      saveCart();
      updateCartBadge();
      renderCart();
    }
  }

  function saveCart() {
    localStorage.setItem('salamandre_cart', JSON.stringify(cart));
  }

  function updateCartBadge() {
    if (cartCountEl) {
      const count = cart.reduce((acc, item) => acc + item.quantity, 0);
      cartCountEl.textContent = count;
      cartCountEl.style.display = count > 0 ? 'flex' : 'none';
    }
  }

  function animateCartBadge() {
    if (cartCountEl) {
      cartCountEl.classList.add('bounce');
      setTimeout(() => {
        cartCountEl.classList.remove('bounce');
      }, 300);
    }
  }

  function renderCart() {
    if (!cartItemsContainer) return;
    
    if (cart.length === 0) {
      cartItemsContainer.innerHTML = `
        <div style="text-align: center; margin-top: 3rem; color: var(--text-muted);">
          <svg style="width: 50px; height: 50px; stroke: currentColor; fill: none; stroke-width: 1.5; margin-bottom: 1rem;" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
          </svg>
          <p>Votre panier est vide</p>
        </div>
      `;
      if (cartSubtotalEl) cartSubtotalEl.textContent = '0.00 $';
      return;
    }

    cartItemsContainer.innerHTML = cart.map(item => `
      <div class="cart-item">
        <img src="${item.image}" alt="${item.title}" class="cart-item-img">
        <div class="cart-item-info">
          <div class="cart-item-title">${item.title}</div>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem;">
            <div class="cart-item-price">${(item.price * item.quantity).toFixed(2)} $</div>
            <div style="display: flex; align-items: center; gap: 0.8rem; border: 1px solid var(--border-color); padding: 0.2rem 0.5rem;">
              <button onclick="changeQuantity('${item.id}', -1)" style="background: none; border: none; cursor: pointer; color: var(--text-primary); font-weight: bold;">-</button>
              <span>${item.quantity}</span>
              <button onclick="changeQuantity('${item.id}', 1)" style="background: none; border: none; cursor: pointer; color: var(--text-primary); font-weight: bold;">+</button>
            </div>
          </div>
        </div>
      </div>
    `).join('');

    const subtotal = cart.reduce((acc, item) => acc + (item.price * item.quantity), 0);
    if (cartSubtotalEl) cartSubtotalEl.textContent = `${subtotal.toFixed(2)} $`;
  }

  // Set global callbacks for quantities
  window.changeQuantity = changeQuantity;

  // Initialize count badge on load
  updateCartBadge();
});

// Helper for confetti animation (checkout success)
window.spawnConfetti = function() {
  const container = document.createElement('div');
  container.style.position = 'fixed';
  container.style.top = '0';
  container.style.left = '0';
  container.style.width = '100vw';
  container.style.height = '100vh';
  container.style.pointerEvents = 'none';
  container.style.zIndex = '9999';
  container.style.overflow = 'hidden';
  document.body.appendChild(container);

  const colors = ['#C5A880', '#8C2630', '#4A90E2', '#50E3C2', '#F5A623'];

  for (let i = 0; i < 100; i++) {
    const confetti = document.createElement('div');
    confetti.style.position = 'absolute';
    confetti.style.width = Math.random() * 10 + 5 + 'px';
    confetti.style.height = Math.random() * 15 + 5 + 'px';
    confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
    confetti.style.left = Math.random() * 100 + 'vw';
    confetti.style.top = '-20px';
    confetti.style.transform = `rotate(${Math.random() * 360}deg)`;
    container.appendChild(confetti);

    const animation = confetti.animate([
      { top: '-20px', transform: `rotate(0deg) translateX(0px)` },
      { top: '100vh', transform: `rotate(${Math.random() * 720}deg) translateX(${Math.random() * 100 - 50}px)` }
    ], {
      duration: Math.random() * 3000 + 2000,
      easing: 'cubic-bezier(0.1, 0.8, 0.3, 1)'
    });

    animation.onfinish = () => confetti.remove();
  }

  setTimeout(() => container.remove(), 5000);
};
