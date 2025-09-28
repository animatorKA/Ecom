<?php
require_once __DIR__ . '/config.php';

echo "Testing database connection...<br>";

try {
    // Test products table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $productCount = $stmt->fetch()['count'];
    echo "Products in database: " . $productCount . "<br>";

    // Test organizations table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM organizations");
    $orgCount = $stmt->fetch()['count'];
    echo "Organizations in database: " . $orgCount . "<br>";

} catch (PDOException $e) {
    die("Error: " . htmlspecialchars($e->getMessage()));
}
?>