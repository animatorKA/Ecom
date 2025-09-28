<?php
require_once __DIR__ . '/../config.php';

function isLoggedIn() {
    error_log('isLoggedIn check - Session data: ' . print_r($_SESSION, true));
    
    // Basic session checks
    if (empty($_SESSION['user_id'])) {
        error_log('Not logged in - no user_id');
        return false;
    }
    
    if (empty($_SESSION['role'])) {
        error_log('Not logged in - no role');
        return false;
    }
    
    if (empty($_SESSION['last_activity'])) {
        error_log('Not logged in - no last_activity');
        return false;
    }
    
    // Check if session has expired (1 hour)
    if (time() - $_SESSION['last_activity'] > 3600) {
        error_log('Session expired');
        return false;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    error_log('User is logged in with role: ' . ($_SESSION['role'] ?? 'none'));
    return true;
}

function requireLogin() {
    error_log('Requiring login for URI: ' . $_SERVER['REQUEST_URI']);
    
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        error_log('Not logged in, redirecting to login page. Will return to: ' . $_SERVER['REQUEST_URI']);
        header('Location: /pns_store/login.php');
        exit;
    }
    
    error_log('User is logged in as: ' . $_SESSION['role']);
}

function requireRole($role) {
    // Prevent redirect loops by not redirecting on login page
    if (strpos($_SERVER['REQUEST_URI'], 'login.php') !== false) {
        return true;
    }

    if (!isLoggedIn()) {
        // Store the current URL for redirect after login, but only if not already set
        if (empty($_SESSION['redirect_after_login'])) {
            $_SESSION['redirect_after_login'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            error_log('Setting redirect_after_login to: ' . $_SESSION['redirect_after_login']);
        }
        header('Location: /pns_store/login.php');
        exit;
    }
    
    // Admin role can access everything
    if ($_SESSION['role'] === 'admin') {
        error_log('User has admin role, access granted');
        return true;
    }
    
    // For non-admin roles, check exact match
    if (empty($_SESSION['role']) || $_SESSION['role'] !== $role) {
        error_log('Access denied. User role: ' . ($_SESSION['role'] ?? 'none') . ', Required role: ' . $role);
        http_response_code(403);
        include __DIR__ . '/../error/403.php';
        exit;
    }
    
    return true;
}

function regenerateSession() {
    if (!empty($_SESSION)) {
        $data = $_SESSION;
        session_regenerate_id(true);
        $_SESSION = $data;
    }
}

function createUserSession($user) {
    // Clear any existing session data
    session_unset();
    
    // Create new session
    regenerateSession();
    
    // Set essential session data
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['org_id'] = $user['org_id'] ?? null;
    $_SESSION['name'] = $user['name'];
    $_SESSION['user_token'] = bin2hex(random_bytes(32));
    $_SESSION['last_activity'] = time();
    
    error_log('Created new session for user: ' . print_r($_SESSION, true));
}
?>