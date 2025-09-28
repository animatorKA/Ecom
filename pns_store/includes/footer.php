<?php
// includes/footer.php - full-width footer with centered inner content
?>
  <!-- Footer -->
  <footer class="footer mt-auto pt-6 pb-4">
    <div class="footer-pattern-overlay"></div>
    <div class="container position-relative">
      <div class="row g-5">
        <!-- Brand -->
        <div class="col-lg-5">
          <div class="footer-brand mb-4">
            <h4 class="text-white fw-bold mb-3 display-6">PNS Store</h4>
            <p class="text-white opacity-90 mb-4 lead">Official student store — bringing quality education essentials closer to you.</p>
          </div>
          <div class="social-links mb-4">
            <a href="#" class="btn btn-outline-light rounded-circle me-2 btn-floating" aria-label="Facebook">
              <i class="bi bi-facebook"></i>
            </a>
            <a href="#" class="btn btn-outline-light rounded-circle me-2 btn-floating" aria-label="Instagram">
              <i class="bi bi-instagram"></i>
            </a>
            <a href="#" class="btn btn-outline-light rounded-circle btn-floating" aria-label="Twitter">
              <i class="bi bi-twitter-x"></i>
            </a>
          </div>
        </div>
        
        <!-- Quick Links -->
        <div class="col-sm-6 col-lg-3">
          <h6 class="text-white fw-semibold mb-4 text-uppercase tracking-wide">Navigation</h6>
          <div class="d-grid gap-2">
            <a href="/pns_store/index.php" class="btn btn-outline-light">
              <span>Home</span>
              <i class="bi bi-arrow-right ms-2 opacity-75"></i>
            </a>
            <a href="/pns_store/products.php" class="btn btn-outline-light">
              <span>Products</span>
              <i class="bi bi-arrow-right ms-2 opacity-75"></i>
            </a>
            <a href="/pns_store/contact.php" class="btn btn-outline-light">
              <span>Contact</span>
              <i class="bi bi-arrow-right ms-2 opacity-75"></i>
            </a>
          </div>
        </div>
        
        <!-- Contact -->
        <div class="col-sm-6 col-lg-4">
          <h6 class="text-white fw-semibold mb-4 text-uppercase tracking-wide">Get in Touch</h6>
          <ul class="list-unstyled footer-contact">
            <li class="d-flex align-items-center mb-3">
              <div class="icon-box bg-white bg-opacity-10 rounded-circle me-3 p-2">
                <i class="bi bi-geo-alt text-white"></i>
              </div>
              <div class="text-white">Palawan National School<br>Puerto Princesa City</div>
            </li>
            <li class="d-flex align-items-center mb-3">
              <div class="icon-box bg-white bg-opacity-10 rounded-circle me-3 p-2">
                <i class="bi bi-envelope text-white"></i>
              </div>
              <a href="mailto:store@palawannationalschool.edu.ph" class="text-white text-decoration-none">
                store@palawannationalschool.edu.ph
              </a>
            </li>
            <li class="d-flex align-items-center">
              <div class="icon-box bg-white bg-opacity-10 rounded-circle me-3 p-2">
                <i class="bi bi-clock text-white"></i>
              </div>
              <div class="text-white">Mon - Fri: 7:00 AM - 4:00 PM</div>
            </li>
          </ul>
        </div>
      </div>
      
      <hr class="my-5 border-white border-opacity-10">
      
      <!-- Copyright -->
      <div class="row align-items-center">
        <div class="col-md">
          <p class="text-muted mb-md-0">© <?= date('Y') ?> Palawan National School — All rights reserved.</p>
        </div>
        <div class="col-md-auto">
          <ul class="list-inline mb-0 small">
            <li class="list-inline-item">
              <a href="#" class="text-muted text-decoration-none">Privacy Policy</a>
            </li>
            <li class="list-inline-item">
              <span class="text-muted">•</span>
            </li>
            <li class="list-inline-item">
              <a href="#" class="text-muted text-decoration-none">Terms of Use</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </footer>

  <!-- Bootstrap and other scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/pns_store/assets/js/main.js"></script>
  
  <!-- Custom script for floating cart -->
  <script>
    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(el => new bootstrap.Tooltip(el));
    
    // Initialize popovers
    const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
    popovers.forEach(el => new bootstrap.Popover(el));
  </script>

  <!-- Floating Cart Button -->
  <a href="/pns_store/cart.php" class="floating-cart">
    <i class="bi bi-cart3"></i>
    <?php if ($cart_count > 0): ?>
    <span class="badge rounded-pill">
      <?= (int)$cart_count ?>
      <span class="visually-hidden">items in cart</span>
    </span>
    <?php endif; ?>
  </a>

  <!-- SCRIPTS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <script src="/pns_store/assets/js/main.js"></script>

  <script>
    // Show/hide floating cart based on scroll position
    document.addEventListener('DOMContentLoaded', function() {
      const floatingCart = document.querySelector('.floating-cart');
      if (!floatingCart) return;

      let lastScrollTop = 0;
      let isVisible = false;

      function toggleFloatingCart() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const shouldShow = scrollTop > 300;

        if (shouldShow && !isVisible) {
          floatingCart.classList.add('show');
          isVisible = true;
        } else if (!shouldShow && isVisible) {
          floatingCart.classList.remove('show');
          isVisible = false;
        }
      }

      // Throttle scroll event
      let ticking = false;
      document.addEventListener('scroll', function() {
        if (!ticking) {
          window.requestAnimationFrame(function() {
            toggleFloatingCart();
            ticking = false;
          });
          ticking = true;
        }
      });

      // Update cart count via AJAX
      window.updateFloatingCartCount = function(count) {
        const badge = floatingCart.querySelector('.badge');
        if (count > 0) {
          if (!badge) {
            const newBadge = document.createElement('span');
            newBadge.className = 'badge rounded-pill';
            newBadge.innerHTML = count + '<span class="visually-hidden">items in cart</span>';
            floatingCart.appendChild(newBadge);
          } else {
            badge.innerHTML = count + '<span class="visually-hidden">items in cart</span>';
          }
        } else if (badge) {
          badge.remove();
        }
      };
    });
  </script>

  <script>
  (function(){
    // Make sure overlays appear above footer.
    function ensureZ(){ document.querySelectorAll('.offcanvas, .modal, .toast').forEach(el=>el.style.zIndex='14000'); }
    document.addEventListener('DOMContentLoaded', ensureZ);
    const ro = new MutationObserver(ensureZ);
    ro.observe(document.body, { childList: true, subtree: true });

    // Focus first control when offcanvas opens
    document.body.addEventListener('shown.bs.offcanvas', function(e){
      try {
        const off = e.target;
        const focusable = off.querySelector('button, a, input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (focusable) focusable.focus();
      } catch(e){}
    });
  })();
  </script>

  </body>
  </html>
