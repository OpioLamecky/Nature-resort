<?php
require_once __DIR__ . '/bootstrap.php';
$title = $title ?? 'Kalitusi Nature Resort | Fort Portal, Uganda';
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Kalitusi Nature Resort - Luxury eco-resort in Fort Portal, Uganda. Experience pristine nature, comfortable accommodations, and unforgettable adventures.">
  <meta name="theme-color" content="#1a4d2e">
  <meta name="author" content="Kalitusi Nature Resort">
  
  <!-- Performance -->
  <link rel="preconnect" href="https://cdn.jsdelivr.net">
  <link rel="preconnect" href="https://cdnjs.cloudflare.com">
  <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
  <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
  
  <title><?= sanitize($title) ?></title>
  
  <!-- Stylesheets -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
  <link rel="stylesheet" href="<?= asset_url('css/custom-styles.css') ?>?v=<?= (int)filemtime(__DIR__ . '/../assets/css/custom-styles.css') ?>">
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="<?= asset_url('images/kalitusi-logo.png') ?>">
</head>
<body class="page-transition">
  
  <!-- Top Contact Bar -->
  <div class="topbar">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="d-flex gap-3 small">
            <a href="tel:<?= sanitize(preg_replace('/[^0-9+]/', '', site_content('contact', 'phone', '+256 772 957 573'))) ?>"><i class="fas fa-phone"></i> <?= sanitize(site_content('contact', 'phone', '+256 772 957 573')) ?></a>
            <a href="mailto:<?= sanitize(site_content('contact', 'email', 'info@kalitusi.com')) ?>"><i class="fas fa-envelope"></i> <?= sanitize(site_content('contact', 'email', 'info@kalitusi.com')) ?></a>
          </div>
        </div>
        <div class="col-md-6 text-end">
          <div class="d-flex gap-2 justify-content-end small">
            <a href="<?= sanitize(site_content('contact', 'facebook_url', '#')) ?>" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
            <a href="<?= sanitize(site_content('contact', 'instagram_url', '#')) ?>" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="<?= sanitize(site_content('contact', 'twitter_url', '#')) ?>" aria-label="X or Twitter"><i class="fab fa-twitter"></i></a>
            <?php if (!empty($_SESSION['user'])): ?>
              <span class="ms-3">|</span>
              <a href="<?= base_url('public/logout.php') ?>" class="ms-2">Logout</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Navigation -->
  <nav class="navbar navbar-expand-lg nav-blue" role="navigation" aria-label="Main navigation">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="<?= base_url('public/') ?>" aria-label="Kalitusi Nature Resort Home">
        <img src="<?= asset_url('images/kalitusi-logo.png') ?>" alt="Kalitusi Nature Resort Logo" class="resort-logo me-2">
        <span class="resort-name">KALITUSI NATURE RESORT</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation menu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0" role="menubar">
          <li class="nav-item"><a class="nav-link" href="<?= base_url('public/') ?>">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('public/about.php') ?>">About Us</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="accommodationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Accommodations
            </a>
            <ul class="dropdown-menu" aria-labelledby="accommodationsDropdown">
              <li><a class="dropdown-item" href="<?= base_url('public/accommodations.php?type=rooms') ?>">Rooms & Suites</a></li>
              <li><a class="dropdown-item" href="<?= base_url('public/accommodations.php?type=chalets') ?>">Chalets</a></li>
              <li><a class="dropdown-item" href="<?= base_url('public/accommodations.php?type=glamping') ?>">Glamping Tents</a></li>
            </ul>
          </li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('public/activities.php') ?>">Activities</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('public/dining.php') ?>">Dining</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('eshop.php') ?>">E-shop</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('public/gallery.php') ?>">Gallery</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('public/news.php') ?>">Blog</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('public/contact.php') ?>">Contact</a></li>
          <li class="nav-item"><a class="btn btn-outline-light btn-sm ms-2" href="<?= base_url('public/booking.php') ?>">Book Now</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="adminMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">Admin</a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminMenu">
              <?php if (!empty($_SESSION['user'])): ?>
                <li><a class="dropdown-item" href="<?= base_url('admin/dashboard.php') ?>"><i class="fas fa-gauge-high me-2"></i>Admin Dashboard</a></li>
                <li><a class="dropdown-item" href="<?= base_url('admin/profile.php') ?>"><i class="fas fa-user-circle me-2"></i>My Profile</a></li>
              <?php else: ?>
                <li><a class="dropdown-item" href="<?= base_url('public/login.php?next=' . urlencode(base_url('admin/dashboard.php'))) ?>"><i class="fas fa-right-to-bracket me-2"></i>Admin sign in</a></li>
              <?php endif; ?>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <main id="main-content" class="py-0" role="main">

  <?php
  // ── Flash Messages ─────────────────────────────────────────────────────────
  $flash = flash();
  if ($flash): ?>
    <div class="container mt-3">
      <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    </div>
  <?php endif; ?>

