<?php
// Include required files
require_once __DIR__ . '/admin_functions.php';  // Include first for utility functions
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';

// Verify admin role
requireRole('admin');

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    setErrorMessage("Invalid product ID");
    header('Location: products.php');
    exit;
}

// Set page info
$page_title = "Edit Product";
$page_icon = "bi-pencil-square";
$active_nav = "products";
$page_subtitle = "Edit Product Details";

// Get messages from session
$success = getSuccessMessage();
$error = getErrorMessage();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    if (!verifyCSRFToken()) {
        setErrorMessage("Invalid form submission, please try again.");
        header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $product_id);
        exit;
    }
    
    try {
        // Get and sanitize form data
        $title = sanitizeInput($_POST['title']);
        $description = sanitizeInput($_POST['description']);
        $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
        $original_price = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
        $stock = (int)$_POST['stock'];
        $org_id = !empty($_POST['org_id']) ? (int)$_POST['org_id'] : null;
        $category_ids = isset($_POST['categories']) ? $_POST['categories'] : [];

        // Handle image upload if new image provided
        $imgName = null;
        if (!empty($_FILES['image']['name'])) {
            // Get old image to delete later
            $stmt = $pdo->prepare("SELECT image FROM products WHERE product_id = ?");
            $stmt->execute([$product_id]);
            $oldImage = $stmt->fetchColumn();

            // Upload new image
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imgName = time() . "." . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../assets/uploads/' . $imgName);

            // Delete old image if exists
            if ($oldImage && file_exists(__DIR__ . '/../assets/uploads/' . $oldImage)) {
                unlink(__DIR__ . '/../assets/uploads/' . $oldImage);
            }
        }

        // Begin transaction
        $pdo->beginTransaction();

        // Update product in database
        $sql = "UPDATE products SET 
                title = ?, 
                description = ?, 
                price = ?, 
                original_price = ?, 
                stock = ?, 
                org_id = ?";
        $params = [$title, $description, $price, $original_price, $stock, $org_id];

        if ($imgName) {
            $sql .= ", image = ?";
            $params[] = $imgName;
        }

        $sql .= " WHERE product_id = ?";
        $params[] = $product_id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Update categories
        $stmt = $pdo->prepare("DELETE FROM product_categories WHERE product_id = ?");
        $stmt->execute([$product_id]);

        if (!empty($category_ids)) {
            $insertStmt = $pdo->prepare("INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)");
            foreach ($category_ids as $category_id) {
                $insertStmt->execute([$product_id, (int)$category_id]);
            }
        }

        // Commit transaction
        $pdo->commit();

        $success = "Product updated successfully!";
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $error = "Error updating product. Please try again.";
        error_log("Error updating product: " . $e->getMessage());
    }
}

// Get product data
$stmt = $pdo->prepare("
    SELECT p.*, o.name as org_name,
           GROUP_CONCAT(c.category_id) as category_ids,
           GROUP_CONCAT(c.name) as category_names
    FROM products p 
    LEFT JOIN organizations o ON p.org_id = o.org_id 
    LEFT JOIN product_categories pc ON p.product_id = pc.product_id
    LEFT JOIN categories c ON pc.category_id = c.category_id
    WHERE p.product_id = ?
    GROUP BY p.product_id
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

// Get all available categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

if (!$product) {
    header('Location: products.php');
    exit;
}

// Get organizations for dropdown
$organizations = $pdo->query("SELECT org_id, name FROM organizations ORDER BY name")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <!-- Breadcrumb -->
  <div class="mb-4">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="products.php">Products</a></li>
        <li class="breadcrumb-item active">Edit Product</li>
      </ol>
    </nav>
  </div>

  <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if (isset($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
      <h5 class="mb-0">Edit Product</h5>
    </div>
    <div class="card-body">
      <form method="post" enctype="multipart/form-data">
        <div class="row">
          <div class="col-md-8">
            <div class="mb-3">
              <label class="form-label" style="font-size: 0.95rem; font-weight: 500; color: #1a365d;">
                Title <span class="text-danger">*</span>
              </label>
              <input type="text" name="title" class="form-control form-control-lg" 
                     value="<?= htmlspecialchars($product['title']) ?>" required
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
              <label class="form-label" style="font-size: 0.95rem; font-weight: 500; color: #1a365d;">
                Description <span class="text-danger">*</span>
              </label>
              <textarea name="description" class="form-control form-control-lg" rows="4" required
                        style="background-color: #f8fafc; border: 1px solid #e2e8f0;
                               font-size: 1rem; padding: 0.75rem 1rem; color: #1e293b;
                               resize: vertical; min-height: 120px; line-height: 1.7;
                               box-shadow: 0 2px 4px rgba(148, 163, 184, 0.1);
                               transition: all 0.2s ease-in-out;"
                        onFocus="this.style.backgroundColor='#ffffff';
                                this.style.boxShadow='0 4px 6px rgba(148, 163, 184, 0.15)';"
                        onBlur="this.style.backgroundColor='#f8fafc';
                               this.style.boxShadow='0 2px 4px rgba(148, 163, 184, 0.1)';"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label" style="font-size: 0.95rem; font-weight: 500; color: #1a365d;">
                Categories <span class="text-danger">*</span>
              </label>
              <div class="card p-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                <?php 
                $currentCategories = explode(',', $product['category_ids'] ?? '');
                foreach ($categories as $category): ?>
                  <div class="form-check mb-2">
                    <input type="checkbox" name="categories[]" class="form-check-input" 
                           value="<?= $category['category_id'] ?>" id="cat_<?= $category['category_id'] ?>"
                           <?= in_array($category['category_id'], $currentCategories) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="cat_<?= $category['category_id'] ?>">
                      <?= htmlspecialchars($category['name']) ?>
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label" style="font-size: 0.95rem; font-weight: 500; color: #1a365d;">
                  Price <span class="text-danger">*</span>
                </label>
                <div class="input-group input-group-lg">
                  <span class="input-group-text" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-right: none;">₱</span>
                  <input type="number" name="price" class="form-control form-control-lg" 
                         step="0.01" value="<?= number_format($product['price'], 2, '.', '') ?>" required
                         style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-left: none;
                                font-size: 1rem; padding: 0.75rem 1rem; color: #1e293b;
                                box-shadow: 0 2px 4px rgba(148, 163, 184, 0.1);
                                transition: all 0.2s ease-in-out;"
                         onFocus="this.style.backgroundColor='#ffffff';
                                 this.style.boxShadow='0 4px 6px rgba(148, 163, 184, 0.15)';
                                 this.previousElementSibling.style.backgroundColor='#ffffff';"
                         onBlur="this.style.backgroundColor='#f8fafc';
                                this.style.boxShadow='0 2px 4px rgba(148, 163, 184, 0.1)';
                                this.previousElementSibling.style.backgroundColor='#f8fafc';">
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label" style="font-size: 0.95rem; font-weight: 500; color: #1a365d;">Original Price</label>
                <div class="input-group input-group-lg">
                  <span class="input-group-text" 
                        style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-right: none;">₱</span>
                  <input type="number" name="original_price" 
                         class="form-control form-control-lg" 
                         step="0.01" 
                         value="<?= $product['original_price'] ? number_format($product['original_price'], 2, '.', '') : '' ?>"
                         style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-left: none;
                                font-size: 1rem; padding: 0.75rem 1rem; color: #1e293b;
                                box-shadow: 0 2px 4px rgba(148, 163, 184, 0.1);
                                transition: all 0.2s ease-in-out;"
                         onFocus="this.style.backgroundColor='#ffffff';
                                 this.style.boxShadow='0 4px 6px rgba(148, 163, 184, 0.15)';
                                 this.previousElementSibling.style.backgroundColor='#ffffff';"
                         onBlur="this.style.backgroundColor='#f8fafc';
                                this.style.boxShadow='0 2px 4px rgba(148, 163, 184, 0.1)';
                                this.previousElementSibling.style.backgroundColor='#f8fafc';">
                </div>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label" style="font-size: 0.95rem; font-weight: 500; color: #1a365d;">
                  Stock <span class="text-danger">*</span>
                </label>
                <input type="number" name="stock" class="form-control" value="<?= (int)$product['stock'] ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Organization</label>
                <select name="org_id" class="form-select">
                  <option value="">Select Organization</option>
                  <?php foreach($organizations as $org): ?>
                    <option value="<?= $org['org_id'] ?>" <?= $org['org_id'] == $product['org_id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($org['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">Current Image</label>
              <div class="text-center border rounded p-3 bg-light">
                <?php if($product['image']): ?>
                  <img src="/pns_store/assets/uploads/<?= htmlspecialchars($product['image']) ?>" 
                       alt="<?= htmlspecialchars($product['title']) ?>" 
                       class="img-fluid rounded mb-2" style="max-height: 200px;">
                <?php else: ?>
                  <img src="/pns_store/assets/images/placeholder.png" 
                       alt="No Image" 
                       class="img-fluid rounded mb-2" style="max-height: 200px;">
                <?php endif; ?>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Update Image</label>
              <input type="file" name="image" class="form-control" accept="image/*">
              <div class="form-text">Leave empty to keep current image</div>
            </div>
          </div>
        </div>

        <hr>

        <div class="d-flex gap-2">
          <button type="submit" name="update" class="btn btn-primary">
            <i class="bi bi-save me-2"></i>Save Changes
          </button>
          <a href="products.php" class="btn btn-outline-secondary">
            <i class="bi bi-x-lg me-2"></i>Cancel
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>