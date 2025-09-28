<?php
require_once 'config.php';

try {
    // Check existing admin accounts
    $stmt = $pdo->query("SELECT user_id, name, email, role, status FROM users WHERE role = 'admin'");
    $admins = $stmt->fetchAll();
    
    echo "Current Admin Accounts:\n";
    print_r($admins);
    
    // If no admin exists, create one
    if (empty($admins)) {
        $password_hash = password_hash('Admin123!', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, status) VALUES (?, ?, ?, 'admin', 'active')");
        $stmt->execute(['System Admin', 'admin@pns.edu.ph', $password_hash]);
        
        echo "\nCreated new admin account:\n";
        echo "Email: admin@pns.edu.ph\n";
        echo "Password: Admin123!\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>