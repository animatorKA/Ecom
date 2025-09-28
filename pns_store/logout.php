<?php
require_once __DIR__ . '/config.php';

// Only process logout for POST requests with valid token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && isset($_SESSION['csrf_token'])) {
    if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // Clear all session data
        $_SESSION = array();

        // Delete the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destroy the session
        session_destroy();
    }
}

// Always redirect to index, even if token invalid (to avoid leaking token validity)
header('Location: /pns_store/index.php');
exit;
?>