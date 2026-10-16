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
  $st = $pdo->query("SELECT post_id, title FROM posts WHERE post_type='news' AND status='published' ORDER BY created_at DESC LIMIT 3");
  $latestNews = $st->fetchAll();
} catch (Throwable $e) { /* ignore */ }

include __DIR__ . '/../includes/layout_header.php';
?>

<!-- Skip to content link for accessibility -->
<a href="#main-content" class="skip-to-content">Skip to main content</a>

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

<!-- Quick links strip under slider -->
<div class="quicklinks py-3">
    <div class="container d-flex flex-wrap justify-content-center gap-4">
      <?php if ($quick): ?>
        <?php foreach ($quick as $q): ?>
          <a class="ql-link" href="<?= sanitize($q['url']) ?>"><i class="fas fa-check-circle me-1"></i> <?= sanitize($q['title']) ?></a>
        <?php endforeach; ?>
      <?php else: ?>
        <a class="ql-link" href="<?= base_url('public/booking.php') ?>"><i class="fas fa-calendar-check me-1"></i> Book Your Stay</a>
        <a class="ql-link" href="<?= base_url('public/accommodations.php') ?>"><i class="fas fa-bed me-1"></i> View Rooms</a>
        <a class="ql-link" href="<?= base_url('public/activities.php') ?>"><i class="fas fa-hiking me-1"></i> Activities</a>
        <a class="ql-link" href="<?= base_url('public/dining.php') ?>"><i class="fas fa-utensils me-1"></i> Dining</a>
        <a class="ql-link" href="<?= base_url('public/contact.php') ?>"><i class="fas fa-phone me-1"></i> Contact Us</a>
      <?php endif; ?>
    </div>
</div>

<!-- About Section -->
<section class="bg-white py-5" style="padding-top: 4.5rem !important;">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6 mb-4 mb-lg-0 slide-up">
        <h2 class="display-5 mb-4"><?= sanitize(site_content('home', 'intro_title', "Experience Fort Portal's Premier Nature Resort")) ?></h2>
        <p class="lead text-muted mb-4"><?= sanitize(site_content('home', 'intro_lead', 'Nestled in the heart of Fort Portal Tourism City, Kalitusi Nature Resort offers an unparalleled blend of luxury, comfort, and natural beauty.')) ?></p>
        <p class="mb-4"><?= sanitize(site_content('home', 'intro_body', 'Our eco-friendly resort is surrounded by pristine wilderness, offering breathtaking views, abundant wildlife, and a tranquil atmosphere perfect for relaxation and adventure.')) ?></p>
        <div class="row g-3 mb-4">
          <div class="col-6">
            <div class="d-flex align-items-center">
              <i class="fas fa-check-circle text-primary me-2 fs-5"></i>
              <span>Luxury Accommodations</span>
            </div>
          </div>
          <div class="col-6">
            <div class="d-flex align-items-center">
              <i class="fas fa-check-circle text-primary me-2 fs-5"></i>
              <span>Fine Dining</span>
            </div>
          </div>
          <div class="col-6">
            <div class="d-flex align-items-center">
              <i class="fas fa-check-circle text-primary me-2 fs-5"></i>
              <span>Nature Activities</span>
            </div>
          </div>
          <div class="col-6">
            <div class="d-flex align-items-center">
              <i class="fas fa-check-circle text-primary me-2 fs-5"></i>
              <span>Bird Watching</span>
            </div>
          </div>
        </div>
        <a href="<?= base_url('public/about.php') ?>" class="btn btn-primary">Learn More About Us</a>
      </div>
      <div class="col-lg-6 fade-in">
        <img src="<?= sanitize(site_content_image('home', 'intro_image', 'images/picnic.webp')) ?>" alt="Resort View" class="img-fluid rounded shadow-lg">
      </div>
    </div>
  </div>
</section>

<!-- Features Section -->
<section class="bg-soft-mint py-5">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="display-6"><?= sanitize(site_content('home', 'features_title', 'Why Choose Kalitusi?')) ?></h2>
      <p class="lead text-muted"><?= sanitize(site_content('home', 'features_lead', 'Experience the perfect blend of nature and luxury')) ?></p>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-3 fade-in">
        <div class="feature-card text-center h-100">
          <div class="feature-icon">
            <i class="fas fa-leaf"></i>
          </div>
          <h5 class="mb-3"><?= sanitize(site_content('home', 'feature_eco_title', 'Eco-Friendly')) ?></h5>
          <p class="text-muted"><?= sanitize(site_content('home', 'feature_eco_copy', 'Sustainable practices and respect for nature')) ?></p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 fade-in">
        <div class="feature-card text-center h-100">
          <div class="feature-icon">
            <i class="fas fa-map-marked-alt"></i>
          </div>
          <h5 class="mb-3"><?= sanitize(site_content('home', 'feature_location_title', 'Prime Location')) ?></h5>
          <p class="text-muted"><?= sanitize(site_content('home', 'feature_location_copy', 'Heart of Fort Portal Tourism City')) ?></p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 fade-in">
        <div class="feature-card text-center h-100">
          <div class="feature-icon">
            <i class="fas fa-binoculars"></i>
          </div>
          <h5 class="mb-3"><?= sanitize(site_content('home', 'feature_birds_title', 'Bird Watching')) ?></h5>
          <p class="text-muted"><?= sanitize(site_content('home', 'feature_birds_copy', '300+ species of birds to discover')) ?></p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 fade-in">
        <div class="feature-card text-center h-100">
          <div class="feature-icon">
            <i class="fas fa-concierge-bell"></i>
          </div>
          <h5 class="mb-3"><?= sanitize(site_content('home', 'feature_service_title', 'Premium Service')) ?></h5>
          <p class="text-muted"><?= sanitize(site_content('home', 'feature_service_copy', 'Attentive staff and personalized experiences')) ?></p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Accommodations Preview -->
<section class="bg-white py-5">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="display-6"><?= sanitize(site_content('home', 'stays_title', 'Our Accommodations')) ?></h2>
      <p class="lead text-muted"><?= sanitize(site_content('home', 'stays_lead', 'From cozy cabins to luxury glamping tents')) ?></p>
    </div>
    <div class="row g-4">
        <?php
        // Fetch 3 random accommodations
        $rooms = [];
        try {
            $stmt = $pdo->query("SELECT * FROM accommodations WHERE status = 'active' ORDER BY RAND() LIMIT 3");
            $rooms = $stmt->fetchAll();
        } catch (Exception $e) {
            $rooms = []; // Fallback
        }

        if ($rooms):
            foreach ($rooms as $room):
        ?>
        <div class="col-lg-4 col-md-6">
            <div class="card room-card h-100 overflow-hidden shadow-lg border-0">
                <?php 
                  $roomImageEntries = resort_accommodation_images($room);
                  $roomImage = $roomImageEntries[0]['url'] ?? asset_url('images/rooms.webp');
                  $isDormitory = in_array(strtolower(trim($room['name'])), ['ginger', 'rocket'], true);
                ?>
                <div class="room-card-media">
                  <img src="<?= sanitize($roomImage) ?>" alt="<?= sanitize($room['name']) ?>" loading="lazy">
                  <span class="room-type-badge"><?= sanitize($room['room_type']) ?></span>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start">
                        <h3 class="card-title h5 mb-1 text-dark-green"><?= sanitize($room['name']) ?></h3>
                    </div>
                    <p class="card-text text-muted mb-3"><small><?= substr(sanitize($room['description']), 0, 70) ?>...</small></p>
                    
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="price-label">From</span>
                                <?php if ($isDormitory): ?>
                                  <span class="price-value">UGX 60,000 / bed</span>
                                  <span class="price-per">/ night; whole dorm: UGX 360,000</span>
                                <?php else: ?>
                                  <span class="price-value">UGX <?= number_format($room['price_per_night']) ?></span>
                                  <span class="price-per">/ night</span>
                                <?php endif; ?>
                            </div>
                            <a href="<?= base_url('public/booking.php?room=' . $room['accommodation_id']) ?>" class="btn btn-primary btn-sm">Details</a>
                        </div>
                    </div>
                </div>
            </div>
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
    <div class="text-center mt-5">
      <a href="<?= base_url('public/accommodations.php') ?>" class="btn btn-primary btn-lg px-5">
        View All Accommodations <i class="fas fa-arrow-right ms-2"></i>
      </a>
    </div>
  </div>
</section>

<!-- Statistics Section -->
<section class="bg-primary text-white py-5">
  <div class="container">
    <div class="row g-4 text-center">
      <div class="col-md-3 col-6 fade-in">
        <div class="stat-item">
          <div class="stat-number" data-count="500">0</div>
          <div class="stat-label">Happy Guests</div>
        </div>
      </div>
      <div class="col-md-3 col-6 fade-in">
        <div class="stat-item">
          <div class="stat-number" data-count="50">0</div>
          <div class="stat-label">Luxury Rooms</div>
        </div>
      </div>
      <div class="col-md-3 col-6 fade-in">
        <div class="stat-item">
          <div class="stat-number" data-count="300">0</div>
          <div class="stat-label">Bird Species</div>
        </div>
      </div>
      <div class="col-md-3 col-6 fade-in">
        <div class="stat-item">
          <div class="stat-number" data-count="15">0</div>
          <div class="stat-label">Years Experience</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Testimonials Section -->
<section class="bg-white py-5">
  <div class="container">
    <div class="text-center mb-5 slide-up">
      <h2 class="display-6">What Our Guests Say</h2>
      <p class="lead text-muted">Real experiences from real travelers</p>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-4 slide-up">
        <div class="testimonial-card">
          <div class="testimonial-rating mb-3">
            <i class="fas fa-star text-warning"></i>
            <i class="fas fa-star text-warning"></i>
            <i class="fas fa-star text-warning"></i>
            <i class="fas fa-star text-warning"></i>
            <i class="fas fa-star text-warning"></i>
          </div>
          <p class="testimonial-text">"An absolutely magical experience! The nature, the accommodation, and the service were all beyond expectations. Perfect for a peaceful getaway."</p>
          <div class="testimonial-author">
            <strong>Sarah Johnson</strong>
            <div class="text-muted small">United Kingdom</div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 slide-up">
        <div class="testimonial-card">
          <div class="testimonial-rating mb-3">
            <i class="fas fa-star text-warning"></i>
            <i class="fas fa-star text-warning"></i>
            <i class="fas fa-star text-warning"></i>
            <i class="fas fa-star text-warning"></i>
            <i class="fas fa-star text-warning"></i>
          </div>
          <p class="testimonial-text">"Best resort in Uganda! The bird watching activities were incredible, and the staff made us feel like family. Can't wait to return!"</p>
          <div class="testimonial-author">
            <strong>Michael Chen</strong>
            <div class="text-muted small">Singapore</div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 slide-up">
        <div class="testimonial-card">
          <div class="testimonial-rating mb-3">
            <i class="fas fa-star text-warning"></i>
            <i class="fas fa-star text-warning"></i>
            <i class="fas fa-star text-warning"></i>
            <i class="fas fa-star text-warning"></i>
            <i class="fas fa-star text-warning"></i>
          </div>
          <p class="testimonial-text">"Stunning location, luxurious rooms, and exceptional dining. This resort truly captures the essence of Fort Portal's natural beauty."</p>
          <div class="testimonial-author">
            <strong>Emma Williams</strong>
            <div class="text-muted small">Canada</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA Section -->
<section class="bg-primary text-white py-5">
  <div class="container text-center">
    <h2 class="display-5 mb-3">Ready to Experience Paradise?</h2>
    <p class="lead mb-4">Book your stay at Kalitusi Nature Resort today</p>
    <a href="<?= base_url('public/booking.php') ?>" class="btn btn-outline-light btn-lg me-2"><i class="fas fa-calendar-check"></i> Book Now</a>
    <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-light btn-lg"><i class="fas fa-phone"></i> Contact Us</a>
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
