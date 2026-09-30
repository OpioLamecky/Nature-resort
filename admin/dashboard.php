<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/permissions.php';

// Check authentication
if (!isset($_SESSION['user'])) {
    redirect(base_url('public/login.php'));
}

$title = 'Admin Dashboard | Kalitusi Nature Resort';
$pageTitle = 'Dashboard';
$currentUser = $_SESSION['user'];

// Get quick stats
try {
    $stats = [
        'bookings_today' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
        'bookings_pending' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn(),
        'checkins_today' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE check_in = CURDATE() AND status IN ('confirmed', 'pending')")->fetchColumn(),
        'rooms_available' => $pdo->query("SELECT COUNT(*) FROM accommodations WHERE availability_status = 'available'")->fetchColumn(),
        'reviews_pending' => $pdo->query("SELECT COUNT(*) FROM testimonials WHERE status = 'pending'")->fetchColumn(),
        'total_staff' => $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn(),
        'total_rooms' => $pdo->query("SELECT COUNT(*) FROM accommodations")->fetchColumn(),
        'occupancy_rate' => 0, // Will calculate below
    ];
    
    // Calculate occupancy rate
    $total_rooms = $stats['total_rooms'];
    $occupied_rooms = $pdo->query("SELECT COUNT(*) FROM accommodations WHERE availability_status = 'occupied'")->fetchColumn();
    $stats['occupancy_rate'] = $total_rooms > 0 ? round(($occupied_rooms / $total_rooms) * 100) : 0;
    
} catch (Exception $e) {
    $stats = [
        'bookings_today' => 0,
        'bookings_pending' => 0,
        'checkins_today' => 0,
        'rooms_available' => 0,
        'reviews_pending' => 0,
        'total_staff' => 0,
        'total_rooms' => 0,
        'occupancy_rate' => 0,
    ];
}

include __DIR__ . '/../includes/admin_layout_header.php';
?>

<!-- Welcome Header -->
<div class="dashboard-welcome mb-4">
  <div class="row align-items-center">
    <div class="col-md-8">
      <h2 class="mb-2">Welcome back, <?= sanitize($currentUser['username']) ?>!</h2>
      <p class="text-muted mb-1">Here's what's happening at Kalitusi Resort today</p>
      <div class="d-flex align-items-center">
        <small class="text-muted me-3">
          <i class="fas fa-calendar me-1"></i>
          <?= date('l, F j, Y') ?>
        </small>
        <small class="text-muted me-3">
          <i class="fas fa-clock me-1"></i>
          <?= date('g:i A') ?>
        </small>
        <?php if ($stats['bookings_pending'] > 0): ?>
        <small class="text-warning">
          <i class="fas fa-exclamation-triangle me-1"></i>
          <?= $stats['bookings_pending'] ?> pending booking<?= $stats['bookings_pending'] > 1 ? 's' : '' ?>
        </small>
        <?php endif; ?>
      </div>
    </div>
    <div class="col-md-4 text-end">
      <div class="d-flex flex-column align-items-end">
        <span class="badge bg-primary fs-6 px-3 py-2 mb-2"><?= strtoupper(sanitize($currentUser['role'])) ?></span>
        <div class="d-flex align-items-center text-muted">
          <i class="fas fa-users me-1"></i>
          <small><?= $stats['total_staff'] ?> staff online</small>
        </div>
      </div>
    </div>
  </div>
</div>

  <!-- Key Metrics -->
  <div class="row g-4 mb-5">
    <div class="col-lg-3 col-md-6">
      <div class="card stat-card stat-card-primary h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div class="stat-content">
              <div class="stat-number-container">
                <h3 class="stat-number mb-1"><?= $stats['bookings_today'] ?></h3>
                <div class="stat-trend">
                  <i class="fas fa-arrow-up text-success me-1"></i>
                  <small class="text-light opacity-75">New today</small>
                </div>
              </div>
              <p class="stat-label mb-0">Today's Bookings</p>
              <small class="stat-description">Fresh reservations received</small>
            </div>
            <div class="stat-icon">
              <i class="fas fa-calendar-check"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="card stat-card stat-card-warning h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div class="stat-content">
              <div class="stat-number-container">
                <h3 class="stat-number mb-1"><?= $stats['bookings_pending'] ?></h3>
                <div class="stat-trend">
                  <i class="fas fa-clock text-warning me-1"></i>
                  <small class="text-dark opacity-75">Awaiting action</small>
                </div>
              </div>
              <p class="stat-label mb-0">Pending Bookings</p>
              <small class="stat-description">Require confirmation</small>
            </div>
            <div class="stat-icon">
              <i class="fas fa-hourglass-half"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="card stat-card stat-card-success h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div class="stat-content">
              <div class="stat-number-container">
                <h3 class="stat-number mb-1"><?= $stats['rooms_available'] ?></h3>
                <div class="stat-trend">
                  <i class="fas fa-check-circle text-success me-1"></i>
                  <small class="text-light opacity-75">Ready to book</small>
                </div>
              </div>
              <p class="stat-label mb-0">Available Rooms</p>
              <small class="stat-description">Open for reservations</small>
            </div>
            <div class="stat-icon">
              <i class="fas fa-bed"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="card stat-card stat-card-info h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div class="stat-content">
              <div class="stat-number-container">
                <h3 class="stat-number mb-1"><?= $stats['checkins_today'] ?></h3>
                <div class="stat-trend">
                  <i class="fas fa-user-check text-info me-1"></i>
                  <small class="text-dark opacity-75">Arriving guests</small>
                </div>
              </div>
              <p class="stat-label mb-0">Today's Check-ins</p>
              <small class="stat-description">Expected arrivals</small>
            </div>
            <div class="stat-icon">
              <i class="fas fa-door-open"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Secondary Metrics -->
  <div class="row g-4 mb-5">
    <div class="col-lg-3 col-md-6">
      <div class="card stat-card stat-card-secondary h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div class="stat-content">
              <div class="stat-number-container">
                <h3 class="stat-number mb-1"><?= $stats['occupancy_rate'] ?>%</h3>
                <div class="stat-trend">
                  <i class="fas fa-chart-line text-info me-1"></i>
                  <small class="text-light opacity-75">Current rate</small>
                </div>
              </div>
              <p class="stat-label mb-0">Occupancy Rate</p>
              <small class="stat-description">Rooms currently occupied</small>
            </div>
            <div class="stat-icon">
              <i class="fas fa-percentage"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="card stat-card stat-card-dark h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div class="stat-content">
              <div class="stat-number-container">
                <h3 class="stat-number mb-1"><?= $stats['reviews_pending'] ?></h3>
                <div class="stat-trend">
                  <i class="fas fa-star text-warning me-1"></i>
                  <small class="text-light opacity-75">Need review</small>
                </div>
              </div>
              <p class="stat-label mb-0">Pending Reviews</p>
              <small class="stat-description">Awaiting moderation</small>
            </div>
            <div class="stat-icon">
              <i class="fas fa-star-half-alt"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="card stat-card stat-card-accent h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div class="stat-content">
              <div class="stat-number-container">
                <h3 class="stat-number mb-1"><?= $stats['total_staff'] ?></h3>
                <div class="stat-trend">
                  <i class="fas fa-users text-success me-1"></i>
                  <small class="text-dark opacity-75">Active team</small>
                </div>
              </div>
              <p class="stat-label mb-0">Staff Members</p>
              <small class="stat-description">Currently active</small>
            </div>
            <div class="stat-icon">
              <i class="fas fa-users-cog"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="card stat-card stat-card-gradient h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div class="stat-content">
              <div class="stat-number-container">
                <h3 class="stat-number mb-1"><?= $stats['total_rooms'] ?></h3>
                <div class="stat-trend">
                  <i class="fas fa-home text-info me-1"></i>
                  <small class="text-light opacity-75">Total inventory</small>
                </div>
              </div>
              <p class="stat-label mb-0">Total Rooms</p>
              <small class="stat-description">Resort capacity</small>
            </div>
            <div class="stat-icon">
              <i class="fas fa-building"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Primary Actions -->
  <div class="row g-4 mb-5">
    
    <!-- Booking Management -->
    <?php if (has_permission('bookings.view')): ?>
    <div class="col-lg-6 col-md-6">
      <div class="card action-card h-100 border-primary">
        <div class="card-body">
          <div class="d-flex align-items-center mb-3">
            <div class="rounded-circle bg-primary text-white p-3 me-3">
              <i class="fas fa-calendar-check fa-2x"></i>
            </div>
            <div>
              <h5 class="card-title mb-0">Bookings & Reservations</h5>
              <small class="text-muted">Manage all guest bookings and check-ins</small>
            </div>
          </div>
          <div class="row g-2">
            <div class="col-6">
              <a href="<?= base_url('admin/bookings.php') ?>" class="btn btn-sm btn-outline-primary w-100">
                <i class="fas fa-list me-1"></i>View All
              </a>
            </div>
            <?php if (has_permission('bookings.create')): ?>
            <div class="col-6">
              <a href="<?= base_url('admin/booking-create.php') ?>" class="btn btn-sm btn-primary w-100">
                <i class="fas fa-plus me-1"></i>New Booking
              </a>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Accommodation Management -->
    <?php if (has_permission('accommodations.view')): ?>
    <div class="col-lg-6 col-md-6">
      <div class="card action-card h-100 border-success">
        <div class="card-body">
          <div class="d-flex align-items-center mb-3">
            <div class="rounded-circle bg-success text-white p-3 me-3">
              <i class="fas fa-bed fa-2x"></i>
            </div>
            <div>
              <h5 class="card-title mb-0">Rooms & Accommodations</h5>
              <small class="text-muted">Manage room availability and pricing</small>
            </div>
          </div>
          <div class="row g-2">
            <div class="col-6">
              <a href="<?= base_url('admin/accommodations-manage.php') ?>" class="btn btn-sm btn-outline-success w-100">
                <i class="fas fa-list me-1"></i>Manage Rooms
              </a>
            </div>
            <?php if (has_permission('accommodations.create')): ?>
            <div class="col-6">
              <a href="<?= base_url('admin/accommodations-manage.php') ?>" class="btn btn-sm btn-success w-100">
                <i class="fas fa-plus me-1"></i>Add Room
              </a>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Secondary Actions -->
  <div class="row g-4 mb-5">
    
    <!-- Staff & Reviews Combined -->
    <div class="col-lg-6 col-md-6">
      <div class="card action-card h-100">
        <div class="card-body">
          <h5 class="card-title mb-3"><i class="fas fa-users me-2"></i>Team & Reviews</h5>
          <div class="row g-2">
            <?php if (has_permission('users.view')): ?>
            <div class="col-6">
              <a href="<?= base_url('admin/staff.php') ?>" class="btn btn-outline-secondary w-100">
                <i class="fas fa-users me-1"></i>Staff
              </a>
            </div>
            <?php endif; ?>
            <?php if (has_permission('testimonials.view')): ?>
            <div class="col-6">
              <a href="<?= base_url('admin/testimonials.php') ?>" class="btn btn-outline-secondary w-100">
                <i class="fas fa-star me-1"></i>Reviews
                <?php if (($stats['reviews_pending'] ?? 0) > 0): ?>
                  <span class="badge bg-warning ms-1"><?= $stats['reviews_pending'] ?></span>
                <?php endif; ?>
              </a>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Content & Analytics Combined -->
    <div class="col-lg-6 col-md-6">
      <div class="card action-card h-100">
        <div class="card-body">
          <h5 class="card-title mb-3"><i class="fas fa-chart-line me-2"></i>Content & Analytics</h5>
          <div class="row g-2">
            <?php if (has_permission('content.view')): ?>
            <div class="col-6">
              <a href="<?= base_url('admin/posts.php') ?>" class="btn btn-outline-secondary w-100">
                <i class="fas fa-file-alt me-1"></i>Content
              </a>
            </div>
            <?php endif; ?>
            <?php if (has_permission('analytics.view')): ?>
            <div class="col-6">
              <a href="<?= base_url('admin/analytics.php') ?>" class="btn btn-outline-secondary w-100">
                <i class="fas fa-chart-bar me-1"></i>Analytics
              </a>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Website Content -->
  <div class="row g-4 mb-4">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-end mb-2">
        <div><h2 class="h4 mb-1">Website Content</h2><p class="text-muted mb-0">Edit public pages, media, and published information.</p></div>
        <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('public/') ?>" target="_blank" rel="noopener">View public site</a>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-header bg-white"><h3 class="h6 mb-0"><i class="fas fa-file-alt me-2 text-success"></i>Pages</h3></div>
        <div class="card-body d-grid gap-2">
          <?php if (has_permission('content.view')): ?>
            <a class="btn btn-outline-secondary text-start" href="<?= base_url('admin/site-content.php?page=home') ?>">Home page copy</a>
            <a class="btn btn-outline-secondary text-start" href="<?= base_url('admin/site-content.php?page=about') ?>">About page copy</a>
            <a class="btn btn-outline-secondary text-start" href="<?= base_url('admin/site-content.php?page=legal') ?>">Policy page headings</a>
            <a class="btn btn-outline-secondary text-start" href="<?= base_url('admin/pages-home.php') ?>">Home slides and quick links</a>
            <a class="btn btn-outline-secondary text-start" href="<?= base_url('admin/about-content.php') ?>">About story and team</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-header bg-white"><h3 class="h6 mb-0"><i class="fas fa-images me-2 text-success"></i>Resort Content</h3></div>
        <div class="card-body d-grid gap-2">
          <?php if (has_permission('accommodations.view')): ?><a class="btn btn-outline-secondary text-start" href="<?= base_url('admin/accommodations-manage.php') ?>">Accommodations and rates</a><?php endif; ?>
          <?php if (has_permission('content.view')): ?>
            <a class="btn btn-outline-secondary text-start" href="<?= base_url('admin/site-content.php?page=activities') ?>">Activities and images</a>
            <a class="btn btn-outline-secondary text-start" href="<?= base_url('admin/site-content.php?page=dining') ?>">Dining page content</a>
            <a class="btn btn-outline-secondary text-start" href="<?= base_url('admin/site-content.php?page=gallery') ?>">Gallery page copy</a>
            <a class="btn btn-outline-secondary text-start" href="<?= base_url('admin/pages-services.php') ?>">Service blocks</a>
          <?php endif; ?>
          <?php if (($currentUser['role'] ?? '') === 'Super Admin'): ?><a class="btn btn-outline-secondary text-start" href="<?= base_url('admin_shop.php') ?>">Gift shop products</a><?php endif; ?>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-header bg-white"><h3 class="h6 mb-0"><i class="fas fa-bullhorn me-2 text-success"></i>Publishing</h3></div>
        <div class="card-body d-grid gap-2">
          <?php if (has_permission('content.view')): ?>
            <a class="btn btn-outline-secondary text-start" href="<?= base_url('admin/posts.php') ?>">News and blog posts</a>
            <a class="btn btn-outline-secondary text-start" href="<?= base_url('admin/downloads.php') ?>">Brochures and downloads</a>
            <a class="btn btn-outline-secondary text-start" href="<?= base_url('admin/announcements.php') ?>">Announcements</a>
            <a class="btn btn-outline-secondary text-start" href="<?= base_url('admin/site-content.php?page=contact') ?>">Contact and social details</a>
            <a class="btn btn-outline-secondary text-start" href="<?= base_url('admin/pages-footer.php?tab=settings') ?>">Contact and site settings</a>
          <?php endif; ?>
          <a class="btn btn-outline-primary text-start" href="<?= base_url('admin/profile.php') ?>">My profile</a>
        </div>
      </div>
    </div>
  </div>

<?php include __DIR__ . '/../includes/admin_layout_footer.php';
