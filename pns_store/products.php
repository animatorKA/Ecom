<?php
// products.php - Polished product catalog with organization checkbox filter
// Main fixes applied: include config first (session + $pdo), inject integer LIMIT/OFFSET, safe sort whitelisting.

require_once __DIR__ . '/config.php';               // config first (provides session-safe start, $pdo, helpers)
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';

// --- Input (search / filter / sort / page)
$q = trim($_GET['q'] ?? '');

// Get category filter
$category_id = (int)($_GET['category'] ?? 0);

// collect orgs as array of ints
$orgs = [];
if (!empty($_GET['orgs']) && is_array($_GET['orgs'])) {
    foreach ($_GET['orgs'] as $oid) {
        $oid = (int)$oid;
        if ($oid > 0) $orgs[] = $oid;
    }
}
// fallback for old single 'org' param (keeps backwards compat)
if (empty($orgs) && !empty($_GET['org'])) {
    $maybe = $_GET['org'];
    if (is_numeric($maybe)) {
        $orgs[] = (int)$maybe;
    } else {
        $stmt = $pdo->prepare("SELECT org_id FROM organizations WHERE name = ? LIMIT 1");
        $stmt->execute([$maybe]);
        $r = $stmt->fetch();
        if ($r) $orgs[] = (int)$r['org_id'];
    }
}

// Get available categories
$categories = $pdo->query("
    SELECT c.*, COUNT(DISTINCT pc.product_id) as product_count 
    FROM categories c 
    LEFT JOIN product_categories pc ON c.category_id = pc.category_id 
    GROUP BY c.category_id 
    ORDER BY c.name")->fetchAll();

$allowed_sorts = ['new' => 'p.created_at DESC', 'price_low' => 'p.price ASC', 'price_high' => 'p.price DESC'];
$sort_key = $_GET['sort'] ?? 'new';
if (!array_key_exists($sort_key, $allowed_sorts)) $sort_key = 'new';
$order_sql = $allowed_sorts[$sort_key];

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

// --- Build dynamic WHERE and params
$where = [];
$params = [];

if ($q !== '') {
    $where[] = '(p.title LIKE ? OR p.description LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}

// if orgs specified, filter by org_id(s)
if (!empty($orgs)) {
    $ph = implode(',', array_fill(0, count($orgs), '?'));
    $where[] = "p.org_id IN ($ph)";
    foreach ($orgs as $o) $params[] = $o;
}

if (!empty($category_id)) {
    $where[] = "EXISTS (
        SELECT 1 FROM product_categories pc 
        WHERE pc.product_id = p.product_id 
        AND pc.category_id = ?
    )";
    $params[] = $category_id;
}

$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// --- Count total for pagination
$count_sql = "SELECT COUNT(*) FROM products p LEFT JOIN organizations o ON p.org_id = o.org_id $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));

// --- Fetch page of products
// IMPORTANT: inject LIMIT/OFFSET as integers (not bound params) to avoid SQL syntax errors on some drivers
$limit = (int)$perPage;
$offset_int = (int)$offset;

$sql = "SELECT p.*, o.name as org_name,
        GROUP_CONCAT(c.name) as category_names,
        GROUP_CONCAT(c.category_id) as category_ids
        FROM products p
        LEFT JOIN organizations o ON p.org_id = o.org_id
        LEFT JOIN product_categories pc ON p.product_id = pc.product_id
        LEFT JOIN categories c ON pc.category_id = c.category_id
        $where_sql
        GROUP BY p.product_id
        ORDER BY $order_sql
        LIMIT $limit OFFSET $offset_int";

$stmt = $pdo->prepare($sql);
$stmt->execute($params); // params contains only the WHERE placeholders
$products = $stmt->fetchAll();

// --- Fetch organizations for left filter
$orgRows = $pdo->query("SELECT org_id, name FROM organizations ORDER BY name ASC")->fetchAll();

// current cart count
$cart_count = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}

// helper for building query strings preserving filters
function qs_with($extra = []) {
    $qs = $_GET;
    foreach ($extra as $k => $v) {
        $qs[$k] = $v;
    }
    // remove empty values
    foreach ($qs as $k => $v) {
        if ($v === '' || $v === null) unset($qs[$k]);
    }
    // flatten arrays to repeated params if necessary
    $parts = [];
    foreach ($qs as $k => $v) {
        if (is_array($v)) {
            foreach ($v as $iv) $parts[] = rawurlencode($k) . '[]=' . rawurlencode($iv);
        } else {
            $parts[] = rawurlencode($k) . '=' . rawurlencode($v);
        }
    }
    return implode('&', $parts);
}
?>

<style>
/* Polished products page styles */
.catalog-wrap { display: grid; grid-template-columns: 260px 1fr; gap: 28px; align-items: start; }
@media (max-width: 980px) { .catalog-wrap { grid-template-columns: 1fr; } .side-panel { order: 2; } .catalog-main { order: 1; } }

.side-panel { position: sticky; top: 24px; background: rgba(255,255,255,0.95); padding: 16px; border-radius:12px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); }
.side-panel h5 { margin-top: 0; }

.product-grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(230px, 1fr)); gap: 18px; }
.card-product { border-radius:12px; overflow:hidden; transition: transform .18s ease, box-shadow .18s ease; background: #fff; }
.card-product:hover { transform: translateY(-8px); box-shadow: 0 18px 44px rgba(8,50,22,0.08); }
.card-product .img-wrap { height:160px; overflow:hidden; display:flex; align-items:center; justify-content:center; background:linear-gradient(180deg,#f8fffa,#fbfff9); }
.card-product img { width:100%; height:100%; object-fit:cover; display:block; }
.card-body-small { padding:12px; }
.price { font-weight:700; color:#0a6b3b; }
.price-old { text-decoration:line-through; color:#7b8a7b; margin-left:8px; font-weight:600; font-size:.9rem; }

.pager { display:flex; gap:8px; justify-content:center; margin-top:22px; }
.pager a { padding:8px 12px; border-radius:8px; text-decoration:none; color:#083b22; background: rgba(0,0,0,0.03); }
.pager a.active { background: linear-gradient(90deg,#0f9d58,#6bd28b); color:#fff; font-weight:700; }

.empty-state { text-align:center; padding:40px; background:rgba(255,255,255,0.9); border-radius:12px; }

.filter-btn { display:block; text-align:left; width:100%; margin-bottom:8px; }

/* checkbox list */
.org-list { max-height:280px; overflow:auto; padding-right:6px; }
.org-item { display:flex; align-items:center; gap:8px; padding:6px 4px; border-radius:6px; }
.org-item input[type="checkbox"] { transform: scale(1.05); }
</style>

<div class="catalog-wrap">
  <!-- Left side panel: search + filters -->
  <aside class="side-panel">
    <h5>Search & Filters</h5>

    <form id="filterForm" method="get" class="mb-3">
      <div class="mb-2">
        <label class="form-label small">Search</label>
        <input name="q" value="<?= htmlspecialchars($q, ENT_QUOTES) ?>" class="form-control" placeholder="Search products or descriptions">
      </div>

      <div class="mb-2">
        <label class="form-label small">Organizations</label>
        <div class="org-list">
          <?php foreach ($orgRows as $o):
              $checked = in_array((int)$o['org_id'], $orgs, true) ? 'checked' : '';
          ?>
            <label class="org-item">
              <input type="checkbox" name="orgs[]" value="<?= (int)$o['org_id'] ?>" <?= $checked ?>>
              <span><?= htmlspecialchars($o['name'], ENT_QUOTES) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="mb-2">
        <label class="form-label small">Sort</label>
        <select name="sort" class="form-select">
          <option value="new" <?= $sort_key==='new' ? 'selected' : '' ?>>Newest</option>
          <option value="price_low" <?= $sort_key==='price_low' ? 'selected' : '' ?>>Price: Low → High</option>
          <option value="price_high" <?= $sort_key==='price_high' ? 'selected' : '' ?>>Price: High → Low</option>
        </select>
      </div>

      <div class="d-grid mt-2">
        <button class="btn btn-success">Apply filters</button>
        <button type="button" id="clearFiltersBtn" class="btn btn-link mt-2">Clear filters</button>
      </div>
    </form>

    <hr>

    <h6 class="small text-muted">Categories</h6>
    <div class="category-list">
        <?php foreach ($categories as $cat): ?>
            <a href="?<?= qs_with(['category' => $cat['category_id']]) ?>" 
               class="btn btn-outline-success btn-sm mb-2 d-block text-start <?= $category_id === (int)$cat['category_id'] ? 'active' : '' ?>">
                <?= htmlspecialchars($cat['name']) ?>
                <span class="badge bg-light text-dark float-end"><?= (int)$cat['product_count'] ?></span>
            </a>
        <?php endforeach; ?>
        <?php if (!empty($category_id)): ?>
            <a href="?<?= qs_with(['category' => '']) ?>" class="btn btn-link btn-sm d-block">Show all categories</a>
        <?php endif; ?>
    </div>
    <button class="btn btn-outline-secondary filter-btn" onclick="location.href='products.php'">All products</button>
    <button class="btn btn-outline-secondary filter-btn" onclick="quickSearch('notebook')">Notebooks</button>
    <button class="btn btn-outline-secondary filter-btn" onclick="quickSearch('uniform')">Uniforms</button>
    <button class="btn btn-outline-secondary filter-btn" onclick="quickSearch('hoodie')">Hoodies</button>

    <hr>

    <h6 class="small text-muted">Your cart</h6>
    <div class="d-flex align-items-center justify-content-between mb-2">
      <div><strong><span id="sideCartCount"><?= (int)$cart_count ?></span> items</strong></div>
      <div><a href="/pns_store/cart.php" class="btn btn-sm btn-outline-secondary">Open cart</a></div>
    </div>

    <div class="small text-muted">Tip: Select one or more organizations to view only their merchandise.</div>
  </aside>

  <!-- Main catalog -->
  <main class="catalog-main">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h3 class="mb-0">Products</h3>
        <div class="small text-muted"><?= $total ?> product<?= $total!==1 ? 's' : '' ?> found</div>
      </div>
      <div>
        <a href="/pns_store/index.php" class="btn btn-outline-secondary btn-sm me-2">Home</a>
        <a href="/pns_store/cart.php" class="btn btn-success btn-sm">Checkout</a>
      </div>
    </div>

    <?php if (empty($products)): ?>
      <div class="empty-state">
        <h5>No products found</h5>
        <p class="small text-muted">Try removing filters or search terms to see more products.</p>
      </div>
    <?php else: ?>
      <div class="product-grid">
        <?php foreach($products as $p):
          $img = $p['image'] ? '/pns_store/assets/uploads/'.htmlspecialchars($p['image'], ENT_QUOTES) : '/pns_store/assets/images/placeholder.png';
          $isSale = (!empty($p['original_price']) && $p['original_price'] > $p['price']);
        ?>
        <div class="card card-product">
          <div class="img-wrap">
            <?php if ($isSale): ?>
              <div style="position:absolute; left:14px; top:12px; background:#fff; padding:6px 8px; border-radius:8px; font-weight:700; color:#0a7a3b; box-shadow:0 8px 20px rgba(0,0,0,0.06);">SALE</div>
            <?php endif; ?>
            <img loading="lazy" src="<?= $img ?>" alt="<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>">
          </div>
          <div class="card-body card-body-small">
            <div style="display:flex; justify-content:space-between; align-items:start;">
              <div>
                <div style="font-weight:700;"><?= htmlspecialchars($p['title'], ENT_QUOTES) ?></div>
                <div class="small text-muted"><?= htmlspecialchars($p['org_name'] ?? 'School', ENT_QUOTES) ?></div>
              </div>
              <div class="text-end">
                <div class="price">₱<?= number_format($p['price'],2) ?>
                  <?php if ($isSale): ?><span class="price-old">₱<?= number_format($p['original_price'],2) ?></span><?php endif; ?>
                </div>
                <div class="small text-muted">Stock: <?= (int)$p['stock'] ?></div>
              </div>
            </div>

            <div class="d-flex gap-2 mt-3">
              <button class="btn btn-outline-secondary btn-sm" onclick="location.href='/pns_store/product.php?id=<?= $p['product_id'] ?>'">View</button>

              <div style="margin-left:auto;">
                <input type="number" min="1" max="<?= (int)$p['stock'] ?>" value="1" class="form-control form-control-sm inline-qty" style="width:86px; display:inline-block;">
                <button class="btn btn-success btn-sm ms-2 add-ajax" data-id="<?= $p['product_id'] ?>" <?= $p['stock']<=0 ? 'disabled' : '' ?>>Add</button>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- pagination -->
      <div class="pager" aria-label="Product pages">
        <?php
          $start = max(1, $page - 3);
          $end = min($pages, $page + 3);
          $base_url = strtok($_SERVER["REQUEST_URI"], '?'); // current script path
        ?>
        <?php if ($page > 1): ?>
          <a href="<?= $base_url . '?' . qs_with(['page' => $page-1]) ?>">&laquo; Prev</a>
        <?php endif; ?>

        <?php for($i=$start;$i<=$end;$i++): ?>
          <a class="<?= $i===$page ? 'active' : '' ?>" href="<?= $base_url . '?' . qs_with(['page' => $i]) ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($page < $pages): ?>
          <a href="<?= $base_url . '?' . qs_with(['page' => $page+1]) ?>">Next &raquo;</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </main>
</div>

<!-- Toast container -->
<div class="toast-container" id="toastContainer"></div>

<script>
// AJAX add-to-cart (reuses add_to_cart_ajax.php)
async function addToCartAjax(productId, qty) {
  try {
    const fm = new FormData();
    fm.append('product_id', productId);
    fm.append('qty', qty);
    const res = await fetch('/pns_store/add_to_cart_ajax.php', { method: 'POST', body: fm });
    const json = await res.json();
    if (json.success) {
      createToast('Added', json.message || 'Item added to cart');
      updateCartCounts(json.cart_count || 0);
      if (typeof refreshOffcanvasCart === 'function') refreshOffcanvasCart();
    } else {
      createToast('Error', json.message || 'Could not add item');
    }
  } catch (e) {
    createToast('Error', 'Network error');
    console.error(e);
  }
}

document.querySelectorAll('.add-ajax').forEach(btn => {
  btn.addEventListener('click', (e) => {
    const id = btn.dataset.id;
    const qtyInput = btn.parentElement.querySelector('.inline-qty');
    const qty = Math.max(1, parseInt(qtyInput?.value || 1));
    addToCartAjax(id, qty);
  });
});

// small helpers (use global ones if present)
function createToast(title, body) {
  if (typeof bootstrap === 'undefined' || !bootstrap.Toast) { alert(title + '\n' + body); return; }
  const container = document.getElementById('toastContainer');
  const el = document.createElement('div');
  el.className = 'toast align-items-center text-bg-light border-0 mb-2';
  el.role = 'alert';
  el.innerHTML = `<div class="d-flex"><div class="toast-body"><strong>${escapeHtml(title)}</strong><div style="font-size:.95rem;color:#333">${escapeHtml(body)}</div></div><button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
  container.appendChild(el);
  const t = new bootstrap.Toast(el, { delay: 1600 });
  t.show();
  el.addEventListener('hidden.bs.toast', ()=>el.remove());
}
function updateCartCounts(n) {
  document.getElementById('sideCartCount').textContent = n;
  const mc = document.getElementById('miniCartCount'); if (mc) mc.textContent = n;
  const fc = document.getElementById('floatCartCount'); if (fc) fc.textContent = n;
}
function escapeHtml(s){ return String(s).replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;'); }

// quick helpers for the UI
function quickSearch(term){
  const f = document.getElementById('filterForm');
  f.q.value = term;
  f.submit();
}
document.getElementById('clearFiltersBtn')?.addEventListener('click', ()=>{
  const f = document.getElementById('filterForm');
  Array.from(f.querySelectorAll('input[type="text"], input[type="search"], textarea')).forEach(i=>i.value='');
  Array.from(f.querySelectorAll('input[type="checkbox"]')).forEach(c=>c.checked=false);
  Array.from(f.querySelectorAll('select')).forEach(s=>s.selectedIndex = 0);
  f.submit();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
