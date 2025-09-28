<?php
// product.php - Single product detail
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT p.*, o.name as org_name
                       FROM products p
                       LEFT JOIN organizations o ON p.org_id = o.org_id
                       WHERE p.product_id = ? LIMIT 1");
$stmt->execute([$id]);
$product = $stmt->fetch();

// fallback
if (!$product) {
  echo "<main class='container'><div class='empty-state'><h4>Product not found</h4><p class='small text-muted'>It may have been removed or is unavailable.</p></div></main>";
  require_once __DIR__ . '/includes/footer.php';
  exit;
}

// related products (same org if possible)
$related = [];
if ($product['org_id']) {
  $stmt = $pdo->prepare("SELECT p.*, o.name as org_name
                         FROM products p
                         LEFT JOIN organizations o ON p.org_id = o.org_id
                         WHERE p.org_id = ? AND p.product_id != ?
                         ORDER BY RAND() LIMIT 4");
  $stmt->execute([$product['org_id'], $id]);
  $related = $stmt->fetchAll();
}
?>
<main class="container" id="main">
  <!-- PRODUCT DETAIL -->
  <section class="reveal" style="display:grid; grid-template-columns: 1fr 1fr; gap:28px; align-items:start; margin-top:28px;">
    <div style="background:#fff; padding:12px; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.06);">
      <?php $img = $product['image'] ? '/pns_store/assets/uploads/'.e($product['image']) : '/pns_store/assets/images/placeholder.png'; ?>
      <img src="<?= $img ?>" alt="<?= e($product['title']) ?>" style="width:100%; border-radius:8px; object-fit:cover;">
    </div>
    <div>
      <h2><?= e($product['title']) ?></h2>
      <div class="small text-muted mb-2"><?= e($product['org_name'] ?? 'School') ?></div>
      
      <div style="display:flex; gap:10px; align-items:center; margin:12px 0;">
        <div class="price" style="font-size:1.4rem; color:#0a6b3b; font-weight:700;">
          ₱<?= number_format($product['price'],2) ?>
        </div>
        <?php if (!empty($product['original_price']) && $product['original_price'] > $product['price']): ?>
          <div class="price-old">₱<?= number_format($product['original_price'],2) ?></div>
        <?php endif; ?>
      </div>

      <div style="margin-bottom:14px;">
        <strong>Stock:</strong> <?= (int)$product['stock'] ?>
      </div>

      <p style="margin-bottom:18px;"><?= nl2br(e($product['description'] ?? 'No description available.')) ?></p>

      <div style="display:flex; gap:12px; align-items:center;">
        <input type="number" min="1" max="<?= (int)$product['stock'] ?>" value="1" class="inline-qty" style="width:90px; padding:6px; border-radius:6px; border:1px solid rgba(0,0,0,0.08);">
        <button class="btn add-ajax" data-id="<?= (int)$product['product_id'] ?>" style="background:linear-gradient(90deg,#22c55e,#4ade80); color:#fff; border-radius:8px; padding:10px 18px;" <?= $product['stock']<=0 ? 'disabled' : '' ?>>
          <i class="bi bi-cart-plus"></i> Add to Cart
        </button>
      </div>
    </div>
  </section>

  <!-- RELATED -->
  <?php if (!empty($related)): ?>
  <section style="margin-top:38px;">
    <h3>Related Products</h3>
    <div class="product-grid">
      <?php foreach($related as $p):
        $img = $p['image'] ? '/pns_store/assets/uploads/'.e($p['image']) : '/pns_store/assets/images/placeholder.png';
        $isSale = (!empty($p['original_price']) && $p['original_price'] > $p['price']);
      ?>
      <article class="product-card reveal">
        <img src="<?= $img ?>" alt="<?= e($p['title']) ?>">
        <div class="card-body">
          <h5><?= e($p['title']) ?></h5>
          <div class="small text-muted"><?= e($p['org_name'] ?? 'School') ?></div>
          <div class="price">₱<?= number_format($p['price'],2) ?></div>
          <?php if ($isSale): ?><div class="price-old">₱<?= number_format($p['original_price'],2) ?></div><?php endif; ?>
          <div style="margin-top:10px;">
            <a href="/pns_store/product.php?id=<?= (int)$p['product_id'] ?>" class="btn" style="background:#fff; color:#166534; border-radius:8px; padding:6px 10px;">View</a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>
</main>

<!-- Toast container -->
<div class="toast-container" id="toastContainer" style="position:fixed; right:18px; bottom:18px; z-index:16000;"></div>

<script>
// AJAX add-to-cart (reuse same code as index)
async function addToCartAjax(productId, qty) {
  try {
    const fm = new FormData();
    fm.append('product_id', productId);
    fm.append('qty', qty);
    const res = await fetch('/pns_store/add_to_cart_ajax.php', { method:'POST', body:fm });
    const json = await res.json();
    if (json.success) {
      createToast('Added', json.message || 'Item added');
      const fc = document.getElementById('floatCartCount');
      if (fc) fc.textContent = json.cart_count || 0;
    } else {
      createToast('Error', json.message || 'Could not add');
    }
  } catch(err) {
    console.error(err);
    createToast('Error','Network error');
  }
}
document.querySelectorAll('.add-ajax').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    const id = btn.dataset.id;
    const qtyInput = document.querySelector('.inline-qty');
    const qty = Math.max(1, parseInt(qtyInput?.value || 1));
    addToCartAjax(id, qty);
  });
});

function createToast(title, body){
  if (!window.bootstrap || !bootstrap.Toast){ alert(title + '\\n' + body); return; }
  const container = document.getElementById('toastContainer');
  const el = document.createElement('div');
  el.className='toast align-items-center text-bg-light border-0 mb-2';
  el.role='alert';
  el.innerHTML='<div class="d-flex"><div class="toast-body"><strong>'+title+'</strong><div style="font-size:.95rem;color:#333">'+body+'</div></div><button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button></div>';
  container.appendChild(el);
  const t=new bootstrap.Toast(el,{delay:1600});t.show();el.addEventListener('hidden.bs.toast',()=>el.remove());
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
