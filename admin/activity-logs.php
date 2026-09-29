<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../includes/activity_logger.php';

// Check authentication
if (!isset($_SESSION['user'])) {
    redirect(base_url('public/login.php'));
}

// Check permission (only admins can view all activity logs)
if (!has_permission('settings.view')) {
    flash('You do not have permission to view activity logs.');
    redirect(base_url('admin/dashboard.php'));
}

$title = 'System Activity Logs | Admin';

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Get activity logs
$activityLogs = get_recent_system_activity($perPage + 1); // Get one extra to check if there are more
$hasMore = count($activityLogs) > $perPage;
if ($hasMore) {
    array_pop($activityLogs); // Remove the extra record
}

// Get total count for selected user if filtered
$totalCount = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM activity_logs");
    $totalCount = (int) $stmt->fetchColumn();
} catch (Exception $e) {
    // Ignore
}

$pageTitle = 'Activity Logs';
include __DIR__ . '/../includes/admin_layout_header.php';
?>


  
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1">
        <i class="fas fa-history me-2"></i>System Activity Logs
      </h1>
      <p class="text-muted mb-0">Monitor all user activities across the system</p>
    </div>
    <a href="<?= base_url('admin/dashboard.php') ?>" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
    </a>
  </div>

  <!-- Stats Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card stat-card bg-primary text-white">
        <div class="card-body text-center">
          <i class="fas fa-database fa-2x mb-2"></i>
          <h3 class="mb-0"><?= number_format($totalCount) ?></h3>
          <small>Total Activities</small>
        </div>
      </div>
    </div>
    <?php
    try {
        $todayCount = $pdo->query("SELECT COUNT(*) FROM activity_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        $loginCount = $pdo->query("SELECT COUNT(*) FROM activity_logs WHERE action = 'login' AND DATE(created_at) = CURDATE()")->fetchColumn();
        $activeUsers = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM activity_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    } catch (Exception $e) {
        $todayCount = 0;
        $loginCount = 0;
        $activeUsers = 0;
    }
    ?>
    <div class="col-md-3">
      <div class="card stat-card bg-success text-white">
        <div class="card-body text-center">
          <i class="fas fa-chart-line fa-2x mb-2"></i>
          <h3 class="mb-0"><?= $todayCount ?></h3>
          <small>Today's Activities</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card stat-card bg-info text-white">
        <div class="card-body text-center">
          <i class="fas fa-sign-in-alt fa-2x mb-2"></i>
          <h3 class="mb-0"><?= $loginCount ?></h3>
          <small>Today's Logins</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card stat-card bg-warning text-white">
        <div class="card-body text-center">
          <i class="fas fa-users fa-2x mb-2"></i>
          <h3 class="mb-0"><?= $activeUsers ?></h3>
          <small>Active Users Today</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Activity Logs Table -->
  <div class="card">
    <div class="card-header" style="background: linear-gradient(135deg, var(--pine-dark), var(--forest-green)); color: var(--white);">
      <h5 class="mb-0">
        <i class="fas fa-list me-2"></i>Recent Activity (Last 100 entries)
      </h5>
    </div>
    <div class="card-body">
      <?php if (empty($activityLogs)): ?>
        <div class="text-center text-muted py-5">
          <i class="fas fa-inbox fa-4x mb-3"></i>
          <h5>No Activity Logged Yet</h5>
          <p>System activity will appear here once users start interacting with the platform</p>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th style="width: 40px;"></th>
                <th>User</th>
                <th>Action</th>
                <th>Description</th>
                <th>Entity</th>
                <th>IP Address</th>
                <th>Date & Time</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($activityLogs as $log): ?>
                <tr>
                  <td>
                    <i class="fas <?= get_action_icon($log['action']) ?> text-<?= get_action_color($log['action']) ?>"></i>
                  </td>
                  <td>
                    <div>
                      <strong><?= sanitize($log['username']) ?></strong>
                      <br>
                      <small class="text-muted"><?= sanitize($log['email']) ?></small>
                    </div>
                    <span class="badge bg-secondary"><?= sanitize($log['role']) ?></span>
                  </td>
                  <td>
                    <span class="badge bg-<?= get_action_color($log['action']) ?>">
                      <?= sanitize(format_action_name($log['action'])) ?>
                    </span>
                  </td>
                  <td><?= sanitize($log['description'] ?? '-') ?></td>
                  <td>
                    <?php if ($log['entity_type']): ?>
                      <small class="text-muted">
                        <i class="fas fa-tag me-1"></i>
                        <?= sanitize($log['entity_type']) ?>
                        <?php if ($log['entity_id']): ?>
                          #<?= $log['entity_id'] ?>
                        <?php endif; ?>
                      </small>
                    <?php else: ?>
                      <small class="text-muted">-</small>
                    <?php endif; ?>
                  </td>
                  <td>
                    <small class="text-muted">
                      <i class="fas fa-network-wired me-1"></i>
                      <?= sanitize($log['ip_address'] ?? 'Unknown') ?>
                    </small>
                  </td>
                  <td>
                    <small class="text-muted">
                      <i class="fas fa-clock me-1"></i>
                      <?= date('M j, Y g:i A', strtotime($log['created_at'])) ?>
                    </small>
                    <br>
                    <small class="text-muted">
                      <?php
                      $timeAgo = time() - strtotime($log['created_at']);
                      if ($timeAgo < 60) {
                          echo 'Just now';
                      } elseif ($timeAgo < 3600) {
                          echo floor($timeAgo / 60) . ' min ago';
                      } elseif ($timeAgo < 86400) {
                          echo floor($timeAgo / 3600) . ' hours ago';
                      } else {
                          echo floor($timeAgo / 86400) . ' days ago';
                      }
                      ?>
                    </small>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Information Card -->
  <div class="row mt-4">
    <div class="col-12">
      <div class="card tips-card">
        <div class="card-header">
          <h5 class="mb-0">
            <i class="fas fa-info-circle me-2"></i>About Activity Logs
          </h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-4">
              <h6><i class="fas fa-shield-alt me-2 text-success"></i>Security Monitoring</h6>
              <p class="text-muted small">
                Activity logs help track user actions, detect suspicious behavior, and maintain system security.
              </p>
            </div>
            <div class="col-md-4">
              <h6><i class="fas fa-search me-2 text-primary"></i>Audit Trail</h6>
              <p class="text-muted small">
                Every action is recorded with timestamp, IP address, and user information for compliance and troubleshooting.
              </p>
            </div>
            <div class="col-md-4">
              <h6><i class="fas fa-database me-2 text-info"></i>Data Retention</h6>
              <p class="text-muted small">
                Logs are stored indefinitely by default. Consider implementing automatic archival for older logs.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php include __DIR__ . '/../includes/admin_layout_footer.php'; ?>
