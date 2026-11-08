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

<div class="container mt-3">
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= base_url('public/') ?>">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">News &amp; Blog</li>
    </ol>
  </nav>
</div>

<!-- Page Header -->
<section class="page-intro-hero page-news-hero bg-soft-mint">
  <div class="container">
    <div class="row g-4 g-lg-5 align-items-center">
      <div class="col-md-7 text-center text-md-start">
        <span class="badge rounded-pill px-3 py-2 mb-2" style="background: rgba(27, 138, 90, 0.12); color: #1b8a5a; font-weight: 600;">
          <i class="fas fa-newspaper me-1"></i> Resort Journal
        </span>
        <h1 class="display-5 fw-bold mb-3"><?= sanitize(site_content('news', 'title', 'News & Blog')) ?></h1>
        <p class="lead mb-0 mx-auto mx-md-0"><?= sanitize(site_content('news', 'intro', 'Stay updated with the latest news, events, and stories from Kalitusi Nature Resort')) ?></p>
      </div>
      <div class="col-md-5">
        <img src="<?= asset_url('images/cabin.webp') ?>" alt="Kalitusi Nature Resort cottages">
      </div>
    </div>
  </div>
</section>

<!-- Featured Post -->
<?php if ($featuredPost && $page === 1 && !$q && !$categoryId): ?>
<div class="container mb-4">
  <article class="scrim-card" style="min-height: 280px;">
    <img class="scrim-card-bg" 
         src="<?= $featuredPost['featured_image'] ?: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=500&fit=crop' ?>" 
         alt="<?= sanitize($featuredPost['title']) ?>">
    <div class="scrim-badge-top is-gold">
      <span class="badge-dot"></span>
      Featured Story &bull; <?= date('M j, Y', strtotime($featuredPost['created_at'])) ?>
    </div>
    <div class="scrim-card-content" style="max-width: 720px;">
      <div class="scrim-card-header">
        <h2 class="scrim-card-title" style="font-size: 1.45rem;"><?= sanitize($featuredPost['title']) ?></h2>
      </div>
      <p class="scrim-card-subtitle mb-3"><?= sanitize($featuredPost['excerpt']) ?></p>
      <div class="scrim-card-action-row" style="justify-content: flex-start;">
        <a href="<?= base_url('public/news-view.php?id=' . $featuredPost['post_id']) ?>" class="btn-scrim-cta">
          Read Full Article &rarr;
        </a>
      </div>
    </div>
  </article>
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
            <article class="scrim-card scrim-card-compact">
              <img class="scrim-card-bg" 
                   src="<?= $post['featured_image'] ?: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=600&h=400&fit=crop' ?>" 
                   loading="lazy"
                   alt="<?= sanitize($post['title']) ?>">
              <div class="scrim-badge-top">
                <span class="badge-dot"></span>
                <?= date('M j, Y', strtotime($post['created_at'])) ?>
              </div>
              <div class="scrim-card-content">
                <div class="scrim-card-header">
                  <h3 class="scrim-card-title" style="font-size: 1.1rem;">
                    <a href="<?= base_url('public/news-view.php?id=' . $post['post_id']) ?>" 
                       style="color: #fff; text-decoration: none;">
                      <?= sanitize($post['title']) ?>
                    </a>
                  </h3>
                </div>
                <p class="scrim-card-subtitle" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                  <?= sanitize(substr($post['excerpt'] ?: strip_tags($post['content']), 0, 120)) ?>...
                </p>
                <div class="scrim-card-action-row">
                  <div class="scrim-price-pill" style="font-size: 0.78rem; padding: 0.35rem 0.75rem;">
                    <i class="fas fa-newspaper me-1"></i> Article
                  </div>
                  <a href="<?= base_url('public/news-view.php?id=' . $post['post_id']) ?>" 
                     class="btn-scrim-cta" style="font-size: 0.78rem; padding: 0.4rem 1rem;">
                    Read More &rarr;
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
