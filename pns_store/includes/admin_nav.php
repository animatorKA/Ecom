<?php
function renderAdminNav($activePage = '') {
    $links = [
        'dashboard' => ['icon' => 'speedometer2', 'title' => 'Dashboard'],
        'products' => ['icon' => 'box', 'title' => 'Products'],
        'categories' => ['icon' => 'tags', 'title' => 'Categories'],
        'organizations' => ['icon' => 'buildings', 'title' => 'Organizations'],
        'messages' => ['icon' => 'envelope', 'title' => 'Messages'],
        'orders' => ['icon' => 'cart', 'title' => 'Orders'],
        'users' => ['icon' => 'people', 'title' => 'Users'],
    ];
    
    echo '<div class="admin-sidebar">';
    echo '<div class="nav flex-column">';
    
    foreach ($links as $page => $info) {
        $isActive = $activePage === $page ? ' active' : '';
        echo sprintf(
            '<a href="/pns_store/admin/%s.php" class="nav-link%s">
                <i class="bi bi-%s"></i>
                %s
            </a>',
            $page,
            $isActive,
            $info['icon'],
            $info['title']
        );
    }
    
    echo '</div>';
    echo '</div>';
}