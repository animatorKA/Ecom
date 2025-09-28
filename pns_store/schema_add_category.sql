-- Add category column to products table
ALTER TABLE products ADD COLUMN category VARCHAR(50) DEFAULT 'General' AFTER description;

-- Update existing products with sample categories
UPDATE products SET category = 'School Supplies' WHERE category = 'General' LIMIT 5;
UPDATE products SET category = 'Uniforms' WHERE category = 'General' LIMIT 5;
UPDATE products SET category = 'Books' WHERE category = 'General' LIMIT 5;
UPDATE products SET category = 'Electronics' WHERE category = 'General' LIMIT 5;