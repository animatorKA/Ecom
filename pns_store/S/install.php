<?php
// install.php - run once
$root = new PDO('mysql:host=127.0.0.1', 'root', '');
$root->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// run SQL from the schema above (or read from schema.sql)
$sql = file_get_contents(__DIR__.'/schema.sql');
$root->exec($sql);

// create default organization and admin
$pdo = new PDO('mysql:host=127.0.0.1;dbname=pns_store', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("INSERT INTO organizations (name, description) VALUES ('School Store','Official PNS store')");
$org_id = $pdo->lastInsertId();

$admin_pass = password_hash('Admin@123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (name,email,password_hash,role,org_id) VALUES (?, ?, ?, 'admin', ?)");
$stmt->execute(['Admin','admin@pns.local',$admin_pass,$org_id]);

echo 'Install finished. Admin: admin@pns.local pass: Admin@123';
?>
