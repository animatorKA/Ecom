<?php
// Force clean all sessions and cookies
session_start();
session_destroy();

// Clear all cookies
if (isset($_SERVER['HTTP_COOKIE'])) {
    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
    foreach($cookies as $cookie) {
        $parts = explode('=', $cookie);
        $name = trim($parts[0]);
        setcookie($name, '', time()-3600);
        setcookie($name, '', time()-3600, '/');
        setcookie($name, '', time()-3600, '/pns_store/');
    }
}

// Redirect to login
header('Location: /pns_store/login.php');
exit;
?>