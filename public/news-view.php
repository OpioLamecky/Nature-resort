<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { 
    header('Location: ' . base_url('public/news.php'));
    exit;
}

// Fetch the post with all details
$stmt = $pdo->prepare("
    SELECT p.post_id, p.title, p.slug, p.content, p.excerpt, 
           p.featured_image, p.created_at, p.category_id,
           c.name as category_name
    FROM posts p
    LEFT JOIN categories c ON p.category_id = c.category_id
    WHERE p.post_id = ? AND p.status = 'published' 
    LIMIT 1
");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) { 
    header('Location: ' . base_url('public/news.php'));
    exit;
}

// Get related posts (same category, different post)
$relatedPosts = [];
if ($post['category_id']) {
    $stmt = $pdo->prepare("
        SELECT post_id, title, excerpt, featured_image, created_at
        FROM posts
        WHERE category_id = ? AND post_id != ? AND status = 'published'
        ORDER BY created_at DESC
        LIMIT 3
    ");
    $stmt->execute([$post['category_id'], $id]);
    $relatedPosts = $stmt->fetchAll();
}

// Get recent posts for sidebar
$recentPosts = $pdo->query("
    SELECT post_id, title, created_at
    FROM posts
    WHERE status = 'published' AND post_id != $id
    ORDER BY created_at DESC
    LIMIT 5
")->fetchAll();

$title = $post['title'] . ' | Blog | Kalitusi Nature Resort';
include __DIR__ . '/../includes/layout_header.php';
?>

<!-- Article Header with Featured Image -->
<div class="article-header mb-4" style="position: relative; height: 400px; overflow: hidden;">
  <?php if ($post['featured_image']): ?>
    <?php $heroImg = cms_image_url($post['featured_image']) ?: asset_url('images/blog/post-hero-default.jpg'); ?>
    <img src="<?= sanitize($heroImg) ?>" 
         alt="<?= sanitize($post['title']) ?>"
         style="width: 100%; height: 100%; object-fit: cover; filter: brightness(0.7);">
  <?php else: ?>
    <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #1a4d2e 0%, #2d5f4d 100%);"></div>
  <?php endif; ?>
  
  <!-- Overlay Content -->
  <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 40px; background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);">
    <div class="container">
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="background: transparent; padding: 0; margin: 0;">
          <li class="breadcrumb-item"><a href="<?= base_url('public/') ?>" class="text-white">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('public/news.php') ?>" class="text-white">Blog</a></li>
          <li class="breadcrumb-item active text-white" aria-current="page"><?= sanitize(substr($post['title'], 0, 50)) ?>...</li>
        </ol>
      </nav>
      
      <!-- Category Badge -->
      <?php if ($post['category_name']): ?>
        <span class="badge bg-success mb-2"><?= sanitize($post['category_name']) ?></span>
      <?php endif; ?>
      
      <!-- Title -->
      <h1 class="display-5 fw-bold text-white mb-3"><?= sanitize($post['title']) ?></h1>
      
      <!-- Meta Info -->
      <div class="text-white">
        <i class="fas fa-calendar me-2"></i>
        <time datetime="<?= $post['created_at'] ?>">
          <?= date('F j, Y', strtotime($post['created_at'])) ?>
        </time>
        <span class="mx-2">•</span>
        <i class="fas fa-clock me-2"></i>
        <span><?= ceil(str_word_count(strip_tags($post['content'])) / 200) ?> min read</span>
      </div>
    </div>
  </div>
</div>

<div class="container mb-5">
  <div class="row g-4">
    <!-- Main Content -->
    <div class="col-lg-8">
      <article class="blog-article">
        
        <!-- Excerpt -->
        <?php if ($post['excerpt']): ?>
          <div class="lead text-muted mb-4 pb-4 border-bottom">
            <?= sanitize($post['excerpt']) ?>
          </div>
        <?php endif; ?>
        
        <!-- Article Content -->
        <div class="article-content">
          <?php
          // WARNING: Unsanitized HTML output - Only trusted Super Admins should publish content
          echo $post['content'];
          ?>
        </div>
        
        <!-- Article Footer -->
        <div class="article-footer mt-5 pt-4 border-top">
          <div class="row align-items-center">
            <div class="col-md-6 mb-3 mb-md-0">
              <a href="<?= base_url('public/news.php') ?>" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to Blog
              </a>
            </div>
            <div class="col-md-6 text-md-end">
              <div class="d-inline-flex gap-2">
                <span class="text-muted me-2">Share:</span>
                <a href="https://facebook.com/sharer/sharer.php?u=<?= urlencode($_SERVER['REQUEST_URI']) ?>" 
                   target="_blank" class="btn btn-sm btn-outline-primary" title="Share on Facebook">
                  <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://twitter.com/intent/tweet?url=<?= urlencode($_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($post['title']) ?>" 
                   target="_blank" class="btn btn-sm btn-outline-info" title="Share on Twitter">
                  <i class="fab fa-twitter"></i>
                </a>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode($_SERVER['REQUEST_URI']) ?>&title=<?= urlencode($post['title']) ?>" 
                   target="_blank" class="btn btn-sm btn-outline-primary" title="Share on LinkedIn">
                  <i class="fab fa-linkedin-in"></i>
                </a>
                <a href="mailto:?subject=<?= urlencode($post['title']) ?>&body=<?= urlencode($_SERVER['REQUEST_URI']) ?>" 
                   class="btn btn-sm btn-outline-secondary" title="Share via Email">
                  <i class="fas fa-envelope"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
        
      </article>
      
      <!-- Related Posts -->
      <?php if (!empty($relatedPosts)): ?>
        <section class="related-posts mt-5 pt-5 border-top">
          <h2 class="h3 mb-4">Related Articles</h2>
          <div class="row g-4">
            <?php foreach ($relatedPosts as $related): ?>
              <div class="col-md-4">
                <article class="card h-100 border-0 shadow-sm hover-lift">
                  <?php $relImg = $related['featured_image'] ? cms_image_url($related['featured_image']) : asset_url('images/blog/post-related-default.jpg'); ?>
                  <img src="<?= sanitize($relImg) ?>" 
                       class="card-img-top" 
                       style="height: 150px; object-fit: cover;"
                       alt="<?= sanitize($related['title']) ?>">
                  <div class="card-body">
                    <small class="text-muted">
                      <i class="fas fa-calendar me-1"></i>
                      <?= date('M j, Y', strtotime($related['created_at'])) ?>
                    </small>
                    <h3 class="h6 mt-2 mb-2">
                      <a href="<?= base_url('public/news-view.php?id=' . $related['post_id']) ?>" 
                         class="text-decoration-none text-dark">
                        <?= sanitize($related['title']) ?>
                      </a>
                    </h3>
                    <p class="card-text small text-muted">
                      <?= sanitize(substr($related['excerpt'], 0, 80)) ?>...
                    </p>
                  </div>
                </article>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>
      
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
      
      <!-- Recent Posts Widget -->
      <div class="card border-0 shadow-sm mb-4 sticky-sidebar">
        <div class="card-header bg-primary text-white">
          <h3 class="h6 mb-0"><i class="fas fa-newspaper me-2"></i>Recent Articles</h3>
        </div>
        <div class="card-body p-0">
          <div class="list-group list-group-flush">
            <?php foreach ($recentPosts as $recent): ?>
              <a href="<?= base_url('public/news-view.php?id=' . $recent['post_id']) ?>" 
                 class="list-group-item list-group-item-action">
                <h4 class="h6 mb-1"><?= sanitize($recent['title']) ?></h4>
                <small class="text-muted">
                  <i class="fas fa-calendar me-1"></i>
                  <?= date('M j, Y', strtotime($recent['created_at'])) ?>
                </small>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="card-footer bg-light text-center">
          <a href="<?= base_url('public/news.php') ?>" class="text-decoration-none">
            View All Articles <i class="fas fa-arrow-right ms-1"></i>
          </a>
        </div>
      </div>
      
      <!-- Newsletter Signup -->
      <div class="card border-0 shadow-sm bg-primary text-white mb-4">
        <div class="card-body text-center py-4">
          <i class="fas fa-envelope-open-text fa-3x mb-3 opacity-75"></i>
          <h3 class="h5 mb-2">Stay Updated</h3>
          <p class="small mb-3">Get the latest news and updates from Kalitusi Nature Resort</p>
          <a href="<?= base_url('public/contact.php') ?>" class="btn btn-light btn-sm">
            Subscribe Now
          </a>
        </div>
      </div>
      
      <!-- Contact CTA -->
      <div class="card border-0 shadow-sm bg-light">
        <div class="card-body text-center py-4">
          <i class="fas fa-phone-alt fa-2x text-primary mb-3"></i>
          <h3 class="h6 mb-2">Have Questions?</h3>
          <p class="small text-muted mb-3">Our team is here to help</p>
          <a href="<?= base_url('public/contact.php') ?>" class="btn btn-primary btn-sm">
            Contact Us
          </a>
        </div>
      </div>
      
    </div>
  </div>
</div>

<!-- Additional Styles -->
<style>
/* Article Content Styling */
.article-content {
  font-size: 1.1rem;
  line-height: 1.8;
  color: #2a2a2a;
}

.article-content h3 {
  font-size: 1.75rem;
  margin-top: 2rem;
  margin-bottom: 1rem;
  color: #1a4d2e;
  font-weight: 600;
}

.article-content h4 {
  font-size: 1.4rem;
  margin-top: 1.5rem;
  margin-bottom: 0.75rem;
  color: #2d5f4d;
}

.article-content h5 {
  font-size: 1.2rem;
  margin-top: 1.25rem;
  margin-bottom: 0.5rem;
  color: #2d5f4d;
}

.article-content p {
  margin-bottom: 1.5rem;
}

.article-content ul,
.article-content ol {
  margin-bottom: 1.5rem;
  padding-left: 2rem;
}

.article-content li {
  margin-bottom: 0.5rem;
}

.article-content blockquote {
  border-left: 4px solid #1a4d2e;
  padding-left: 1.5rem;
  margin: 2rem 0;
  font-style: italic;
  color: #5a5a5a;
}

.article-content img {
  max-width: 100%;
  height: auto;
  border-radius: 8px;
  margin: 2rem 0;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.article-content a {
  color: #1a4d2e;
  text-decoration: underline;
}

.article-content a:hover {
  color: #2d5f4d;
}

/* Sticky Sidebar */
@media (min-width: 992px) {
  .sticky-sidebar {
    position: sticky;
    top: 20px;
  }
}

/* Print Styles */
@media print {
  .article-header,
  .breadcrumb,
  .article-footer,
  .related-posts,
  .col-lg-4,
  nav,
  footer {
    display: none !important;
  }
  
  .col-lg-8 {
    width: 100% !important;
  }
  
  .article-content {
    font-size: 12pt;
  }
}
</style>
<?php include __DIR__ . '/../includes/layout_footer.php';
