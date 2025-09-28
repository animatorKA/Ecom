<?php
// includes/header.php - interactive header
require_once __DIR__ . '/../config.php';  // This includes session handling
require_once __DIR__ . '/page_utils.php';
require_once __DIR__ . '/auth.php';

// Check session status
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Quick cart count
$cart_count = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}

$site_title = 'Palawan National School Store';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once __DIR__ . '/partials/meta.php'; ?>
    
    <!-- Store-specific meta -->
    <meta name="theme-color" content="#198754">
    <meta name="description" content="Palawan National School Store - Student discounts on school supplies and organization merchandise">
    <title><?= htmlspecialchars($site_title) ?></title>
    
    <!-- PWA support -->
    <link rel="manifest" href="/pns_store/manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <!-- Store Styles -->
    <link rel="stylesheet" href="/pns_store/assets/css/style.css">
    <link rel="stylesheet" href="/pns_store/assets/css/floating-cart.css">
    <link rel="stylesheet" href="/pns_store/assets/css/header.css">
    
    <!-- Preload key assets -->
    <link rel="preload" as="image" href="/pns_store/assets/images/placeholder.png">
    
    <!-- Preconnect to external resources -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</head>
<body>
  <header class="site-header-wrap">
    <nav id="mainNav" class="navbar navbar-expand-lg navbar-dark">
      <div class="container">
        <a class="navbar-brand fw-bold text-white" href="/pns_store/index.php">
          PNS STORE
        </a>
        <div class="ms-auto d-flex align-items-center gap-3">
          <div class="navbar-nav">
            <a href="/pns_store/index.php" class="nav-link <?= isActive('index.php') ? 'active' : '' ?>">Home</a>
            <a href="/pns_store/products.php" class="nav-link <?= isActive(['products.php', 'product.php']) ? 'active' : '' ?>">Products</a>
            <a href="/pns_store/contact.php" class="nav-link <?= isActive('contact.php') ? 'active' : '' ?>">Contact</a>
            <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
              <a href="/pns_store/admin/dashboard.php" class="nav-link <?= isActive('admin') ? 'active' : '' ?>">Admin</a>
            <?php endif; ?>
            <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'org'): ?>
              <a href="/pns_store/org/dashboard.php" class="nav-link <?= isActive('org') ? 'active' : '' ?>">Organization</a>
            <?php endif; ?>
          </div>
          
          <form class="d-flex mx-3" role="search" method="get" action="/pns_store/products.php">
            <div class="input-group">
              <input type="text" name="q" class="form-control" placeholder="Search products" aria-label="Search products">
              <button class="btn btn-light" type="submit">
                <i class="bi bi-search"></i>
              </button>
            </div>
          </form>

          <div class="d-flex align-items-center gap-2">
            <div class="dropdown">
              <a href="#" class="btn btn-light d-flex align-items-center gap-2" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle"></i>
                <?php if (!empty($_SESSION['user_id'])): ?>
                  <span><?= htmlspecialchars($_SESSION['name'] ?? 'Account') ?></span>
                <?php else: ?>
                  <span>Account</span>
                <?php endif; ?>
                <i class="bi bi-chevron-down ms-1 opacity-75"></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <?php if (!empty($_SESSION['user_id'])): ?>
                  <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li>
                      <a class="dropdown-item d-flex align-items-center gap-2 <?= isActive('dashboard.php') ? 'active' : '' ?>" href="/pns_store/admin/dashboard.php">
                        <i class="bi bi-speedometer2 text-success"></i>
                        Admin Dashboard
                      </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                      <a class="dropdown-item-modern d-flex align-items-center gap-2 <?= isActive('products.php') ? 'active' : '' ?>" href="/pns_store/admin/products.php">
                        <i class="bi bi-box text-primary"></i>
                        Manage Products
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item-modern d-flex align-items-center gap-2 <?= isActive('categories.php') ? 'active' : '' ?>" href="/pns_store/admin/categories.php">
                        <i class="bi bi-tags text-warning"></i>
                        Manage Categories
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item-modern d-flex align-items-center gap-2 <?= isActive('organizations.php') ? 'active' : '' ?>" href="/pns_store/admin/organizations.php">
                        <i class="bi bi-buildings text-info"></i>
                        Manage Organizations
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item-modern d-flex align-items-center gap-2 <?= isActive('messages.php') ? 'active' : '' ?>" href="/pns_store/admin/messages.php">
                        <i class="bi bi-envelope text-warning"></i>
                        Messages
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item-modern d-flex align-items-center gap-2 <?= isActive('orders.php') ? 'active' : '' ?>" href="/pns_store/admin/orders.php">
                        <i class="bi bi-cart text-success"></i>
                        Manage Orders
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item-modern d-flex align-items-center gap-2 <?= isActive('users.php') ? 'active' : '' ?>" href="/pns_store/admin/users.php">
                        <i class="bi bi-people text-secondary"></i>
                        Manage Users
                      </a>
                    </li>
                  <?php elseif ($_SESSION['role'] === 'org'): ?>
                    <li>
                      <a class="dropdown-item-modern d-flex align-items-center gap-2 <?= isActive('dashboard.php') ? 'active' : '' ?>" href="/pns_store/org/dashboard.php">
                        <i class="bi bi-shop text-success"></i>
                        Organization Portal
                      </a>
                    </li>
                  <?php endif; ?>
                  <li>
                    <a class="dropdown-item-modern d-flex align-items-center gap-2 <?= isActive('order_history.php') ? 'active' : '' ?>" href="/pns_store/order_history.php">
                      <i class="bi bi-clock-history text-success"></i>
                      Order History
                    </a>
                  </li>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <form action="/pns_store/logout.php" method="post" class="dropdown-item-modern">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                      <button type="submit" class="btn btn-link text-danger p-0 d-flex align-items-center gap-2 w-100" style="text-decoration: none;">
                        <i class="bi bi-box-arrow-right"></i>
                        Sign Out
                      </button>
                    </form>
                  </li>
                <?php else: ?>
                  <li>
                    <a class="dropdown-item-modern d-flex align-items-center gap-2 <?= isActive('login.php') ? 'active' : '' ?>" href="/pns_store/login.php">
                      <i class="bi bi-box-arrow-in-right text-success"></i>
                      Sign in
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item-modern d-flex align-items-center gap-2 <?= isActive('register.php') ? 'active' : '' ?>" href="/pns_store/register.php">
                      <i class="bi bi-person-plus text-success"></i>
                      Register
                    </a>
                  </li>
                <?php endif; ?>
              </ul>
            </div>

            <div class="position-relative">
              <a href="/pns_store/cart.php" class="btn btn-light btn-sm position-relative d-flex align-items-center gap-2 px-3">
                <i class="bi bi-cart3"></i>
                <span class="fw-semibold">Cart</span>
                <?php if ($cart_count > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">
                  <?= (int)$cart_count ?>
                  <span class="visually-hidden">items in cart</span>
                </span>
                <?php endif; ?>
              </a>
            </div>
          </div>
        </div>
      </div>
    </nav>
    <div class="store-subtitle">STUDENT DISCOUNTS • ORG MERCH</div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
      <div class="offcanvas-header">
        <h5 id="mobileMenuLabel">Menu</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <div class="mb-3">
          <form method="get" action="/pns_store/products.php">
            <input name="q" class="form-control form-control-sm" placeholder="Search products">
          </form>
        </div>
        <ul class="list-unstyled">
          <li class="mb-2"><a href="/pns_store/index.php" class="d-block">Home</a></li>
          <li class="mb-2"><a href="/pns_store/products.php" class="d-block">Products</a></li>
          <li class="mb-2"><a href="/pns_store/contact.php" class="d-block">Contact</a></li>
        </ul>
        <hr>
        <div>
          <?php if (!empty($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['role'] === 'admin'): ?>
              <a class="btn btn-success btn-sm w-100 mb-2" href="/pns_store/admin/dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
              </a>
            <?php elseif ($_SESSION['role'] === 'org'): ?>
              <a class="btn btn-success btn-sm w-100 mb-2" href="/pns_store/org/dashboard.php">
                <i class="bi bi-shop me-2"></i>Organization Portal
              </a>
            <?php endif; ?>
            <a class="btn btn-outline-secondary btn-sm w-100 mb-2" href="/pns_store/order_history.php">
              <i class="bi bi-clock-history me-2"></i>Order History
            </a>
            <a class="btn btn-danger btn-sm w-100" href="/pns_store/logout.php">
              <i class="bi bi-box-arrow-right me-2"></i>Sign Out
            </a>
          <?php else: ?>
            <a class="btn btn-outline-secondary btn-sm w-100 mb-2" href="/pns_store/login.php">Sign in</a>
            <a class="btn btn-success btn-sm w-100" href="/pns_store/register.php">Register</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </header>

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

  <script>
    // Show/hide floating cart based on scroll position
    document.addEventListener('DOMContentLoaded', function() {
      const floatingCart = document.querySelector('.floating-cart');
      let lastScrollTop = 0;
      let isVisible = false;

      function toggleFloatingCart() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const shouldShow = scrollTop > 300 && scrollTop > lastScrollTop;

        if (shouldShow && !isVisible) {
          floatingCart.classList.add('show');
          isVisible = true;
        } else if (!shouldShow && isVisible) {
          floatingCart.classList.remove('show');
          isVisible = false;
        }

        lastScrollTop = scrollTop;
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
      function updateFloatingCartCount(count) {
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
      }

      // Make updateFloatingCartCount available globally
      window.updateFloatingCartCount = updateFloatingCartCount;
    });
  </script>

  <!-- offcanvas cart remains elsewhere (index.php includes it). -->
