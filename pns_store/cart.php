<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/CartManager.php';
require_once __DIR__ . '/includes/header.php';

// Initialize cart manager
$cart = new CartManager($pdo);

// Get cart items with product details
$items = $cart->getItems();

// Get cart total
$total = $cart->getTotal();
?>
<div class="container py-4">
  <div class="row">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-header bg-white">
          <h3 class="mb-0">Shopping Cart</h3>
        </div>
        <div class="card-body">
          <?php if(empty($items)): ?>
            <div class="alert alert-info">Your cart is empty. <a href="products.php" class="alert-link">Continue shopping</a></div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover">
                <thead class="table-light">
                  <tr>
                    <th width="50%">Product</th>
                    <th width="15%">Quantity</th>
                    <th width="15%">Price</th>
                    <th width="15%">Subtotal</th>
                    <th width="5%"></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($items as $item): ?>
                  <tr data-product-id="<?= (int)$item['product_id'] ?>">
                    <td>
                      <div class="d-flex align-items-center">
                        <img src="<?= !empty($item['image']) ? 'assets/uploads/' . htmlspecialchars($item['image']) : 'assets/images/placeholder.png' ?>" 
                             alt="<?=htmlspecialchars($item['title'])?>" 
                             class="img-thumbnail me-3" 
                             style="width: 60px; height: 60px; object-fit: cover;">
                        <div>
                          <h6 class="mb-0"><?=htmlspecialchars($item['title'])?></h6>
                          <?php if(isset($item['stock'])): ?>
                          <small class="text-muted">Stock: <?=(int)$item['stock']?></small>
                          <?php endif; ?>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div class="input-group input-group-sm">
                        <button class="btn btn-outline-secondary qty-adjust" data-action="decrease">-</button>
                        <input type="number" class="form-control text-center qty-input" value="<?= (int)$item['quantity'] ?>" min="1" max="<?= (int)($item['stock'] ?? 99) ?>">
                        <button class="btn btn-outline-secondary qty-adjust" data-action="increase">+</button>
                      </div>
                    </td>
                    <td>₱<?= number_format($item['price'],2)?></td>
                    <td class="subtotal">₱<?= number_format($item['subtotal'],2)?></td>
                    <td>
                      <button class="btn btn-sm btn-outline-danger remove-item" title="Remove item">
                        <i class="bi bi-trash"></i>
                      </button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4 mt-4 mt-lg-0">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Order Summary</h5>
          <div class="d-flex justify-content-between mb-3">
            <span>Subtotal</span>
            <span class="cart-total">₱<?= number_format($total,2) ?></span>
          </div>
          <a href="checkout.php" class="btn btn-primary w-100 <?= empty($items) ? 'disabled' : '' ?>">
            Proceed to Checkout
          </a>
          <a href="products.php" class="btn btn-outline-secondary w-100 mt-2">
            Continue Shopping
          </a>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
<script src="assets/js/cart.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>