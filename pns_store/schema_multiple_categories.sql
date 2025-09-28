-- Create categories table
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_category_name (name)
);

-- Create product_categories junction table
CREATE TABLE product_categories (
    product_id INT,
    category_id INT,
    PRIMARY KEY (product_id, category_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
);

-- Migrate existing categories from products table
INSERT IGNORE INTO categories (name)
SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '';

-- Insert into junction table based on existing categories
INSERT INTO product_categories (product_id, category_id)
SELECT p.product_id, c.category_id
FROM products p
JOIN categories c ON p.category = c.name
WHERE p.category IS NOT NULL AND p.category != '';

-- Remove old category column
ALTER TABLE products DROP COLUMN category;