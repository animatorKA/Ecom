<?php
// get_cart_ajax.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/json; charset=utf-8');

$items = getCartItems($pdo);
$cart_count = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) $cart_count = array_sum($_SESSION['cart']);

// normalize items for JSON
$payload_items = [];
foreach ($items as $it) {
    $payload_items[] = [
        'product_id' => (int)$it['product_id'],
        'title' => $it['title'],
        'qty' => (int)$it['qty'],
        'unit_price' => (float)$it['price'],
        'subtotal' => (float)$it['subtotal'],
    ];
}

echo json_encode(['items'=>$payload_items, 'cart_count'=>$cart_count]);
exit;
