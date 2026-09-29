<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$title = 'News & Blog | Kalitusi Nature Resort';

// Get category filter
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$q = trim($_GET['q'] ?? '');
list($page, $per, $offset) = paginate_params(6);

// Build WHERE clause
$where = "WHERE post_type = 'news' AND status = 'published'";
$params = [];

if ($categoryId > 0) {
  $where .= " AND category_id = ?";
  $params[] = $categoryId;
}

if ($q !== '') {
  $where .= " AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)";
  $like = "%$q%";
  $params[] = $like;
  $params[] = $like;
  $params[] = $like;
}

// Count total
$countSql = "SELECT COUNT(*) FROM posts $where";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();

// Fetch posts with images
$sql = "SELECT post_id, title, slug, excerpt, content, featured_image, created_at, category_id 
        FROM posts $where 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($sql);
$allParams = array_merge($params, [$per, $offset]);
$stmt->execute($allParams);
$posts = $stmt->fetchAll();
$pages = max(1, (int)ceil($total / $per));

// Get categories for filter
$categories = $pdo->query(
  "SELECT category_id, name FROM categories 
   WHERE category_type = 'post' AND status = 'active' 
   ORDER BY name"
)->fetchAll();

// Get recent posts for sidebar
$recentPosts = $pdo->query(
  "SELECT post_id, title, created_at FROM posts 
   WHERE post_type = 'news' AND status = 'published' 
   ORDER BY created_at DESC 
   LIMIT 5"
)->fetchAll();

// Get featured post
$featuredPost = $pdo->query(
  "SELECT post_id, title, excerpt, featured_image, created_at 
   FROM posts 
   WHERE post_type = 'news' AND status = 'published' 
   ORDER BY created_at DESC 
   LIMIT 1"
)->fetch();

include __DIR__ . '/../includes/layout_header.php';
?>

<!-- Page Header -->
<div class="bg-primary text-white py-5 mb-4">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1 class="display-5 fw-bold mb-2">News & Blog</h1>
        <p class="lead mb-0">Stay updated with the latest news, events, and stories from Kalitusi Nature Resort</p>
      </div>
      <div class="col-lg-4 text-lg-end">
        <i class="fas fa-newspaper fa-5x opacity-25"></i>
      </div>
    </div>
  </div>
</div>

<!-- Featured Post -->
<?php if ($featuredPost && $page === 1 && !$q && !$categoryId): ?>
<div class="container mb-5">
  <div class="card border-0 shadow-lg overflow-hidden">
    <div class="row g-0">
      <div class="col-md-6">
        <img src="<?= $featuredPost['featured_image'] ?: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=500&fit=crop' ?>" 
             class="img-fluid h-100 object-fit-cover" 
             alt="<?= sanitize($featuredPost['title']) ?>">
      </div>
      <div class="col-md-6">
        <div class="card-body p-4 p-lg-5">
          <div class="badge bg-success mb-3">Featured Post</div>
          <h2 class="h3 mb-3"><?= sanitize($featuredPost['title']) ?></h2>
          <p class="text-muted mb-4">
            <i class="fas fa-calendar me-2"></i><?= date('F j, Y', strtotime($featuredPost['created_at'])) ?>
          </p>
          <p class="card-text"><?= sanitize($featuredPost['excerpt']) ?></p>
          <a href="<?= base_url('public/news-view.php?id=' . $featuredPost['post_id']) ?>" 
             class="btn btn-primary">
            Read Full Article <i class="fas fa-arrow-right ms-2"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="container">
<!-- Search and Filter Bar -->
<div class="row g-3 mb-4">
  <div class="col-lg-8">
    <form class="row g-2" method="get">
      <div class="col-md-7">
        <input type="text" class="form-control" name="q" 
               placeholder="Search articles..." value="<?= sanitize($q) ?>">
      </div>
      <div class="col-md-3">
        <select class="form-select" name="category">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['category_id'] ?>" <?= $categoryId == $cat['category_id'] ? 'selected' : '' ?>>
              <?= sanitize($cat['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 d-grid">
        <button class="btn btn-primary"><i class="fas fa-search"></i></button>
      </div>
    </form>
  </div>
  <div class="col-lg-4 text-lg-end">
    <p class="text-muted mb-0">
      <strong><?= $total ?></strong> article<?= $total != 1 ? 's' : '' ?> found
    </p>
  </div>
</div>

<!-- Main Content -->
<div class="row g-4">
  <div class="col-lg-8">
    <?php if (!$posts): ?>
      <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        No articles found. Try adjusting your search or filters.
      </div>
    <?php else: ?>
      <!-- Blog Posts Grid -->
      <div class="row g-4 mb-4">
        <?php foreach ($posts as $post): ?>
          <div class="col-md-6">
            <article class="card h-100 border-0 shadow-sm hover-lift">
              <img src="<?= $post['featured_image'] ?: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=600&h=400&fit=crop' ?>" 
                   class="card-img-top" 
                   style="height: 200px; object-fit: cover;"
                   alt="<?= sanitize($post['title']) ?>">
              <div class="card-body d-flex flex-column">
                <div class="mb-2">
                  <small class="text-muted">
                    <i class="fas fa-calendar me-1"></i>
                    <?= date('M j, Y', strtotime($post['created_at'])) ?>
                  </small>
                </div>
                <h3 class="h5 card-title mb-2">
                  <a href="<?= base_url('public/news-view.php?id=' . $post['post_id']) ?>" 
                     class="text-decoration-none text-dark">
                    <?= sanitize($post['title']) ?>
                  </a>
                </h3>
                <p class="card-text text-muted small flex-grow-1">
                  <?= sanitize(substr($post['excerpt'] ?: strip_tags($post['content']), 0, 120)) ?>...
                </p>
                <div class="mt-auto">
                  <a href="<?= base_url('public/news-view.php?id=' . $post['post_id']) ?>" 
                     class="btn btn-sm btn-outline-primary">
                    Read More <i class="fas fa-arrow-right ms-1"></i>
                  </a>
                </div>
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($pages > 1): ?>
      <nav aria-label="Blog pagination">
        <ul class="pagination justify-content-center">
          <?php if ($page > 1): ?>
            <li class="page-item">
              <a class="page-link" href="?page=<?= $page-1 ?><?= $categoryId ? '&category='.$categoryId : '' ?><?= $q ? '&q='.urlencode($q) : '' ?>">
                <i class="fas fa-chevron-left"></i> Previous
              </a>
            </li>
          <?php endif; ?>
          
          <?php for ($p=1; $p <= $pages; $p++): ?>
            <?php if ($p == 1 || $p == $pages || abs($p - $page) <= 2): ?>
              <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $p ?><?= $categoryId ? '&category='.$categoryId : '' ?><?= $q ? '&q='.urlencode($q) : '' ?>">
                  <?= $p ?>
                </a>
              </li>
            <?php elseif (abs($p - $page) == 3): ?>
              <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php endif; ?>
          <?php endfor; ?>
          
          <?php if ($page < $pages): ?>
            <li class="page-item">
              <a class="page-link" href="?page=<?= $page+1 ?><?= $categoryId ? '&category='.$categoryId : '' ?><?= $q ? '&q='.urlencode($q) : '' ?>">
                Next <i class="fas fa-chevron-right"></i>
              </a>
            </li>
          <?php endif; ?>
        </ul>
      </nav>
      <?php endif; ?>
    <?php endif; ?>
  </div>
  
  <!-- Sidebar -->
  <div class="col-lg-4">
    <!-- Recent Posts -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-primary text-white">
        <h3 class="h6 mb-0"><i class="fas fa-clock me-2"></i>Recent Articles</h3>
      </div>
      <div class="card-body p-0">
        <div class="list-group list-group-flush">
          <?php foreach ($recentPosts as $recent): ?>
            <a href="<?= base_url('public/news-view.php?id=' . $recent['post_id']) ?>" 
               class="list-group-item list-group-item-action">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <h4 class="h6 mb-1"><?= sanitize($recent['title']) ?></h4>
                  <small class="text-muted">
                    <i class="fas fa-calendar me-1"></i>
                    <?= date('M j, Y', strtotime($recent['created_at'])) ?>
                  </small>
                </div>
                <i class="fas fa-chevron-right text-muted"></i>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    
    <!-- Categories -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-success text-white">
        <h3 class="h6 mb-0"><i class="fas fa-folder me-2"></i>Categories</h3>
      </div>
      <div class="card-body p-0">
        <div class="list-group list-group-flush">
          <a href="?" class="list-group-item list-group-item-action <?= !$categoryId ? 'active' : '' ?>">
            All Articles
          </a>
          <?php foreach ($categories as $cat): ?>
            <a href="?category=<?= $cat['category_id'] ?>" 
               class="list-group-item list-group-item-action <?= $categoryId == $cat['category_id'] ? 'active' : '' ?>">
              <?= sanitize($cat['name']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    
    <!-- Subscribe Box -->
    <div class="card border-0 shadow-sm bg-light">
      <div class="card-body text-center">
        <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
        <h3 class="h5 mb-2">Stay Updated</h3>
        <p class="small text-muted mb-3">Get the latest news and updates from our resort</p>
        <a href="<?= base_url('public/contact.php') ?>" class="btn btn-primary btn-sm">
          Contact Us
        </a>
      </div>
    </div>
  </div>
</div>

</div>
<?php include __DIR__ . '/../includes/layout_footer.php';
