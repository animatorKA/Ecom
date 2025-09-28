<?php
// admin/admin_template.php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/admin_errors.log');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include core files if not already included
require_once __DIR__ . '/admin_functions.php';

// Debug log current session state with session ID
error_log('Admin template session state [SID: ' . session_id() . ']: ' . print_r($_SESSION, true));

// Clear any existing redirect_after_login to prevent loops
if (isset($_SESSION['redirect_after_login'])) {
    error_log('Clearing redirect_after_login to prevent loops');
    unset($_SESSION['redirect_after_login']);
}

// Default page settings if not set
$page_title = $page_title ?? 'Admin';
$page_icon = $page_icon ?? 'bi-grid';
$active_nav = $active_nav ?? '';
$page_subtitle = $page_subtitle ?? '';
$success = getSuccessMessage();
$error = getErrorMessage();

// Set no-cache headers for admin pages
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Debug before role check
error_log('Checking admin role for: ' . $_SERVER['REQUEST_URI']);

// Verify admin role
requireRole('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($page_title) ?> - PNS Store Admin</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Admin Styles -->
    <link rel="stylesheet" href="/pns_store/assets/css/admin.css">
    <link rel="stylesheet" href="/pns_store/assets/css/professional-dashboard.css">
    <link rel="stylesheet" href="/pns_store/assets/css/admin-forms.css">
    <link rel="stylesheet" href="/pns_store/assets/css/spacing.css">
    
    <!-- jQuery (some admin features need it) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
</head>
<body class="admin-body">
    <div class="admin-layout">
        <!-- Sidebar Toggle Button -->
        <button id="sidebarToggle" class="sidebar-toggle-btn">
            <i class="bi bi-list"></i>
        </button>

        <!-- Sidebar -->
        <nav class="admin-sidebar">
            <div class="d-flex flex-column h-100">
                <div class="p-3 border-bottom">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-shield-check fs-4 text-success me-2"></i>
                        <div>
                            <div class="fw-semibold">Admin Panel</div>
                            <small class="text-muted">Store Management</small>
                        </div>
                    </div>
                </div>
                
                <div class="nav flex-column gap-1 p-2">
                    <a href="/pns_store/admin/dashboard.php" class="nav-link <?= $active_nav === 'dashboard' ? 'active' : '' ?> px-3 py-2 rounded">
                        <i class="bi bi-speedometer2 me-2"></i>
                        Dashboard
                    </a>
                    <a href="/pns_store/admin/products.php" class="nav-link <?= $active_nav === 'products' ? 'active' : '' ?> px-3 py-2 rounded">
                        <i class="bi bi-box me-2"></i>
                        Products
                    </a>
                    <a href="/pns_store/admin/categories.php" class="nav-link <?= $active_nav === 'categories' ? 'active' : '' ?> px-3 py-2 rounded">
                        <i class="bi bi-tags me-2"></i>
                        Categories
                    </a>
                    <a href="/pns_store/admin/orders.php" class="nav-link <?= $active_nav === 'orders' ? 'active' : '' ?> px-3 py-2 rounded">
                        <i class="bi bi-cart me-2"></i>
                        Orders
                    </a>
                    <a href="/pns_store/admin/organizations.php" class="nav-link <?= $active_nav === 'organizations' ? 'active' : '' ?> px-3 py-2 rounded">
                        <i class="bi bi-buildings me-2"></i>
                        Organizations
                    </a>
                    <a href="/pns_store/admin/users.php" class="nav-link <?= $active_nav === 'users' ? 'active' : '' ?> px-3 py-2 rounded">
                        <i class="bi bi-people me-2"></i>
                        Users
                    </a>
                    <a href="/pns_store/admin/messages.php" class="nav-link <?= $active_nav === 'messages' ? 'active' : '' ?> px-3 py-2 rounded">
                        <i class="bi bi-envelope me-2"></i>
                        Messages
                    </a>
                </div>

                <div class="mt-auto p-3 border-top">
                    <a href="/pns_store/logout.php" class="nav-link text-danger px-3 py-2 rounded">
                        <i class="bi bi-box-arrow-left me-2"></i>
                        Sign Out
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Page Header -->
            <header class="admin-header p-3 border-bottom bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="h3 mb-0">
                            <i class="bi <?= $page_icon ?> me-2"></i>
                            <?= htmlspecialchars($page_title) ?>
                        </h1>
                        <?php if ($page_subtitle): ?>
                            <p class="text-muted mb-0"><?= htmlspecialchars($page_subtitle) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($page_actions)): ?>
                        <div class="page-actions">
                            <?= $page_actions ?>
                        </div>
                    <?php endif; ?>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-3">
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>