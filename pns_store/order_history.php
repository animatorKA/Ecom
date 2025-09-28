<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config.php';

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>
<h3>Your Orders</h3>
<?php if(empty($orders)): ?><div class="alert alert-info">No orders yet.</div><?php else: ?>
  <div class="list-group">
    <?php foreach($orders as $o): ?>
      <div class="list-group-item">
        <strong>Order #<?= $o['order_id'] ?></strong> — <?= htmlspecialchars($o['status']) ?> — ₱<?= number_format($o['total_amount'],2) ?>
        <a href="#" class="btn btn-sm btn-outline-secondary float-end">Details</a>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>