<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once __DIR__ . '/../includes/partials/meta.php'; ?>
    
    <!-- Admin Security Headers -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net fonts.googleapis.com; font-src fonts.gstatic.com; img-src 'self' data: blob:;">
    
    <title><?= htmlspecialchars($page_title ?? 'Admin') ?> - PNS Store Admin</title>
    
    <!-- Admin Styles -->
    <link rel="stylesheet" href="/pns_store/assets/css/admin.css">
    <link rel="stylesheet" href="/pns_store/assets/css/professional-dashboard.css">
    <link rel="stylesheet" href="/pns_store/assets/css/spacing.css">
    <link rel="stylesheet" href="/pns_store/assets/css/admin-forms.css">
    
    <!-- CSRF Token -->
    <?= getCSRFToken() ?>
</head>
<body>
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
            
                <div class="nav flex-column gap-1">
                    <a href="/pns_store/admin/dashboard.php" class="nav-link <?= $active_nav === 'dashboard' ? 'active' : '' ?>">
                        <i class="bi bi-speedometer2 me-2"></i>
                        Dashboard
                    </a>
                    <a href="/pns_store/admin/products.php" class="nav-link <?= $active_nav === 'products' ? 'active' : '' ?>">
                        <i class="bi bi-box me-2"></i>
                        Products
                    </a>
                    <a href="/pns_store/admin/categories.php" class="nav-link <?= $active_nav === 'categories' ? 'active' : '' ?>">
                        <i class="bi bi-tags me-2"></i>
                        Categories
                    </a>
                    <a href="/pns_store/admin/orders.php" class="nav-link <?= $active_nav === 'orders' ? 'active' : '' ?>">
                        <i class="bi bi-cart me-2"></i>
                        Orders
                    </a>
                    <a href="/pns_store/admin/organizations.php" class="nav-link <?= $active_nav === 'organizations' ? 'active' : '' ?>">
                        <i class="bi bi-building me-2"></i>
                        Organizations
                    </a>
                    <a href="/pns_store/admin/messages.php" class="nav-link <?= $active_nav === 'messages' ? 'active' : '' ?> d-flex align-items-center">
                        <i class="bi bi-envelope me-2"></i>
                        Messages
                        <?php if (isset($newMessagesCount) && $newMessagesCount > 0): ?>
                            <span class="badge text-bg-danger ms-auto rounded-pill"><?= $newMessagesCount ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="/pns_store/admin/users.php" class="nav-link <?= $active_nav === 'users' ? 'active' : '' ?>">
                        <i class="bi bi-people me-2"></i>
                        Users
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-container">
                <div class="dashboard-content">
                    <!-- Page Header -->
                    <div class="section-header">
                        <div>
                            <h1 class="section-title">
                                <i class="bi <?= $page_icon ?>"></i>
                                <?= $page_title ?>
                            </h1>
                            <?php if (isset($page_subtitle)): ?>
                                <p class="text-muted mb-0"><?= $page_subtitle ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if (isset($page_actions)): ?>
                            <div class="page-actions">
                                <?= $page_actions ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Main Content Area -->
                    <?php if (isset($error)): ?>
                        <?= displayError($error) ?>
                    <?php endif; ?>

                    <?php if (isset($success)): ?>
                        <?= displaySuccess($success) ?>
                    <?php endif; ?>

                    <!-- Page specific content goes here -->