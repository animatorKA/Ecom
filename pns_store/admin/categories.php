<?php
// Include required files
require_once __DIR__ . '/admin_functions.php';  // Include first for utility functions
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';

// Verify admin role
requireRole('admin');

// Set page info
$page_title = "Categories";
$page_icon = "bi-tags";
$active_nav = "categories";
$page_subtitle = "Manage product categories";

// Get messages from session
$success = getSuccessMessage();
$error = getErrorMessage();

// Handle add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    if (!verifyCSRFToken()) {
        setErrorMessage("Invalid form submission, please try again.");
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    try {
        $name = sanitizeInput($_POST['name']);
        if (!empty($name)) {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$name]);
            $success = "Category added successfully!";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry error
            $error = "This category already exists.";
        } else {
            $error = "Error adding category.";
            error_log("Error adding category: " . $e->getMessage());
        }
    }
}

// Handle delete category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    try {
        $category_id = (int)$_POST['category_id'];
        $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->execute([$category_id]);
        echo json_encode(['success' => true]);
        exit;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error']);
        exit;
    }
}

// Get categories with product count
$categories = $pdo->query("
    SELECT c.*, COUNT(pc.product_id) as product_count 
    FROM categories c 
    LEFT JOIN product_categories pc ON c.category_id = pc.category_id 
    GROUP BY c.category_id 
    ORDER BY c.name")->fetchAll();
?>

<!-- Page Header -->
<div class="container py-4">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h2 class="h3 mb-0">Categories Management</h2>
      <p class="text-muted mb-0">Manage your product categories</p>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="/pns_store/" class="text-decoration-none">Store</a></li>
        <li class="breadcrumb-item"><a href="/pns_store/admin/dashboard.php" class="text-decoration-none">Admin</a></li>
        <li class="breadcrumb-item active">Categories</li>
      </ol>
    </nav>
  </div>

  <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if (isset($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <!-- Categories Card -->
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
      <div class="d-flex align-items-center justify-content-between">
        <h5 class="mb-0">All Categories</h5>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
          <i class="bi bi-plus-lg me-2"></i>Add Category
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
              <th class="fw-medium">Products</th>
              <th class="fw-medium">Created</th>
              <th class="fw-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($categories as $cat): ?>
            <tr>
              <td><?= (int)$cat['category_id'] ?></td>
              <td><?= htmlspecialchars($cat['name']) ?></td>
              <td><?= (int)$cat['product_count'] ?></td>
              <td><?= htmlspecialchars(date('M j, Y', strtotime($cat['created_at']))) ?></td>
              <td>
                <div class="btn-group btn-group-sm">
                  <button type="button" class="btn btn-outline-danger" 
                          onclick="deleteCategory(<?= $cat['category_id'] ?>)"
                          <?= $cat['product_count'] > 0 ? 'disabled title="Remove from products first"' : '' ?>>
                    <i class="bi bi-trash"></i>
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
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add New Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label" style="font-size: 0.95rem; font-weight: 500; color: #1a365d;">
              Category Name <span class="text-danger">*</span>
            </label>
            <input name="name" class="form-control form-control-lg" required
                   style="background-color: #f8fafc; border: 1px solid #e2e8f0;
                          font-size: 1rem; padding: 0.75rem 1rem; color: #1e293b;
                          box-shadow: 0 2px 4px rgba(148, 163, 184, 0.1);
                          transition: all 0.2s ease-in-out;"
                   onFocus="this.style.backgroundColor='#ffffff';
                           this.style.boxShadow='0 4px 6px rgba(148, 163, 184, 0.15)';"
                   onBlur="this.style.backgroundColor='#f8fafc';
                          this.style.boxShadow='0 2px 4px rgba(148, 163, 184, 0.1)';">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="add_category" class="btn btn-success">Add Category</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function deleteCategory(categoryId) {
    if (!confirm('Are you sure you want to delete this category?')) return;
    
    fetch('categories.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'delete_category=1&category_id=' + categoryId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error deleting category');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting category');
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>