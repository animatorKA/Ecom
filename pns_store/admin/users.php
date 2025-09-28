<?php
// Include required files
require_once __DIR__ . '/admin_functions.php';  // Include first for utility functions
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';

// Verify admin role
requireRole('admin');

// Set page info
$page_title = "Users";
$page_icon = "bi-people";
$active_nav = "users";
$page_subtitle = "Manage User Accounts";

// Get messages from session
$success = getSuccessMessage();
$error = getErrorMessage();

// Get all users with their stats
$query = "SELECT u.*, 
          COUNT(DISTINCT o.order_id) as order_count,
          MAX(o.created_at) as last_order_date,
          SUM(CASE WHEN o.status != 'cancelled' THEN oi.quantity * oi.unit_price ELSE 0 END) as total_spent
          FROM users u 
          LEFT JOIN orders o ON u.user_id = o.user_id
          LEFT JOIN order_items oi ON o.order_id = oi.order_id
          GROUP BY u.user_id
          ORDER BY u.created_at DESC";
$users = $pdo->query($query)->fetchAll();

// Update user role/status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_role'])) {
        $userId = $_POST['user_id'];
        $role = $_POST['role'];
        $pdo->prepare("UPDATE users SET role = ? WHERE user_id = ?")
            ->execute([$role, $userId]);
    } elseif (isset($_POST['update_status'])) {
        $userId = $_POST['user_id'];
        $status = $_POST['status'];
        $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?")
            ->execute([$status, $userId]);
    }
    header('Location: /pns_store/admin/users.php');
    exit;
}
?>

<!-- Page Header -->
<div class="container py-4">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h2 class="h3 mb-0">Users Management</h2>
      <p class="text-muted mb-0">View and manage user accounts.</p>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="/pns_store/" class="text-decoration-none">Store</a></li>
        <li class="breadcrumb-item"><a href="/pns_store/admin/dashboard.php" class="text-decoration-none">Admin</a></li>
        <li class="breadcrumb-item active">Users</li>
      </ol>
    </nav>
  </div>

  <!-- Users Table Card -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
      <div class="row align-items-center">
        <div class="col">
          <h5 class="mb-0">All Users</h5>
        </div>
        <div class="col-auto">
          <div class="row g-2">
            <div class="col-auto">
              <select class="form-select form-select-sm" id="roleFilter">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="org">Organization</option>
                <option value="user">User</option>
              </select>
            </div>
            <div class="col-auto">
              <select class="form-select form-select-sm" id="statusFilter">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr>
              <th class="fw-medium">User</th>
              <th class="fw-medium">Role</th>
              <th class="fw-medium">Status</th>
              <th class="fw-medium">Orders</th>
              <th class="fw-medium">Total Spent</th>
              <th class="fw-medium">Last Order</th>
              <th class="fw-medium">Joined</th>
              <th class="fw-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($users as $user): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center">
                  <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                    <i class="bi bi-person text-success"></i>
                  </div>
                  <div>
                    <div><?=htmlspecialchars($user['first_name'] . ' ' . $user['last_name'])?></div>
                    <div class="small text-muted"><?=htmlspecialchars($user['email'])?></div>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge <?php
                  switch($user['role']) {
                    case 'admin': echo 'bg-danger'; break;
                    case 'org': echo 'bg-warning'; break;
                    default: echo 'bg-success';
                  }
                ?>">
                  <?=ucfirst($user['role'])?>
                </span>
              </td>
              <td>
                <span class="badge <?php
                  switch($user['status']) {
                    case 'active': echo 'bg-success'; break;
                    case 'inactive': echo 'bg-secondary'; break;
                    case 'suspended': echo 'bg-danger'; break;
                    default: echo 'bg-secondary';
                  }
                ?>">
                  <?=ucfirst($user['status'])?>
                </span>
              </td>
              <td><?=$user['order_count']?></td>
              <td>₱<?=number_format($user['total_spent'] ?? 0, 2)?></td>
              <td>
                <?php if($user['last_order_date']): ?>
                  <div><?=date('M d, Y', strtotime($user['last_order_date']))?></div>
                  <div class="small text-muted"><?=date('h:i A', strtotime($user['last_order_date']))?></div>
                <?php else: ?>
                  <span class="text-muted">Never</span>
                <?php endif; ?>
              </td>
              <td>
                <div><?=date('M d, Y', strtotime($user['created_at']))?></div>
                <div class="small text-muted"><?=date('h:i A', strtotime($user['created_at']))?></div>
              </td>
              <td>
                <div class="btn-group btn-group-sm">
                  <button type="button" class="btn btn-outline-primary" 
                          data-bs-toggle="modal" 
                          data-bs-target="#viewUserModal"
                          onclick="viewUser(<?=$user['user_id']?>)">
                    <i class="bi bi-eye"></i>
                  </button>
                  <button type="button" class="btn btn-outline-success" 
                          data-bs-toggle="modal" 
                          data-bs-target="#updateRoleModal"
                          onclick="prepareRoleUpdate(<?=$user['user_id']?>, '<?=$user['role']?>')">
                    <i class="bi bi-person-gear"></i>
                  </button>
                  <button type="button" class="btn btn-outline-secondary" 
                          data-bs-toggle="modal" 
                          data-bs-target="#updateStatusModal"
                          onclick="prepareStatusUpdate(<?=$user['user_id']?>, '<?=$user['status']?>')">
                    <i class="bi bi-toggle-on"></i>
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

  <!-- View User Modal -->
  <div class="modal fade" id="viewUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">User Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="userDetails">Loading...</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Update Role Modal -->
  <div class="modal fade" id="updateRoleModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Update User Role</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="post">
          <div class="modal-body">
            <input type="hidden" name="user_id" id="roleUserId">
            <div class="mb-3">
              <label class="form-label">Role</label>
              <select name="role" class="form-select" id="roleSelect">
                <option value="user">User</option>
                <option value="org">Organization</option>
                <option value="admin">Admin</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="update_role" class="btn btn-success">Update Role</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Update Status Modal -->
  <div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Update User Status</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="post">
          <div class="modal-body">
            <input type="hidden" name="user_id" id="statusUserId">
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select name="status" class="form-select" id="statusSelect">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
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
// Filter users by role
document.getElementById('roleFilter').addEventListener('change', function() {
  filterUsers();
});

// Filter users by status
document.getElementById('statusFilter').addEventListener('change', function() {
  filterUsers();
});

function filterUsers() {
  const role = document.getElementById('roleFilter').value.toLowerCase();
  const status = document.getElementById('statusFilter').value.toLowerCase();
  const rows = document.querySelectorAll('tbody tr');
  
  rows.forEach(row => {
    const roleCell = row.querySelector('td:nth-child(2)');
    const statusCell = row.querySelector('td:nth-child(3)');
    const roleText = roleCell.textContent.trim().toLowerCase();
    const statusText = statusCell.textContent.trim().toLowerCase();
    
    const roleMatch = role === '' || roleText === role;
    const statusMatch = status === '' || statusText === status;
    
    if (roleMatch && statusMatch) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
}

function viewUser(userId) {
  const detailsDiv = document.getElementById('userDetails');
  detailsDiv.innerHTML = 'Loading user details...';
  
  // TODO: Implement AJAX call to fetch user details
  // This would show user profile, order history, etc.
}

function prepareRoleUpdate(userId, currentRole) {
  document.getElementById('roleUserId').value = userId;
  document.getElementById('roleSelect').value = currentRole;
}

function prepareStatusUpdate(userId, currentStatus) {
  document.getElementById('statusUserId').value = userId;
  document.getElementById('statusSelect').value = currentStatus;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
