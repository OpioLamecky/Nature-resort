<?php
/**
 * Modern Admin Sidebar Navigation
 * Provides a collapsible sidebar with modern design
 */

// Only show if user is logged in as admin
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    return;
}

$user = $_SESSION['user'];
$currentPage = basename($_SERVER['PHP_SELF']);

// Define navigation items with permissions
$navItems = [
    [
        'title' => 'Dashboard',
        'icon' => 'fas fa-tachometer-alt',
        'url' => 'dashboard.php',
        'permission' => null,
        'active' => $currentPage === 'dashboard.php'
    ],
    [
        'title' => 'Bookings',
        'icon' => 'fas fa-calendar-check',
        'url' => 'bookings.php',
        'permission' => 'bookings.view',
        'active' => in_array($currentPage, ['bookings.php', 'booking-create.php', 'booking-edit.php']),
        'badge' => $stats['bookings_pending'] ?? 0
    ],
    [
        'title' => 'Meal Reservations',
        'icon' => 'fas fa-utensils',
        'url' => 'meal-reservations.php',
        'permission' => 'bookings.view',
        'active' => $currentPage === 'meal-reservations.php'
    ],
    [
        'title' => 'Accommodations',
        'icon' => 'fas fa-bed',
        'url' => 'accommodations-manage.php',
        'permission' => 'accommodations.view',
        'active' => strpos($currentPage, 'accommodations') !== false
    ],
    [
        'title' => 'Staff Management',
        'icon' => 'fas fa-users',
        'url' => 'staff.php',
        'permission' => 'users.view',
        'active' => $currentPage === 'staff.php'
    ],
    [
        'title' => 'Reviews',
        'icon' => 'fas fa-star',
        'url' => 'testimonials.php',
        'permission' => 'testimonials.view',
        'active' => $currentPage === 'testimonials.php',
        'badge' => $stats['reviews_pending'] ?? 0
    ],
    [
        'title' => 'Newsletter',
        'icon' => 'fas fa-envelope',
        'url' => 'newsletter.php',
        'permission' => 'manage_content',
        'active' => $currentPage === 'newsletter.php'
    ],
    [
        'title' => 'Analytics',
        'icon' => 'fas fa-chart-line',
        'url' => 'analytics.php',
        'permission' => 'analytics.view',
        'active' => $currentPage === 'analytics.php'
    ],
    [
        'title' => 'Content',
        'icon' => 'fas fa-file-alt',
        'url' => '#',
        'permission' => 'content.view',
        'active' => in_array($currentPage, ['site-content.php', 'posts.php', 'pages-home.php', 'pages-services.php', 'about-content.php']),
        'submenu' => [
            ['title' => 'Public Page Content', 'url' => 'site-content.php', 'icon' => 'fas fa-edit'],
            ['title' => 'Blog Posts', 'url' => 'posts.php', 'icon' => 'fas fa-newspaper'],
            ['title' => 'Home Page', 'url' => 'pages-home.php', 'icon' => 'fas fa-home'],
            ['title' => 'Services', 'url' => 'pages-services.php', 'icon' => 'fas fa-concierge-bell'],
            ['title' => 'About Content', 'url' => 'about-content.php', 'icon' => 'fas fa-info-circle']
        ]
    ],
    [
        'title' => 'Gift Shop',
        'icon' => 'fas fa-store',
        'url' => 'admin_shop.php',
        'path' => base_url('admin_shop.php'),
        'permission' => null,
        'roles' => ['Super Admin'],
        'active' => $currentPage === 'admin_shop.php'
    ],
    [
        'title' => 'Categories',
        'icon' => 'fas fa-folder',
        'url' => 'categories.php',
        'permission' => null,
        'active' => in_array($currentPage, ['categories.php', 'downloads.php'])
    ],
    [
        'title' => 'Announcements',
        'icon' => 'fas fa-bullhorn',
        'url' => 'announcements.php',
        'permission' => null,
        'active' => $currentPage === 'announcements.php'
    ],
    [
        'title' => 'Settings',
        'icon' => 'fas fa-cog',
        'url' => '#',
        'permission' => 'settings.view',
        'active' => in_array($currentPage, ['pages-footer.php', 'profile.php', 'test-sidebar.php']),
        'submenu' => [
            ['title' => 'Footer Links', 'url' => 'pages-footer.php', 'icon' => 'fas fa-link'],
            ['title' => 'My Profile', 'url' => 'profile.php', 'icon' => 'fas fa-user-circle'],
            ['title' => 'Test Sidebar', 'url' => 'test-sidebar.php', 'icon' => 'fas fa-vial']
        ]
    ]
];

$sectionByTitle = [
    'Dashboard' => 'Overview',
    'Bookings' => 'Guest Operations',
    'Meal Reservations' => 'Guest Operations',
    'Accommodations' => 'Property',
    'Staff Management' => 'People',
    'Reviews' => 'People',
    'Newsletter' => 'Communications',
    'Analytics' => 'Insights',
    'Content' => 'Website',
    'Gift Shop' => 'Website',
    'Categories' => 'Website',
    'Announcements' => 'Website',
    'Settings' => 'Account',
];
$visibleNavItems = [];
foreach ($navItems as $item) {
    if (!empty($item['roles']) && !in_array($user['role'] ?? '', $item['roles'], true)) {
        continue;
    }
    if (!empty($item['permission']) && !has_permission($item['permission'])) {
        continue;
    }
    $item['section'] = $sectionByTitle[$item['title']] ?? 'Other';
    $visibleNavItems[] = $item;
}
?>

<!-- Modern Admin Sidebar -->
<div class="admin-sidebar" id="adminSidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <img src="<?= asset_url('images/kalitusi-logo.png') ?>" alt="Logo" class="sidebar-logo">
            <span class="brand-text">Kalitusi Admin</span>
        </div>
        <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Collapse sidebar" aria-expanded="true">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- User Profile Section -->
    <div class="sidebar-user">
        <div class="user-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="user-info">
            <div class="user-name"><?= sanitize($user['username']) ?></div>
            <div class="user-role"><?= sanitize($user['role']) ?></div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav" aria-label="Admin navigation">
        <ul class="nav-list">
            <?php $currentSection = null; foreach ($visibleNavItems as $item): ?>
                <?php if ($currentSection !== $item['section']): $currentSection = $item['section']; ?>
                    <li class="nav-section-label"><?= sanitize($currentSection) ?></li>
                <?php endif; ?>
                <?php $submenuId = 'submenu-' . strtolower(str_replace(' ', '-', $item['title'])); ?>
                <li class="nav-item <?= $item['active'] ? 'active' : '' ?> <?= !empty($item['submenu']) ? 'has-submenu' . ($item['active'] ? ' submenu-open' : '') : '' ?>">
                    <a href="<?= !empty($item['submenu']) ? '#' : ($item['path'] ?? base_url('admin/' . $item['url'])) ?>"
                       class="nav-link <?= !empty($item['submenu']) ? 'submenu-toggle' : '' ?>"
                       data-tooltip="<?= sanitize($item['title']) ?>"
                       <?= !empty($item['submenu']) ? 'aria-expanded="' . ($item['active'] ? 'true' : 'false') . '" aria-controls="' . $submenuId . '"' : '' ?>>
                        <i class="<?= $item['icon'] ?>"></i>
                        <span class="nav-text"><?= $item['title'] ?></span>
                        <?php if (!empty($item['badge']) && $item['badge'] > 0): ?>
                            <span class="nav-badge"><?= $item['badge'] ?></span>
                        <?php endif; ?>
                        <?php if (!empty($item['submenu'])): ?>
                            <i class="fas fa-chevron-down submenu-arrow"></i>
                        <?php endif; ?>
                    </a>
                    
                    <?php if (!empty($item['submenu'])): ?>
                        <ul class="submenu <?= $item['active'] ? 'show' : '' ?>" id="<?= $submenuId ?>">
                            <?php foreach ($item['submenu'] as $subitem): ?>
                                <li class="submenu-item">
                                    <a href="<?= $subitem['path'] ?? base_url('admin/' . $subitem['url']) ?>" class="submenu-link">
                                        <i class="<?= $subitem['icon'] ?>"></i>
                                        <span><?= $subitem['title'] ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <a href="<?= base_url('public/') ?>" class="footer-link" target="_blank">
            <i class="fas fa-external-link-alt"></i>
            <span>View Website</span>
        </a>
        <a href="<?= base_url('public/logout.php') ?>" class="footer-link logout-link">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
