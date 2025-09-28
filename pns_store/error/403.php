<?php
$page_title = "Access Denied";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="display-1 text-danger mb-4">
                <i class="bi bi-shield-x"></i>
            </div>
            <h1 class="display-4 mb-4">Access Denied</h1>
            <p class="lead text-muted mb-4">
                Sorry, you don't have permission to access this area. This section is restricted to administrators only.
            </p>
            <a href="/pns_store/" class="btn btn-primary">
                <i class="bi bi-house me-2"></i>Return to Homepage
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>