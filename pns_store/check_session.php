<?php
require_once __DIR__ . '/config.php';

// Print session configuration
echo "<pre>";
echo "Session name: " . session_name() . "\n";
echo "Session save path: " . session_save_path() . "\n";
echo "Session cookie parameters:\n";
print_r(session_get_cookie_params());
echo "\nCurrent session data:\n";
print_r($_SESSION);
echo "</pre>";
?>