<?php
require_once '../config.php';

try {
    // Check existing admin accounts
    $stmt = $pdo->query("SELECT user_id, name, email, role, status FROM users WHERE role = 'admin'");
    $admins = $stmt->fetchAll();
    
    echo "Current Admin Accounts:\n";
    print_r($admins);
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>