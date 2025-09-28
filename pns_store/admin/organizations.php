<?php
// Include required files
require_once __DIR__ . '/admin_functions.php';  // Include first for utility functions
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';

// Verify admin role
requireRole('admin');

// Set page info
$page_title = "Organizations";
$page_icon = "bi-building";
$active_nav = "organizations";
$page_subtitle = "Manage Store Organizations";

// Get messages from session
$success = getSuccessMessage();
$error = getErrorMessage();

// Handle add organization
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    if (!verifyCSRFToken()) {
        setErrorMessage("Invalid form submission, please try again.");
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    try {
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        
        // Check if organization already exists
        $stmt = $pdo->prepare("SELECT org_id FROM organizations WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            $error = "An organization with this name already exists.";
        } else {
            // Add new organization
            $stmt = $pdo->prepare("INSERT INTO organizations (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            $success = "Organization added successfully!";
        }
    } catch (PDOException $e) {
        $error = "Database error. Please try again.";
        error_log("Error adding organization: " . $e->getMessage());
    }
}

// Handle delete organization
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    try {
        $org_id = (int)$_POST['org_id'];
        
        // Check if org has products
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE org_id = ?");
        $stmt->execute([$org_id]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Cannot delete organization that has products. Please remove or reassign products first.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM organizations WHERE org_id = ?");
            $stmt->execute([$org_id]);
            $success = "Organization deleted successfully!";
        }
    } catch (PDOException $e) {
        $error = "Database error. Please try again.";
        error_log("Error deleting organization: " . $e->getMessage());
    }
}

// Get all organizations
$organizations = $pdo->query("
    SELECT o.*, COUNT(p.product_id) as product_count 
    FROM organizations o 
    LEFT JOIN products p ON o.org_id = p.org_id 
    GROUP BY o.org_id 
    ORDER BY o.name
")->fetchAll();
?>

<div class="container py-4">
  <!-- Breadcrumb -->
  <div class="mb-4">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Organizations</li>
      </ol>
    </nav>
  </div>

  <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  
  <?php if (isset($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <!-- Organizations Table Card -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
      <div class="d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Organizations</h5>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addOrgModal">
          <i class="bi bi-plus-lg me-2"></i>Add Organization
        </button>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr>
              <th class="fw-medium">ID</th>
              <th class="fw-medium">Name</th>
              <th class="fw-medium">Description</th>
              <th class="fw-medium">Products</th>
              <th class="fw-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($organizations as $org): ?>
            <tr>
              <td><?= (int)$org['org_id'] ?></td>
              <td><?= htmlspecialchars($org['name']) ?></td>
              <td class="text-break" style="max-width: 300px;">
                <?= htmlspecialchars($org['description'] ?: '-') ?>
              </td>
              <td>
                <span class="badge bg-secondary"><?= (int)$org['product_count'] ?></span>
              </td>
              <td>
                <div class="btn-group btn-group-sm">
                  <?php if ($org['product_count'] == 0): ?>
                  <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this organization?');">
                    <input type="hidden" name="org_id" value="<?= (int)$org['org_id'] ?>">
                    <button type="submit" name="delete" class="btn btn-outline-danger">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Add Organization Modal -->
<div class="modal fade" id="addOrgModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add New Organization</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label" style="font-size: 0.95rem; font-weight: 500; color: #1a365d;">
              Name <span class="text-danger">*</span>
            </label>
            <input type="text" name="name" class="form-control form-control-lg" required
                   style="background-color: #f8fafc; border: 1px solid #e2e8f0;
                          font-size: 1rem; padding: 0.75rem 1rem; color: #1e293b;
                          box-shadow: 0 2px 4px rgba(148, 163, 184, 0.1);
                          transition: all 0.2s ease-in-out;"
                   onFocus="this.style.backgroundColor='#ffffff';
                           this.style.boxShadow='0 4px 6px rgba(148, 163, 184, 0.15)';"
                   onBlur="this.style.backgroundColor='#f8fafc';
                          this.style.boxShadow='0 2px 4px rgba(148, 163, 184, 0.1)';">
          </div>
          <div class="mb-3">
            <label class="form-label" style="font-size: 0.95rem; font-weight: 500; color: #1a365d;">Description</label>
            <textarea name="description" class="form-control form-control-lg" rows="4"
                     style="background-color: #f8fafc; border: 1px solid #e2e8f0;
                            font-size: 1rem; padding: 0.75rem 1rem; color: #1e293b;
                            resize: vertical; min-height: 120px; line-height: 1.7;
                            box-shadow: 0 2px 4px rgba(148, 163, 184, 0.1);
                            transition: all 0.2s ease-in-out;"
                     onFocus="this.style.backgroundColor='#ffffff';
                             this.style.boxShadow='0 4px 6px rgba(148, 163, 184, 0.15)';"
                     onBlur="this.style.backgroundColor='#f8fafc';
                            this.style.boxShadow='0 2px 4px rgba(148, 163, 184, 0.1);"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="add" class="btn btn-success">
            <i class="bi bi-plus-lg me-2"></i>Add Organization
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>