<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/permissions.php';

if (!isset($_SESSION['user'])) {
    redirect(base_url('public/login.php'));
}
require_permission('testimonials.view');

$title = 'Manage Testimonials | Admin';
$pageTitle = 'Testimonials Management';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? '');
    
    $action = $_POST['action'] ?? '';
    $testimonialId = (int)($_POST['testimonial_id'] ?? 0);
    
    try {
        if ($action === 'approve' && has_permission('testimonials.approve')) {
            $stmt = $pdo->prepare("UPDATE testimonials SET status = 'approved' WHERE testimonial_id = ?");
            $stmt->execute([$testimonialId]);
            flash('Testimonial approved!', 'success');
            log_admin_action('testimonial_approved', null, $testimonialId);
            
        } elseif ($action === 'reject' && has_permission('testimonials.approve')) {
            $stmt = $pdo->prepare("UPDATE testimonials SET status = 'rejected' WHERE testimonial_id = ?");
            $stmt->execute([$testimonialId]);
            flash('Testimonial rejected.', 'info');
            log_admin_action('testimonial_rejected', null, $testimonialId);
            
        } elseif ($action === 'feature' && has_permission('testimonials.approve')) {
            $featured = (int)($_POST['featured'] ?? 0);
            $stmt = $pdo->prepare("UPDATE testimonials SET featured = ? WHERE testimonial_id = ?");
            $stmt->execute([$featured, $testimonialId]);
            flash('Featured status updated!', 'success');
            
        } elseif ($action === 'delete' && has_permission('testimonials.delete')) {
            $stmt = $pdo->prepare("DELETE FROM testimonials WHERE testimonial_id = ?");
            $stmt->execute([$testimonialId]);
            flash('Testimonial deleted.', 'warning');
            log_admin_action('testimonial_deleted', null, $testimonialId);
        }
        
        redirect(base_url('admin/testimonials.php'));
        
    } catch (Exception $e) {
        flash('Error: ' . $e->getMessage(), 'danger');
    }
}

// Filters
$status = $_GET['status'] ?? '';
$rating = $_GET['rating'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where = [];
$params = [];

if ($status) {
    $where[] = "status = ?";
    $params[] = $status;
}

if ($rating) {
    $where[] = "rating = ?";
    $params[] = (int)$rating;
}

if ($search) {
    $where[] = "(title LIKE ? OR content LIKE ? OR guest_name LIKE ? OR guest_email LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get testimonials
$sql = "SELECT * FROM testimonials $whereClause ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$testimonials = $stmt->fetchAll();

// Get stats
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM testimonials")->fetchColumn(),
    'pending' => $pdo->query("SELECT COUNT(*) FROM testimonials WHERE status = 'pending'")->fetchColumn(),
    'approved' => $pdo->query("SELECT COUNT(*) FROM testimonials WHERE status = 'approved'")->fetchColumn(),
    'featured' => $pdo->query("SELECT COUNT(*) FROM testimonials WHERE featured = TRUE")->fetchColumn(),
];

include __DIR__ . '/../includes/admin_layout_header.php';
?>

  
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1">Manage Testimonials</h1>
      <p class="text-muted mb-0">Review and approve guest reviews</p>
    </div>
  </div>

  <!-- Stats -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card">
        <div class="card-body">
          <h6 class="text-muted">Total Reviews</h6>
          <h3><?= $stats['total'] ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-warning bg-opacity-10">
        <div class="card-body">
          <h6 class="text-muted">Pending</h6>
          <h3 class="text-warning"><?= $stats['pending'] ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-success bg-opacity-10">
        <div class="card-body">
          <h6 class="text-muted">Approved</h6>
          <h3 class="text-success"><?= $stats['approved'] ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-primary bg-opacity-10">
        <div class="card-body">
          <h6 class="text-muted">Featured</h6>
          <h3 class="text-primary"><?= $stats['featured'] ?></h3>
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
          <input type="text" class="form-control" name="search" value="<?= sanitize($search) ?>" 
                 placeholder="Title, content, guest name, or email">
        </div>
        <div class="col-md-2">
          <label class="form-label">Status</label>
          <select class="form-select" name="status">
            <option value="">All Status</option>
            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
            <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Rating</label>
          <select class="form-select" name="rating">
            <option value="">All Ratings</option>
            <option value="5" <?= $rating === '5' ? 'selected' : '' ?>>5 Stars</option>
            <option value="4" <?= $rating === '4' ? 'selected' : '' ?>>4 Stars</option>
            <option value="3" <?= $rating === '3' ? 'selected' : '' ?>>3 Stars</option>
            <option value="2" <?= $rating === '2' ? 'selected' : '' ?>>2 Stars</option>
            <option value="1" <?= $rating === '1' ? 'selected' : '' ?>>1 Star</option>
          </select>
        </div>
        <div class="col-md-4 d-flex align-items-end gap-2">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-filter me-2"></i>Filter
          </button>
          <a href="?" class="btn btn-outline-secondary">Reset</a>
          <div class="ms-auto">
            <small class="text-muted">Showing <?= count($testimonials) ?> testimonials</small>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Testimonials List -->
  <div class="row g-3">
    <?php if (empty($testimonials)): ?>
      <div class="col-12">
        <div class="alert alert-info text-center">
          <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
          No testimonials found
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($testimonials as $testimonial): ?>
        <div class="col-md-6">
          <div class="card h-100">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <h5 class="mb-1"><?= sanitize($testimonial['guest_name']) ?></h5>
                  <small class="text-muted"><?= sanitize($testimonial['guest_country'] ?? 'Unknown') ?></small>
                </div>
                <div class="text-warning">
                  <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                    <i class="fas fa-star"></i>
                  <?php endfor; ?>
                  <?php for ($i = $testimonial['rating']; $i < 5; $i++): ?>
                    <i class="far fa-star"></i>
                  <?php endfor; ?>
                </div>
              </div>
              
              <?php if ($testimonial['review_title']): ?>
                <h6 class="mb-2">"<?= sanitize($testimonial['review_title']) ?>"</h6>
              <?php endif; ?>
              
              <p class="mb-3"><?= sanitize($testimonial['review_text']) ?></p>
              
              <div class="d-flex justify-content-between align-items-center mb-3">
                <small class="text-muted">
                  <?php if ($testimonial['stay_date']): ?>
                    Stayed: <?= date('M Y', strtotime($testimonial['stay_date'])) ?>
                  <?php endif; ?>
                  <br>
                  Submitted: <?= date('M j, Y', strtotime($testimonial['created_at'])) ?>
                </small>
                
                <div>
                  <?php
                  $statusColors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
                  $statusColor = $statusColors[$testimonial['status']] ?? 'secondary';
                  ?>
                  <span class="badge bg-<?= $statusColor ?>">
                    <?= ucfirst($testimonial['status']) ?>
                  </span>
                  
                  <?php if ($testimonial['featured']): ?>
                    <span class="badge bg-primary">Featured</span>
                  <?php endif; ?>
                </div>
              </div>
              
              <!-- Actions -->
              <div class="btn-group w-100" role="group">
                <?php if ($testimonial['status'] === 'pending' && has_permission('testimonials.approve')): ?>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="testimonial_id" value="<?= $testimonial['testimonial_id'] ?>">
                    <button type="submit" class="btn btn-success btn-sm">
                      <i class="fas fa-check me-1"></i>Approve
                    </button>
                  </form>
                  
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="testimonial_id" value="<?= $testimonial['testimonial_id'] ?>">
                    <button type="submit" class="btn btn-warning btn-sm">
                      <i class="fas fa-times me-1"></i>Reject
                    </button>
                  </form>
                <?php endif; ?>
                
                <?php if ($testimonial['status'] === 'approved' && has_permission('testimonials.approve')): ?>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="feature">
                    <input type="hidden" name="testimonial_id" value="<?= $testimonial['testimonial_id'] ?>">
                    <input type="hidden" name="featured" value="<?= $testimonial['featured'] ? 0 : 1 ?>">
                    <button type="submit" class="btn btn-<?= $testimonial['featured'] ? 'outline-primary' : 'primary' ?> btn-sm">
                      <i class="fas fa-star me-1"></i><?= $testimonial['featured'] ? 'Unfeature' : 'Feature' ?>
                    </button>
                  </form>
                <?php endif; ?>
                
                <?php if (has_permission('testimonials.delete')): ?>
                  <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this testimonial?');">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="testimonial_id" value="<?= $testimonial['testimonial_id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">
                      <i class="fas fa-trash me-1"></i>Delete
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

<?php include __DIR__ . '/../includes/admin_layout_footer.php'; ?>
