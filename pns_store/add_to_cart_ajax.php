<?php
// add_to_cart_ajax.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/CartManager.php';

header('Content-Type: application/json; charset=utf-8');

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !CartManager::verifyCSRFToken($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$qty = isset($_POST['qty']) ? max(1, (int)$_POST['qty']) : 1;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']); 
    exit;
}

try {
    $cart = new CartManager($pdo);
    $result = $cart->addItem($product_id, $qty);
    echo json_encode($result);
    exit;
} catch (Exception $e) {
    error_log("Cart error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
    exit;
}
