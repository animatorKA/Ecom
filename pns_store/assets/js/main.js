// assets/js/main.js - global interactive site JS (header + UI)
// Overwrites previous main.js. Includes:
// - bootstrap carousel init
// - reveal on scroll
// - hover tilt for product cards
// - add-to-cart fly-to-cart animation + toast + bounce
// - navbar shrink on scroll
// - keyboard shortcut to focus search (/)
// - small enhancements (button ripple)

document.addEventListener('DOMContentLoaded', function(){

  /* ---------- Bootstrap carousel init (if present) ---------- */
  try {
    const carouselEl = document.querySelector('#heroCarousel.carousel');
    if (carouselEl && window.bootstrap && bootstrap.Carousel) {
      new bootstrap.Carousel(carouselEl, { interval: 4500, pause: 'hover' });
    }
  } catch (e){ console.warn('Carousel init error', e); }

  /* ---------- Header shrink on scroll ---------- */
  const mainNav = document.getElementById('mainNav');
  const shrinkOn = 80; // px scrolled before shrink
  function onScrollHeader(){
    if (!mainNav) return;
    if (window.scrollY > shrinkOn) mainNav.classList.add('shrink');
    else mainNav.classList.remove('shrink');
  }
  window.addEventListener('scroll', onScrollHeader, { passive: true });
  onScrollHeader();

  /* ---------- keyboard shortcut: '/' focuses search ---------- */
  const navSearch = document.getElementById('navSearch');
  window.addEventListener('keydown', function(e){
    // ignore if typing in input
    const tag = (document.activeElement && document.activeElement.tagName) || '';
    if (tag === 'INPUT' || tag === 'TEXTAREA' || document.activeElement?.isContentEditable) return;
    if (e.key === '/') {
      e.preventDefault();
      if (navSearch) { navSearch.focus(); navSearch.select(); }
    }
  });

  /* ---------- Reveal on scroll ---------- */
  const reveals = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window && reveals.length) {
    const obs = new IntersectionObserver((entries, o) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('visible');
          o.unobserve(e.target);
        }
      });
    }, { threshold: 0.12 });
    reveals.forEach(el => obs.observe(el));
  } else {
    reveals.forEach(el => el.classList.add('visible'));
  }

  /* ---------- Hover tilt for product cards (small parallax) ---------- */
  document.querySelectorAll('.product-card').forEach(card => {
    const tilt = card.querySelector('.card-tilt') || card;
    card.addEventListener('mousemove', e => {
      const rect = card.getBoundingClientRect();
      const px = (e.clientX - rect.left) / rect.width;
      const py = (e.clientY - rect.top) / rect.height;
      const rx = (py - 0.5) * 6; // rotateX
      const ry = (px - 0.5) * -8; // rotateY
      tilt.style.transform = `perspective(800px) rotateX(${rx}deg) rotateY(${ry}deg)`;
    });
    card.addEventListener('mouseleave', () => {
      tilt.style.transform = '';
    });
  });

  /* ---------- Toast helper ---------- */
  function createToast(title, body){
    if (!document.getElementById('toastContainer')) return;
    if (!window.bootstrap || !bootstrap.Toast) { alert(title + '\n' + body); return; }
    const container = document.getElementById('toastContainer');
    const el = document.createElement('div');
    el.className = 'toast align-items-center text-bg-light border-0 mb-2';
    el.role = 'alert';
    el.style.minWidth = '220px';
    el.innerHTML = `<div class="d-flex"><div class="toast-body"><strong>${title}</strong><div style="font-size:.95rem;color:#333">${body}</div></div><button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
    container.appendChild(el);
    const t = new bootstrap.Toast(el, { delay: 1400 });
    t.show();
    el.addEventListener('hidden.bs.toast', ()=>el.remove());
  }

  /* ---------- Fly-to-cart animation ---------- */
  function flyToCart(imgSrc, startRect) {
    const cartBtn = document.querySelector('.floating-cart') || document.querySelector('#navCartBtn');
    if (!cartBtn) return;
    const cartRect = cartBtn.getBoundingClientRect();
    const img = document.createElement('img');
    img.src = imgSrc;
    img.style.position = 'fixed';
    img.style.left = `${startRect.left}px`;
    img.style.top = `${startRect.top}px`;
    img.style.width = `${startRect.width}px`;
    img.style.height = `${startRect.height}px`;
    img.style.borderRadius = '8px';
    img.style.zIndex = 99999;
    img.style.transition = 'transform .9s cubic-bezier(.2,.9,.3,1), opacity .9s ease';
    document.body.appendChild(img);
    const dx = cartRect.left + cartRect.width/2 - (startRect.left + startRect.width/2);
    const dy = cartRect.top + cartRect.height/2 - (startRect.top + startRect.height/2);
    requestAnimationFrame(()=> {
      img.style.transform = `translate(${dx}px, ${dy}px) scale(.18) rotate(18deg)`;
      img.style.opacity = '0.4';
    });
    setTimeout(()=> img.remove(), 900);
  }

  /* ---------- Update cart counters helper ---------- */
  function updateCartCountUI(n) {
    document.getElementById('navCartCount')?.textContent = n;
    document.getElementById('navCartCount')?.setAttribute('aria-label', `${n} items in cart`);
    document.getElementById('miniCartCount')?.textContent = n;
    document.getElementById('floatCartCount')?.textContent = n;
    document.querySelectorAll('.floating-cart span').forEach(s=>s.textContent = n);
  }

  /* ---------- Add-to-cart AJAX + animation ---------- */
  document.querySelectorAll('.add-btn[data-id]').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      btn.disabled = true; // Prevent double clicks
      
      try {
        const pid = btn.dataset.id;
        const card = btn.closest('.product-card');
        const imgEl = card?.querySelector('img') || document.querySelector('.product-image');
        const imgSrc = imgEl?.src;
        const startRect = imgEl ? imgEl.getBoundingClientRect() : { left: window.innerWidth/2, top: window.innerHeight/2, width:60, height:60 };
        const qtyInput = btn.closest('.card-footer')?.querySelector('.qty-input');
        const qty = Math.max(1, parseInt(qtyInput?.value || 1));

        const form = new FormData();
        form.append('product_id', pid);
        form.append('qty', qty);
        
        const res = await fetch('/pns_store/add_to_cart_ajax.php', { 
          method: 'POST', 
          body: form,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }
        
        const json = await res.json();
        
        if (json.success) {
          if (imgSrc) flyToCart(imgSrc, startRect);
          createToast('Success', 'Item added to cart');
          // Update cart count everywhere
          if (typeof json.cart_count === 'number') {
            updateCartCountUI(json.cart_count);
          }
          // bounce floating cart
          const fc = document.querySelector('.floating-cart');
          if (fc) {
            fc.classList.remove('bounce');
            void fc.offsetWidth;
            fc.classList.add('bounce');
            setTimeout(()=> fc.classList.remove('bounce'), 1000);
          }
          createToast('Added', json.message || 'Added to cart');
          updateCartCountUI(json.cart_count || 0);
          if (typeof refreshOffcanvasCart === 'function') refreshOffcanvasCart();
        } else {
          createToast('Error', json.message || 'Could not add item');
        }
      } catch (err) {
        console.error(err);
        createToast('Error', 'Network error');
      }
    });
  });

  /* ---------- Button ripple micro-interaction ---------- */
  document.querySelectorAll('.btn.ripple').forEach(btn=>{
    btn.addEventListener('click', function(){
      btn.classList.remove('animating');
      void btn.offsetWidth;
      btn.classList.add('animating');
      setTimeout(()=> btn.classList.remove('animating'), 600);
    });
  });

  /* ---------- Populate header datalist with suggestions (small client-side fallback)
     If you'd prefer server-sourced suggestions, we can add an endpoint that returns JSON.
     Here we attempt to collect product titles present on the page and insert into datalist.
  ---------- */
  (function populateDatalist(){
    try {
      const dl = document.getElementById('headerProdList');
      if (!dl) return;
      // gather product titles from page if present
      const titles = new Set();
      document.querySelectorAll('.product-card .card-body h5, .product-card .card-body h6, .product-card [data-title]').forEach(el=>{
        const t = el.dataset.title || el.textContent || el.innerText;
        if (t) titles.add(t.trim());
      });
      // also try to read product titles from a JSON blob if site supplies later
      titles.forEach(t => {
        const opt = document.createElement('option');
        opt.value = t;
        dl.appendChild(opt);
      });
    } catch(e){}
  })();

});
