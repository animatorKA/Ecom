<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Validate required fields
$required_fields = ['name', 'email', 'subject', 'message'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
}

// Validate email
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

try {
    // Insert into messages table
    $stmt = $pdo->prepare("
        INSERT INTO messages (name, email, subject, message, created_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $_POST['name'],
        $_POST['email'],
        $_POST['subject'],
        $_POST['message']
    ]);

    // Send email notification (you can implement this based on your email setup)
    // mail('admin@example.com', 'New Contact Form Submission', $message);

    echo json_encode([
        'success' => true, 
        'message' => 'Thank you for your message. We will get back to you soon!'
    ]);

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Sorry, there was an error sending your message. Please try again later.'
    ]);
}