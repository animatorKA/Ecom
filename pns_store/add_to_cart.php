<?php
require_once __DIR__ . '/config.php';
$id = (int)($_POST['product_id'] ?? 0);
$qty = max(1,(int)($_POST['qty'] ?? 1));
if ($id <= 0) { header('Location: index.php'); exit; }
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
if (isset($_SESSION['cart'][$id])) $_SESSION['cart'][$id] += $qty; else $_SESSION['cart'][$id] = $qty;
header('Location: cart.php'); exit;
?>