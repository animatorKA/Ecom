<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/CartManager.php';

header('Content-Type: application/json; charset=utf-8');

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']); 
    exit;
}

try {
    $cart = new CartManager($pdo);
    $result = $cart->removeItem($product_id);
    echo json_encode($result);
    exit;
} catch (Exception $e) {
    error_log("Cart remove error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
    exit;
}