<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/accommodation_images.php';
$title = 'Kalitusi Nature Resort | Fort Portal, Uganda - Luxury Eco-Resort';

// Announcement ticker (published and within date range if set)
$ann = [];
try {
  $stmt = $pdo->query("SELECT title FROM announcements WHERE status='published' AND (start_date IS NULL OR start_date <= CURDATE()) AND (end_date IS NULL OR end_date >= CURDATE()) ORDER BY priority DESC, start_date DESC LIMIT 10");
  $ann = $stmt->fetchAll();
} catch (Throwable $e) { /* ignore */ }

// Sliders — reads from the `sliders` table that admin/pages-home.php writes to
$slides = [];
try {
    $stmt = $pdo->query("
        SELECT slider_id, title, subtitle, image_path AS image_url, button_text, button_url
        FROM sliders
        WHERE status = 'published'
          AND (active_from IS NULL OR active_from <= CURDATE())
          AND (active_to   IS NULL OR active_to   >= CURDATE())
        ORDER BY display_order, slider_id
    ");
    $slides = $stmt->fetchAll();
} catch (Throwable $e) {
    $slides = [];
}

// Fallback slide if database is empty
if (empty($slides)) {
    $slides = [
        [
            'slider_id' => 'fallback',
            'title' => 'Welcome to Kalitusi Nature Resort',
            'subtitle' => 'Experience the tranquility of nature.',
            'image_url' => asset_url('images/rooms.webp'),
            'button_text' => 'Explore More',
            'button_url' => base_url('public/about.php'),
        ]
    ];
}

// Quick Links
$quick = [];
try {
  // Admin saves status='visible'; column is 'label' not 'title'
  $st = $pdo->query("SELECT label AS title, url FROM quick_links WHERE status='visible' ORDER BY display_order, link_id DESC LIMIT 10");
  $quick = $st->fetchAll();
} catch (Throwable $e) { /* ignore */ }

// Latest 3 news
$latestNews = [];
try {
  $st = $pdo->query("SELECT post_id, title, excerpt, featured_image, created_at FROM posts WHERE post_type='news' AND status='published' ORDER BY created_at DESC LIMIT 3");
  $latestNews = $st->fetchAll();
} catch (Throwable $e) { /* ignore */ }

include __DIR__ . '/../includes/layout_header.php';
?>

<!-- Skip to content link for accessibility -->
<a href="#main-content" class="skip-to-content">Skip to main content</a>

<?php if (!empty($ann)): ?>
<!-- Announcement Bar -->
<div style="background: linear-gradient(90deg, #0f172a 0%, #14532d 100%); color: #f8fafc; font-size: 0.85rem; padding: 0.45rem 0; border-bottom: 1px solid rgba(255,255,255,0.1);">
  <div class="container d-flex align-items-center justify-content-center gap-2 flex-wrap text-center">
    <span class="badge rounded-pill" style="background: rgba(74, 222, 128, 0.2); color: #4ade80; font-weight: 600;"><i class="fas fa-bullhorn me-1"></i> Notice</span>
    <span><?= sanitize($ann[0]['title']) ?></span>
  </div>
</div>
<?php endif; ?>

<!-- Hero Slider -->
<div id="heroCarousel" class="carousel slide hero-slider" data-bs-ride="carousel" data-bs-interval="7000" data-bs-pause="hover" data-bs-touch="true" style="position: relative;" aria-roledescription="carousel" aria-label="Featured content">
  <div class="carousel-inner">
    <?php if ($slides): $first=true; foreach ($slides as $s): ?>
      <?php $slideImg = cms_image_url($s['image_url'] ?? '') ?: asset_url('images/rooms.webp'); ?>
      <div class="carousel-item hero-slide <?= $first ? 'active' : '' ?>" aria-roledescription="slide" aria-label="<?= ($s['title'] ? sanitize($s['title']) : 'Slide') . ($s['subtitle'] ? ': ' . sanitize($s['subtitle']) : '') ?>">
        <div class="hero-slide__bg" style="background-image: url('<?= sanitize($slideImg ?? '') ?>');" role="img" aria-label="<?= sanitize($s['title'] ?? 'Hero image') ?>"></div>
        <div class="hero-slide__overlay"></div>
        <div class="hero-slide__content container">
          <div class="hero-slide__text">
            <?php if (!empty($s['title'])): ?><h2 class="hero-slide__title" id="slide-title-<?= $s['slider_id'] ?>"><?= sanitize($s['title']) ?></h2><?php endif; ?>
            <?php if (!empty($s['subtitle'])): ?><p class="hero-slide__subtitle" id="slide-subtitle-<?= $s['slider_id'] ?>"><?= sanitize($s['subtitle']) ?></p><?php endif; ?>
            <?php if (!empty($s['button_text']) && !empty($s['button_url'])): ?>
              <a class="btn btn-primary btn-lg" href="<?= sanitize($s['button_url']) ?>" aria-describedby="<?= !empty($s['title']) ? 'slide-title-' . $s['slider_id'] : '' ?>"><?= sanitize($s['button_text']) ?></a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php $first=false; endforeach; endif; ?>
  </div>

  <?php if (count($slides) > 1): ?>
    <div class="carousel-indicators hero-slider__indicators d-none d-md-flex flex-column" role="tablist">
      <?php for ($i = 0; $i < count($slides); $i++): ?>
        <button type="button"
                role="tab"
                data-bs-target="#heroCarousel"
                data-bs-slide-to="<?= $i ?>"
                class="<?= $i === 0 ? 'active' : '' ?>"
                aria-current="<?= $i === 0 ? 'true' : 'false' ?>"
                aria-label="Slide <?= $i + 1 ?><?= !empty($slides[$i]['title']) ? ': ' . sanitize($slides[$i]['title']) : '' ?>"
                aria-controls="heroCarousel"></button>
      <?php endfor; ?>
    </div>
  <?php endif; ?>

  <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" aria-label="Previous slide">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" aria-label="Next slide">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
  </button>

</div>

<!-- Quick Booking & Quick Links Bar under Hero Slider -->
<section class="position-relative" style="z-index: 10; margin-top: -1.75rem; margin-bottom: 1.5rem;">
  <div class="container">
    <div class="bg-white rounded-4 shadow-lg p-3 p-lg-4" style="border: 1px solid rgba(15, 23, 42, 0.08);">
      <form action="<?= base_url('public/booking.php') ?>" method="GET" class="row g-3 align-items-end">
        <div class="col-sm-6 col-lg-3">
          <label class="form-label small fw-bold text-uppercase text-muted mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">
            <i class="fas fa-calendar-alt text-success me-1"></i> Check-In Date
          </label>
          <input type="date" name="check_in" class="form-control rounded-3" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
        </div>
        <div class="col-sm-6 col-lg-3">
          <label class="form-label small fw-bold text-uppercase text-muted mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">
            <i class="fas fa-calendar-check text-success me-1"></i> Check-Out Date
          </label>
          <input type="date" name="check_out" class="form-control rounded-3" min="<?= date('Y-m-d', strtotime('+2 days')) ?>" value="<?= date('Y-m-d', strtotime('+3 days')) ?>">
        </div>
        <div class="col-sm-6 col-lg-3">
          <label class="form-label small fw-bold text-uppercase text-muted mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">
            <i class="fas fa-user-friends text-success me-1"></i> Guests
          </label>
          <select name="adults" class="form-select rounded-3">
            <option value="1">1 Adult</option>
            <option value="2" selected>2 Adults</option>
            <option value="3">3 Adults</option>
            <option value="4">4+ Adults / Family</option>
          </select>
        </div>
        <div class="col-sm-6 col-lg-3">
          <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold" style="height: 42px;">
            <i class="fas fa-search me-2"></i>Check Availability
          </button>
        </div>
      </form>

      <!-- Quick Links Pills -->
      <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 pt-3 mt-3 border-top">
        <span class="small text-muted fw-semibold me-1"><i class="fas fa-compass text-success me-1"></i>Quick Explore:</span>
        <?php if ($quick): ?>
          <?php foreach ($quick as $q): ?>
            <a class="badge rounded-pill text-decoration-none px-3 py-2" style="background: #f1f5f3; color: #1a4d2e; font-weight: 600;" href="<?= sanitize($q['url']) ?>">
              <i class="fas fa-arrow-right me-1 small opacity-75"></i> <?= sanitize($q['title']) ?>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <a class="badge rounded-pill text-decoration-none px-3 py-2" style="background: #f1f5f3; color: #1a4d2e; font-weight: 600;" href="<?= base_url('public/accommodations.php') ?>"><i class="fas fa-bed me-1"></i> Rooms &amp; Suites</a>
          <a class="badge rounded-pill text-decoration-none px-3 py-2" style="background: #f1f5f3; color: #1a4d2e; font-weight: 600;" href="<?= base_url('public/activities.php') ?>"><i class="fas fa-binoculars me-1"></i> Safari &amp; Birding</a>
          <a class="badge rounded-pill text-decoration-none px-3 py-2" style="background: #f1f5f3; color: #1a4d2e; font-weight: 600;" href="<?= base_url('public/dining.php') ?>"><i class="fas fa-utensils me-1"></i> Mountain Dining</a>
          <a class="badge rounded-pill text-decoration-none px-3 py-2" style="background: #f1f5f3; color: #1a4d2e; font-weight: 600;" href="<?= base_url('eshop.php') ?>"><i class="fas fa-gift me-1"></i> Gift Shop</a>
          <a class="badge rounded-pill text-decoration-none px-3 py-2" style="background: #f1f5f3; color: #1a4d2e; font-weight: 600;" href="<?= base_url('public/gallery.php') ?>"><i class="fas fa-camera me-1"></i> Photo Gallery</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- About / Editorial Welcome Section -->
<section id="main-content" class="bg-white py-5">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6 slide-up">
        <span class="badge rounded-pill px-3 py-2 mb-3" style="background: rgba(27, 138, 90, 0.12); color: #1b8a5a; font-weight: 600;">
          <i class="fas fa-leaf me-1"></i> Welcome to Kalitusi Nature Resort
        </span>
        <h2 class="display-5 fw-bold mb-3" style="line-height: 1.18;"><?= sanitize(site_content('home', 'intro_title', "Experience Fort Portal's Premier Nature Resort")) ?></h2>
        <p class="lead text-muted mb-3"><?= sanitize(site_content('home', 'intro_lead', 'Nestled in the heart of Fort Portal Tourism City, Kalitusi Nature Resort offers an unparalleled blend of luxury, comfort, and natural beauty.')) ?></p>
        <p class="text-secondary mb-4"><?= sanitize(site_content('home', 'intro_body', 'Our eco-friendly resort is surrounded by pristine wilderness, offering breathtaking views, abundant wildlife, and a tranquil atmosphere perfect for relaxation and adventure.')) ?></p>

        <div class="row g-3 mb-4">
          <div class="col-sm-6">
            <div class="d-flex align-items-center gap-3 p-3 rounded-4" style="background: #f8faf9; border: 1px solid rgba(15,23,42,0.06);">
              <span class="d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0" style="width: 40px; height: 40px; background: rgba(27,138,90,0.14); color: #1b8a5a;">
                <i class="fas fa-bed"></i>
              </span>
              <div>
                <strong class="d-block text-dark" style="font-size: 0.92rem;">Luxury Eco-Stays</strong>
                <span class="text-muted small">Cabins, suites &amp; glamping</span>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="d-flex align-items-center gap-3 p-3 rounded-4" style="background: #f8faf9; border: 1px solid rgba(15,23,42,0.06);">
              <span class="d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0" style="width: 40px; height: 40px; background: rgba(27,138,90,0.14); color: #1b8a5a;">
                <i class="fas fa-utensils"></i>
              </span>
              <div>
                <strong class="d-block text-dark" style="font-size: 0.92rem;">Scenic Dining</strong>
                <span class="text-muted small">Fresh local &amp; global cuisine</span>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="d-flex align-items-center gap-3 p-3 rounded-4" style="background: #f8faf9; border: 1px solid rgba(15,23,42,0.06);">
              <span class="d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0" style="width: 40px; height: 40px; background: rgba(27,138,90,0.14); color: #1b8a5a;">
                <i class="fas fa-hiking"></i>
              </span>
              <div>
                <strong class="d-block text-dark" style="font-size: 0.92rem;">Nature Trails</strong>
                <span class="text-muted small">Guided forest &amp; crater walks</span>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="d-flex align-items-center gap-3 p-3 rounded-4" style="background: #f8faf9; border: 1px solid rgba(15,23,42,0.06);">
              <span class="d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0" style="width: 40px; height: 40px; background: rgba(27,138,90,0.14); color: #1b8a5a;">
                <i class="fas fa-dove"></i>
              </span>
              <div>
                <strong class="d-block text-dark" style="font-size: 0.92rem;">300+ Bird Species</strong>
                <span class="text-muted small">Prime birdwatching sanctuary</span>
              </div>
            </div>
          </div>
        </div>

        <div class="d-flex flex-wrap gap-3">
          <a href="<?= base_url('public/about.php') ?>" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">Discover Our Story <i class="fas fa-arrow-right ms-1"></i></a>
          <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-primary rounded-pill px-4 py-2">Plan Your Visit</a>
        </div>
      </div>

      <div class="col-lg-6 fade-in">
        <div class="position-relative">
          <img src="<?= sanitize(site_content_image('home', 'intro_image', 'images/picnic.webp')) ?>" alt="Kalitusi Nature Resort Grounds" class="img-fluid w-100 shadow-lg" style="height: 360px; object-fit: cover; border-radius: 22px;">
          <div class="position-absolute bottom-0 start-0 m-3 p-3 rounded-4 shadow" style="background: rgba(15, 29, 22, 0.84); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.18); color: #fff; max-width: 290px;">
            <div class="d-flex align-items-center gap-2 mb-1">
              <span class="badge rounded-pill" style="background: #a3e635; color: #0f172a; font-weight: 700;">4.9 ★</span>
              <span class="small fw-semibold">Top-Rated Eco-Sanctuary</span>
            </div>
            <p class="small mb-0" style="color: rgba(255,255,255,0.82); font-size: 0.78rem;">Overlooking the Rwenzori foothills in Fort Portal Tourism City.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Features Section (Pinterest Bento Grid) -->
<section class="bg-soft-mint py-5">
  <div class="container">
    <div class="text-center mb-5">
      <span class="badge rounded-pill px-3 py-2 mb-2" style="background: rgba(27, 138, 90, 0.12); color: #1b8a5a; font-weight: 600;">Resort Highlights</span>
      <h2 class="display-6 fw-bold"><?= sanitize(site_content('home', 'features_title', 'Why Choose Kalitusi?')) ?></h2>
      <p class="lead text-muted mb-0"><?= sanitize(site_content('home', 'features_lead', 'Experience the perfect blend of nature and luxury')) ?></p>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-3 fade-in">
        <div class="bg-white h-100 p-4 rounded-4 shadow-sm position-relative" style="border: 1px solid rgba(15,23,42,0.06);">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="d-inline-flex align-items-center justify-content-center rounded-4" style="width: 52px; height: 52px; background: rgba(27,138,90,0.12); color: #1b8a5a; font-size: 1.35rem;">
              <i class="fas fa-leaf"></i>
            </span>
            <span class="fw-bold text-muted opacity-25 fs-4">01</span>
          </div>
          <h5 class="fw-bold mb-2"><?= sanitize(site_content('home', 'feature_eco_title', 'Eco-Friendly')) ?></h5>
          <p class="text-muted small mb-0"><?= sanitize(site_content('home', 'feature_eco_copy', 'Sustainable practices, solar energy, and deep respect for the surrounding forest ecosystem.')) ?></p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 fade-in">
        <div class="bg-white h-100 p-4 rounded-4 shadow-sm position-relative" style="border: 1px solid rgba(15,23,42,0.06);">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="d-inline-flex align-items-center justify-content-center rounded-4" style="width: 52px; height: 52px; background: rgba(37,99,235,0.1); color: #2563eb; font-size: 1.35rem;">
              <i class="fas fa-map-marked-alt"></i>
            </span>
            <span class="fw-bold text-muted opacity-25 fs-4">02</span>
          </div>
          <h5 class="fw-bold mb-2"><?= sanitize(site_content('home', 'feature_location_title', 'Prime Location')) ?></h5>
          <p class="text-muted small mb-0"><?= sanitize(site_content('home', 'feature_location_copy', 'Minutes from crater lakes, Kibale chimpanzee forest, and Fort Portal Tourism City.')) ?></p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 fade-in">
        <div class="bg-white h-100 p-4 rounded-4 shadow-sm position-relative" style="border: 1px solid rgba(15,23,42,0.06);">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="d-inline-flex align-items-center justify-content-center rounded-4" style="width: 52px; height: 52px; background: rgba(217,119,6,0.12); color: #d97706; font-size: 1.35rem;">
              <i class="fas fa-binoculars"></i>
            </span>
            <span class="fw-bold text-muted opacity-25 fs-4">03</span>
          </div>
          <h5 class="fw-bold mb-2"><?= sanitize(site_content('home', 'feature_birds_title', 'Bird Watching')) ?></h5>
          <p class="text-muted small mb-0"><?= sanitize(site_content('home', 'feature_birds_copy', 'Over 300 vibrant bird species right within our lush gardens and forest canopy.')) ?></p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 fade-in">
        <div class="bg-white h-100 p-4 rounded-4 shadow-sm position-relative" style="border: 1px solid rgba(15,23,42,0.06);">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="d-inline-flex align-items-center justify-content-center rounded-4" style="width: 52px; height: 52px; background: rgba(225,29,72,0.1); color: #e11d48; font-size: 1.35rem;">
              <i class="fas fa-concierge-bell"></i>
            </span>
            <span class="fw-bold text-muted opacity-25 fs-4">04</span>
          </div>
          <h5 class="fw-bold mb-2"><?= sanitize(site_content('home', 'feature_service_title', 'Warm Hospitality')) ?></h5>
          <p class="text-muted small mb-0"><?= sanitize(site_content('home', 'feature_service_copy', 'Attentive 24/7 concierge team dedicated to crafting personalized guest experiences.')) ?></p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Accommodations Preview -->
<section class="bg-white py-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
      <div>
        <span class="badge rounded-pill px-3 py-2 mb-2" style="background: rgba(27, 138, 90, 0.12); color: #1b8a5a; font-weight: 600;">Curated Stays</span>
        <h2 class="display-6 fw-bold mb-1"><?= sanitize(site_content('home', 'stays_title', 'Our Accommodations')) ?></h2>
        <p class="text-muted mb-0"><?= sanitize(site_content('home', 'stays_lead', 'From cozy forest cottages to luxury glamping tents')) ?></p>
      </div>
      <a href="<?= base_url('public/accommodations.php') ?>" class="btn btn-outline-primary rounded-pill px-4">
        Explore All Rooms <i class="fas fa-arrow-right ms-1"></i>
      </a>
    </div>
    <div class="row g-4">
        <?php
        $rooms = [];
        try {
            $stmt = $pdo->query("SELECT * FROM accommodations WHERE status = 'active' ORDER BY display_order, price_per_night LIMIT 3");
            $rooms = $stmt->fetchAll();
        } catch (Exception $e) {
            $rooms = [];
        }

        if ($rooms):
            foreach ($rooms as $room):
        ?>
        <div class="col-lg-4 col-md-6">
            <article class="scrim-card">
                <?php 
                  $roomImageEntries = resort_accommodation_images($room);
                  $roomImage = $roomImageEntries[0]['url'] ?? asset_url('images/rooms.webp');
                  $isDormitory = in_array(strtolower(trim($room['name'])), ['ginger', 'rocket'], true);
                ?>
                <img class="scrim-card-bg" src="<?= sanitize($roomImage) ?>" alt="<?= sanitize($room['name']) ?>" loading="lazy">
                <div class="scrim-badge-top">
                  <span class="badge-dot"></span>
                  <?= sanitize($room['room_type']) ?>
                </div>
                <div class="scrim-badge-right" title="<?= (int)$room['capacity_adults'] ?> Adults">
                  <i class="fas fa-user-friends"></i>
                </div>
                <div class="scrim-card-content">
                    <div class="scrim-card-header">
                        <h3 class="scrim-card-title"><?= sanitize($room['name']) ?></h3>
                    </div>
                    <p class="scrim-card-subtitle">
                      <?= sanitize($room['description']) ?>
                    </p>
                    <div class="scrim-card-specs">
                      <span class="scrim-spec-item"><i class="fas fa-user"></i> <?= (int)$room['capacity_adults'] ?> Adults</span>
                      <span class="scrim-spec-divider"></span>
                      <span class="scrim-spec-item"><i class="fas fa-child"></i> <?= (int)$room['capacity_children'] ?> Kids</span>
                      <span class="scrim-spec-divider"></span>
                      <span class="scrim-spec-item"><i class="fas fa-bed"></i> <?= sanitize($room['room_type']) ?></span>
                    </div>
                    <div class="scrim-card-action-row">
                        <div class="scrim-price-pill">
                            <?php if ($isDormitory): ?>
                              UGX 60k <small>/bed</small>
                            <?php else: ?>
                              UGX <?= number_format($room['price_per_night']) ?> <small>/night</small>
                            <?php endif; ?>
                        </div>
                        <a href="<?= base_url('public/booking.php?room=' . $room['accommodation_id']) ?>" class="btn-scrim-cta">Book Stay &rarr;</a>
                    </div>
                </div>
            </article>
        </div>
        <?php 
            endforeach;
        else:
        ?>
        <div class="col-12">
            <p class="text-center text-muted">Our accommodations are currently being updated. Please check back soon!</p>
        </div>
        <?php endif; ?>
    </div>
  </div>
</section>

<!-- Signature Resort Experiences (Pinterest Compact Scrim Showcase) -->
<section class="bg-soft-mint py-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
      <div>
        <span class="badge rounded-pill px-3 py-2 mb-2" style="background: rgba(27, 138, 90, 0.12); color: #1b8a5a; font-weight: 600;">Unforgettable Moments</span>
        <h2 class="display-6 fw-bold mb-1">Signature Resort Experiences</h2>
        <p class="text-muted mb-0">Curated adventures, farm-to-table dining, and tranquil nature immersion</p>
      </div>
      <a href="<?= base_url('public/activities.php') ?>" class="btn btn-outline-primary rounded-pill px-4">
        All Experiences <i class="fas fa-arrow-right ms-1"></i>
      </a>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <article class="scrim-card scrim-card-compact">
          <img class="scrim-card-bg" src="<?= asset_url('images/birds.webp') ?>" alt="Bird Watching & Forest Walks" loading="lazy">
          <div class="scrim-badge-top"><span class="badge-dot"></span> Guided Nature</div>
          <div class="scrim-card-content">
            <div class="scrim-card-header">
              <h3 class="scrim-card-title">Bird Watching &amp; Trails</h3>
            </div>
            <p class="scrim-card-subtitle">Spot rare Turacos and over 300 species with our expert naturalist guides.</p>
            <div class="scrim-card-action-row">
              <div class="scrim-price-pill"><i class="fas fa-binoculars me-1"></i> Daily Tours</div>
              <a href="<?= base_url('public/activities.php') ?>" class="btn-scrim-cta">Explore &rarr;</a>
            </div>
          </div>
        </article>
      </div>
      <div class="col-md-4">
        <article class="scrim-card scrim-card-compact">
          <img class="scrim-card-bg" src="<?= asset_url('images/food1.webp') ?>" alt="Mountain View Dining" loading="lazy">
          <div class="scrim-badge-top"><span class="badge-dot"></span> Culinary</div>
          <div class="scrim-card-content">
            <div class="scrim-card-header">
              <h3 class="scrim-card-title">Mountain View Dining</h3>
            </div>
            <p class="scrim-card-subtitle">Authentic Ugandan dishes and international cuisine crafted from fresh local harvest.</p>
            <div class="scrim-card-action-row">
              <div class="scrim-price-pill"><i class="fas fa-utensils me-1"></i> Restaurant</div>
              <a href="<?= base_url('public/dining.php') ?>" class="btn-scrim-cta">View Menu &rarr;</a>
            </div>
          </div>
        </article>
      </div>
      <div class="col-md-4">
        <article class="scrim-card scrim-card-compact">
          <img class="scrim-card-bg" src="<?= asset_url('images/campfire.webp') ?>" alt="Evening Campfire & Glamping" loading="lazy">
          <div class="scrim-badge-top"><span class="badge-dot"></span> Evening Magic</div>
          <div class="scrim-card-content">
            <div class="scrim-card-header">
              <h3 class="scrim-card-title">Bonfire &amp; Stargazing</h3>
            </div>
            <p class="scrim-card-subtitle">Unwind by the warm evening campfire under Fort Portal's crystal-clear night skies.</p>
            <div class="scrim-card-action-row">
              <div class="scrim-price-pill"><i class="fas fa-fire me-1"></i> Every Evening</div>
              <a href="<?= base_url('public/booking.php') ?>" class="btn-scrim-cta">Book Stay &rarr;</a>
            </div>
          </div>
        </article>
      </div>
    </div>
  </div>
</section>

<!-- Statistics Section -->
<section class="py-5 text-white" style="background: linear-gradient(120deg, #0f291e 0%, #1a4d2e 55%, #245e3b 100%);">
  <div class="container">
    <div class="row g-4 text-center">
      <div class="col-md-3 col-6 fade-in">
        <div class="p-3 rounded-4" style="background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12);">
          <div class="stat-number display-5 fw-bold" style="color: #a3e635;" data-count="500">500+</div>
          <div class="stat-label small text-uppercase mt-1" style="letter-spacing: 0.08em; color: rgba(255,255,255,0.85);">Happy Guests</div>
        </div>
      </div>
      <div class="col-md-3 col-6 fade-in">
        <div class="p-3 rounded-4" style="background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12);">
          <div class="stat-number display-5 fw-bold" style="color: #a3e635;" data-count="50">50+</div>
          <div class="stat-label small text-uppercase mt-1" style="letter-spacing: 0.08em; color: rgba(255,255,255,0.85);">Luxury Rooms &amp; Cottages</div>
        </div>
      </div>
      <div class="col-md-3 col-6 fade-in">
        <div class="p-3 rounded-4" style="background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12);">
          <div class="stat-number display-5 fw-bold" style="color: #a3e635;" data-count="300">300+</div>
          <div class="stat-label small text-uppercase mt-1" style="letter-spacing: 0.08em; color: rgba(255,255,255,0.85);">Bird Species</div>
        </div>
      </div>
      <div class="col-md-3 col-6 fade-in">
        <div class="p-3 rounded-4" style="background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12);">
          <div class="stat-number display-5 fw-bold" style="color: #a3e635;" data-count="15">15+</div>
          <div class="stat-label small text-uppercase mt-1" style="letter-spacing: 0.08em; color: rgba(255,255,255,0.85);">Years of Hospitality</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Testimonials Section (Pinterest Review Cards) -->
<section class="bg-white py-5">
  <div class="container">
    <div class="text-center mb-5 slide-up">
      <span class="badge rounded-pill px-3 py-2 mb-2" style="background: rgba(27, 138, 90, 0.1); color: #1b8a5a; font-weight: 600;">Guest Stories</span>
      <h2 class="display-6 fw-bold">What Our Guests Say</h2>
      <p class="lead text-muted">Real experiences from verified travelers around the world</p>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-4 slide-up">
        <div class="testimonial-card h-100 d-flex flex-column justify-content-between" style="border-radius: 18px; border: 1px solid rgba(15, 23, 42, 0.07); padding: 1.5rem; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);">
          <div>
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div class="testimonial-rating">
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
              </div>
              <span class="badge rounded-pill" style="background: rgba(27, 138, 90, 0.1); color: #1b8a5a; font-size: 0.72rem;"><i class="fas fa-check-circle me-1"></i>Verified Stay</span>
            </div>
            <p class="testimonial-text text-secondary mb-4">"An absolutely magical experience! The nature, the accommodation, and the service were all beyond expectations. Perfect for a peaceful getaway."</p>
          </div>
          <div class="d-flex align-items-center gap-3 pt-3 border-top">
            <img src="<?= asset_url('images/guest-sarah.jpg') ?>" alt="Sarah Johnson" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover;" onerror="this.src='<?= asset_url('images/default-avatar.png') ?>'">
            <div>
              <strong class="d-block text-dark" style="font-size: 0.95rem;">Sarah Johnson</strong>
              <div class="text-muted small"><i class="fas fa-globe-europe me-1"></i>United Kingdom</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 slide-up">
        <div class="testimonial-card h-100 d-flex flex-column justify-content-between" style="border-radius: 18px; border: 1px solid rgba(15, 23, 42, 0.07); padding: 1.5rem; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);">
          <div>
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div class="testimonial-rating">
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
              </div>
              <span class="badge rounded-pill" style="background: rgba(27, 138, 90, 0.1); color: #1b8a5a; font-size: 0.72rem;"><i class="fas fa-check-circle me-1"></i>Verified Stay</span>
            </div>
            <p class="testimonial-text text-secondary mb-4">"Best resort in Uganda! The bird watching activities were incredible, and the staff made us feel like family. Can't wait to return!"</p>
          </div>
          <div class="d-flex align-items-center gap-3 pt-3 border-top">
            <img src="<?= asset_url('images/resort-host-manager.jpg') ?>" alt="Michael Chen" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover;" onerror="this.src='<?= asset_url('images/default-avatar.png') ?>'">
            <div>
              <strong class="d-block text-dark" style="font-size: 0.95rem;">Michael Chen</strong>
              <div class="text-muted small"><i class="fas fa-globe-asia me-1"></i>Singapore</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 slide-up">
        <div class="testimonial-card h-100 d-flex flex-column justify-content-between" style="border-radius: 18px; border: 1px solid rgba(15, 23, 42, 0.07); padding: 1.5rem; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);">
          <div>
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div class="testimonial-rating">
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
              </div>
              <span class="badge rounded-pill" style="background: rgba(27, 138, 90, 0.1); color: #1b8a5a; font-size: 0.72rem;"><i class="fas fa-check-circle me-1"></i>Verified Stay</span>
            </div>
            <p class="testimonial-text text-secondary mb-4">"Stunning location, luxurious rooms, and exceptional dining. This resort truly captures the essence of Fort Portal's natural beauty."</p>
          </div>
          <div class="d-flex align-items-center gap-3 pt-3 border-top">
            <img src="<?= asset_url('images/default-avatar.png') ?>" alt="Emma Williams" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover;">
            <div>
              <strong class="d-block text-dark" style="font-size: 0.95rem;">Emma Williams</strong>
              <div class="text-muted small"><i class="fas fa-globe-americas me-1"></i>Canada</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if (!empty($latestNews)): ?>
<!-- Latest News & Stories Section -->
<section class="bg-light py-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
      <div>
        <span class="badge rounded-pill px-3 py-2 mb-2" style="background: rgba(27, 138, 90, 0.1); color: #1b8a5a; font-weight: 600;">Resort Journal</span>
        <h2 class="display-6 fw-bold mb-0">Latest News &amp; Updates</h2>
      </div>
      <a href="<?= base_url('public/news.php') ?>" class="btn btn-outline-primary rounded-pill px-4">View All Stories <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
    <div class="row g-4">
      <?php foreach ($latestNews as $newsItem): 
        $newsImg = !empty($newsItem['featured_image']) ? cms_image_url($newsItem['featured_image']) : asset_url('images/view.webp');
      ?>
      <div class="col-md-4">
        <article class="scrim-card scrim-card-compact">
          <img class="scrim-card-bg" src="<?= sanitize($newsImg) ?>" alt="<?= sanitize($newsItem['title']) ?>" loading="lazy" onerror="this.src='<?= asset_url('images/view.webp') ?>'">
          <div class="scrim-badge-top">
            <span class="badge-dot"></span> <?= date('M d, Y', strtotime($newsItem['created_at'])) ?>
          </div>
          <div class="scrim-card-content">
            <div class="scrim-card-header">
              <h3 class="scrim-card-title"><?= sanitize($newsItem['title']) ?></h3>
            </div>
            <?php if (!empty($newsItem['excerpt'])): ?>
              <p class="scrim-card-subtitle"><?= sanitize($newsItem['excerpt']) ?></p>
            <?php endif; ?>
            <div class="scrim-card-action-row">
              <div class="scrim-price-pill"><i class="fas fa-newspaper me-1"></i> News</div>
              <a href="<?= base_url('public/news-view.php?id=' . (int)$newsItem['post_id']) ?>" class="btn-scrim-cta">Read Story &rarr;</a>
            </div>
          </div>
        </article>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="py-5 bg-white">
  <div class="container">
    <div class="rounded-4 p-4 p-lg-5 text-white text-center shadow-lg position-relative overflow-hidden" style="background: linear-gradient(115deg, rgba(15, 41, 30, 0.88) 0%, rgba(26, 77, 46, 0.78) 100%), url('<?= asset_url('images/view.webp') ?>') center / cover no-repeat;">
      <span class="badge rounded-pill px-3 py-2 mb-3" style="background: rgba(255,255,255,0.16); color: #d9f99d; border: 1px solid rgba(255,255,255,0.22); font-weight: 600;">
        <i class="fas fa-sparkles me-1"></i> Escape to Fort Portal
      </span>
      <h2 class="display-5 fw-bold mb-3 text-white">Ready to Experience Paradise?</h2>
      <p class="lead mb-4 mx-auto" style="max-width: 38rem; color: rgba(255,255,255,0.9);">Reserve your cottage, suite, or glamping tent at Kalitusi Nature Resort and wake up to the sounds of nature.</p>
      <div class="d-flex flex-wrap justify-content-center gap-3">
        <a href="<?= base_url('public/booking.php') ?>" class="btn btn-light btn-lg rounded-pill px-4 fw-semibold" style="color: #143625;"><i class="fas fa-calendar-check me-2"></i>Book Your Stay</a>
        <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-light btn-lg rounded-pill px-4"><i class="fas fa-phone-alt me-2"></i>Contact Concierge</a>
      </div>
    </div>
  </div>
</section>

<script>
// Lazy loading for slider images
document.addEventListener('DOMContentLoaded', function() {
    const slideBackgrounds = document.querySelectorAll('.hero-slide__bg');
    
    const loadImage = (bgElement) => {
        const imageUrl = bgElement.style.backgroundImage.match(/url\(['"]?([^'"]+)['"]?\)/);
        if (imageUrl && imageUrl[1]) {
            const img = new Image();
            img.onload = function() {
                bgElement.classList.add('loaded');
                bgElement.classList.remove('loading');
            };
            img.onerror = function() {
                bgElement.style.backgroundImage = "url('<?= asset_url('images/rooms.webp') ?>')";
                bgElement.classList.add('loaded');
                bgElement.classList.remove('loading');
            };
            img.src = imageUrl[1];
        }
    };
    
    // Load first slide immediately
    if (slideBackgrounds.length > 0) {
        loadImage(slideBackgrounds[0]);
    }
    
    // Load other slides when carousel changes
    const carousel = document.getElementById('heroCarousel');
    if (carousel) {
        carousel.addEventListener('slide.bs.carousel', function (event) {
            const nextSlide = event.relatedTarget;
            const bgElement = nextSlide.querySelector('.hero-slide__bg');
            if (bgElement && !bgElement.classList.contains('loaded')) {
                loadImage(bgElement);
            }
        });
    }
    
    // Preload all slides after initial load
    setTimeout(() => {
        slideBackgrounds.forEach((bg, index) => {
            if (index > 0 && !bg.classList.contains('loaded')) {
                loadImage(bg);
            }
        });
    }, 2000);
});
</script>

<?php include __DIR__ . '/../includes/layout_footer.php';
