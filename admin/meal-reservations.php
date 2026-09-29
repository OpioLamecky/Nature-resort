<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/permissions.php';

// Check auth & permissions
if (!isset($_SESSION['user'])) {
    redirect(base_url('public/login.php'));
}
require_permission('bookings.view');

$title = 'Meal Reservations | Admin';
$pageTitle = 'Meal Reservations';

$status = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo   = $_GET['date_to'] ?? '';

$where = [];
$params = [];

if ($status !== '') {
    $where[] = 'status = ?';
    $params[] = $status;
}
if ($dateFrom !== '') {
    $where[] = 'meal_date >= ?';
    $params[] = $dateFrom;
}
if ($dateTo !== '') {
    $where[] = 'meal_date <= ?';
    $params[] = $dateTo;
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$rows = [];
$errorMsg = '';

try {
    $sql = "SELECT * FROM meal_reservations $whereSql ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (Throwable $e) {
    $errorMsg = 'Meal reservations table not found. Please create the meal_reservations table in the database.';
}

include __DIR__ . '/../includes/admin_layout_header.php';
?>

  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1">Meal Reservations</h1>
      <p class="text-muted mb-0">View meal bookings linked to guest stays</p>
    </div>
  </div>

  <?php if ($errorMsg): ?>
    <div class="alert alert-warning">
      <?= sanitize($errorMsg) ?><br>
      <small>You can create it in phpMyAdmin using SQL similar to:<br>
      <code>CREATE TABLE meal_reservations (meal_reservation_id INT AUTO_INCREMENT PRIMARY KEY, booking_id INT NULL, guest_name VARCHAR(255), guest_email VARCHAR(255), guest_phone VARCHAR(100), meal_type VARCHAR(50), meal_date DATE NULL, meal_time TIME NULL, guests INT DEFAULT 0, special_requests TEXT NULL, status VARCHAR(50) DEFAULT 'pending', source VARCHAR(50) DEFAULT 'website', created_at DATETIME DEFAULT CURRENT_TIMESTAMP);</code></small>
    </div>
  <?php else: ?>

  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Status</label>
          <select class="form-select" name="status">
            <option value="">All</option>
            <option value="pending"   <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
            <option value="served"    <?= $status === 'served' ? 'selected' : '' ?>>Served</option>
            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">From Date</label>
          <input type="date" class="form-control" name="date_from" value="<?= sanitize($dateFrom) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">To Date</label>
          <input type="date" class="form-control" name="date_to" value="<?= sanitize($dateTo) ?>">
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
          <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-2"></i>Filter</button>
          <a href="?" class="btn btn-outline-secondary">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Guest</th>
              <th>Meal</th>
              <th>Date / Time</th>
              <th>Guests</th>
              <th>Booking Ref</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($rows)): ?>
              <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                  <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                  No meal reservations found
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td>
                    <strong><?= sanitize($row['guest_name'] ?? '') ?></strong><br>
                    <small class="text-muted"><?= sanitize($row['guest_email'] ?? '') ?></small>
                  </td>
                  <td><?= ucfirst(sanitize($row['meal_type'] ?? '')) ?></td>
                  <td>
                    <?= $row['meal_date'] ? date('M j, Y', strtotime($row['meal_date'])) : '-' ?><br>
                    <small class="text-muted"><?= $row['meal_time'] ? date('g:i A', strtotime($row['meal_time'])) : '' ?></small>
                  </td>
                  <td><?= (int)($row['guests'] ?? 0) ?></td>
                  <td><?= isset($row['booking_id']) ? (int)$row['booking_id'] : '-' ?></td>
                  <td>
                    <span class="badge bg-secondary"><?= ucfirst(sanitize($row['status'] ?? 'pending')) ?></span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <?php endif; ?>

<?php include __DIR__ . '/../includes/admin_layout_footer.php'; ?>
