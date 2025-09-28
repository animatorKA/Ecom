<?php
require_once __DIR__ . '/includes/header.php';
$order_id = (int)($_GET['order_id'] ?? 0);
?>
<div class="row justify-content-center">
  <div class="col-md-6 text-center">
    <div class="card p-4">
      <h3>Order Placed</h3>
      <p>Your order #<?= $order_id ?> has been placed. Status: Pending.</p>
      <a href="/pns_store/order_history.php" class="btn btn-primary">View Orders</a>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>