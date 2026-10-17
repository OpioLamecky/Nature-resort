<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/permissions.php';
$title = $title ?? 'Admin Dashboard | Kalitusi Nature Resort';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    redirect(base_url('public/login.php'));
}

// Initialize stats for sidebar badges if not already set
if (!isset($stats)) {
    try {
        $stats = [
            'bookings_pending' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn(),
            'reviews_pending' => $pdo->query("SELECT COUNT(*) FROM testimonials WHERE status = 'pending'")->fetchColumn(),
        ];
    } catch (Exception $e) {
        $stats = [
            'bookings_pending' => 0,
            'reviews_pending' => 0,
        ];
    }
}
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Kalitusi Nature Resort Admin Dashboard">
    <meta name="theme-color" content="#1C2D2A">
    <meta name="robots" content="noindex, nofollow">
    
    <title><?= sanitize($title) ?></title>
    
    <!-- Stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>?v=<?= (int)filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/admin-sidebar.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/admin-theme.css') ?>?v=<?= (int)filemtime(__DIR__ . '/../assets/css/admin-theme.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/admin-timepicker.css') ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset_url('images/kalitusi-logo-transparent.png') ?>">
</head>
<body class="admin-layout">
    
    <?php 
    // Include sidebar navigation
    include __DIR__ . '/admin_sidebar.php';
    ?>
    
    <!-- Main Content Area -->
    <div class="main-content" id="mainContent">
        <!-- Top Header Bar -->
        <header class="admin-header">
            <div class="header-left">
                <button class="mobile-sidebar-toggle" id="mobileSidebarToggle" type="button" aria-label="Open navigation" aria-controls="adminSidebar" aria-expanded="false">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title"><?= $pageTitle ?? 'Dashboard' ?></h1>
            </div>
            <div class="header-right">
                <div class="header-actions">
                    <!-- Quick Actions -->
                    <div class="dropdown">
                        <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-plus me-1"></i> Quick Add
                        </button>
                        <ul class="dropdown-menu">
                            <?php if (has_permission('bookings.create')): ?>
                            <li><a class="dropdown-item" href="<?= base_url('admin/booking-create.php') ?>">
                                <i class="fas fa-calendar-plus me-2"></i>New Booking
                            </a></li>
                            <?php endif; ?>
                            <?php if (has_permission('accommodations.create')): ?>
                            <li><a class="dropdown-item" href="<?= base_url('admin/accommodations-manage.php') ?>">
                                <i class="fas fa-bed me-2"></i>Add Room
                            </a></li>
                            <?php endif; ?>
                            <?php if (has_permission('users.create')): ?>
                            <li><a class="dropdown-item" href="<?= base_url('admin/staff.php') ?>">
                                <i class="fas fa-user-plus me-2"></i>Add Staff
                            </a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <!-- Notifications -->
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm position-relative" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <?php if (($stats['bookings_pending'] ?? 0) + ($stats['reviews_pending'] ?? 0) > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= ($stats['bookings_pending'] ?? 0) + ($stats['reviews_pending'] ?? 0) ?>
                            </span>
                            <?php endif; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Notifications</h6></li>
                            <?php if (($stats['bookings_pending'] ?? 0) > 0): ?>
                            <li><a class="dropdown-item" href="<?= base_url('admin/bookings.php?status=pending') ?>">
                                <i class="fas fa-calendar-check text-warning me-2"></i>
                                <?= $stats['bookings_pending'] ?> Pending Bookings
                            </a></li>
                            <?php endif; ?>
                            <?php if (($stats['reviews_pending'] ?? 0) > 0): ?>
                            <li><a class="dropdown-item" href="<?= base_url('admin/testimonials.php?status=pending') ?>">
                                <i class="fas fa-star text-info me-2"></i>
                                <?= $stats['reviews_pending'] ?> Pending Reviews
                            </a></li>
                            <?php endif; ?>
                            <?php if (($stats['bookings_pending'] ?? 0) + ($stats['reviews_pending'] ?? 0) == 0): ?>
                            <li><span class="dropdown-item-text text-muted">No new notifications</span></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <main class="page-content" role="main">

        <?php
        // ── Flash Messages ───────────────────────────────────────────────────
        $flash = flash();
        if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show mx-3 mt-3" role="alert">
                <?= htmlspecialchars($flash['msg']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

