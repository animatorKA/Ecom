<?php
require_once __DIR__ . '/../config.php';

/**
 * Get cart items with product details including subtotals
 */
function getCartItems($pdo) {
    try {
        // Initialize or validate cart
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        $cart = $_SESSION['cart'];
        $items = [];
        
        // Return empty array if cart is empty
        if (empty($cart)) {
            return $items;
        }

        // Filter out invalid quantities
        $cart = array_filter($cart, function($qty) { return $qty > 0; });
        $_SESSION['cart'] = $cart;
        
        if (empty($cart)) {
            return $items;
        }

        // Get product details for cart items
        $ids = array_keys($cart);
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $pdo->prepare("
            SELECT p.*, o.name AS org_name 
            FROM products p
            LEFT JOIN organizations o ON p.org_id = o.org_id
            WHERE p.product_id IN ($placeholders)
            ORDER BY p.title
        ");
        $stmt->execute($ids);
        
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($products as $p) {
            $pid = $p['product_id'];
            if (isset($cart[$pid]) && $cart[$pid] > 0) {
                $qty = min($cart[$pid], $p['stock']); // Don't allow more than stock
                $p['qty'] = $qty;
                $p['subtotal'] = $qty * $p['price'];
                $items[] = $p;
                if ($qty > $p['stock']) {
                    $qty = $p['stock'];
                    $_SESSION['cart'][$pid] = $qty;
                }
                $p['qty'] = $qty;
                $p['subtotal'] = $qty * $p['price'];
                $items[] = $p;
            }
        }
        
        return $items;
    } catch (PDOException $e) {
        error_log("Cart error: " . $e->getMessage());
        return [];
    }
}

/**
 * Format price with proper currency symbol
 */
function formatPrice($price) {
    return '₱' . number_format($price, 2);
}

/**
 * Get total cart value
 */
function getCartTotal($items) {
    return array_reduce($items, function($total, $item) {
        return $total + ($item['price'] * $item['qty']);
    }, 0);
}

/**
 * Clean and validate input
 */
function clean($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a random reference number
 */
function generateRefNumber() {
    return date('Ymd') . strtoupper(substr(uniqid(), -6));
}

/**
 * Check if product is on sale
 */
function isOnSale($product) {
    return !empty($product['original_price']) && $product['original_price'] > $product['price'];
}

/**
 * Calculate discount percentage
 */
function getDiscountPercentage($original, $current) {
    if ($original <= 0) return 0;
    return round(($original - $current) / $original * 100);
}

/**
 * Validate quantity against stock
 */
function validateQuantity($qty, $stock) {
    $qty = max(1, min((int)$qty, (int)$stock));
    return $qty;
}

/**
 * Get product image URL
 */
function getProductImage($image) {
    return $image ? '/pns_store/assets/uploads/' . clean($image) : '/pns_store/assets/images/placeholder.png';
}
?>