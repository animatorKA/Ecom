<?php
// admin/admin_layout.php
?>
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
                        <i class="bi <?= $page_icon ?? 'bi-grid' ?> me-2"></i>
                        <?= $page_title ?? 'Admin' ?>
                    </h1>
                    <?php if (!empty($page_subtitle)): ?>
                        <p class="text-muted mb-0"><?= $page_subtitle ?></p>
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
            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>