<?php
require_once __DIR__ . '/config.php';

try {
    // Create carts table
    $pdo->exec("CREATE TABLE IF NOT EXISTS carts (
        cart_id VARCHAR(64) PRIMARY KEY,
        user_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
    ) ENGINE=InnoDB");

    // Create cart items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS cart_items (
        item_id INT AUTO_INCREMENT PRIMARY KEY,
        cart_id VARCHAR(64) NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (cart_id) REFERENCES carts(cart_id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
        UNIQUE KEY unique_cart_product (cart_id, product_id)
    ) ENGINE=InnoDB");
    
    echo "Cart tables created successfully!\n";
} catch (PDOException $e) {
    die("Error creating cart tables: " . $e->getMessage() . "\n");
}