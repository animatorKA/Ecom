<?php
// Include required files
require_once __DIR__ . '/admin_functions.php';  // Include first for utility functions
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';

// Verify admin role
requireRole('admin');

// Set page info
$page_title = "Orders";
$page_icon = "bi-cart";
$active_nav = "orders";
$page_subtitle = "Manage Customer Orders";

// Get messages from session
$success = getSuccessMessage();
$error = getErrorMessage();

// Get all orders with user information
$query = "SELECT o.*, 
          u.name as customer_name, u.email, 
          COUNT(oi.id) as item_count,
          COALESCE(SUM(oi.quantity * oi.unit_price), 0) as items_total
          FROM orders o
          JOIN users u ON o.user_id = u.user_id
          LEFT JOIN order_items oi ON o.order_id = oi.order_id
          GROUP BY o.order_id
          ORDER BY o.created_at DESC";
$orders = $pdo->query($query)->fetchAll();

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = $_POST['order_id'];
    $status = $_POST['status'];
    $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?")
        ->execute([$status, $orderId]);
    header('Location: /pns_store/admin/orders.php');
    exit;
}
?>

<!-- Page Header -->
<div class="container py-4">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h2 class="h3 mb-0">Orders Management</h2>
      <p class="text-muted mb-0">View and manage customer orders.</p>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="/pns_store/" class="text-decoration-none">Store</a></li>
        <li class="breadcrumb-item"><a href="/pns_store/admin/dashboard.php" class="text-decoration-none">Admin</a></li>
        <li class="breadcrumb-item active">Orders</li>
      </ol>
    </nav>
  </div>

  <!-- Orders Table Card -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
      <div class="row align-items-center">
        <div class="col">
          <h5 class="mb-0">All Orders</h5>
        </div>
        <div class="col-auto">
          <select class="form-select form-select-sm" id="statusFilter">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="processing">Processing</option>
            <option value="shipped">Shipped</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr>
              <th class="fw-medium">Order ID</th>
              <th class="fw-medium">Customer</th>
              <th class="fw-medium">Items</th>
              <th class="fw-medium">Total</th>
              <th class="fw-medium">Status</th>
              <th class="fw-medium">Date</th>
              <th class="fw-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($orders as $order): ?>
            <tr>
              <td>#<?=$order['order_id']?></td>
              <td>
                <div><?=htmlspecialchars($order['customer_name'])?></div>
                <div class="small text-muted"><?=htmlspecialchars($order['email'])?></div>
              </td>
              <td><?=$order['item_count']?> items</td>
              <td>₱<?=number_format($order['total_amount'],2)?></td>
              <td>
                <span class="badge <?php
                  switch($order['status']) {
                    case 'pending': echo 'bg-warning'; break;
                    case 'processing': echo 'bg-info'; break;
                    case 'shipped': echo 'bg-primary'; break;
                    case 'delivered': echo 'bg-success'; break;
                    case 'cancelled': echo 'bg-danger'; break;
                    default: echo 'bg-secondary';
                  }
                ?>">
                  <?=ucfirst($order['status'])?>
                </span>
              </td>
              <td>
                <div><?=date('M d, Y', strtotime($order['created_at']))?></div>
                <div class="small text-muted"><?=date('h:i A', strtotime($order['created_at']))?></div>
              </td>
              <td>
                <div class="btn-group btn-group-sm">
                  <button type="button" class="btn btn-outline-primary" 
                          data-bs-toggle="modal" 
                          data-bs-target="#viewOrderModal" 
                          onclick="viewOrder(<?=$order['order_id']?>)">
                    <i class="bi bi-eye"></i>
                  </button>
                  <button type="button" class="btn btn-outline-secondary" 
                          data-bs-toggle="modal" 
                          data-bs-target="#updateStatusModal"
                          onclick="prepareStatusUpdate(<?=$order['order_id']?>, '<?=$order['status']?>')">
                    <i class="bi bi-arrow-repeat"></i>
                  </button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- View Order Modal -->
  <div class="modal fade" id="viewOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Order Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="orderDetails">Loading...</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Update Status Modal -->
  <div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Update Order Status</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="post">
          <div class="modal-body">
            <input type="hidden" name="order_id" id="statusOrderId">
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select name="status" class="form-select" id="statusSelect">
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Filter orders by status
document.getElementById('statusFilter').addEventListener('change', function() {
  const status = this.value.toLowerCase();
  const rows = document.querySelectorAll('tbody tr');
  
  rows.forEach(row => {
    const statusCell = row.querySelector('td:nth-child(5)');
    const statusText = statusCell.textContent.trim().toLowerCase();
    
    if (status === '' || statusText === status) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
});

function viewOrder(orderId) {
  const detailsDiv = document.getElementById('orderDetails');
  detailsDiv.innerHTML = 'Loading order details...';
  
  // TODO: Implement AJAX call to fetch order details
  // This would show order items, customer details, shipping info, etc.
}

function prepareStatusUpdate(orderId, currentStatus) {
  document.getElementById('statusOrderId').value = orderId;
  document.getElementById('statusSelect').value = currentStatus;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
