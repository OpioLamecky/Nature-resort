<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$title = 'Resort Services & Amenities | Kalitusi Nature Resort';

// Fetch content blocks for Services page
$blocks = [];
try {
  $st = $pdo->query("SELECT title, body, icon FROM content_blocks WHERE page='services' AND status='visible' ORDER BY display_order, block_id ASC");
  $blocks = $st->fetchAll();
} catch (Throwable $e) { /* ignore */ }

include __DIR__ . '/../includes/layout_header.php';
?>

<nav aria-label="breadcrumb" class="mb-2 mt-2">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= base_url('public/') ?>">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Services</li>
  </ol>
  <a id="backToTop" href="#" class="btn btn-sm btn-outline-secondary float-end d-none">Back to top ↑</a>
</nav>

<!-- Page hero slider -->
<div id="svcHero" class="carousel slide hero-slider mb-3" data-bs-ride="carousel">
  <div class="carousel-inner">
    <div class="carousel-item hero-slide active">
      <div class="hero-slide__bg" style="background-image: url('<?= sanitize(site_content_image('services', 'hero_image', 'images/view.webp')) ?>');"></div>
      <div class="hero-slide__overlay"></div>
      <div class="hero-slide__content container">
        <div class="hero-slide__text">
          <h2 class="hero-slide__title"><?= sanitize(site_content('services', 'title', 'Resort Services & Amenities')) ?></h2>
          <p class="hero-slide__subtitle"><?= sanitize(site_content('services', 'subtitle', 'Everything you need for a perfect stay')) ?></p>
        </div>
      </div>
    </div>
    <div class="carousel-item hero-slide">
      <div class="hero-slide__bg" style="background-image: url('<?= sanitize(site_content_image('services', 'secondary_image', 'images/campfire.webp')) ?>');"></div>
      <div class="hero-slide__overlay"></div>
      <div class="hero-slide__content container">
        <div class="hero-slide__text">
          <h2 class="hero-slide__title"><?= sanitize(site_content('services', 'secondary_title', 'Premium Guest Services')) ?></h2>
          <p class="hero-slide__subtitle"><?= sanitize(site_content('services', 'secondary_subtitle', 'Concierge, spa, activities and more')) ?></p>
        </div>
      </div>
    </div>
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#svcHero" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span><span class="visually-hidden">Prev</span></button>
  <button class="carousel-control-next" type="button" data-bs-target="#svcHero" data-bs-slide="next"><span class="carousel-control-next-icon"></span><span class="visually-hidden">Next</span></button>
</div>

<h1 class="h3 mb-3"><?= sanitize(site_content('services', 'section_title', 'Services & Amenities')) ?></h1>
<div class="row g-3">
  <?php if ($blocks): ?>
    <?php foreach ($blocks as $b): ?>
      <div class="col-md-6">
        <div class="card h-100"><div class="card-body">
          <h2 class="h5 mb-2"><?= sanitize($b['title']) ?></h2>
          <div class="mb-2"><?= $b['body'] /* admin-authored HTML */ ?></div>
        </div></div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="col-md-6">
      <div class="card h-100"><div class="card-body">
        <h2 class="h5"><i class="fas fa-concierge-bell text-primary me-2"></i>Concierge Services</h2>
        <ul class="mb-2">
          <li>24/7 Reception desk</li>
          <li>Tour bookings & arrangements</li>
          <li>Transportation services</li>
          <li>Local recommendations</li>
        </ul>
        <a class="btn btn-sm btn-outline-primary" href="<?= base_url('public/contact.php') ?>">Contact Concierge</a>
      </div></div>
    </div>
    <div class="col-md-6">
      <div class="card h-100"><div class="card-body">
        <h2 class="h5"><i class="fas fa-spa text-primary me-2"></i>Spa & Wellness</h2>
        <ul class="mb-2">
          <li>Massage therapy</li>
          <li>Sauna & steam room</li>
          <li>Yoga sessions</li>
          <li>Beauty treatments</li>
        </ul>
        <a class="btn btn-sm btn-outline-primary" href="<?= base_url('public/contact.php') ?>">Book Appointment</a>
      </div></div>
    </div>
    <div class="col-md-6">
      <div class="card h-100"><div class="card-body">
        <h2 class="h5"><i class="fas fa-utensils text-primary me-2"></i>Dining Services</h2>
        <ul class="mb-2">
          <li>Restaurant & bar</li>
          <li>Room service</li>
          <li>Special dietary menus</li>
          <li>Private dining events</li>
        </ul>
        <a class="btn btn-sm btn-outline-primary" href="<?= base_url('public/dining.php') ?>">View Menus</a>
      </div></div>
    </div>
    <div class="col-md-6">
      <div class="card h-100"><div class="card-body">
        <h2 class="h5"><i class="fas fa-wifi text-primary me-2"></i>Modern Amenities</h2>
        <ul class="mb-2">
          <li>Free high-speed WiFi</li>
          <li>Swimming pool</li>
          <li>Fitness center</li>
          <li>Conference facilities</li>
        </ul>
        <a class="btn btn-sm btn-outline-primary" href="<?= base_url('public/about.php') ?>">Learn More</a>
      </div></div>
    </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/layout_footer.php';
