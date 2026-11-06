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
  <div class="analytics-heading d-flex justify-content-between align-items-center mb-4">
    <div>
      <p class="text-muted mb-0">Booking performance, occupancy, revenue, and guest feedback.</p>
    </div>
    <div class="analytics-heading-actions">
      <a href="<?= base_url('admin/dashboard.php') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-2"></i>Dashboard
      </a>
    </div>
  </div>

  <!-- Date Range Filter -->
  <div class="card analytics-filter-card mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3 align-items-end">
        <div class="col-12 col-sm-5 col-lg-3">
          <label for="start_date" class="form-label">Start Date</label>
          <input type="date" class="form-control" id="start_date" name="start_date" value="<?= sanitize($startDate) ?>">
        </div>
        <div class="col-12 col-sm-5 col-lg-3">
          <label for="end_date" class="form-label">End Date</label>
          <input type="date" class="form-control" id="end_date" name="end_date" value="<?= sanitize($endDate) ?>">
        </div>
        <div class="col-12 col-sm-12 col-lg-6 analytics-filter-actions">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-filter me-2"></i>Filter
          </button>
          <a href="?" class="btn btn-outline-secondary">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Stats Cards -->
  <div class="analytics-section-heading">
    <h2>Booking performance</h2>
    <p>Summary for the selected date range, except pending bookings.</p>
  </div>
  <div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3 d-flex">
      <div class="card analytics-metric-card analytics-metric-primary h-100 w-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="stat-icon">
              <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-number"><?= number_format($totalBookings ?? 0) ?></h3>
              <p class="stat-label">Total Bookings</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 d-flex">
      <div class="card analytics-metric-card analytics-metric-success h-100 w-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="stat-icon">
              <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-number">UGX <?= number_format(($totalRevenue ?? 0) / 1000000, 1) ?>M</h3>
              <p class="stat-label">Total Revenue</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 d-flex">
      <div class="card analytics-metric-card analytics-metric-info h-100 w-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="stat-icon">
              <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-number"><?= number_format($confirmedBookings ?? 0) ?></h3>
              <p class="stat-label">Confirmed</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 d-flex">
      <div class="card analytics-metric-card analytics-metric-warning h-100 w-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="stat-icon">
              <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-number"><?= number_format($pendingBookings ?? 0) ?></h3>
              <p class="stat-label">Pending</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Additional Analytics Cards -->
  <div class="analytics-section-heading">
    <h2>Guest experience</h2>
    <p>Average booking value uses the selected range; reviews cover the last 30 days.</p>
  </div>
  <div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3 d-flex">
      <div class="card analytics-metric-card analytics-metric-info h-100 w-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="stat-icon">
              <i class="fas fa-money-bill-alt"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-number">UGX <?= number_format(($avgBookingValue ?? 0) / 1000, 0) ?>K</h3>
              <p class="stat-label">Avg Booking Value</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 d-flex">
      <div class="card analytics-metric-card analytics-metric-warning h-100 w-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="stat-icon">
              <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-number"><?= number_format($cancellationRate ?? 0, 1) ?>%</h3>
              <p class="stat-label">Cancellation Rate</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 d-flex">
      <div class="card analytics-metric-card analytics-metric-success h-100 w-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="stat-icon">
              <i class="fas fa-star"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-number"><?= number_format($avgRating ?? 0, 1) ?>/5</h3>
              <p class="stat-label">Guest Rating</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3 d-flex">
      <div class="card analytics-metric-card analytics-metric-primary h-100 w-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="stat-icon">
              <i class="fas fa-comments"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-number"><?= number_format($totalReviews ?? 0) ?></h3>
              <p class="stat-label">Reviews (30 days)</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    
    <!-- Recent Bookings -->
    <div class="col-12">
      <div class="card h-100">
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

  <div class="row g-3 mt-1">
    <!-- Occupancy by Room Type -->
    <div class="col-xl-4">
      <div class="card h-100">
        <div class="card-header">
          <h3 class="h5 mb-0"><i class="fas fa-bed me-2"></i>Occupancy by Type</h3>
        </div>
        <div class="card-body">
          <?php if (empty($occupancyData)): ?>
            <p class="text-muted text-center">No occupancy data available</p>
          <?php else: ?>
            <?php foreach ($occupancyData as $room): ?>
              <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="small"><?= sanitize($room['room_type']) ?></span>
                  <strong><?= number_format($room['occupancy_rate'] ?? 0, 1) ?>%</strong>
                </div>
                <div class="progress" style="height: 8px;">
                  <div class="progress-bar bg-success" role="progressbar" 
                       style="width: <?= $room['occupancy_rate'] ?>%" 
                       aria-valuenow="<?= $room['occupancy_rate'] ?>" 
                       aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-muted"><?= $room['occupied_rooms'] ?>/<?= $room['total_rooms'] ?> occupied</small>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Upcoming Arrivals -->
    <div class="col-xl-4 col-md-6">
      <div class="card h-100">
        <div class="card-header">
          <h3 class="h5 mb-0"><i class="fas fa-plane-arrival me-2"></i>Upcoming Arrivals</h3>
          <p class="small text-muted mb-0 mt-1">Confirmed arrivals over the next 7 days.</p>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead class="table-light">
                <tr>
                  <th>Guest</th>
                  <th>Check-in</th>
                  <th>Guests</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($upcomingArrivals)): ?>
                  <tr>
                    <td colspan="4" class="text-center text-muted py-3">No upcoming arrivals</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($upcomingArrivals as $arrival): ?>
                    <tr>
                      <td>
                        <strong><?= sanitize($arrival['guest_name']) ?></strong><br>
                        <small class="text-muted"><?= sanitize($arrival['booking_reference']) ?></small>
                      </td>
                      <td>
                        <?= date('M j', strtotime($arrival['check_in'])) ?><br>
                        <small class="text-muted">In <?= $arrival['days_until_arrival'] ?> days</small>
                      </td>
                      <td><?= $arrival['total_guests'] ?></td>
                      <td><span class="badge bg-info"><?= ucfirst($arrival['status']) ?></span></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Monthly Revenue -->
    <div class="col-xl-4 col-md-6">
      <div class="card h-100">
        <div class="card-header">
          <h3 class="h5 mb-1"><i class="fas fa-chart-line me-2"></i>Monthly Revenue Trend</h3>
          <p class="small text-muted mb-0">Last 12 months; excludes cancelled bookings.</p>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead class="table-light">
                <tr>
                  <th>Month</th>
                  <th>Bookings</th>
                  <th>Revenue</th>
                  <th>Avg Value</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($revenueByMonth)): ?>
                  <tr>
                    <td colspan="4" class="text-center text-muted py-3">No revenue data available</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($revenueByMonth as $month): ?>
                    <tr>
                      <td><?= date('M Y', strtotime($month['month'] . '-01')) ?></td>
                      <td><?= number_format($month['total_bookings'] ?? 0) ?></td>
                      <td>UGX <?= number_format(($month['total_revenue'] ?? 0) / 1000000, 1) ?>M</td>
                      <td>UGX <?= number_format(($month['avg_booking_value'] ?? 0) / 1000, 0) ?>K</td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Popular Room Types -->
  <div class="row g-3 mt-3">
    <div class="col-lg-12">
      <div class="card h-100">
        <div class="card-header">
          <h3 class="h5 mb-1"><i class="fas fa-trophy me-2"></i>Popular Room Types</h3>
          <p class="small text-muted mb-0 mt-1">Top room types by booking count over the last 30 days.</p>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Room Type</th>
                  <th>Bookings</th>
                  <th>Revenue</th>
                  <th>Avg Revenue per Booking</th>
                  <th>Popularity</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($popularRoomTypes)): ?>
                  <tr>
                    <td colspan="5" class="text-center text-muted py-4">No booking data available</td>
                  </tr>
                <?php else: ?>
                  <?php 
                  $maxBookings = max(array_column($popularRoomTypes, 'bookings_count'));
                  foreach ($popularRoomTypes as $index => $roomType): 
                    $popularityPercent = $maxBookings > 0 ? ($roomType['bookings_count'] / $maxBookings) * 100 : 0;
                    $avgRevenue = $roomType['bookings_count'] > 0 ? $roomType['revenue'] / $roomType['bookings_count'] : 0;
                  ?>
                    <tr>
                      <td>
                        <strong><?= sanitize($roomType['room_type']) ?></strong>
                        <?php if ($index === 0): ?>
                          <span class="badge bg-warning ms-2">Most Popular</span>
                        <?php endif; ?>
                      </td>
                      <td><?= number_format($roomType['bookings_count'] ?? 0) ?></td>
                      <td>UGX <?= number_format(($roomType['revenue'] ?? 0) / 1000000, 1) ?>M</td>
                      <td>UGX <?= number_format(($avgRevenue ?? 0) / 1000, 0) ?>K</td>
                      <td>
                        <div class="progress" style="height: 8px;">
                          <div class="progress-bar bg-success" role="progressbar" 
                               style="width: <?= $popularityPercent ?>%" 
                               aria-valuenow="<?= $popularityPercent ?>" 
                               aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-muted"><?= number_format($popularityPercent ?? 0, 1) ?>% of max</small>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
</div>

<?php include __DIR__ . '/../includes/admin_layout_footer.php'; ?>
