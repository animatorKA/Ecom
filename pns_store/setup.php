<?php
require_once __DIR__ . '/config.php';

try {
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $pdo->exec("USE " . DB_NAME);
    
    // Create organizations table
    $pdo->exec("CREATE TABLE IF NOT EXISTS organizations (
        org_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Create products table
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        product_id INT AUTO_INCREMENT PRIMARY KEY,
        org_id INT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        stock INT NOT NULL DEFAULT 0,
        image VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (org_id) REFERENCES organizations(org_id)
    )");

    // Insert test data if tables are empty
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM organizations");
    if ($stmt->fetch()['count'] == 0) {
        $pdo->exec("INSERT INTO organizations (name, description) VALUES
            ('Student Council', 'Official student governing body'),
            ('Science Club', 'For science enthusiasts'),
            ('Math Club', 'Mathematics and problem solving')");
    }

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    if ($stmt->fetch()['count'] == 0) {
        $pdo->exec("INSERT INTO products (org_id, name, description, price, stock) VALUES
            (1, 'School Notebook', 'High quality ruled notebook', 45.00, 100),
            (1, 'Pencil Set', '2H, HB, and 2B pencils', 25.00, 200),
            (2, 'Lab Goggles', 'Safety first!', 150.00, 50),
            (3, 'Calculator', 'Scientific calculator', 350.00, 30)");
    }

    echo "Database setup completed successfully!";

} catch (PDOException $e) {
    die("Setup failed: " . htmlspecialchars($e->getMessage()));
}
?>