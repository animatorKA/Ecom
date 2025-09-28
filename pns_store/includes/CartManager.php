<?php
/**
 * CartManager.php - Handles all cart operations with database persistence
 */
class CartManager {
    private $pdo;
    private $cart_id;
    private $user_id;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->cart_id = $_SESSION['cart_id'] ?? null;
        $this->user_id = $_SESSION['user_id'] ?? null;
        
        if (!$this->cart_id) {
            $this->createNewCart();
        }
    }

    /**
     * Verify CSRF token for cart operations
     */
    public static function verifyCSRFToken($token) {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Create a new cart and store its ID in session
     */
    private function createNewCart() {
        $this->cart_id = bin2hex(random_bytes(16)); // 32 character unique ID
        $stmt = $this->pdo->prepare("INSERT INTO carts (cart_id, user_id) VALUES (?, ?)");
        $stmt->execute([$this->cart_id, $this->user_id]);
        $_SESSION['cart_id'] = $this->cart_id;
    }

    /**
     * Add an item to cart
     */
    public function addItem($product_id, $quantity = 1) {
        try {
            // First check if product exists and has enough stock
            $stmt = $this->pdo->prepare("SELECT stock FROM products WHERE product_id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if (!$product || $product['stock'] < $quantity) {
                return ['success' => false, 'message' => 'Not enough stock available'];
            }

            // Try to insert new item
            $stmt = $this->pdo->prepare("
                INSERT INTO cart_items (cart_id, product_id, quantity)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                quantity = quantity + VALUES(quantity)
            ");
            $stmt->execute([$this->cart_id, $product_id, $quantity]);

            return [
                'success' => true,
                'message' => 'Item added to cart',
                'cart_count' => $this->getCartCount()
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error adding item to cart'];
        }
    }

    /**
     * Get all items in cart with product details
     */
    public function getItems() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    ci.*, 
                    p.title, 
                    p.price, 
                    p.image, 
                    p.stock,
                    ci.quantity,
                    COALESCE(p.price, 0) as price,
                    COALESCE(p.price * ci.quantity, 0) as subtotal,
                    o.name as org_name
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.product_id
                LEFT JOIN organizations o ON p.org_id = o.org_id
                WHERE ci.cart_id = ?
                ORDER BY ci.added_at DESC
            ");
            $stmt->execute([$this->cart_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Error fetching cart items: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update item quantity
     */
    public function updateQuantity($product_id, $quantity) {
        if ($quantity <= 0) {
            return $this->removeItem($product_id);
        }

        try {
            // Check stock
            $stmt = $this->pdo->prepare("SELECT stock FROM products WHERE product_id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if (!$product || $product['stock'] < $quantity) {
                return ['success' => false, 'message' => 'Not enough stock available'];
            }

            $stmt = $this->pdo->prepare("
                UPDATE cart_items 
                SET quantity = ?, updated_at = CURRENT_TIMESTAMP
                WHERE cart_id = ? AND product_id = ?
            ");
            $stmt->execute([$quantity, $this->cart_id, $product_id]);

            return [
                'success' => true,
                'cart_count' => $this->getCartCount()
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error updating quantity'];
        }
    }

    /**
     * Remove an item from cart
     */
    public function removeItem($product_id) {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM cart_items 
                WHERE cart_id = ? AND product_id = ?
            ");
            $stmt->execute([$this->cart_id, $product_id]);

            return [
                'success' => true,
                'cart_count' => $this->getCartCount()
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error removing item'];
        }
    }

    /**
     * Get total number of items in cart
     */
    public function getCartCount() {
        $stmt = $this->pdo->prepare("
            SELECT SUM(quantity) as count
            FROM cart_items
            WHERE cart_id = ?
        ");
        $stmt->execute([$this->cart_id]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get cart total
     */
    public function getTotal() {
        $stmt = $this->pdo->prepare("
            SELECT SUM(p.price * ci.quantity) as total
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.product_id
            WHERE ci.cart_id = ?
        ");
        $stmt->execute([$this->cart_id]);
        return (float)$stmt->fetchColumn();
    }

    /**
     * Clear all items from cart
     */
    public function clear() {
        $stmt = $this->pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        $stmt->execute([$this->cart_id]);
        return ['success' => true, 'cart_count' => 0];
    }

    /**
     * Associate cart with user (after login)
     */
    public function setUser($user_id) {
        $this->user_id = $user_id;
        $stmt = $this->pdo->prepare("UPDATE carts SET user_id = ? WHERE cart_id = ?");
        $stmt->execute([$user_id, $this->cart_id]);
    }
}