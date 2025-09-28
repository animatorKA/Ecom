<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) { header('Location: cart.php'); exit; }

try {
    $pdo->beginTransaction();
    $total = 0;
    // lock and compute
    foreach ($cart as $pid => $qty) {
        $stmt = $pdo->prepare("SELECT product_id,price,stock FROM products WHERE product_id=? FOR UPDATE");
        $stmt->execute([$pid]);
        $p = $stmt->fetch();
        if (!$p || $p['stock'] < $qty) throw new Exception('Insufficient stock for product ' . $pid);
        $total += $p['price'] * $qty;
    }
    $stmt = $pdo->prepare("INSERT INTO orders (user_id,total_amount,status) VALUES (?,?,?)");
    $stmt->execute([$_SESSION['user_id'],$total,'Pending']);
    $order_id = $pdo->lastInsertId();
    foreach ($cart as $pid=>$qty) {
        $stmt = $pdo->prepare("SELECT price FROM products WHERE product_id=?"); $stmt->execute([$pid]); $p = $stmt->fetch();
        $pdo->prepare("INSERT INTO order_items (order_id,product_id,quantity,unit_price) VALUES (?,?,?,?)")
            ->execute([$order_id,$pid,$qty,$p['price']]);
        $pdo->prepare("UPDATE products SET stock = stock - ? WHERE product_id=?")
            ->execute([$qty,$pid]);
    }
    $pdo->commit();
    unset($_SESSION['cart']);
    header('Location: order_success.php?order_id='.$order_id); exit;
} catch (Exception $e) {
    $pdo->rollBack();
    die('Checkout error: '.$e->getMessage());
}
?>