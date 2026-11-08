<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'] ?? '', ['Super Admin', 'Content Manager'])) {
    redirect(base_url('public/login.php?next=' . urlencode($_SERVER['REQUEST_URI'])));
}

$title = 'Analytics Dashboard | Admin';
$pageTitle = 'Analytics & Reports';

// Get date range from query params or default to last 30 days
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Fetch dashboard statistics
try {
    // Total bookings
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE created_at >= ? AND created_at <= ?");
    $stmt->execute([$startDate, $endDate . ' 23:59:59']);
    $totalBookings = $stmt->fetchColumn();
    
    // Revenue
    $stmt = $pdo->prepare("SELECT SUM(total_amount) as revenue FROM bookings WHERE status != 'cancelled' AND created_at >= ? AND created_at <= ?");
    $stmt->execute([$startDate, $endDate . ' 23:59:59']);
    $totalRevenue = $stmt->fetchColumn() ?? 0;
    
    // Confirmed bookings
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed' AND created_at >= ? AND created_at <= ?");
    $stmt->execute([$startDate, $endDate . ' 23:59:59']);
    $confirmedBookings = $stmt->fetchColumn();
    
    // Pending bookings
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE status = 'pending'");
    $stmt->execute();
    $pendingBookings = $stmt->fetchColumn();
    
    // Recent bookings
    $stmt = $pdo->prepare("
        SELECT booking_reference, guest_name, guest_email, check_in, check_out, total_amount, status, created_at
        FROM bookings 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $recentBookings = $stmt->fetchAll();
    
    // Occupancy by room type
    $stmt = $pdo->query("
        SELECT 
            a.room_type,
            COUNT(*) as total_rooms,
            SUM(CASE WHEN a.availability_status = 'occupied' THEN 1 ELSE 0 END) as occupied_rooms,
            ROUND((SUM(CASE WHEN a.availability_status = 'occupied' THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 1) as occupancy_rate
        FROM accommodations a 
        WHERE a.status = 'active'
        GROUP BY a.room_type
        ORDER BY occupancy_rate DESC
    ");
    $occupancyData = $stmt->fetchAll();
    
    // Upcoming arrivals (next 7 days)
    $stmt = $pdo->query("
        SELECT 
            b.guest_name,
            b.booking_reference,
            b.check_in,
            b.status,
            (b.adults + b.children) as total_guests,
            DATEDIFF(b.check_in, CURDATE()) as days_until_arrival
        FROM bookings b 
        WHERE b.check_in BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        AND b.status IN ('confirmed', 'checked_in')
        ORDER BY b.check_in ASC
        LIMIT 10
    ");
    $upcomingArrivals = $stmt->fetchAll();
    
    // Revenue by month (last 12 months)
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(b.created_at, '%Y-%m') as month,
            COUNT(*) as total_bookings,
            SUM(b.total_amount) as total_revenue,
            AVG(b.total_amount) as avg_booking_value
        FROM bookings b 
        WHERE b.status != 'cancelled' 
        AND b.created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(b.created_at, '%Y-%m')
        ORDER BY month DESC
        LIMIT 12
    ");
    $revenueByMonth = $stmt->fetchAll();
    
    // Additional analytics
    // Average booking value
    $stmt = $pdo->prepare("SELECT AVG(total_amount) FROM bookings WHERE status != 'cancelled' AND created_at >= ? AND created_at <= ?");
    $stmt->execute([$startDate, $endDate . ' 23:59:59']);
    $avgBookingValue = $stmt->fetchColumn() ?? 0;
    
    // Cancellation rate
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE status = 'cancelled' AND created_at >= ? AND created_at <= ?");
    $stmt->execute([$startDate, $endDate . ' 23:59:59']);
    $cancelledBookings = $stmt->fetchColumn();
    $cancellationRate = $totalBookings > 0 ? ($cancelledBookings / ($totalBookings + $cancelledBookings)) * 100 : 0;
    
    // Popular room types
    $stmt = $pdo->query("
        SELECT 
            a.room_type,
            COUNT(b.booking_id) as bookings_count,
            SUM(b.total_amount) as revenue
        FROM bookings b
        JOIN accommodations a ON b.accommodation_id = a.accommodation_id
        WHERE b.status != 'cancelled'
        AND b.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY a.room_type
        ORDER BY bookings_count DESC
        LIMIT 5
    ");
    $popularRoomTypes = $stmt->fetchAll();
    
    // Guest satisfaction (from testimonials)
    $stmt = $pdo->query("
        SELECT 
            AVG(rating) as avg_rating,
            COUNT(*) as total_reviews
        FROM testimonials 
        WHERE status = 'approved' 
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $satisfactionData = $stmt->fetch();
    $avgRating = $satisfactionData['avg_rating'] ?? 0;
    $totalReviews = $satisfactionData['total_reviews'] ?? 0;
    
} catch (Exception $e) {
    $totalBookings = 0;
    $totalRevenue = 0;
    $confirmedBookings = 0;
    $pendingBookings = 0;
    $recentBookings = [];
    $occupancyData = [];
    $upcomingArrivals = [];
    $revenueByMonth = [];
    $avgBookingValue = 0;
    $cancelledBookings = 0;
    $cancellationRate = 0;
    $popularRoomTypes = [];
    $avgRating = 0;
    $totalReviews = 0;
}

include __DIR__ . '/../includes/admin_layout_header.php';
?>

<div class="analytics-page">
  <!-- Header -->
  <div class="analytics-heading d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1">Analytics Dashboard</h1>
      <p class="text-muted mb-0">Real-time insights and reports</p>
    </div>
    <div>
      <a href="<?= base_url('admin/dashboard.php') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
      </a>
    </div>
  </div>

  <!-- Date Range Filter -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label for="start_date" class="form-label">Start Date</label>
          <input type="date" class="form-control" id="start_date" name="start_date" value="<?= sanitize($startDate) ?>">
        </div>
        <div class="col-md-3">
          <label for="end_date" class="form-label">End Date</label>
          <input type="date" class="form-control" id="end_date" name="end_date" value="<?= sanitize($endDate) ?>">
        </div>
        <div class="col-md-3">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-filter me-2"></i>Filter
          </button>
          <a href="?" class="btn btn-outline-secondary">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <style>
    .analytics-bento-panel {
      background: #ffffff;
      border-radius: 18px;
      border: 1px solid rgba(15, 23, 42, 0.07);
      box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
      padding: 1.4rem 1.5rem;
      height: 100%;
    }
    .analytics-panel-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem;
      margin-bottom: 1.25rem;
      padding-bottom: 0.85rem;
      border-bottom: 1px solid rgba(15, 23, 42, 0.06);
    }
    .analytics-panel-title {
      font-size: 1.02rem;
      font-weight: 700;
      color: #0f172a;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 0.6rem;
    }
    .analytics-occupancy-item {
      background: #f8faf9;
      border: 1px solid rgba(15, 23, 42, 0.05);
      border-radius: 14px;
      padding: 0.9rem 1rem;
      margin-bottom: 0.75rem;
    }
    .analytics-occupancy-item:last-child {
      margin-bottom: 0;
    }
    .analytics-clean-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
    }
    .analytics-clean-table th {
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #64748b;
      padding: 0.65rem 0.75rem;
      border-bottom: 1px solid rgba(15, 23, 42, 0.08);
      white-space: nowrap;
      background: #f8fafc;
    }
    .analytics-clean-table th:first-child { border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
    .analytics-clean-table th:last-child { border-top-right-radius: 10px; border-bottom-right-radius: 10px; }
    .analytics-clean-table td {
      padding: 0.8rem 0.75rem;
      font-size: 0.88rem;
      color: #1e293b;
      border-bottom: 1px solid rgba(15, 23, 42, 0.05);
      white-space: nowrap;
      vertical-align: middle;
    }
    .analytics-clean-table tbody tr:last-child td {
      border-bottom: none;
    }
    .analytics-empty-state {
      text-align: center;
      padding: 2.25rem 1rem;
      background: #f8faf9;
      border-radius: 14px;
      border: 1px dashed rgba(15, 23, 42, 0.1);
    }
  </style>

  <!-- Stats Cards (Booking Performance) -->
  <div class="analytics-section-heading mb-2">
    <h2 class="h6 fw-bold text-uppercase text-muted mb-0" style="letter-spacing: 0.05em;">Booking Performance</h2>
  </div>
  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="bento-kpi-card">
        <div class="bento-kpi-top">
          <span class="bento-kpi-label">Total Bookings</span>
          <span class="bento-kpi-icon is-blue"><i class="fas fa-calendar-check"></i></span>
        </div>
        <div class="bento-kpi-bottom">
          <span class="bento-kpi-value"><?= number_format($totalBookings ?? 0) ?></span>
          <span class="bento-kpi-pill is-up"><i class="fas fa-calendar"></i> Range</span>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="bento-kpi-card">
        <div class="bento-kpi-top">
          <span class="bento-kpi-label">Total Revenue</span>
          <span class="bento-kpi-icon is-green"><i class="fas fa-money-bill-wave"></i></span>
        </div>
        <div class="bento-kpi-bottom">
          <span class="bento-kpi-value" style="font-size: 1.55rem;">UGX <?= number_format(($totalRevenue ?? 0) / 1000000, 1) ?>M</span>
          <span class="bento-kpi-pill is-up"><i class="fas fa-arrow-trend-up"></i> Gross</span>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="bento-kpi-card">
        <div class="bento-kpi-top">
          <span class="bento-kpi-label">Confirmed Stays</span>
          <span class="bento-kpi-icon is-green"><i class="fas fa-check-circle"></i></span>
        </div>
        <div class="bento-kpi-bottom">
          <span class="bento-kpi-value"><?= number_format($confirmedBookings ?? 0) ?></span>
          <span class="bento-kpi-pill is-up"><i class="fas fa-check"></i> Active</span>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="bento-kpi-card">
        <div class="bento-kpi-top">
          <span class="bento-kpi-label">Pending Approval</span>
          <span class="bento-kpi-icon is-amber"><i class="fas fa-clock"></i></span>
        </div>
        <div class="bento-kpi-bottom">
          <span class="bento-kpi-value"><?= number_format($pendingBookings ?? 0) ?></span>
          <span class="bento-kpi-pill is-warn"><i class="fas fa-hourglass-half"></i> Queue</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Additional Analytics Cards (Guest Experience) -->
  <div class="analytics-section-heading mb-2">
    <h2 class="h6 fw-bold text-uppercase text-muted mb-0" style="letter-spacing: 0.05em;">Guest Experience &amp; Value</h2>
  </div>
  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="bento-kpi-card">
        <div class="bento-kpi-top">
          <span class="bento-kpi-label">Avg Booking Value</span>
          <span class="bento-kpi-icon is-blue"><i class="fas fa-coins"></i></span>
        </div>
        <div class="bento-kpi-bottom">
          <span class="bento-kpi-value" style="font-size: 1.55rem;">UGX <?= number_format(($avgBookingValue ?? 0) / 1000, 0) ?>K</span>
          <span class="bento-kpi-pill is-up">Per Stay</span>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="bento-kpi-card">
        <div class="bento-kpi-top">
          <span class="bento-kpi-label">Cancellation Rate</span>
          <span class="bento-kpi-icon is-amber"><i class="fas fa-ban"></i></span>
        </div>
        <div class="bento-kpi-bottom">
          <span class="bento-kpi-value"><?= number_format($cancellationRate ?? 0, 1) ?>%</span>
          <span class="bento-kpi-pill is-warn">Selected Range</span>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="bento-kpi-card">
        <div class="bento-kpi-top">
          <span class="bento-kpi-label">Guest Rating</span>
          <span class="bento-kpi-icon is-rose"><i class="fas fa-star"></i></span>
        </div>
        <div class="bento-kpi-bottom">
          <span class="bento-kpi-value"><?= number_format($avgRating ?? 0, 1) ?><small class="text-muted fs-6">/5</small></span>
          <span class="bento-kpi-pill is-up"><i class="fas fa-heart"></i> Verified</span>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="bento-kpi-card">
        <div class="bento-kpi-top">
          <span class="bento-kpi-label">Reviews (30 Days)</span>
          <span class="bento-kpi-icon is-green"><i class="fas fa-comments"></i></span>
        </div>
        <div class="bento-kpi-bottom">
          <span class="bento-kpi-value"><?= number_format($totalReviews ?? 0) ?></span>
          <span class="bento-kpi-pill is-up">Last 30d</span>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    
    <!-- Recent Bookings -->
    <div class="col-12">
      <div class="card">
        <div class="card-header analytics-card-heading">
          <div>
            <h3 class="h5 mb-1"><i class="fas fa-list me-2"></i>Recent Bookings</h3>
            <p class="small text-muted mb-0">The latest 10 bookings across all dates.</p>
          </div>
          <a href="<?= base_url('admin/bookings.php') ?>" class="btn btn-outline-secondary btn-sm">View all bookings</a>
        </div>
        <div class="card-body analytics-bookings-body">
          <?php if (empty($recentBookings)): ?>
            <p class="text-center text-muted mb-0 py-3">No bookings yet</p>
          <?php else: ?>
            <div class="analytics-booking-list">
              <?php foreach ($recentBookings as $booking): ?>
                <?php
                $statusColors = [
                    'pending' => 'warning',
                    'confirmed' => 'success',
                    'checked_in' => 'info',
                    'checked_out' => 'secondary',
                    'cancelled' => 'danger'
                ];
                $color = $statusColors[$booking['status']] ?? 'secondary';
                ?>
                <article class="analytics-booking-item">
                  <div class="analytics-booking-heading">
                    <div class="analytics-booking-guest">
                      <strong><?= sanitize($booking['guest_name']) ?></strong>
                      <span class="text-muted"><?= sanitize($booking['guest_email']) ?></span>
                    </div>
                    <span class="badge bg-<?= $color ?>"><?= ucfirst($booking['status']) ?></span>
                  </div>
                  <div class="analytics-booking-details">
                    <div>
                      <span class="analytics-detail-label">Reference</span>
                      <strong><?= sanitize($booking['booking_reference']) ?></strong>
                    </div>
                    <div>
                      <span class="analytics-detail-label">Check-in</span>
                      <strong><?= date('M j, Y', strtotime($booking['check_in'])) ?></strong>
                    </div>
                    <div>
                      <span class="analytics-detail-label">Amount</span>
                      <strong>UGX <?= number_format($booking['total_amount'] ?? 0) ?></strong>
                    </div>
                    <div>
                      <span class="analytics-detail-label">Created</span>
                      <strong><?= date('M j, Y', strtotime($booking['created_at'])) ?> at <?= date('g:i A', strtotime($booking['created_at'])) ?></strong>
                    </div>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>

  <!-- Bento Row 1: Occupancy by Type (5 cols) + Monthly Revenue Trend (7 cols) -->
  <div class="row g-4 mt-1">
    <!-- Occupancy by Room Type -->
    <div class="col-lg-5">
      <div class="analytics-bento-panel">
        <div class="analytics-panel-header">
          <h3 class="analytics-panel-title">
            <span class="bento-kpi-icon is-green" style="width: 34px; height: 34px; font-size: 0.9rem;"><i class="fas fa-bed"></i></span>
            Occupancy by Room Type
          </h3>
          <span class="badge rounded-pill bg-light text-secondary border">Live Status</span>
        </div>

        <?php if (empty($occupancyData)): ?>
          <div class="analytics-empty-state">
            <i class="fas fa-bed text-muted fs-3 mb-2 d-block"></i>
            <p class="text-muted small mb-0">No active room occupancy data available</p>
          </div>
        <?php else: ?>
          <?php foreach ($occupancyData as $room):
            $occRate = (float)($room['occupancy_rate'] ?? 0);
          ?>
            <div class="analytics-occupancy-item">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                  <strong class="d-block" style="font-size: 0.92rem; color: #0f172a;"><?= sanitize($room['room_type']) ?></strong>
                  <span class="text-muted" style="font-size: 0.78rem;"><?= (int)$room['occupied_rooms'] ?> of <?= (int)$room['total_rooms'] ?> rooms occupied</span>
                </div>
                <span class="badge rounded-pill px-3 py-2" style="background: <?= $occRate > 0 ? 'rgba(22, 163, 74, 0.12)' : '#eef2f6' ?>; color: <?= $occRate > 0 ? '#15803d' : '#475569' ?>; font-size: 0.82rem; font-weight: 700;">
                  <?= number_format($occRate, 1) ?>%
                </span>
              </div>
              <div class="progress" style="height: 7px; border-radius: 999px; background: #e2e8f0;">
                <div class="progress-bar" role="progressbar"
                     style="width: <?= max($occRate, 4) ?>%; background: <?= $occRate > 0 ? 'linear-gradient(90deg, #1b8a5a, #34d399)' : '#cbd5e1' ?>; border-radius: 999px;"
                     aria-valuenow="<?= $occRate ?>"
                     aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Monthly Revenue Trend -->
    <div class="col-lg-7">
      <div class="analytics-bento-panel">
        <div class="analytics-panel-header">
          <h3 class="analytics-panel-title">
            <span class="bento-kpi-icon is-blue" style="width: 34px; height: 34px; font-size: 0.9rem;"><i class="fas fa-chart-line"></i></span>
            Monthly Revenue Trend
          </h3>
          <span class="badge rounded-pill bg-light text-secondary border">Last 12 Months</span>
        </div>

        <?php if (empty($revenueByMonth)): ?>
          <div class="analytics-empty-state">
            <i class="fas fa-chart-bar text-muted fs-3 mb-2 d-block"></i>
            <p class="text-muted small mb-0">No monthly revenue records available yet</p>
          </div>
        <?php else:
          $maxMonthlyRev = max(array_map(static fn($r) => (float)($r['total_revenue'] ?? 0), $revenueByMonth)) ?: 1;
        ?>
          <div class="table-responsive">
            <table class="analytics-clean-table">
              <thead>
                <tr>
                  <th>Month</th>
                  <th>Bookings</th>
                  <th>Total Revenue</th>
                  <th>Avg Value</th>
                  <th style="width: 24%;">Trend</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($revenueByMonth as $month):
                  $revVal = (float)($month['total_revenue'] ?? 0);
                  $barPct = min(100, max(8, round(($revVal / $maxMonthlyRev) * 100)));
                ?>
                  <tr>
                    <td>
                      <strong class="text-dark"><?= date('M Y', strtotime($month['month'] . '-01')) ?></strong>
                    </td>
                    <td>
                      <span class="badge rounded-pill" style="background: rgba(37, 99, 235, 0.1); color: #1d4ed8; font-weight: 600;">
                        <?= number_format($month['total_bookings'] ?? 0) ?> <?= (int)($month['total_bookings'] ?? 0) === 1 ? 'stay' : 'stays' ?>
                      </span>
                    </td>
                    <td>
                      <strong style="color: #15803d;">UGX <?= number_format($revVal / 1000000, 1) ?>M</strong>
                    </td>
                    <td class="text-secondary">
                      UGX <?= number_format(($month['avg_booking_value'] ?? 0) / 1000, 0) ?>K
                    </td>
                    <td>
                      <div class="progress" style="height: 6px; border-radius: 999px; background: #e2e8f0;">
                        <div class="progress-bar" style="width: <?= $barPct ?>%; background: linear-gradient(90deg, #1b8a5a, #34d399); border-radius: 999px;"></div>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Bento Row 2: Upcoming Arrivals (6 cols) + Popular Room Types (6 cols) -->
  <div class="row g-4 mt-1">
    <!-- Upcoming Arrivals (Next 7 Days) -->
    <div class="col-lg-6">
      <div class="analytics-bento-panel">
        <div class="analytics-panel-header">
          <h3 class="analytics-panel-title">
            <span class="bento-kpi-icon is-amber" style="width: 34px; height: 34px; font-size: 0.9rem;"><i class="fas fa-plane-arrival"></i></span>
            Upcoming Arrivals (Next 7 Days)
          </h3>
          <span class="badge rounded-pill bg-light text-secondary border"><?= count($upcomingArrivals) ?> Scheduled</span>
        </div>

        <?php if (empty($upcomingArrivals)): ?>
          <div class="analytics-empty-state">
            <span class="bento-kpi-icon is-green mx-auto mb-2" style="width: 48px; height: 48px; font-size: 1.2rem;"><i class="fas fa-calendar-check"></i></span>
            <h4 class="h6 fw-bold mb-1">No Arrivals in the Next 7 Days</h4>
            <p class="text-muted small mb-0">Confirmed check-ins scheduled within the upcoming week will appear here automatically.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="analytics-clean-table">
              <thead>
                <tr>
                  <th>Guest</th>
                  <th>Check-In</th>
                  <th>Guests</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($upcomingArrivals as $arrival): ?>
                  <tr>
                    <td>
                      <strong class="d-block text-dark"><?= sanitize($arrival['guest_name']) ?></strong>
                      <small class="text-muted"><?= sanitize($arrival['booking_reference']) ?></small>
                    </td>
                    <td>
                      <strong class="d-block"><?= date('M j, Y', strtotime($arrival['check_in'])) ?></strong>
                      <small class="text-success fw-semibold">In <?= (int)$arrival['days_until_arrival'] ?> days</small>
                    </td>
                    <td>
                      <span class="badge rounded-pill bg-light text-dark border"><i class="fas fa-user-friends me-1 text-muted"></i><?= (int)$arrival['total_guests'] ?></span>
                    </td>
                    <td>
                      <span class="bento-kpi-pill is-up"><?= ucfirst(sanitize($arrival['status'])) ?></span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Popular Room Types (Last 30 Days) -->
    <div class="col-lg-6">
      <div class="analytics-bento-panel">
        <div class="analytics-panel-header">
          <h3 class="analytics-panel-title">
            <span class="bento-kpi-icon is-rose" style="width: 34px; height: 34px; font-size: 0.9rem;"><i class="fas fa-trophy"></i></span>
            Popular Room Types (Last 30 Days)
          </h3>
          <span class="badge rounded-pill bg-light text-secondary border">Top Performers</span>
        </div>

        <?php if (empty($popularRoomTypes)): ?>
          <div class="analytics-empty-state">
            <span class="bento-kpi-icon is-amber mx-auto mb-2" style="width: 48px; height: 48px; font-size: 1.2rem;"><i class="fas fa-award"></i></span>
            <h4 class="h6 fw-bold mb-1">No Room Popularity Data Yet</h4>
            <p class="text-muted small mb-0">Room rankings will populate as bookings are completed over the last 30 days.</p>
          </div>
        <?php else:
          $maxBookings = max(array_column($popularRoomTypes, 'bookings_count')) ?: 1;
        ?>
          <div class="table-responsive">
            <table class="analytics-clean-table">
              <thead>
                <tr>
                  <th>Room Type</th>
                  <th>Bookings</th>
                  <th>Revenue</th>
                  <th>Share</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($popularRoomTypes as $index => $roomType):
                  $popularityPercent = ($roomType['bookings_count'] / $maxBookings) * 100;
                ?>
                  <tr>
                    <td>
                      <strong class="text-dark"><?= sanitize($roomType['room_type']) ?></strong>
                      <?php if ($index === 0): ?>
                        <span class="badge rounded-pill ms-1" style="background: rgba(245, 158, 11, 0.15); color: #b45309;">#1 Top</span>
                      <?php endif; ?>
                    </td>
                    <td><?= number_format($roomType['bookings_count'] ?? 0) ?></td>
                    <td><strong style="color: #15803d;">UGX <?= number_format(($roomType['revenue'] ?? 0) / 1000000, 1) ?>M</strong></td>
                    <td style="min-width: 110px;">
                      <div class="progress mb-1" style="height: 6px; border-radius: 999px; background: #e2e8f0;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $popularityPercent ?>%; border-radius: 999px;"></div>
                      </div>
                      <small class="text-muted" style="font-size: 0.72rem;"><?= number_format($popularityPercent, 0) ?>% of top</small>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>
</div>

<?php include __DIR__ . '/../includes/admin_layout_footer.php'; ?>
