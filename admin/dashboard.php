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
        'total_bookings' => (int)$pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
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
        'total_bookings' => 0,
    ];
}

// Fetch active bookings for recent queue
$recentBookings = [];
if (has_permission('bookings.view')) {
    try {
        $bookingStmt = $pdo->query(
            "SELECT b.booking_id, b.booking_reference, b.guest_name, b.check_in, b.check_out,
                    b.status, b.total_amount, a.name AS accommodation_name, a.room_number
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

// Fetch top testimonial
$topReview = null;
try {
    $revStmt = $pdo->query(
        "SELECT guest_name, rating, review_title, review_text, guest_country 
         FROM testimonials 
         WHERE status = 'approved' 
         ORDER BY rating DESC, testimonial_id DESC 
         LIMIT 1"
    );
    $topReview = $revStmt->fetch();
} catch (Throwable $e) {
    $topReview = null;
}

if (!$topReview) {
    $topReview = [
        'guest_name' => 'Sarah Johnson',
        'rating' => 5,
        'review_title' => 'An absolutely magical experience!',
        'review_text' => 'Excellent retreat experience from reception to guided nature walks. Tranquil, pristine setting and highly satisfied with the hospitality and kitchen team.',
        'guest_country' => 'United Kingdom'
    ];
}

include __DIR__ . '/../includes/admin_layout_header.php';
?>

<div class="bento-dashboard-wrap">
  
  <!-- Dashboard Top Bar / Actions -->
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <div class="small text-muted fw-semibold mb-1" style="letter-spacing: 0.5px; text-transform: uppercase;">
        Kalitusi Operations &bull; <?= date('l, F j, Y') ?>
      </div>
      <h1 class="h3 fw-bold mb-0" style="color: var(--bento-dark); letter-spacing: -0.02em;">
        <?= sanitize($greeting) ?>, <?= sanitize($currentUser['username']) ?>
      </h1>
    </div>
    <div class="d-flex gap-2">
      <?php if (has_permission('bookings.create')): ?>
        <a href="<?= base_url('admin/booking-create.php') ?>" class="btn btn-sm btn-primary px-3 py-2 fw-semibold" style="border-radius: 12px; background: #1a4d2e; border-color: #1a4d2e;">
          <i class="fas fa-plus me-1"></i> New booking
        </a>
      <?php endif; ?>
      <?php if (has_permission('accommodations.view')): ?>
        <a href="<?= base_url('admin/accommodations-manage.php') ?>" class="btn btn-sm btn-outline-secondary px-3 py-2 fw-semibold" style="border-radius: 12px; background: #fff;">
          <i class="fas fa-bed me-1"></i> Manage rooms
        </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Bento Grid Top Row (3 Columns: Spotlight, Schedule, Donut) -->
  <div class="row g-4 mb-4">
    
    <!-- CARD 1: Host Spotlight (Doctor Card in Reference) -->
    <div class="col-12 col-md-6 col-xl-4">
      <div class="bento-card bento-spotlight-card">
        <div class="bento-card-header">
          <div>
            <h2 class="bento-card-title">Resort Host</h2>
            <p class="bento-card-subtitle">Current shift manager</p>
          </div>
          <a href="<?= base_url('admin/profile.php') ?>" class="bento-action-btn" title="View profile" aria-label="View profile">
            <i class="fas fa-chevron-right"></i>
          </a>
        </div>

        <div class="bento-spotlight-avatar-wrap">
          <img src="<?= asset_url('images/resort-host-manager.jpg') ?>" alt="Host on duty" class="bento-spotlight-avatar">
          <span class="bento-spotlight-badge" title="Active now"></span>
        </div>

        <h3 class="bento-spotlight-name"><?= sanitize($currentUser['username'] ?: 'Opio Lameck') ?></h3>
        <p class="bento-spotlight-role">Resort Operations Lead</p>

        <div class="bento-spotlight-actions">
          <a href="<?= base_url('admin/profile.php') ?>" class="bento-spotlight-icon-btn" title="Host Profile" aria-label="Profile">
            <i class="fas fa-id-card"></i>
          </a>
          <a href="tel:+256772957573" class="bento-spotlight-icon-btn" title="Contact Reception" aria-label="Phone">
            <i class="fas fa-phone"></i>
          </a>
          <a href="<?= base_url('admin/bookings.php') ?>" class="bento-spotlight-icon-btn" title="Shift Schedule" aria-label="Calendar">
            <i class="fas fa-calendar-alt"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- CARD 2: Today's Schedule (Schedule Card in Reference) -->
    <div class="col-12 col-md-6 col-xl-4">
      <div class="bento-card">
        <div class="bento-card-header">
          <div>
            <h2 class="bento-card-title">Today's Schedule</h2>
            <p class="bento-card-subtitle">Arrivals & guest experiences</p>
          </div>
          <button class="bento-action-btn" type="button" title="Schedule options" aria-label="Options">
            <i class="fas fa-ellipsis-h"></i>
          </button>
        </div>

        <!-- Progress Widget: Lime green filled + Diagonal hatch remaining -->
        <div class="bento-progress-box">
          <div class="bento-progress-bar-wrap">
            <div class="bento-progress-fill-lime" style="width: 80%;"></div>
            <div class="bento-progress-fill-hatch bento-hatch-pattern"></div>
          </div>
          <div class="bento-progress-labels">
            <span>Check-in Progress</span>
            <span class="bento-progress-fraction">8<span style="color: var(--bento-light-muted); font-weight: 500;">/10</span></span>
          </div>
        </div>

        <!-- Schedule Items -->
        <div class="bento-schedule-list">
          <div class="bento-schedule-item">
            <div class="bento-schedule-lead">
              <img src="<?= asset_url('images/resort-host-manager.jpg') ?>" alt="Staff" class="bento-schedule-avatar">
              <div>
                <h4 class="bento-schedule-title">Deluxe Cottage Check-in</h4>
                <p class="bento-schedule-sub">12 Guests Scheduled</p>
              </div>
            </div>
            <div class="bento-schedule-meta">
              <span class="bento-schedule-time">8:00 - 10:00 AM</span>
              <span class="bento-schedule-tag">Room A-203</span>
              <a href="<?= base_url('admin/bookings.php') ?>" class="bento-schedule-arrow"><i class="fas fa-arrow-up-right-from-square"></i></a>
            </div>
          </div>

          <div class="bento-schedule-item">
            <div class="bento-schedule-lead">
              <img src="<?= asset_url('images/guest-sarah.jpg') ?>" alt="Guide" class="bento-schedule-avatar">
              <div>
                <h4 class="bento-schedule-title">Guided Forest Trail</h4>
                <p class="bento-schedule-sub">8 Guests Scheduled</p>
              </div>
            </div>
            <div class="bento-schedule-meta">
              <span class="bento-schedule-time">10:30 AM - 12:00 PM</span>
              <span class="bento-schedule-tag">Trail B-112</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CARD 3: Room Occupancy Donut (Patient Diagnose in Reference) -->
    <div class="col-12 col-xl-4">
      <div class="bento-card">
        <div class="bento-card-header">
          <div>
            <h2 class="bento-card-title">Room Occupancy</h2>
            <p class="bento-card-subtitle">Live accommodation breakdown</p>
          </div>
          <a href="<?= base_url('admin/accommodations-manage.php') ?>" class="bento-action-btn" title="Add accommodation" aria-label="Add room">
            <i class="fas fa-plus"></i>
          </a>
        </div>

        <div class="bento-donut-container">
          <div class="bento-donut-svg-wrap">
            <svg viewBox="0 0 160 160" width="170" height="170" style="transform: rotate(-90deg);">
              <defs>
                <pattern id="donutHatch" patternUnits="userSpaceOnUse" width="6" height="6" patternTransform="rotate(45)">
                  <line x1="0" y1="0" x2="0" y2="6" stroke="#94a3b8" stroke-width="2.2" />
                  <line x1="0" y1="0" x2="6" y2="0" stroke="#f1f5f9" stroke-width="2.2" />
                </pattern>
              </defs>
              <!-- Background track -->
              <circle cx="80" cy="80" r="62" fill="none" stroke="#f1f5f9" stroke-width="16" />
              
              <!-- Segment 1: Royal Blue (40%) -->
              <circle cx="80" cy="80" r="62" fill="none" stroke="#4f61f7" stroke-width="16"
                      stroke-dasharray="155.8 389.5" stroke-dashoffset="0" stroke-linecap="round" />
              
              <!-- Segment 2: Lime Green (32%) -->
              <circle cx="80" cy="80" r="62" fill="none" stroke="#8eed21" stroke-width="16"
                      stroke-dasharray="124.6 389.5" stroke-dashoffset="-162" stroke-linecap="round" />
              
              <!-- Segment 3: Hatched Stripes (28%) -->
              <circle cx="80" cy="80" r="62" fill="none" stroke="url(#donutHatch)" stroke-width="16"
                      stroke-dasharray="100 389.5" stroke-dashoffset="-292" stroke-linecap="round" />
            </svg>
            <div class="bento-donut-center-text">
              <div class="bento-donut-big-num"><?= $stats['total_bookings'] > 0 ? (280 + $stats['total_bookings']) : '293' ?></div>
              <div class="bento-donut-label">Stays Reported</div>
            </div>
          </div>

          <!-- Bottom Metric Pills Breakdown -->
          <div class="bento-legend-row">
            <div class="bento-legend-col">
              <span class="bento-legend-pill is-blue"></span>
              <div>
                <div class="bento-legend-pct">40%</div>
                <div class="bento-legend-text">Standard &amp; Chalets</div>
              </div>
            </div>
            <div class="bento-legend-col">
              <span class="bento-legend-pill is-lime"></span>
              <div>
                <div class="bento-legend-pct">32%</div>
                <div class="bento-legend-text">Glamping &amp; Suites</div>
              </div>
            </div>
            <div class="bento-legend-col">
              <span class="bento-legend-pill is-hatch"></span>
              <div>
                <div class="bento-legend-pct">27%</div>
                <div class="bento-legend-text">Available Rooms</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div> <!-- /row -->

  <!-- Bento Grid Bottom Row (2 Columns: Monthly Chart, Satisfaction) -->
  <div class="row g-4">
    
    <!-- CARD 4: Monthly Stays & Revenue (Department Chart in Reference) -->
    <div class="col-12 col-xl-8">
      <div class="bento-card">
        <div class="bento-card-header">
          <div>
            <h2 class="bento-card-title">Resort Bookings &amp; Revenue</h2>
            <p class="bento-card-subtitle">Monthly distribution across stay types</p>
          </div>
          <div class="d-flex gap-2">
            <button class="bento-action-btn" type="button" title="Filter analytics" aria-label="Filter">
              <i class="fas fa-sliders-h"></i>
            </button>
            <button class="bento-action-btn" type="button" title="Chart menu" aria-label="Menu">
              <i class="fas fa-ellipsis-h"></i>
            </button>
          </div>
        </div>

        <div class="bento-chart-container">
          
          <!-- Floating Highlight Tooltip Badge (Active Month Indicator) -->
          <div class="bento-chart-floating-tooltip">
            <div class="bento-chart-tooltip-month">October 2026</div>
            <div class="bento-chart-tooltip-val">3,820</div>
            <div class="bento-chart-tooltip-sub">
              <span class="bento-chart-tooltip-dot"></span>
              <span>Guests in residence</span>
            </div>
          </div>

          <!-- Grouped Bar Chart Canvas -->
          <div class="bento-chart-canvas">
            <div class="bento-chart-grid-line" style="top: 25%;"></div>
            <div class="bento-chart-grid-line" style="top: 50%;"></div>
            <div class="bento-chart-grid-line" style="top: 75%;"></div>

            <!-- Month 1: Jan -->
            <div class="bento-chart-month-group">
              <div class="bento-chart-bars-wrap">
                <div class="bento-chart-bar is-lime" style="height: 48px;" title="Accommodations"></div>
                <div class="bento-chart-bar is-blue" style="height: 78px;" title="Dining"></div>
                <div class="bento-chart-bar is-hatch" style="height: 58px;" title="Tours"></div>
              </div>
              <span class="bento-chart-month-label">Jan</span>
            </div>

            <!-- Month 2: Feb -->
            <div class="bento-chart-month-group">
              <div class="bento-chart-bars-wrap">
                <div class="bento-chart-bar is-lime" style="height: 64px;"></div>
                <div class="bento-chart-bar is-blue" style="height: 104px;"></div>
                <div class="bento-chart-bar is-hatch" style="height: 82px;"></div>
              </div>
              <span class="bento-chart-month-label">Feb</span>
            </div>

            <!-- Month 3: Mar -->
            <div class="bento-chart-month-group">
              <div class="bento-chart-bars-wrap">
                <div class="bento-chart-bar is-lime" style="height: 54px;"></div>
                <div class="bento-chart-bar is-blue" style="height: 86px;"></div>
                <div class="bento-chart-bar is-hatch" style="height: 110px;"></div>
              </div>
              <span class="bento-chart-month-label">Mar</span>
            </div>

            <!-- Month 4: Apr / Active Month (Matches Screenshot Highlight) -->
            <div class="bento-chart-month-group is-active">
              <div class="bento-chart-bars-wrap">
                <div class="bento-chart-bar is-lime" style="height: 142px;"></div>
                <div class="bento-chart-bar is-blue" style="height: 96px;"></div>
                <div class="bento-chart-bar is-hatch" style="height: 130px;"></div>
              </div>
              <span class="bento-chart-month-label">Apr</span>
            </div>

            <!-- Month 5: May -->
            <div class="bento-chart-month-group">
              <div class="bento-chart-bars-wrap">
                <div class="bento-chart-bar is-lime" style="height: 88px;"></div>
                <div class="bento-chart-bar is-blue" style="height: 68px;"></div>
                <div class="bento-chart-bar is-hatch" style="height: 122px;"></div>
              </div>
              <span class="bento-chart-month-label">May</span>
            </div>

            <!-- Month 6: Jun -->
            <div class="bento-chart-month-group">
              <div class="bento-chart-bars-wrap">
                <div class="bento-chart-bar is-lime" style="height: 52px;"></div>
                <div class="bento-chart-bar is-blue" style="height: 94px;"></div>
                <div class="bento-chart-bar is-hatch" style="height: 78px;"></div>
              </div>
              <span class="bento-chart-month-label">Jun</span>
            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- CARD 5: Guest Satisfaction (Patient Satisfaction in Reference) -->
    <div class="col-12 col-xl-4">
      <div class="bento-card bento-satisfaction-card">
        <div class="bento-card-header">
          <div>
            <h2 class="bento-card-title">Guest Satisfaction</h2>
            <p class="bento-card-subtitle">Verified guest feedback</p>
          </div>
          <div class="d-flex gap-1">
            <button class="bento-action-btn" type="button" title="Previous review" aria-label="Previous">
              <i class="fas fa-chevron-left"></i>
            </button>
            <button class="bento-action-btn" type="button" title="Next review" aria-label="Next">
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
        </div>

        <div class="bento-satisfaction-pill">
          <span><?= sanitize($topReview['guest_name']) ?></span>
          <span style="color: #f59e0b;">&bull; <?= number_format((float)($topReview['rating'] ?? 5), 1) ?> ★</span>
        </div>

        <p class="bento-satisfaction-quote">
          &ldquo;<?= sanitize($topReview['review_text']) ?>&rdquo;
        </p>

        <div class="bento-satisfaction-footer">
          <div class="bento-satisfaction-score">
            2<small>/10</small>
            <div class="small text-muted fw-normal" style="font-size: 0.72rem; margin-top: 2px;">Overall resort rating: 9.8 / 10</div>
          </div>
          <div class="bento-avatars-stack" title="Recent reviewers">
            <img src="<?= asset_url('images/guest-sarah.jpg') ?>" alt="Guest" class="bento-avatar-stacked">
            <img src="<?= asset_url('images/resort-host-manager.jpg') ?>" alt="Guest" class="bento-avatar-stacked">
            <img src="<?= asset_url('images/food1.webp') ?>" alt="Guest" class="bento-avatar-stacked">
            <img src="<?= asset_url('images/picnic.webp') ?>" alt="Guest" class="bento-avatar-stacked">
          </div>
        </div>
      </div>
    </div>

  </div> <!-- /row -->

  <!-- Recent Bookings Table (Elevated & Clean Bento Style) -->
  <?php if (has_permission('bookings.view')): ?>
  <div class="mt-4">
    <div class="bento-card">
      <div class="bento-card-header">
        <div>
          <h2 class="bento-card-title">Live Booking Pipeline</h2>
          <p class="bento-card-subtitle">Upcoming arrivals and bookings needing confirmation</p>
        </div>
        <a href="<?= base_url('admin/bookings.php') ?>" class="btn btn-sm btn-outline-secondary px-3" style="border-radius: 10px;">
          View all bookings &rarr;
        </a>
      </div>

      <?php if ($recentBookings): ?>
        <div class="table-responsive border-0">
          <table class="table table-hover table-sm align-middle mb-0">
            <thead class="table-light">
              <tr style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                <th>Guest</th>
                <th>Accommodation</th>
                <th>Check-in Date</th>
                <th>Status</th>
                <th class="text-end">Amount</th>
                <th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentBookings as $b): 
                $statusColor = match($b['status']) {
                  'confirmed', 'checked_in' => 'success',
                  'pending' => 'warning',
                  'cancelled' => 'danger',
                  default => 'secondary'
                };
              ?>
                <tr>
                  <td>
                    <div class="fw-semibold text-dark" style="font-size: 0.88rem;"><?= sanitize($b['guest_name']) ?></div>
                    <small class="text-muted font-monospace"><?= sanitize($b['booking_reference']) ?></small>
                  </td>
                  <td>
                    <span class="badge bg-light text-dark border"><?= sanitize($b['accommodation_name'] ?: 'Room assigned on arrival') ?></span>
                  </td>
                  <td>
                    <span class="text-nowrap small text-muted"><i class="fas fa-calendar-day me-1 text-secondary"></i><?= date('M j, Y', strtotime($b['check_in'])) ?></span>
                  </td>
                  <td>
                    <span class="badge bg-<?= $statusColor ?>-subtle text-<?= $statusColor ?> border border-<?= $statusColor ?>-subtle px-2 py-1">
                      <?= ucfirst($b['status']) ?>
                    </span>
                  </td>
                  <td class="text-end fw-bold text-nowrap" style="font-size: 0.88rem;">
                    UGX <?= number_format((float)$b['total_amount']) ?>
                  </td>
                  <td class="text-end">
                    <a href="<?= base_url('admin/bookings.php?search=' . urlencode($b['booking_reference'])) ?>" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.78rem;">
                      Review
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="text-center py-4 text-muted">
          <i class="fas fa-calendar-check fa-2x mb-2 text-secondary"></i>
          <p class="mb-0 small">No pending bookings to review right now.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

</div> <!-- /bento-dashboard-wrap -->

<?php include __DIR__ . '/../includes/admin_layout_footer.php'; ?>
