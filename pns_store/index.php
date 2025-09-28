<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';

// Fetch featured products
$limit = 8;
$sql = "SELECT p.*, o.name AS org_name
        FROM products p
        LEFT JOIN organizations o ON p.org_id = o.org_id
        WHERE p.stock > 0
        ORDER BY p.created_at DESC
        LIMIT " . (int)$limit;

try {
    $stmt = $pdo->query($sql);
    $featured = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "<!-- Debug: Database error: " . htmlspecialchars($e->getMessage()) . " -->\n";
    $featured = [];
}
?>

<main>
  <!-- Hero Section -->
  <section class="bg-success text-white py-5 mb-4 w-100">
    <div class="container text-center">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <h1 class="display-4 fw-bold mb-3">Palawan National School — Student Store</h1>
          <p class="lead mb-4">Discounted school essentials, official organization merch, and quick campus pickup. Shop with student prices and support your organizations.</p>
          <div class="d-flex gap-3 justify-content-center">
            <a href="/pns_store/products.php" class="btn btn-light btn-lg fw-semibold">Browse products</a>
            <a href="/pns_store/products.php?sort=price_low" class="btn btn-outline-light btn-lg">Lowest price</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <div class="container">
    <!-- Benefits Section -->
      <!-- SIMPLE PROMO / INFO ROW -->
  <section class="rounded p-4 mb-5" style="background: rgba(255,255,255,0.8); backdrop-filter: blur(10px);">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-4">
      <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width:46px;height:46px">
          <i class="bi bi-check2-circle fs-4"></i>
        </div>
        <div>
          <div class="fw-bold">Student Discounts</div>
          <div class="small text-muted">Verified student pricing on all items</div>
        </div>
      </div>

      <div class="d-flex align-items-center gap-3">
        <div class="text-end">
          <div class="fw-bold">Campus Pickup</div>
          <div class="small text-muted">Free pickup at admin office</div>
        </div>
        <a href="/pns_store/contact.php" class="btn btn-outline-success">
          Contact us
        </a>
      </div>
    </div>
  </section>

    <!-- Quick Links Section -->
    <section class="mb-5">
      <div class="d-flex gap-2 align-items-center flex-wrap">
        <h6 class="text-success mb-0 me-2">Quick orgs:</h6>
        <?php 
        try {
            $orgs = $pdo->query("SELECT org_id, name FROM organizations ORDER BY name ASC LIMIT 8")->fetchAll();
            if (!empty($orgs)): foreach($orgs as $o): 
            ?>
              <a href="/pns_store/products.php?orgs[]=<?= (int)$o['org_id'] ?>" 
                 class="btn btn-outline-success btn-sm rounded-pill">
                <?= htmlspecialchars($o['name']) ?>
              </a>
            <?php endforeach; else: ?>
              <div class="text-muted small">No organizations available yet.</div>
            <?php endif;
        } catch (PDOException $e) {
            echo '<div class="text-muted small">Unable to load organizations</div>';
        }
        ?>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="mb-5">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0">Featured & New</h2>
        <a href="/pns_store/products.php" class="btn btn-link text-success text-decoration-none">
          See all products <i class="bi bi-arrow-right"></i>
        </a>
      </div>

      <?php if (empty($featured)): ?>
        <div class="text-center py-5 bg-light rounded">
          <div class="mb-3">
            <i class="bi bi-box h1 text-muted"></i>
          </div>
          <h4>No featured products yet</h4>
          <p class="text-muted">Add products from the admin panel to populate this section.</p>
        </div>
      <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
          <?php foreach($featured as $p):
            $img = $p['image'] ? '/pns_store/assets/uploads/'.htmlspecialchars($p['image']) : '/pns_store/assets/images/placeholder.png';
            $isSale = (!empty($p['original_price']) && $p['original_price'] > $p['price']);
          ?>
          <div class="col">
            <div class="card h-100 product-card border-0 shadow-sm">
              <img src="<?= $img ?>" class="card-img-top" alt="<?= htmlspecialchars($p['title']) ?>" style="height: 200px; object-fit: cover;">
              <?php if ($isSale): ?>
                <div class="position-absolute top-0 start-0 m-2">
                  <span class="badge bg-danger">SALE</span>
                </div>
              <?php endif; ?>
              <div class="card-body">
                <h5 class="card-title mb-1"><?= htmlspecialchars($p['title']) ?></h5>
                <p class="text-muted small mb-2"><?= htmlspecialchars($p['org_name'] ?? 'School') ?></p>
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div>
                    <span class="h5 text-success mb-0">₱<?= number_format($p['price'],2) ?></span>
                    <?php if ($isSale): ?>
                      <small class="text-muted text-decoration-line-through ms-2">₱<?= number_format($p['original_price'],2) ?></small>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="d-flex gap-2">
                  <div class="input-group input-group-sm" style="width: 100px;">
                    <input type="number" class="form-control text-center quantity-input" 
                           value="1" min="1" max="<?= (int)$p['stock'] ?>">
                  </div>
                  <button class="btn btn-success add-ajax" data-id="<?= (int)$p['product_id'] ?>" 
                          <?= $p['stock']<=0 ? 'disabled' : '' ?> title="Add to cart">
                    <i class="bi bi-cart-plus"></i>
                  </button>
                  <a href="/pns_store/product.php?id=<?= (int)$p['product_id'] ?>" 
                     class="btn btn-outline-secondary ms-auto" title="View details">
                    <i class="bi bi-info-circle"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </div>
</main>

<!-- FLOATING CART -->
<div class="floating-cart" aria-live="polite" aria-atomic="true">
  <a href="/pns_store/cart.php" class="btn">
    <i class="bi bi-cart3" aria-hidden="true"></i>
    <span id="floatCartCount"><?= (int)$cart_count ?></span>
  </a>
</div>

<!-- Toast container for notifications -->
<div class="toast-container" id="toastContainer" aria-live="polite" aria-atomic="true" style="position:fixed; right:18px; bottom:18px; z-index:16000;"></div>

<script>
(function(){
  // Toast helper
  function createToast(title, body) {
    if (!window.bootstrap || !bootstrap.Toast) {
      alert(title + '\n' + body);
      return;
    }
    const container = document.getElementById('toastContainer');
    const el = document.createElement('div');
    el.className = 'toast align-items-center text-bg-light border-0 mb-2';
    el.role = 'alert';
    el.innerHTML = '<div class="d-flex"><div class="toast-body"><strong>' + title + '</strong><div style="font-size:.95rem;color:#333">' + body + '</div></div><button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button></div>';
    container.appendChild(el);
    const t = new bootstrap.Toast(el, { delay: 1500 });
    t.show();
    el.addEventListener('hidden.bs.toast', ()=> el.remove());
  }

  // Update cart counters
  function updateCartCount(n) {
    document.getElementById('floatCartCount').textContent = n;
    const side = document.getElementById('sideCartCount');
    if (side) side.textContent = n;
  }

  // AJAX add-to-cart
  async function addToCartAjax(productId, qty) {
    try {
      const fm = new FormData();
      fm.append('product_id', productId);
      fm.append('qty', qty);
      const res = await fetch('/pns_store/add_to_cart_ajax.php', { method: 'POST', body: fm });
      const json = await res.json();
      if (json.success) {
        createToast('Added', json.message || 'Item added to cart');
        updateCartCount(json.cart_count || 0);
        // Animate floating cart (bounce)
        const fc = document.querySelector('.floating-cart a');
        if (fc) {
          fc.classList.remove('bounce');
          void fc.offsetWidth;
          fc.classList.add('bounce');
          setTimeout(()=> fc.classList.remove('bounce'), 900);
        }
      } else {
        createToast('Error', json.message || 'Could not add item');
      }
    } catch (err) {
      console.error(err);
      createToast('Error', 'Network error');
    }
  }

  // Attach handlers to add-to-cart buttons
  document.querySelectorAll('.add-ajax').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const id = btn.getAttribute('data-id');
      const qtyInput = btn.parentElement.querySelector('.quantity-input');
      const qty = Math.max(1, parseInt(qtyInput?.value || 1));
      addToCartAjax(id, qty);
    });
  });
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
