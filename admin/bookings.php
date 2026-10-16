<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/permissions.php';

// Check authentication and permissions
if (!isset($_SESSION['user'])) {
    redirect(base_url('public/login.php'));
}
require_permission('bookings.view');

$title = 'Manage Bookings | Admin';
$pageTitle = 'Bookings Management';

// Handle actions (update status, cancel, etc.)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verify_csrf($_POST['csrf_token'] ?? '');
    
    $bookingId = (int)($_POST['booking_id'] ?? 0);
    $action = $_POST['action'];
    
    try {
        if ($action === 'confirm' && has_permission('bookings.confirm')) {
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE booking_id = ?");
            $stmt->execute([$bookingId]);
            flash('Booking confirmed successfully!', 'success');
            log_admin_action('booking_confirmed', null, $bookingId);
            
        } elseif ($action === 'cancel' && has_permission('bookings.cancel')) {
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE booking_id = ?");
            $stmt->execute([$bookingId]);
            flash('Booking cancelled.', 'info');
            log_admin_action('booking_cancelled', null, $bookingId);
            
        } elseif ($action === 'checkin' && has_permission('bookings.edit')) {
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'checked_in' WHERE booking_id = ?");
            $stmt->execute([$bookingId]);
            flash('Guest checked in successfully!', 'success');
            log_admin_action('guest_checked_in', null, $bookingId);
            
        } elseif ($action === 'checkout' && has_permission('bookings.edit')) {
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'checked_out' WHERE booking_id = ?");
            $stmt->execute([$bookingId]);
            flash('Guest checked out successfully!', 'success');
            log_admin_action('guest_checked_out', null, $bookingId);
            
        } elseif ($action === 'delete' && has_permission('bookings.delete')) {
            $stmt = $pdo->prepare("DELETE FROM bookings WHERE booking_id = ?");
            $stmt->execute([$bookingId]);
            flash('Booking deleted.', 'warning');
            log_admin_action('booking_deleted', null, $bookingId);
        }
        
        redirect(base_url('admin/bookings.php'));
        
    } catch (Exception $e) {
        flash('Error: ' . $e->getMessage(), 'danger');
    }
}

// Filters
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// Build query conditions
$where = [];
$whereSimple = []; // For queries without table aliases
$params = [];

if ($status) {
    $where[] = "b.status = ?";
    $whereSimple[] = "status = ?";
    $params[] = $status;
}

if ($search) {
    $where[] = "(b.guest_name LIKE ? OR b.guest_email LIKE ? OR b.booking_reference LIKE ?)";
    $whereSimple[] = "(guest_name LIKE ? OR guest_email LIKE ? OR booking_reference LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($dateFrom) {
    $where[] = "b.check_in >= ?";
    $whereSimple[] = "check_in >= ?";
    $params[] = $dateFrom;
}

if ($dateTo) {
    $where[] = "b.check_in <= ?";
    $whereSimple[] = "check_in <= ?";
    $params[] = $dateTo;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
$whereClauseSimple = !empty($whereSimple) ? 'WHERE ' . implode(' AND ', $whereSimple) : '';

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Get total count
$countSql = "SELECT COUNT(*) FROM bookings $whereClauseSimple";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalBookings = $stmt->fetchColumn();
$totalPages = ceil($totalBookings / $perPage);

// Get bookings
$sql = "
    SELECT b.*, a.room_number, a.room_type, a.name as room_name
    FROM bookings b
    LEFT JOIN accommodations a ON b.accommodation_id = a.accommodation_id
    $whereClause
    ORDER BY b.created_at DESC
    LIMIT $perPage OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// Get statistics
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
    'pending' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn(),
    'confirmed' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed'")->fetchColumn(),
    'checked_in' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'checked_in'")->fetchColumn(),
];

include __DIR__ . '/../includes/admin_layout_header.php';
?>

  
  <!-- Header -->
  <div class="booking-page-heading d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
    <div>
      <h1 class="h3 mb-1">Manage Bookings</h1>
      <p class="text-muted mb-0">View and manage all resort bookings</p>
    </div>
    <div class="booking-page-actions">
      <?php if (has_permission('bookings.create')): ?>
        <a href="<?= base_url('admin/booking-create.php') ?>" class="btn btn-primary">
          <i class="fas fa-plus me-2"></i>New Booking
        </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Statistics Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card bg-light">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <p class="text-muted mb-1">Total Bookings</p>
              <h3 class="mb-0"><?= number_format($stats['total'] ?? 0) ?></h3>
            </div>
            <div class="text-primary fs-2"><i class="fas fa-calendar-alt"></i></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-warning bg-opacity-10">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <p class="text-muted mb-1">Pending</p>
              <h3 class="mb-0"><?= number_format($stats['pending'] ?? 0) ?></h3>
            </div>
            <div class="text-warning fs-2"><i class="fas fa-clock"></i></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-success bg-opacity-10">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <p class="text-muted mb-1">Confirmed</p>
              <h3 class="mb-0"><?= number_format($stats['confirmed'] ?? 0) ?></h3>
            </div>
            <div class="text-success fs-2"><i class="fas fa-check-circle"></i></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-info bg-opacity-10">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <p class="text-muted mb-1">Checked In</p>
              <h3 class="mb-0"><?= number_format($stats['checked_in'] ?? 0) ?></h3>
            </div>
            <div class="text-info fs-2"><i class="fas fa-door-open"></i></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Search</label>
          <input type="text" class="form-control" name="search" value="<?= sanitize($search) ?>" 
                 placeholder="Name, email, or reference">
        </div>
        <div class="col-md-2">
          <label class="form-label">Status</label>
          <select class="form-select" name="status">
            <option value="">All Status</option>
            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
            <option value="checked_in" <?= $status === 'checked_in' ? 'selected' : '' ?>>Checked In</option>
            <option value="checked_out" <?= $status === 'checked_out' ? 'selected' : '' ?>>Checked Out</option>
            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">From Date</label>
          <input type="date" class="form-control" name="date_from" value="<?= sanitize($dateFrom) ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label">To Date</label>
          <input type="date" class="form-control" name="date_to" value="<?= sanitize($dateTo) ?>">
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-filter me-2"></i>Filter
          </button>
          <a href="?" class="btn btn-outline-secondary">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Bookings Table -->
  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive booking-table-wrap">
        <table class="table table-hover mb-0 booking-list-table">
          <thead class="table-light">
            <tr>
              <th>Reference</th>
              <th>Guest</th>
              <th>Room</th>
              <th>Check-in</th>
              <th>Check-out</th>
              <th>Guests</th>
              <th>Amount</th>
              <th>Payment</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($bookings)): ?>
              <tr>
                <td colspan="10" class="text-center py-5 text-muted">
                  <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                  No bookings found
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($bookings as $booking): ?>
                <tr>
                  <td data-label="Reference">
                    <strong><?= sanitize($booking['booking_reference']) ?></strong><br>
                    <small class="text-muted"><?= date('M j, g:i A', strtotime($booking['created_at'])) ?></small>
                  </td>
                  <td data-label="Guest">
                    <?= sanitize($booking['guest_name']) ?><br>
                    <small class="text-muted"><?= sanitize($booking['guest_email']) ?></small>
                    <?php if ($booking['guest_phone']): ?>
                      <br><small class="text-muted"><i class="fas fa-phone me-1"></i><?= sanitize($booking['guest_phone']) ?></small>
                    <?php endif; ?>
                  </td>
                  <td data-label="Room">
                    <?php if ($booking['room_name']): ?>
                      <strong><?= sanitize($booking['room_type']) ?></strong><br>
                      <small class="text-muted"><?= sanitize($booking['room_name']) ?></small>
                    <?php else: ?>
                      <span class="text-muted">Not assigned</span>
                    <?php endif; ?>
                  </td>
                  <td data-label="Check-in"><?= date('M j, Y', strtotime($booking['check_in'])) ?></td>
                  <td data-label="Check-out">
                    <?= date('M j, Y', strtotime($booking['check_out'])) ?><br>
                    <small class="text-muted"><?= $booking['nights'] ?> nights</small>
                  </td>
                  <td data-label="Guests"><?= $booking['adults'] + $booking['children'] ?></td>
                  <td data-label="Amount">
                    <strong>UGX <?= number_format($booking['total_amount'] ?? 0) ?></strong><br>
                    <small class="text-muted">Deposit: UGX <?= number_format($booking['deposit_amount'] ?? 0) ?></small>
                  </td>
                  <td data-label="Payment">
                    <?php
                    $paymentColors = [
                        'unpaid' => 'danger',
                        'deposit_paid' => 'warning',
                        'fully_paid' => 'success',
                        'refunded' => 'secondary'
                    ];
                    $paymentColor = $paymentColors[$booking['payment_status']] ?? 'secondary';
                    ?>
                    <span class="badge bg-<?= $paymentColor ?>">
                      <?= ucwords(str_replace('_', ' ', $booking['payment_status'])) ?>
                    </span>
                  </td>
                  <td data-label="Status">
                    <?php
                    $statusColors = [
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'checked_in' => 'info',
                        'checked_out' => 'secondary',
                        'cancelled' => 'danger'
                    ];
                    $statusColor = $statusColors[$booking['status']] ?? 'secondary';
                    ?>
                    <span class="badge bg-<?= $statusColor ?>">
                      <?= ucfirst($booking['status']) ?>
                    </span>
                  </td>
                  <td data-label="Actions">
                    <div class="btn-group btn-group-sm">
                      <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-cog"></i>
                      </button>
                      <ul class="dropdown-menu">
                        <?php if ($booking['status'] === 'pending' && has_permission('bookings.confirm')): ?>
                          <li>
                            <form method="POST" style="display:inline;">
                              <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                              <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                              <input type="hidden" name="action" value="confirm">
                              <button type="submit" class="dropdown-item text-success">
                                <i class="fas fa-check me-2"></i>Confirm
                              </button>
                            </form>
                          </li>
                        <?php endif; ?>
                        
                        <?php if ($booking['status'] === 'confirmed' && has_permission('bookings.edit')): ?>
                          <li>
                            <form method="POST" style="display:inline;">
                              <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                              <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                              <input type="hidden" name="action" value="checkin">
                              <button type="submit" class="dropdown-item text-info">
                                <i class="fas fa-door-open me-2"></i>Check In
                              </button>
                            </form>
                          </li>
                        <?php endif; ?>
                        
                        <?php if ($booking['status'] === 'checked_in' && has_permission('bookings.edit')): ?>
                          <li>
                            <form method="POST" style="display:inline;">
                              <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                              <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                              <input type="hidden" name="action" value="checkout">
                              <button type="submit" class="dropdown-item text-secondary">
                                <i class="fas fa-door-closed me-2"></i>Check Out
                              </button>
                            </form>
                          </li>
                        <?php endif; ?>
                        
                        <?php if (in_array($booking['status'], ['pending', 'confirmed']) && has_permission('bookings.cancel')): ?>
                          <li><hr class="dropdown-divider"></li>
                          <li>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Cancel this booking?');">
                              <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                              <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                              <input type="hidden" name="action" value="cancel">
                              <button type="submit" class="dropdown-item text-warning">
                                <i class="fas fa-ban me-2"></i>Cancel
                              </button>
                            </form>
                          </li>
                        <?php endif; ?>
                        
                        <?php if (has_permission('bookings.delete')): ?>
                          <li>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Permanently delete this booking?');">
                              <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                              <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                              <input type="hidden" name="action" value="delete">
                              <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-trash me-2"></i>Delete
                              </button>
                            </form>
                          </li>
                        <?php endif; ?>
                      </ul>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
      <div class="card-footer">
        <nav>
          <ul class="pagination justify-content-center mb-0">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?><?= $status ? '&status=' . $status : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                  <?= $i ?>
                </a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
      </div>
    <?php endif; ?>
  </div>

<?php include __DIR__ . '/../includes/admin_layout_footer.php'; ?>
