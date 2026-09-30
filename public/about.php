<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$title = 'About Us - Kalitusi Nature Resort';

// Fetch About Us content from database
$content = [];
try {
  $stmt = $pdo->query("SELECT section, title, content FROM about_content WHERE status='active' ORDER BY display_order");
  foreach ($stmt->fetchAll() as $row) {
    $content[$row['section']] = $row;
  }
} catch (Throwable $e) { /* ignore */ }

// Fetch leadership
$leaders = [];
try {
  $stmt = $pdo->query("SELECT * FROM about_leadership WHERE status='active' ORDER BY display_order, leader_id LIMIT 6");
  $leaders = $stmt->fetchAll();
} catch (Throwable $e) { /* ignore */ }

// Fetch achievements
$achievements = [];
try {
  $stmt = $pdo->query("SELECT * FROM about_achievements WHERE status='active' ORDER BY display_order, achievement_id LIMIT 4");
  $achievements = $stmt->fetchAll();
} catch (Throwable $e) { /* ignore */ }

// Fetch timeline
$timeline = [];
try {
  $stmt = $pdo->query("SELECT * FROM about_timeline WHERE status='active' ORDER BY display_order, timeline_id");
  $timeline = $stmt->fetchAll();
} catch (Throwable $e) { /* ignore */ }

include __DIR__ . '/../includes/layout_header.php';
?>

<!-- Hero Slider -->
<div id="aboutHero" class="carousel slide hero-slider mb-0" data-bs-ride="carousel">
  <div class="carousel-inner">
    <div class="carousel-item hero-slide active">
      <div class="hero-slide__bg" style="background-image: url('<?= sanitize(site_content_image('about', 'hero_image', 'images/view.webp')) ?>');"></div>
      <div class="hero-slide__overlay"></div>
      <div class="hero-slide__content container">
        <div class="hero-slide__text">
          <h2 class="hero-slide__title"><?= sanitize(site_content('about', 'hero_title', 'About Kalitusi Nature Resort')) ?></h2>
          <p class="hero-slide__subtitle"><?= sanitize(site_content('about', 'hero_subtitle', 'Your Gateway to Nature in Fort Portal Tourism City')) ?></p>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container py-4">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= base_url('public/') ?>">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">About Us</li>
    </ol>
  </nav>

  <!-- Mission & Vision Section -->
  <div class="row g-4 mb-5">
    <div class="col-lg-6">
      <div class="card h-100 shadow-sm border-0 ura-mission-card">
        <div class="card-body p-4 text-center">
          <div class="icon-container-lg mb-3">
            <i class="bi bi-bullseye" style="font-size: 3rem; color: #0b5394;"></i>
          </div>
          <h5 class="fw-bold"><?= sanitize($content['mission']['title'] ?? 'Our Mission') ?></h5>
          <p class="mb-0"><?= sanitize($content['mission']['content'] ?? 'To provide an exceptional eco-tourism experience that harmonizes luxury with nature conservation, offering our guests unforgettable moments while preserving the pristine beauty of Fort Portal for future generations.') ?></p>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card h-100 shadow-sm border-0 ura-vision-card">
        <div class="card-body p-4 text-center">
          <div class="icon-container-lg mb-3">
            <i class="bi bi-eye" style="font-size: 3rem; color: #ffd400;"></i>
          </div>
          <h5 class="fw-bold"><?= sanitize($content['vision']['title'] ?? 'Our Vision') ?></h5>
          <p class="mb-0"><?= sanitize($content['vision']['content'] ?? 'To become Uganda\'s premier eco-resort destination, renowned for exceptional hospitality, sustainable practices, and unique nature-based experiences that inspire environmental stewardship.') ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Core Values Section -->
  <div class="mb-5">
    <h2 class="h3 text-center mb-2"><?= sanitize(site_content('about', 'values_title', 'Our Core Values')) ?></h2>
    <p class="text-center text-muted mb-4"><?= sanitize(site_content('about', 'values_subtitle', 'Guiding principles that define our operations')) ?></p>
    <div class="row g-4">
      <div class="col-lg-3 col-md-6">
        <div class="card h-100 text-center border-0 shadow-sm">
          <div class="card-body p-4">
            <div class="value-icon-circle mb-3">
              <i class="bi bi-shield-check" style="font-size: 2.5rem; color: #0b5394;"></i>
            </div>
            <h4 class="h5"><?= sanitize($content['value_integrity']['title'] ?? 'Integrity') ?></h4>
            <p class="small"><?= sanitize($content['value_integrity']['content'] ?? 'We maintain honesty and ethical standards in all our operations') ?></p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="card h-100 text-center border-0 shadow-sm">
          <div class="card-body p-4">
            <div class="value-icon-circle mb-3">
              <i class="bi bi-people" style="font-size: 2.5rem; color: #0b5394;"></i>
            </div>
            <h4 class="h5"><?= sanitize($content['value_teamwork']['title'] ?? 'Teamwork') ?></h4>
            <p class="small"><?= sanitize($content['value_teamwork']['content'] ?? 'Collaborating effectively to achieve common goals') ?></p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="card h-100 text-center border-0 shadow-sm">
          <div class="card-body p-4">
            <div class="value-icon-circle mb-3">
              <i class="bi bi-lightbulb" style="font-size: 2.5rem; color: #0b5394;"></i>
            </div>
            <h4 class="h5"><?= sanitize($content['value_innovation']['title'] ?? 'Innovation') ?></h4>
            <p class="small"><?= sanitize($content['value_innovation']['content'] ?? 'Embracing technology and creative solutions') ?></p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="card h-100 text-center border-0 shadow-sm">
          <div class="card-body p-4">
            <div class="value-icon-circle mb-3">
              <i class="bi bi-heart" style="font-size: 2.5rem; color: #0b5394;"></i>
            </div>
            <h4 class="h5"><?= sanitize($content['value_patriotism']['title'] ?? 'Patriotism') ?></h4>
            <p class="small"><?= sanitize($content['value_patriotism']['content'] ?? 'Serving Uganda with dedication and commitment') ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- History Section -->
  <div class="row g-4 mb-5">
    <div class="col-lg-6">
      <h2 class="h3 mb-3"><?= sanitize($content['history']['title'] ?? 'Our Story') ?></h2>
      <p><?= sanitize($content['history']['content'] ?? 'Kalitusi Nature Resort was born from a passion for Uganda\'s natural beauty and a vision to share Fort Portal\'s pristine wilderness with the world. Established in the heart of Fort Portal Tourism City, our resort has grown from a simple eco-lodge to a premier destination that seamlessly blends luxury hospitality with environmental conservation. Every detail of Kalitusi reflects our commitment to sustainable tourism and our deep respect for the land and local communities.') ?></p>
    </div>
    <div class="col-lg-6">
      <div class="timeline-ura">
        <?php if ($timeline): foreach ($timeline as $t): ?>
          <h3 class="h6">Resort Manager</h3>
          <div class="leader-image-circle mb-3">
            <img src="<?= sanitize($l['photo_url']) ?>" alt="<?= sanitize($l['name']) ?>" onerror="this.src='https://via.placeholder.com/150x150/1a4d2e/ffffff?text=Manager'">
          </div>
          <div class="fw-bold mb-1"><?= sanitize($l['name']) ?></div>
          <div class="small text-muted mb-2"><?= sanitize($l['position']) ?></div>
          <div class="small"><?= sanitize($l['bio'] ?? '') ?></div>
        <?php endforeach; else: ?>
          <div class="timeline-item-ura">
            <div class="timeline-year-ura">1991</div>
            <div class="timeline-content-ura">
              <h5 class="h6 fw-bold">Kalitusi Founded</h5>
              <p class="small mb-0">Establishment of Kalitusi Nature Resort in Fort Portal</p>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Resort Services Section -->
  <div class="mb-5">
    <h2 class="h3 text-center mb-4"><?= sanitize(site_content('about', 'services_title', 'Our Services')) ?></h2>
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body p-4">
            <h4 class="h5 mb-3"><i class="bi bi-house-heart text-success me-2"></i><?= sanitize(site_content('about', 'service_stays_title', 'Accommodation')) ?></h4>
            <ul class="list-unstyled">
              <?php foreach (site_content_rows('about', 'service_stays_items', "Luxury Suites\nStandard Rooms\nFamily Cottages\nEco-Friendly Lodges\nConference Facilities", "\n") as $service): ?><li class="py-2 border-bottom"><i class="fas fa-check text-success me-2"></i><?= sanitize($service[0] ?? '') ?></li><?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body p-4">
            <h4 class="h5 mb-3"><i class="bi bi-tree text-primary me-2"></i><?= sanitize(site_content('about', 'service_activities_title', 'Nature Activities')) ?></h4>
            <ul class="list-unstyled">
              <?php foreach (site_content_rows('about', 'service_activities_items', "Guided Nature Walks\nBird Watching Tours\nWildlife Photography\nHiking Adventures\nCultural Experiences", "\n") as $service): ?><li class="py-2 border-bottom"><i class="fas fa-check text-success me-2"></i><?= sanitize($service[0] ?? '') ?></li><?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body p-4">
            <h4 class="h5 mb-3"><i class="bi bi-heart-pulse text-danger me-2"></i><?= sanitize(site_content('about', 'service_wellness_title', 'Wellness & Dining')) ?></h4>
            <ul class="list-unstyled">
              <?php foreach (site_content_rows('about', 'service_wellness_items', "Spa Services\nLocal Cuisine Restaurant\nFitness Center\nSwimming Pool\nEvent Planning", "\n") as $service): ?><li class="py-2 border-bottom"><i class="fas fa-check text-success me-2"></i><?= sanitize($service[0] ?? '') ?></li><?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Leadership Section -->
  <div class="mb-5">
    <h2 class="h3 text-center mb-4"><?= sanitize(site_content('about', 'leaders_title', 'Our Leadership')) ?></h2>
    <div class="row g-4">
      <?php if ($leaders): foreach ($leaders as $leader): ?>
        <div class="col-lg-4">
          <div class="card h-100 border-0 shadow-sm text-center">
            <div class="card-body p-4">
              <div class="leader-image-circle mb-3">
                <?php 
                  $imgPath = !empty($leader['image_url'])
                    ? cms_image_url($leader['image_url'])
                    : asset_url('images/about/default-leader.png');
                ?>
                <img src="<?= sanitize($imgPath) ?>" alt="<?= sanitize($leader['name']) ?>">
              </div>
              <h4 class="h5 mb-1"><?= sanitize($leader['name']) ?></h4>
              <p class="text-primary fw-semibold mb-2"><?= sanitize($leader['position']) ?></p>
              <?php if (!empty($leader['bio'])): ?>
                <p class="small text-muted"><?= sanitize($leader['bio']) ?></p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; else: ?>
        <div class="col-lg-12 text-center text-muted">
          <p>No leadership members found.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Achievements Section -->
  <div class="mb-5">
    <h2 class="h3 text-center mb-4"><?= sanitize(site_content('about', 'achievements_title', 'Key Achievements')) ?></h2>
    <div class="row g-4 text-center">
      <?php if ($achievements): foreach ($achievements as $ach): ?>
        <div class="col-lg-3 col-md-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
              <div class="achievement-number"><?= sanitize($ach['stat_value']) ?></div>
              <p class="mb-0 text-muted"><?= sanitize($ach['stat_label']) ?></p>
              <?php if (!empty($ach['description'])): ?>
                <small class="text-muted d-block mt-1"><?= sanitize($ach['description']) ?></small>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; else: ?>
        <div class="col-lg-12 text-center text-muted">
          <p>No achievements found.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Contact CTA Section -->
  <div class="card bg-dark text-white mb-4">
    <div class="card-body p-5 text-center">
      <h2 class="h3 mb-3"><?= sanitize(site_content('about', 'cta_title', 'Visit Kalitusi Nature Resort')) ?></h2>
      <p class="lead mb-4"><?= sanitize(site_content('about', 'cta_copy', "We're here to make your stay unforgettable. Contact us for bookings or inquiries.")) ?></p>
      <a href="<?= base_url('public/contact.php') ?>" class="btn btn-primary btn-lg me-2"><?= sanitize(site_content('about', 'cta_contact_label', 'Contact Us')) ?></a>
      <a href="<?= base_url('public/booking.php') ?>" class="btn btn-outline-light btn-lg"><?= sanitize(site_content('about', 'cta_booking_label', 'Book Your Stay')) ?></a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/layout_footer.php';
