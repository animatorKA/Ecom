<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<pre>";
echo "Session Information:\n";
echo "-------------------\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Name: " . session_name() . "\n";
echo "Session Cookie Path: " . ini_get('session.cookie_path') . "\n";
echo "Session Cookie Domain: " . ini_get('session.cookie_domain') . "\n";
echo "Session Cookie Lifetime: " . ini_get('session.cookie_lifetime') . "\n\n";

echo "Session Data:\n";
echo "------------\n";
print_r($_SESSION);

echo "\nCookie Information:\n";
echo "-----------------\n";
print_r($_COOKIE);

echo "\nServer Information:\n";
echo "------------------\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "PHP_SELF: " . $_SERVER['PHP_SELF'] . "\n";
echo "HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "\n";
echo "</pre>";
?>