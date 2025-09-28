<?php
// config.php - Central configuration for PNS Store

// -------------------------
// SAFE SESSION START
// -------------------------

// Clean up any existing session
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
    // Clear session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    if (isset($_COOKIE['PNSSTORE'])) {
        setcookie('PNSSTORE', '', time()-3600, '/');
    }
}

// Set session name first
session_name('PNSSTORE');

// Configure session settings
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_secure', '0');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', '3600');
ini_set('session.cookie_lifetime', '0');

// Set cookie parameters
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/pns_store/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Start or resume session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check session age
if (!empty($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
    // If last activity was more than 1 hour ago
    session_unset();
    session_destroy();
    // Start fresh session
    session_start();
}

// Update last activity time
$_SESSION['last_activity'] = time();

// -------------------------
// DATABASE CONNECTION
// -------------------------

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'pns_store');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Create PDO connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

// -------------------------
// APPLICATION SETTINGS
// -------------------------

// Base URL and paths
define('BASE_URL', '/pns_store');
define('UPLOAD_DIR', __DIR__ . '/assets/uploads');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Ensure upload directory exists
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// -------------------------
// ERROR HANDLING
// -------------------------

// Development error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// -------------------------
// HELPER FUNCTIONS
// -------------------------

function redirect($path) {
    header("Location: " . BASE_URL . $path);
    exit;
}