<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/permissions.php';

if (!isset($_SESSION['user'])) {
    redirect(base_url('public/login.php'));
}

$title = 'Admin Dashboard | Kalitusi Nature Resort';
$pageTitle = 'Dashboard';
$currentUser = $_SESSION['user'];
$today = date('Y-m-d');
$hour = (int)date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

try {
    $stats = [
        'bookings_today' => (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
        'bookings_pending' => (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn(),
        'checkins_today' => (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE check_in = CURDATE() AND status IN ('confirmed', 'pending')")->fetchColumn(),
        'rooms_available' => (int)$pdo->query("SELECT COUNT(*) FROM accommodations WHERE availability_status = 'available'")->fetchColumn(),
        'total_rooms' => (int)$pdo->query("SELECT COUNT(*) FROM accommodations")->fetchColumn(),
        'occupied_rooms' => (int)$pdo->query("SELECT COUNT(*) FROM accommodations WHERE availability_status = 'occupied'")->fetchColumn(),
        'reviews_pending' => (int)$pdo->query("SELECT COUNT(*) FROM testimonials WHERE status = 'pending'")->fetchColumn(),
    ];
    $stats['occupancy_rate'] = $stats['total_rooms'] > 0
        ? (int)round(($stats['occupied_rooms'] / $stats['total_rooms']) * 100)
        : 0;
} catch (Throwable $e) {
    error_log('Admin dashboard metrics failed: ' . $e->getMessage());
    $stats = [
        'bookings_today' => 0,
        'bookings_pending' => 0,
        'checkins_today' => 0,
        'rooms_available' => 0,
        'total_rooms' => 0,
        'occupied_rooms' => 0,
        'reviews_pending' => 0,
        'occupancy_rate' => 0,
    ];
}

$recentBookings = [];
if (has_permission('bookings.view')) {
    try {
        $bookingStmt = $pdo->query(
            "SELECT b.booking_id, b.booking_reference, b.guest_name, b.check_in, b.check_out,
                    b.status, b.total_amount, a.name AS accommodation_name
             FROM bookings b
             LEFT JOIN accommodations a ON a.accommodation_id = b.accommodation_id
             WHERE b.status IN ('pending', 'confirmed', 'checked_in')
             ORDER BY CASE b.status WHEN 'pending' THEN 0 WHEN 'confirmed' THEN 1 ELSE 2 END,
                      b.check_in ASC, b.created_at DESC
             LIMIT 6"
        );
        $recentBookings = $bookingStmt->fetchAll();
    } catch (Throwable $e) {
        error_log('Admin dashboard booking queue failed: ' . $e->getMessage());
    }
}

$dashboardShortcuts = [];
if (has_permission('bookings.view')) {
    $dashboardShortcuts[] = ['label' => 'All bookings', 'icon' => 'fa-calendar-check', 'url' => 'admin/bookings.php'];
    $dashboardShortcuts[] = ['label' => 'Meal reservations', 'icon' => 'fa-utensils', 'url' => 'admin/meal-reservations.php'];
}
if (has_permission('accommodations.view')) {
    $dashboardShortcuts[] = ['label' => 'Accommodations', 'icon' => 'fa-bed', 'url' => 'admin/accommodations-manage.php'];
}
if (has_permission('users.view')) {
    $dashboardShortcuts[] = ['label' => 'Staff', 'icon' => 'fa-users', 'url' => 'admin/staff.php'];
}
if (has_permission('testimonials.view')) {
    $dashboardShortcuts[] = ['label' => 'Reviews', 'icon' => 'fa-star', 'url' => 'admin/testimonials.php'];
}
if (has_permission('content.view')) {
    $dashboardShortcuts[] = ['label' => 'Website content', 'icon' => 'fa-pen-to-square', 'url' => 'admin/site-content.php'];
    $dashboardShortcuts[] = ['label' => 'Announcements', 'icon' => 'fa-bullhorn', 'url' => 'admin/announcements.php'];
}
if (has_permission('analytics.view')) {
    $dashboardShortcuts[] = ['label' => 'Analytics', 'icon' => 'fa-chart-line', 'url' => 'admin/analytics.php'];
}

include __DIR__ . '/../includes/admin_layout_header.php';
?>

<div class="admin-dashboard">
  <section class="dashboard-heading" aria-labelledby="dashboardTitle">
    <div>
      <div class="dashboard-eyebrow">Resort operations / <?= date('l, F j, Y') ?></div>
      <h2 class="dashboard-title" id="dashboardTitle"><?= sanitize($greeting) ?>, <?= sanitize($currentUser['username']) ?></h2>
      <p class="dashboard-subtitle">Today at Kalitusi Nature Resort. Review arrivals and open requests below.</p>
    </div>
    <div class="dashboard-heading-actions">
      <?php if (has_permission('bookings.create')): ?>
        <a href="<?= base_url('admin/booking-create.php') ?>" class="btn btn-primary"><i class="fas fa-plus me-2"></i>New booking</a>
      <?php endif; ?>
      <?php if (has_permission('accommodations.view')): ?>
        <a href="<?= base_url('admin/accommodations-manage.php') ?>" class="btn btn-outline-secondary"><i class="fas fa-bed me-2"></i>Manage rooms</a>
      <?php endif; ?>
    </div>
  </section>

  <section class="dashboard-metrics" aria-label="Today's resort metrics">
    <?php if (has_permission('bookings.view')): ?>
      <a class="dashboard-metric" href="<?= base_url('admin/bookings.php') ?>">
        <div><div class="dashboard-metric-label">New bookings today</div><p class="dashboard-metric-value"><?= number_format($stats['bookings_today']) ?></p><small class="dashboard-metric-note">Requests received today</small></div>
        <span class="dashboard-metric-icon"><i class="fas fa-calendar-plus" aria-hidden="true"></i></span>
      </a>
      <a class="dashboard-metric" href="<?= base_url('admin/bookings.php?status=pending') ?>">
        <div><div class="dashboard-metric-label">Awaiting confirmation</div><p class="dashboard-metric-value"><?= number_format($stats['bookings_pending']) ?></p><small class="dashboard-metric-note">Bookings needing action</small></div>
        <span class="dashboard-metric-icon"><i class="fas fa-hourglass-half" aria-hidden="true"></i></span>
      </a>
      <a class="dashboard-metric" href="<?= base_url('admin/bookings.php?date_from=' . $today . '&date_to=' . $today) ?>">
        <div><div class="dashboard-metric-label">Arrivals today</div><p class="dashboard-metric-value"><?= number_format($stats['checkins_today']) ?></p><small class="dashboard-metric-note">Expected check-ins</small></div>
        <span class="dashboard-metric-icon"><i class="fas fa-right-to-bracket" aria-hidden="true"></i></span>
      </a>
    <?php endif; ?>
    <?php if (has_permission('accommodations.view')): ?>
      <a class="dashboard-metric" href="<?= base_url('admin/accommodations-manage.php') ?>">
        <div><div class="dashboard-metric-label">Rooms available</div><p class="dashboard-metric-value"><?= number_format($stats['rooms_available']) ?></p><small class="dashboard-metric-note">Of <?= number_format($stats['total_rooms']) ?> listed rooms</small></div>
        <span class="dashboard-metric-icon"><i class="fas fa-bed" aria-hidden="true"></i></span>
      </a>
    <?php endif; ?>
  </section>

  <div class="row g-3 align-items-stretch mb-3">
    <?php if (has_permission('bookings.view')): ?>
    <div class="col-xl-8">
      <section class="dashboard-panel" aria-labelledby="bookingQueueTitle">
        <div class="dashboard-panel-header">
          <div><h2 id="bookingQueueTitle">Booking queue</h2><small class="text-muted">Pending requests and upcoming arrivals</small></div>
          <a href="<?= base_url('admin/bookings.php') ?>" class="btn btn-sm btn-outline-secondary">View all</a>
        </div>
        <?php if ($recentBookings): ?>
          <div class="table-responsive border-0 rounded-0">
            <table class="table table-hover">
              <thead><tr><th scope="col">Guest</th><th scope="col">Stay</th><th scope="col">Check-in</th><th scope="col">Status</th><th scope="col" class="text-end">Amount</th></tr></thead>
              <tbody>
                <?php foreach ($recentBookings as $booking):
                  $statusClass = strtolower(str_replace(' ', '_', (string)$booking['status']));
                  $checkIn = strtotime((string)$booking['check_in']);
                ?>
                  <tr>
                    <td><span class="booking-guest"><?= sanitize($booking['guest_name']) ?></span><a class="booking-reference" href="<?= base_url('admin/bookings.php?search=' . urlencode($booking['booking_reference'])) ?>"><?= sanitize($booking['booking_reference']) ?></a></td>
                    <td><?= sanitize($booking['accommodation_name'] ?: 'Room to be assigned') ?></td>
                    <td><?= $checkIn ? date('M j, Y', $checkIn) : 'Not set' ?></td>
                    <td><span class="booking-status is-<?= sanitize($statusClass) ?>"><?= sanitize(str_replace('_', ' ', $booking['status'])) ?></span></td>
                    <td class="text-end text-nowrap">UGX <?= number_format((float)$booking['total_amount']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="dashboard-empty"><i class="fas fa-calendar-check mb-2" aria-hidden="true"></i><p class="mb-0">No active bookings to review.</p></div>
        <?php endif; ?>
      </section>
    </div>
    <?php endif; ?>

    <div class="col-xl-4">
      <?php if (has_permission('accommodations.view')): ?>
      <section class="dashboard-panel mb-3" aria-labelledby="occupancyTitle">
        <div class="dashboard-panel-header"><h2 id="occupancyTitle">Room occupancy</h2><i class="fas fa-chart-pie text-success" aria-hidden="true"></i></div>
        <div class="dashboard-panel-body">
          <div class="d-flex align-items-end justify-content-between mb-3">
            <div><span class="occupancy-value"><?= $stats['occupancy_rate'] ?>%</span><div class="small text-muted mt-1">Currently occupied</div></div>
            <div class="text-end small text-muted"><?= number_format($stats['occupied_rooms']) ?> occupied<br><?= number_format($stats['rooms_available']) ?> available</div>
          </div>
          <div class="occupancy-bar" role="progressbar" aria-label="Room occupancy" aria-valuenow="<?= $stats['occupancy_rate'] ?>" aria-valuemin="0" aria-valuemax="100"><span style="width: <?= $stats['occupancy_rate'] ?>%"></span></div>
        </div>
      </section>
      <?php endif; ?>

      <section class="dashboard-panel" aria-labelledby="shortcutsTitle">
        <div class="dashboard-panel-header"><h2 id="shortcutsTitle">Quick access</h2><span class="small text-muted"><?= count($dashboardShortcuts) ?> links</span></div>
        <div class="dashboard-panel-body">
          <?php if ($dashboardShortcuts): ?>
            <div class="dashboard-shortcut-list">
              <?php foreach ($dashboardShortcuts as $shortcut): ?>
                <a class="dashboard-shortcut" href="<?= base_url($shortcut['url']) ?>"><i class="fas <?= sanitize($shortcut['icon']) ?>" aria-hidden="true"></i><span><?= sanitize($shortcut['label']) ?></span></a>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="small text-muted mb-0">No shortcuts are available for this account.</p>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/admin_layout_footer.php';
