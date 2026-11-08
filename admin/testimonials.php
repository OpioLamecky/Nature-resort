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
    $where[] = "(review_title LIKE ? OR review_text LIKE ? OR guest_name LIKE ? OR guest_email LIKE ?)";
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

  <style>
    .review-bento-card {
      background: #ffffff;
      border-radius: 18px;
      border: 1px solid rgba(15, 23, 42, 0.07);
      box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
      padding: 1.4rem 1.5rem;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .review-bento-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
    }
    .review-avatar {
      width: 44px;
      height: 44px;
      border-radius: 14px;
      background: linear-gradient(135deg, rgba(27, 138, 90, 0.16), rgba(27, 138, 90, 0.06));
      color: #156d46;
      font-weight: 700;
      font-size: 0.95rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      border: 1px solid rgba(27, 138, 90, 0.15);
    }
    .review-rating-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.3rem;
      padding: 0.3rem 0.65rem;
      border-radius: 999px;
      background: #fffbeb;
      border: 1px solid rgba(245, 158, 11, 0.25);
      font-size: 0.78rem;
      font-weight: 700;
      color: #b45309;
    }
    .review-quote-box {
      background: #f8faf9;
      border-radius: 14px;
      padding: 1rem 1.1rem;
      border: 1px solid rgba(15, 23, 42, 0.05);
      margin: 0.85rem 0 1rem;
      flex-grow: 1;
    }
    .review-quote-title {
      font-weight: 700;
      color: #0f172a;
      font-size: 0.96rem;
      margin-bottom: 0.35rem;
    }
    .review-quote-text {
      color: #475569;
      font-size: 0.9rem;
      line-height: 1.58;
      margin-bottom: 0;
    }
    .review-status-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.3rem;
      padding: 0.28rem 0.7rem;
      border-radius: 999px;
      font-size: 0.74rem;
      font-weight: 600;
    }
    .review-status-pill.is-approved { background: rgba(22, 163, 74, 0.12); color: #15803d; }
    .review-status-pill.is-pending { background: rgba(217, 119, 6, 0.14); color: #b45309; }
    .review-status-pill.is-rejected { background: rgba(220, 38, 38, 0.12); color: #b91c1c; }
    .review-status-pill.is-featured { background: rgba(37, 99, 235, 0.12); color: #1d4ed8; }
    .review-action-btn {
      border-radius: 999px;
      padding: 0.38rem 0.9rem;
      font-size: 0.8rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
    }
  </style>

  <!-- Stats (Pinterest Bento KPI Strip) -->
  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="bento-kpi-card">
        <div class="bento-kpi-top">
          <span class="bento-kpi-label">Total Reviews</span>
          <span class="bento-kpi-icon is-blue"><i class="fas fa-comments"></i></span>
        </div>
        <div class="bento-kpi-bottom">
          <span class="bento-kpi-value"><?= number_format((int)$stats['total']) ?></span>
          <span class="bento-kpi-pill is-up"><i class="fas fa-layer-group"></i> All Time</span>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="bento-kpi-card">
        <div class="bento-kpi-top">
          <span class="bento-kpi-label">Pending Moderation</span>
          <span class="bento-kpi-icon is-amber"><i class="fas fa-hourglass-half"></i></span>
        </div>
        <div class="bento-kpi-bottom">
          <span class="bento-kpi-value"><?= number_format((int)$stats['pending']) ?></span>
          <span class="bento-kpi-pill is-warn"><i class="fas fa-clock"></i> Awaiting</span>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="bento-kpi-card">
        <div class="bento-kpi-top">
          <span class="bento-kpi-label">Approved Reviews</span>
          <span class="bento-kpi-icon is-green"><i class="fas fa-check-circle"></i></span>
        </div>
        <div class="bento-kpi-bottom">
          <span class="bento-kpi-value"><?= number_format((int)$stats['approved']) ?></span>
          <span class="bento-kpi-pill is-up"><i class="fas fa-check"></i> Published</span>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="bento-kpi-card">
        <div class="bento-kpi-top">
          <span class="bento-kpi-label">Featured Highlights</span>
          <span class="bento-kpi-icon is-rose"><i class="fas fa-star"></i></span>
        </div>
        <div class="bento-kpi-bottom">
          <span class="bento-kpi-value"><?= number_format((int)$stats['featured']) ?></span>
          <span class="bento-kpi-pill is-up"><i class="fas fa-award"></i> Spotlight</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label small fw-semibold text-secondary">Search Reviews</label>
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
            <input type="text" class="form-control border-start-0" name="search" value="<?= sanitize($search) ?>" 
                   placeholder="Guest name, country, title, or review text...">
          </div>
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold text-secondary">Status</label>
          <select class="form-select" name="status">
            <option value="">All Status</option>
            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
            <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold text-secondary">Star Rating</label>
          <select class="form-select" name="rating">
            <option value="">All Ratings</option>
            <option value="5" <?= $rating === '5' ? 'selected' : '' ?>>5 Stars</option>
            <option value="4" <?= $rating === '4' ? 'selected' : '' ?>>4 Stars</option>
            <option value="3" <?= $rating === '3' ? 'selected' : '' ?>>3 Stars</option>
            <option value="2" <?= $rating === '2' ? 'selected' : '' ?>>2 Stars</option>
            <option value="1" <?= $rating === '1' ? 'selected' : '' ?>>1 Star</option>
          </select>
        </div>
        <div class="col-md-4 d-flex align-items-center gap-2">
          <button type="submit" class="btn btn-primary rounded-pill px-4">
            <i class="fas fa-filter me-1"></i>Filter
          </button>
          <a href="?" class="btn btn-outline-secondary rounded-pill px-3">Reset</a>
          <div class="ms-auto">
            <span class="badge rounded-pill bg-light text-secondary border px-3 py-2"><?= count($testimonials) ?> reviews</span>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Testimonials Grid -->
  <div class="row g-4">
    <?php if (empty($testimonials)): ?>
      <div class="col-12">
        <div class="review-bento-card text-center py-5">
          <div class="mb-3">
            <span class="bento-kpi-icon is-blue mx-auto" style="width: 56px; height: 56px; font-size: 1.5rem;"><i class="fas fa-comment-slash"></i></span>
          </div>
          <h5 class="fw-bold mb-1">No Guest Reviews Found</h5>
          <p class="text-muted small mb-0">Try adjusting your search filters to see more results.</p>
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($testimonials as $testimonial):
        $guestName = trim((string)($testimonial['guest_name'] ?? 'Guest'));
        $nameParts = preg_split('/\s+/', $guestName);
        $initials  = strtoupper(substr($nameParts[0] ?? 'G', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
        $starCount = max(1, min(5, (int)($testimonial['rating'] ?? 5)));
        $statusVal = strtolower((string)($testimonial['status'] ?? 'pending'));
      ?>
        <div class="col-md-6">
          <div class="review-bento-card">
            <div>
              <!-- Card Top: Guest Avatar, Name, Country & Star Rating Pill -->
              <div class="d-flex justify-content-between align-items-start gap-2">
                <div class="d-flex align-items-center gap-3">
                  <div class="review-avatar"><?= sanitize($initials) ?></div>
                  <div>
                    <h5 class="fw-bold mb-0" style="font-size: 1.02rem; color: #0f172a;"><?= sanitize($guestName) ?></h5>
                    <span class="text-muted small"><i class="fas fa-map-marker-alt me-1 text-success opacity-75"></i><?= sanitize($testimonial['guest_country'] ?? 'International Guest') ?></span>
                  </div>
                </div>
                <div class="review-rating-pill" title="<?= $starCount ?> out of 5 stars">
                  <span><?= number_format($starCount, 1) ?></span>
                  <span>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                      <i class="fas fa-star" style="color: <?= $i <= $starCount ? '#f59e0b' : '#cbd5e1' ?>;"></i>
                    <?php endfor; ?>
                  </span>
                </div>
              </div>

              <!-- Quote Box -->
              <div class="review-quote-box">
                <?php if (!empty($testimonial['review_title'])): ?>
                  <div class="review-quote-title">&ldquo;<?= sanitize($testimonial['review_title']) ?>&rdquo;</div>
                <?php endif; ?>
                <p class="review-quote-text"><?= sanitize($testimonial['review_text']) ?></p>
              </div>
            </div>

            <div>
              <!-- Meta & Status Row -->
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pb-3 mb-3 border-bottom">
                <div class="d-flex flex-wrap gap-3 text-muted" style="font-size: 0.78rem;">
                  <?php if (!empty($testimonial['stay_date'])): ?>
                    <span><i class="fas fa-bed me-1"></i>Stayed: <strong><?= date('M Y', strtotime($testimonial['stay_date'])) ?></strong></span>
                  <?php endif; ?>
                  <span><i class="fas fa-calendar-alt me-1"></i>Submitted: <?= date('M j, Y', strtotime($testimonial['created_at'])) ?></span>
                </div>

                <div class="d-flex align-items-center gap-1">
                  <span class="review-status-pill is-<?= sanitize($statusVal) ?>">
                    <?php if ($statusVal === 'approved'): ?>
                      <i class="fas fa-check-circle"></i>
                    <?php elseif ($statusVal === 'pending'): ?>
                      <i class="fas fa-clock"></i>
                    <?php else: ?>
                      <i class="fas fa-times-circle"></i>
                    <?php endif; ?>
                    <?= ucfirst(sanitize($statusVal)) ?>
                  </span>

                  <?php if (!empty($testimonial['featured'])): ?>
                    <span class="review-status-pill is-featured">
                      <i class="fas fa-star"></i> Featured
                    </span>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Actions Bar -->
              <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex flex-wrap gap-2">
                  <?php if ($testimonial['status'] === 'pending' && has_permission('testimonials.approve')): ?>
                    <form method="POST" class="d-inline">
                      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                      <input type="hidden" name="action" value="approve">
                      <input type="hidden" name="testimonial_id" value="<?= (int)$testimonial['testimonial_id'] ?>">
                      <button type="submit" class="btn btn-success review-action-btn">
                        <i class="fas fa-check"></i> Approve
                      </button>
                    </form>

                    <form method="POST" class="d-inline">
                      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                      <input type="hidden" name="action" value="reject">
                      <input type="hidden" name="testimonial_id" value="<?= (int)$testimonial['testimonial_id'] ?>">
                      <button type="submit" class="btn btn-outline-warning review-action-btn">
                        <i class="fas fa-times"></i> Reject
                      </button>
                    </form>
                  <?php endif; ?>

                  <?php if ($testimonial['status'] === 'approved' && has_permission('testimonials.approve')): ?>
                    <form method="POST" class="d-inline">
                      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                      <input type="hidden" name="action" value="feature">
                      <input type="hidden" name="testimonial_id" value="<?= (int)$testimonial['testimonial_id'] ?>">
                      <input type="hidden" name="featured" value="<?= $testimonial['featured'] ? 0 : 1 ?>">
                      <button type="submit" class="btn <?= $testimonial['featured'] ? 'btn-outline-secondary' : 'btn-primary' ?> review-action-btn">
                        <i class="fas fa-star <?= $testimonial['featured'] ? 'text-warning' : '' ?>"></i>
                        <?= $testimonial['featured'] ? 'Remove Featured' : 'Feature on Site' ?>
                      </button>
                    </form>
                  <?php endif; ?>
                </div>

                <?php if (has_permission('testimonials.delete')): ?>
                  <form method="POST" class="d-inline ms-auto" onsubmit="return confirm('Delete this testimonial?');">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="testimonial_id" value="<?= (int)$testimonial['testimonial_id'] ?>">
                    <button type="submit" class="btn btn-outline-danger review-action-btn">
                      <i class="fas fa-trash-alt"></i> Delete
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
