<?php
// Set page info before including template
$page_title = "Products";
$page_icon = "bi-box";
$active_nav = "products";
$page_subtitle = "Manage store products";

// Initialize database helper - $pdo comes from config.php via admin_functions.php
require_once __DIR__ . '/admin_template.php';  // This includes admin_functions.php which has all we need
$db = new DatabaseHelper($pdo);

// Get list of organizations and categories
try {
    $organizations = $db->select("SELECT org_id, name FROM organizations ORDER BY name");
    $categories = $db->select("SELECT * FROM categories ORDER BY name");
} catch (Exception $e) {
    setErrorMessage($e->getMessage());
}

// Handle add product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    // Verify CSRF token
    if (!verifyCSRFToken()) {
        setErrorMessage("Invalid form submission, please try again.");
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    try {
        // Validate form data
        validateForm($_POST, 
            // Rules setup
            function($validator) {
                $validator
                    ->addRule('title', 'Product Title', ['required', ['min', 3], ['max', 255]])
                    ->addRule('price', 'Price', ['required', 'float', ['min_val', 0]])
                    ->addRule('stock', 'Stock', ['required', 'integer', ['min_val', 0]])
                    ->addRule('description', 'Description', ['required', ['min', 10]])
                    ->addRule('original_price', 'Original Price', ['float', ['min_val', 0]]);
            },
            // Data processor
            function($data) use ($db) { 
                return $data;
            }
        );

        // Sanitize input
        $title = FormValidator::sanitize($_POST['title']);
        $description = FormValidator::sanitize($_POST['description'], 'html');
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];
        $orig = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
        $org_id = isset($_POST['org_id']) ? (int)$_POST['org_id'] : null;
        $category_ids = isset($_POST['categories']) ? array_map('intval', $_POST['categories']) : [];
        
        // Handle image upload
        $imgName = null;
        if (!empty($_FILES['image']['name'])) {
            // Validate file
            $imageValidator = getValidator($_FILES['image']);
            $imageValidator->addRule('type', 'File type', [
                function($value) {
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    return in_array($value, $allowedTypes) ? true : "Only JPG, PNG and GIF files are allowed.";
                }
            ])->addRule('size', 'File size', [
                function($value) {
                    $maxSize = 5 * 1024 * 1024; // 5MB
                    return $value <= $maxSize ? true : "Maximum file size is 5MB.";
                }
            ])->addRule('error', 'Upload status', [
                function($value) {
                    return $value === UPLOAD_ERR_OK ? true : "File upload failed.";
                }
            ]);

            if (!$imageValidator->validate()) {
                throw new Exception($imageValidator->getFirstError());
            }
            
            // Process upload
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $imgName = uniqid('product_') . '.' . $ext;
            $uploadPath = __DIR__ . '/../assets/uploads/' . $imgName;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                throw new Exception("Failed to upload image.");
            }
        }

        // Insert product using database helper
        $productData = [
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'original_price' => $orig,
            'stock' => $stock,
            'image' => $imgName,
            'org_id' => $org_id
        ];
        
        $product_id = handleDatabaseOperation(
            function($db) use ($productData, $category_ids) {
                // Insert product
                $product_id = $db->insert('products', $productData);
                
                // Insert categories
                if (!empty($category_ids)) {
                    foreach ($category_ids as $category_id) {
                        $db->insert('product_categories', [
                            'product_id' => $product_id,
                            'category_id' => (int)$category_id
                        ]);
                    }
                }
                
                return $product_id;
            },
            "Product successfully added.",
            "Error adding product",
            true
        );
        
        // Set success message and redirect
        setSuccessMessage("Product successfully added.");
        header('Location: /pns_store/admin/products.php');
        exit;
    } catch (Exception $e) {
        if (isset($imgName)) {
            $uploadPath = __DIR__ . '/../assets/uploads/' . $imgName;
            if (file_exists($uploadPath)) {
                unlink($uploadPath);
            }
        }
        setErrorMessage("Error adding product: " . $e->getMessage());
        error_log("Product addition error: " . $e->getMessage());
    }
}

// Handle delete product request via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    try {
        handleDatabaseOperation(
            function($db) {
                $product_id = (int)$_POST['product_id'];
                
                // Get product info to delete image
                $product = $db->select(
                    "SELECT image FROM products WHERE product_id = ?",
                    [$product_id],
                    true
                );

                // Delete product and its categories
                $db->delete('product_categories', ['product_id' => $product_id]);
                $db->delete('products', ['product_id' => $product_id]);

                // Delete the image file if it exists
                if ($product && $product['image']) {
                    $imagePath = __DIR__ . '/../assets/uploads/' . $product['image'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                
                return true;
            },
            null,
            "Error deleting product",
            true
        );

        echo json_encode(['success' => true]);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// Get all products with related data
try {
    $products = $db->select("
        SELECT p.*, o.name as org_name,
               GROUP_CONCAT(c.name) as category_names
        FROM products p 
        LEFT JOIN organizations o ON p.org_id = o.org_id
        LEFT JOIN product_categories pc ON p.product_id = pc.product_id
        LEFT JOIN categories c ON pc.category_id = c.category_id
        GROUP BY p.product_id
        ORDER BY p.created_at DESC
    ");
} catch (Exception $e) {
    $products = [];
    setErrorMessage("Error loading products: " . $e->getMessage());
}

// Start output buffering for the page content
ob_start();
?>
<div class="container py-4">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h2 class="h3 mb-0">Products Management</h2>
      <p class="text-muted mb-0">Manage your store's products and inventory.</p>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="/pns_store/" class="text-decoration-none">Store</a></li>
        <li class="breadcrumb-item"><a href="/pns_store/admin/dashboard.php" class="text-decoration-none">Admin</a></li>
        <li class="breadcrumb-item active">Products</li>
      </ol>
    </nav>
  </div>

  <!-- Products Table Card -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
      <div class="d-flex align-items-center justify-content-between">
        <h5 class="mb-0">All Products</h5>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal">
          <i class="bi bi-plus-lg me-2"></i>Add Product
        </button>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr>
              <th class="fw-medium">ID</th>
              <th class="fw-medium">Image</th>
              <th class="fw-medium">Title</th>
              <th class="fw-medium">Price</th>
              <th class="fw-medium">Original Price</th>
              <th class="fw-medium">Stock</th>
              <th class="fw-medium">Organization</th>
              <th class="fw-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($products as $p): ?>
            <tr>
              <td><?=$p['product_id']?></td>
              <td>
                <?php if($p['image']): ?>
                  <img src="/pns_store/assets/uploads/<?=htmlspecialchars($p['image'])?>" alt="Product" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                <?php else: ?>
                  <img src="/pns_store/assets/images/placeholder.png" alt="No Image" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                <?php endif; ?>
              </td>
              <td class="text-break" style="max-width: 200px;"><?=htmlspecialchars($p['title'])?></td>
              <td>₱<?=number_format($p['price'],2)?></td>
              <td><?=$p['original_price'] ? '₱'.number_format($p['original_price'],2) : '-'?></td>
              <td>
                <span class="badge <?=(int)$p['stock'] > 0 ? 'bg-success' : 'bg-danger'?>">
                  <?=(int)$p['stock']?>
                </span>
              </td>
              <td><?=htmlspecialchars($p['org_name'] ?? '-')?></td>
              <td>
                <div class="btn-group btn-group-sm">
                  <button type="button" class="btn btn-outline-primary" 
                          onclick="editProduct(<?=$p['product_id']?>)">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button type="button" class="btn btn-outline-danger" 
                          onclick="deleteProduct(<?=$p['product_id']?>)">
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

  <!-- Add Product Modal -->
  <div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="post" enctype="multipart/form-data">
          <!-- CSRF Token -->
          <?php echo getCSRFToken(); ?>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label" style="font-size: 0.95rem; font-weight: 500; color: #1a365d;">
                Title <span class="text-danger">*</span>
              </label>
              <input name="title" class="form-control form-control-lg" required
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
                Categories <span class="text-danger">*</span>
              </label>
              <div class="card p-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                <?php foreach ($categories as $category): ?>
                  <div class="form-check mb-2">
                    <input type="checkbox" name="categories[]" class="form-check-input" 
                           value="<?= $category['category_id'] ?>" id="addcat_<?= $category['category_id'] ?>">
                    <label class="form-check-label" for="addcat_<?= $category['category_id'] ?>">
                      <?= htmlspecialchars($category['name']) ?>
                    </label>
                  </div>
                <?php endforeach; ?>
                <small class="text-muted mt-2 d-block">Select all that apply</small>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label" style="font-size: 0.95rem; font-weight: 500; color: #1a365d;">
                  Price <span class="text-danger">*</span>
                </label>
                <div class="input-group input-group-lg">
                  <span class="input-group-text" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-right: none;">₱</span>
                  <input name="price" type="number" step="0.01" class="form-control form-control-lg" required
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
                  <span class="input-group-text" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-right: none;">₱</span>
                  <input name="original_price" type="number" step="0.01" class="form-control form-control-lg"
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
            <div class="mb-3">
              <label class="form-label" style="font-size: 0.95rem; font-weight: 500; color: #1a365d;">
                Stock <span class="text-danger">*</span>
              </label>
              <input name="stock" type="number" class="form-control form-control-lg" value="10" required
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
                               this.style.boxShadow='0 2px 4px rgba(148, 163, 184, 0.1)'"></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label" style="font-size: 0.95rem; font-weight: 500; color: #1a365d;">Organization</label>
              <select name="org_id" class="form-select form-select-lg"
                      style="background-color: #f8fafc; border: 1px solid #e2e8f0;
                             font-size: 1rem; padding: 0.75rem 1rem; color: #1e293b;
                             box-shadow: 0 2px 4px rgba(148, 163, 184, 0.1);
                             transition: all 0.2s ease-in-out;"
                      onFocus="this.style.backgroundColor='#ffffff';
                              this.style.boxShadow='0 4px 6px rgba(148, 163, 184, 0.15)';"
                      onBlur="this.style.backgroundColor='#f8fafc';
                             this.style.boxShadow='0 2px 4px rgba(148, 163, 184, 0.1)';">
                <option value="">Select Organization</option>
                <?php foreach($organizations as $org): ?>
                <option value="<?= $org['org_id'] ?>"><?= htmlspecialchars($org['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label" style="font-size: 0.95rem; font-weight: 500; color: #1a365d;">Product Image</label>
              <input type="file" name="image" class="form-control form-control-lg" accept="image/*"
                     style="background-color: #f8fafc; border: 1px solid #e2e8f0;
                            font-size: 1rem; padding: 0.75rem 1rem; color: #1e293b;
                            box-shadow: 0 2px 4px rgba(148, 163, 184, 0.1);
                            transition: all 0.2s ease-in-out;"
                     onFocus="this.style.backgroundColor='#ffffff';
                             this.style.boxShadow='0 4px 6px rgba(148, 163, 184, 0.15)';"
                     onBlur="this.style.backgroundColor='#f8fafc';
                            this.style.boxShadow='0 2px 4px rgba(148, 163, 184, 0.1)';">
              <div class="form-text" style="color: #64748b; margin-top: 0.5rem;">Recommended size: 500x500px</div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="add" class="btn btn-success">
              <i class="bi bi-plus-lg me-2"></i>Add Product
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function editProduct(id) {
  window.location.href = 'product.php?id=' + id;
}

function deleteProduct(id) {
  if (confirm('Are you sure you want to delete this product?')) {
    fetch('products.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'delete_product=1&product_id=' + id
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Refresh the page to show updated list
        window.location.reload();
      } else {
        alert('Error deleting product: ' + (data.error || 'Unknown error'));
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error deleting product. Please try again.');
    });
  }
}
</script>
<?php
$page_content = ob_get_clean();
?>