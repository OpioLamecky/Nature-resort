<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/permissions.php';

// Check if user is logged in and has permission
if (!isset($_SESSION['user'])) {
    redirect(base_url('public/login.php'));
}
require_permission('content.view');

$pageTitle = 'Newsletter Subscriptions';
$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verify_csrf($_POST['csrf_token'] ?? '');
    
    try {
        if ($_POST['action'] === 'export_csv') {
            // Export subscribers to CSV
            $stmt = $pdo->prepare("SELECT email, name, status, subscription_date FROM newsletter_subscriptions ORDER BY subscription_date DESC");
            $stmt->execute();
            $subscribers = $stmt->fetchAll();
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="newsletter_subscribers_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Email', 'Name', 'Status', 'Subscription Date']);
            
            foreach ($subscribers as $subscriber) {
                fputcsv($output, [
                    $subscriber['email'],
                    $subscriber['name'] ?: 'N/A',
                    $subscriber['status'],
                    $subscriber['subscription_date']
                ]);
            }
            
            fclose($output);
            exit;
        }
        
        if ($_POST['action'] === 'delete_subscriber' && isset($_POST['subscription_id'])) {
            $stmt = $pdo->prepare("DELETE FROM newsletter_subscriptions WHERE subscription_id = ?");
            $stmt->execute([$_POST['subscription_id']]);
            
            $message = 'Subscriber deleted successfully.';
            $messageType = 'success';
        }
        
        if ($_POST['action'] === 'toggle_status' && isset($_POST['subscription_id'])) {
            $stmt = $pdo->prepare("UPDATE newsletter_subscriptions SET status = CASE WHEN status = 'active' THEN 'unsubscribed' ELSE 'active' END WHERE subscription_id = ?");
            $stmt->execute([$_POST['subscription_id']]);
            
            $message = 'Subscriber status updated successfully.';
            $messageType = 'success';
        }
        
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Get filter parameters
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where = [];
$params = [];

if ($status) {
    $where[] = "status = ?";
    $params[] = $status;
}

if ($search) {
    $where[] = "(email LIKE ? OR name LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get subscribers
try {
    $stmt = $pdo->prepare("SELECT * FROM newsletter_subscriptions $whereClause ORDER BY subscription_date DESC");
    $stmt->execute($params);
    $subscribers = $stmt->fetchAll();
    
    // Get statistics
    $stats = [
        'total' => $pdo->query("SELECT COUNT(*) FROM newsletter_subscriptions")->fetchColumn(),
        'active' => $pdo->query("SELECT COUNT(*) FROM newsletter_subscriptions WHERE status = 'active'")->fetchColumn(),
        'unsubscribed' => $pdo->query("SELECT COUNT(*) FROM newsletter_subscriptions WHERE status = 'unsubscribed'")->fetchColumn(),
        'today' => $pdo->query("SELECT COUNT(*) FROM newsletter_subscriptions WHERE DATE(subscription_date) = CURDATE()")->fetchColumn()
    ];
} catch (Exception $e) {
    $subscribers = [];
    $stats = ['total' => 0, 'active' => 0, 'unsubscribed' => 0, 'today' => 0];
}

include __DIR__ . '/../includes/admin_layout_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Newsletter Subscriptions</h1>
        <p class="text-muted mb-0">Manage newsletter subscribers and export data</p>
    </div>
    <div>
        <form method="POST" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="export_csv">
            <button type="submit" class="btn btn-outline-success">
                <i class="fas fa-download me-2"></i>Export CSV
            </button>
        </form>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
        <?= sanitize($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="ms-3">
                        <div class="stat-number"><?= number_format($stats['total']) ?></div>
                        <div class="stat-label">Total Subscribers</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="ms-3">
                        <div class="stat-number"><?= number_format($stats['active']) ?></div>
                        <div class="stat-label">Active Subscribers</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="fas fa-user-times"></i>
                    </div>
                    <div class="ms-3">
                        <div class="stat-number"><?= number_format($stats['unsubscribed']) ?></div>
                        <div class="stat-label">Unsubscribed</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="ms-3">
                        <div class="stat-number"><?= number_format($stats['today']) ?></div>
                        <div class="stat-label">New Today</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Email or name..." value="<?= sanitize($search) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="unsubscribed" <?= $status === 'unsubscribed' ? 'selected' : '' ?>>Unsubscribed</option>
                </select>
            </div>
            <div class="col-md-5 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>Filter
                </button>
                <a href="<?= base_url('admin/newsletter.php') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Subscribers Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Newsletter Subscribers (<?= count($subscribers) ?> results)</h5>
    </div>
    <div class="card-body p-0">
        <?php if ($subscribers): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Subscription Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscribers as $subscriber): ?>
                            <tr>
                                <td>
                                    <strong><?= sanitize($subscriber['email']) ?></strong>
                                </td>
                                <td><?= sanitize($subscriber['name'] ?: 'N/A') ?></td>
                                <td>
                                    <span class="badge bg-<?= $subscriber['status'] === 'active' ? 'success' : 'secondary' ?>">
                                        <?= ucfirst($subscriber['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('M j, Y g:i A', strtotime($subscriber['subscription_date'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="subscription_id" value="<?= $subscriber['subscription_id'] ?>">
                                            <button type="submit" class="btn btn-outline-<?= $subscriber['status'] === 'active' ? 'warning' : 'success' ?>" 
                                                    title="<?= $subscriber['status'] === 'active' ? 'Unsubscribe' : 'Reactivate' ?>">
                                                <i class="fas fa-<?= $subscriber['status'] === 'active' ? 'user-times' : 'user-check' ?>"></i>
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this subscriber?')">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="action" value="delete_subscriber">
                                            <input type="hidden" name="subscription_id" value="<?= $subscriber['subscription_id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No subscribers found</h5>
                <p class="text-muted">Newsletter subscriptions will appear here once visitors start subscribing.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_layout_footer.php'; ?>
