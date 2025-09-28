<?php
// Get current page for highlighting active links
$current_page = basename($_SERVER['PHP_SELF']);
$current_path = $_SERVER['REQUEST_URI'];
$is_admin = strpos($current_path, '/admin/') !== false;
$is_org = strpos($current_path, '/org/') !== false;

function isActive($page) {
    global $current_page, $current_path;
    
    if (is_array($page)) {
        foreach ($page as $p) {
            if ($current_page === $p) return true;
        }
        return false;
    }
    
    // Special case for admin section
    if ($page === 'admin' && strpos($current_path, '/admin/') !== false) return true;
    if ($page === 'org' && strpos($current_path, '/org/') !== false) return true;
    
    return $current_page === $page;
}