-- Cart database structure for PNS Store-- Cart tables for PNS Store

CREATE TABLE IF NOT EXISTS carts (

-- Create carts table    cart_id INT AUTO_INCREMENT PRIMARY KEY,

CREATE TABLE IF NOT EXISTS carts (    user_id INT,

    cart_id VARCHAR(64) PRIMARY KEY,    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    user_id INT NULL,    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,);

    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL

) ENGINE=InnoDB;CREATE TABLE IF NOT EXISTS cart_items (

    cart_item_id INT AUTO_INCREMENT PRIMARY KEY,

-- Create cart items table    cart_id INT NOT NULL,

CREATE TABLE IF NOT EXISTS cart_items (    product_id INT NOT NULL,

    item_id INT AUTO_INCREMENT PRIMARY KEY,    quantity INT NOT NULL DEFAULT 1,

    cart_id VARCHAR(64) NOT NULL,    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    product_id INT NOT NULL,    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    quantity INT NOT NULL DEFAULT 1,    FOREIGN KEY (cart_id) REFERENCES carts(cart_id) ON DELETE CASCADE,

    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,    UNIQUE KEY unique_cart_product (cart_id, product_id)

    FOREIGN KEY (cart_id) REFERENCES carts(cart_id) ON DELETE CASCADE,);
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_product (cart_id, product_id)
) ENGINE=InnoDB;