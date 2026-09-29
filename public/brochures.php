<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$title = 'Brochures & Resources | Kalitusi Nature Resort';

// Fetch downloads with categories
$rows = $pdo->query("SELECT d.download_id, d.title, d.file_type, d.download_count, d.file_path, c.name AS cat
                     FROM downloads d LEFT JOIN categories c ON d.category=c.category_id
                     ORDER BY c.name, d.upload_date DESC")->fetchAll();

include __DIR__ . '/../includes/layout_header.php';
?>

<nav aria-label="breadcrumb" class="mb-2 mt-2">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= base_url('public/') ?>">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Brochures</li>
  </ol>
</nav>

<!-- Page hero slider -->
<div id="dlHero" class="carousel slide hero-slider mb-3" data-bs-ride="carousel">
  <div class="carousel-inner">
    <div class="carousel-item active">
      <img src="<?= asset_url('images/hero-brochures-1.jpg') ?>" alt="Brochures" onerror="this.src='https://images.unsplash.com/photo-1488190211105-8b0e65b80b4e?w=1600&h=360&fit=crop'">
      <div class="carousel-caption text-start hero-caption">
        <h2 class="h3 mb-1">Resort Brochures & Menus</h2>
        <p>Download our guides, menus and informational materials</p>
      </div>
    </div>
    <div class="carousel-item">
      <img src="<?= asset_url('images/hero-brochures-2.jpg') ?>" alt="Information" onerror="this.src='https://images.unsplash.com/photo-1517842645767-c639042777db?w=1600&h=360&fit=crop'">
      <div class="carousel-caption text-start hero-caption">
        <h2 class="h3 mb-1">Guest Information</h2>
        <p>Everything you need to plan your perfect stay</p>
      </div>
    </div>
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#dlHero" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span><span class="visually-hidden">Prev</span></button>
  <button class="carousel-control-next" type="button" data-bs-target="#dlHero" data-bs-slide="next"><span class="carousel-control-next-icon"></span><span class="visually-hidden">Next</span></button>
</div>

<h1 class="h3 mb-3">Brochures & Resources</h1>

<?php
// Group by category in PHP for simple rendering
$grouped = [];
foreach ($rows as $r) {
  $key = $r['cat'] ?: 'General';
  $grouped[$key][] = $r;
}
?>

<div class="row g-3">
  <?php foreach ($grouped as $cat => $items): ?>
    <div class="col-lg-6">
      <div class="card h-100"><div class="card-body">
        <h2 class="h6 mb-3"><?= sanitize($cat) ?></h2>
        <ul class="mb-0">
          <?php foreach ($items as $it): ?>
            <li>
              <a href="<?= base_url('public/download.php?id=' . (int)$it['download_id']) ?>">
                <?= sanitize($it['title']) ?>
              </a>
              <span class="text-muted small">(<?= strtoupper(sanitize($it['file_type'] ?? '')) ?> · <?= (int)$it['download_count'] ?>)</span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div></div>
    </div>
  <?php endforeach; ?>
</div>
<?php include __DIR__ . '/../includes/layout_footer.php';
