<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$title = 'Activities & Experiences | Kalitusi Nature Resort';

include __DIR__ . '/../includes/layout_header.php';
?>

<!-- Breadcrumb -->
<div class="container mt-3">
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= base_url('public/') ?>">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Activities</li>
    </ol>
  </nav>
</div>

<!-- Hero Section -->
<section class="page-intro-hero bg-soft-mint">
  <div class="container">
    <div class="row align-items-center g-4 g-lg-5">
      <div class="col-lg-7 text-center text-lg-start">
        <span class="badge rounded-pill px-3 py-2 mb-2" style="background: rgba(27, 138, 90, 0.12); color: #1b8a5a; font-weight: 600;">
          <i class="fas fa-compass me-1"></i> Nature &amp; Adventure
        </span>
        <h1 class="display-5 fw-bold mb-3"><?= sanitize(site_content('activities', 'title', 'Activities & Experiences')) ?></h1>
        <p class="lead mb-4 mx-auto mx-lg-0"><?= sanitize(site_content('activities', 'intro', "Discover the beauty of Fort Portal through our carefully curated activities. From bird watching to nature walks, every experience is designed to connect you with Uganda's natural wonders.")) ?></p>
        <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-lg-start">
          <a href="<?= base_url('public/booking.php') ?>" class="btn btn-primary btn-lg rounded-pill px-4"><?= sanitize(site_content('activities', 'booking_label', 'Book Your Experience')) ?></a>
          <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-primary btn-lg rounded-pill px-4">Custom Group Tours</a>
        </div>
      </div>
      <div class="col-lg-5">
        <img src="<?= sanitize(site_content_image('activities', 'hero_image', 'images/view.webp')) ?>" alt="Nature Activities">
      </div>
    </div>
  </div>
</section>

<!-- Activities Grid -->
<section class="py-4">
  <div class="container">
    <div class="row g-4">
      
      <!-- Bird Watching -->
      <div class="col-md-6 col-lg-4">
        <article class="scrim-card">
          <img class="scrim-card-bg" src="<?= sanitize(site_content_image('activities', 'bird_image', 'images/birds.webp')) ?>" alt="<?= sanitize(site_content('activities', 'bird_title', 'Bird Watching')) ?>" loading="lazy">
          <div class="scrim-badge-top">
            <span class="badge-dot"></span>
            Popular
          </div>
          <div class="scrim-badge-right" title="3-4 hours"><i class="fas fa-clock"></i></div>
          <div class="scrim-card-content">
            <div class="scrim-card-header">
              <h3 class="scrim-card-title"><i class="fas fa-binoculars me-2" style="color: var(--scrim-lime);"></i><?= sanitize(site_content('activities', 'bird_title', 'Bird Watching')) ?></h3>
            </div>
            <p class="scrim-card-subtitle"><?= sanitize(site_content('activities', 'bird_copy', 'Explore over 300 species of birds in their natural habitat. Our expert guides will help you spot rare and beautiful birds unique to the region.')) ?></p>
            <div class="scrim-card-specs">
              <span class="scrim-spec-item"><i class="fas fa-clock"></i> 3-4 hrs</span>
              <span class="scrim-spec-divider"></span>
              <span class="scrim-spec-item"><i class="fas fa-user-friends"></i> Guided</span>
              <span class="scrim-spec-divider"></span>
              <span class="scrim-spec-item"><i class="fas fa-sun"></i> 6:00 AM</span>
            </div>
            <div class="scrim-card-action-row">
              <div class="scrim-price-pill"><i class="fas fa-binoculars me-1"></i> Experience</div>
              <a href="<?= base_url('public/contact.php') ?>" class="btn-scrim-cta">Book Now &rarr;</a>
            </div>
          </div>
        </article>
      </div>

      <!-- Nature Walks -->
      <div class="col-md-6 col-lg-4">
        <article class="scrim-card">
          <img class="scrim-card-bg" src="<?= sanitize(site_content_image('activities', 'walk_image', 'images/shade.webp')) ?>" alt="<?= sanitize(site_content('activities', 'walk_title', 'Nature Walks')) ?>" loading="lazy">
          <div class="scrim-badge-top is-gold">
            <span class="badge-dot"></span>
            Relaxing
          </div>
          <div class="scrim-badge-right" title="2-3 hours"><i class="fas fa-clock"></i></div>
          <div class="scrim-card-content">
            <div class="scrim-card-header">
              <h3 class="scrim-card-title"><i class="fas fa-hiking me-2" style="color: var(--scrim-lime);"></i><?= sanitize(site_content('activities', 'walk_title', 'Nature Walks')) ?></h3>
            </div>
            <p class="scrim-card-subtitle"><?= sanitize(site_content('activities', 'walk_copy', 'Immerse yourself in the pristine wilderness surrounding our resort. Walk through forests, meadows, and scenic trails with breathtaking views.')) ?></p>
            <div class="scrim-card-specs">
              <span class="scrim-spec-item"><i class="fas fa-clock"></i> 2-3 hrs</span>
              <span class="scrim-spec-divider"></span>
              <span class="scrim-spec-item"><i class="fas fa-route"></i> Self/Guided</span>
              <span class="scrim-spec-divider"></span>
              <span class="scrim-spec-item"><i class="fas fa-calendar"></i> Daily</span>
            </div>
            <div class="scrim-card-action-row">
              <div class="scrim-price-pill"><i class="fas fa-hiking me-1"></i> Experience</div>
              <a href="<?= base_url('public/contact.php') ?>" class="btn-scrim-cta">Book Now &rarr;</a>
            </div>
          </div>
        </article>
      </div>

      <!-- Mountain Biking -->
      <div class="col-md-6 col-lg-4">
        <article class="scrim-card">
          <img class="scrim-card-bg" src="<?= sanitize(site_content_image('activities', 'bike_image', 'images/camp_van.webp')) ?>" alt="<?= sanitize(site_content('activities', 'bike_title', 'Mountain Biking')) ?>" loading="lazy">
          <div class="scrim-badge-top">
            <span class="badge-dot"></span>
            Adventure
          </div>
          <div class="scrim-badge-right" title="2-5 hours"><i class="fas fa-clock"></i></div>
          <div class="scrim-card-content">
            <div class="scrim-card-header">
              <h3 class="scrim-card-title"><i class="fas fa-bicycle me-2" style="color: var(--scrim-lime);"></i><?= sanitize(site_content('activities', 'bike_title', 'Mountain Biking')) ?></h3>
            </div>
            <p class="scrim-card-subtitle"><?= sanitize(site_content('activities', 'bike_copy', 'Explore the rugged terrain on two wheels. We provide quality mountain bikes and safety gear for all skill levels.')) ?></p>
            <div class="scrim-card-specs">
              <span class="scrim-spec-item"><i class="fas fa-clock"></i> 2-5 hrs</span>
              <span class="scrim-spec-divider"></span>
              <span class="scrim-spec-item"><i class="fas fa-bicycle"></i> Gear Incl.</span>
              <span class="scrim-spec-divider"></span>
              <span class="scrim-spec-item"><i class="fas fa-sun"></i> AM/PM</span>
            </div>
            <div class="scrim-card-action-row">
              <div class="scrim-price-pill"><i class="fas fa-bicycle me-1"></i> Experience</div>
              <a href="<?= base_url('public/contact.php') ?>" class="btn-scrim-cta">Book Now &rarr;</a>
            </div>
          </div>
        </article>
      </div>

      <!-- Cultural Tours -->
      <div class="col-md-6 col-lg-4">
        <article class="scrim-card">
          <img class="scrim-card-bg" src="<?= sanitize(site_content_image('activities', 'culture_image', 'images/picnic.webp')) ?>" alt="<?= sanitize(site_content('activities', 'culture_title', 'Cultural Tours')) ?>" loading="lazy">
          <div class="scrim-badge-top is-gold">
            <span class="badge-dot"></span>
            Cultural
          </div>
          <div class="scrim-badge-right" title="Half or Full Day"><i class="fas fa-clock"></i></div>
          <div class="scrim-card-content">
            <div class="scrim-card-header">
              <h3 class="scrim-card-title"><i class="fas fa-monument me-2" style="color: var(--scrim-lime);"></i><?= sanitize(site_content('activities', 'culture_title', 'Cultural Tours')) ?></h3>
            </div>
            <p class="scrim-card-subtitle"><?= sanitize(site_content('activities', 'culture_copy', 'Visit local communities and experience the rich cultural heritage of Fort Portal. Meet artisans, learn traditional crafts, and enjoy authentic cuisine.')) ?></p>
            <div class="scrim-card-specs">
              <span class="scrim-spec-item"><i class="fas fa-clock"></i> Half/Full Day</span>
              <span class="scrim-spec-divider"></span>
              <span class="scrim-spec-item"><i class="fas fa-user-friends"></i> Expert Guide</span>
              <span class="scrim-spec-divider"></span>
              <span class="scrim-spec-item"><i class="fas fa-calendar"></i> On Request</span>
            </div>
            <div class="scrim-card-action-row">
              <div class="scrim-price-pill"><i class="fas fa-monument me-1"></i> Experience</div>
              <a href="<?= base_url('public/contact.php') ?>" class="btn-scrim-cta">Book Now &rarr;</a>
            </div>
          </div>
        </article>
      </div>

      <!-- Crater Lake Tours -->
      <div class="col-md-6 col-lg-4">
        <article class="scrim-card">
          <img class="scrim-card-bg" src="<?= sanitize(site_content_image('activities', 'lake_image', 'images/rooms.webp')) ?>" alt="<?= sanitize(site_content('activities', 'lake_title', 'Crater Lake Tours')) ?>" loading="lazy">
          <div class="scrim-badge-top">
            <span class="badge-dot"></span>
            Scenic
          </div>
          <div class="scrim-badge-right" title="Full Day"><i class="fas fa-clock"></i></div>
          <div class="scrim-card-content">
            <div class="scrim-card-header">
              <h3 class="scrim-card-title"><i class="fas fa-water me-2" style="color: var(--scrim-lime);"></i><?= sanitize(site_content('activities', 'lake_title', 'Crater Lake Tours')) ?></h3>
            </div>
            <p class="scrim-card-subtitle"><?= sanitize(site_content('activities', 'lake_copy', 'Explore the stunning crater lakes surrounding Fort Portal. Perfect for photography, picnics, and peaceful reflection.')) ?></p>
            <div class="scrim-card-specs">
              <span class="scrim-spec-item"><i class="fas fa-clock"></i> Full Day</span>
              <span class="scrim-spec-divider"></span>
              <span class="scrim-spec-item"><i class="fas fa-car"></i> Transport Incl.</span>
              <span class="scrim-spec-divider"></span>
              <span class="scrim-spec-item"><i class="fas fa-calendar"></i> Daily</span>
            </div>
            <div class="scrim-card-action-row">
              <div class="scrim-price-pill"><i class="fas fa-water me-1"></i> Experience</div>
              <a href="<?= base_url('public/contact.php') ?>" class="btn-scrim-cta">Book Now &rarr;</a>
            </div>
          </div>
        </article>
      </div>

      <!-- Yoga & Wellness -->
      <div class="col-md-6 col-lg-4">
        <article class="scrim-card">
          <img class="scrim-card-bg" src="<?= sanitize(site_content_image('activities', 'wellness_image', 'images/activity_wellness.jpg')) ?>" alt="<?= sanitize(site_content('activities', 'wellness_title', 'Yoga & Wellness')) ?>" loading="lazy">
          <div class="scrim-badge-top is-gold">
            <span class="badge-dot"></span>
            Wellness
          </div>
          <div class="scrim-badge-right" title="1-2 hours"><i class="fas fa-clock"></i></div>
          <div class="scrim-card-content">
            <div class="scrim-card-header">
              <h3 class="scrim-card-title"><i class="fas fa-spa me-2" style="color: var(--scrim-lime);"></i><?= sanitize(site_content('activities', 'wellness_title', 'Yoga & Wellness')) ?></h3>
            </div>
            <p class="scrim-card-subtitle"><?= sanitize(site_content('activities', 'wellness_copy', 'Start your day with sunrise yoga sessions in nature. Professional instructors guide you through rejuvenating practices.')) ?></p>
            <div class="scrim-card-specs">
              <span class="scrim-spec-item"><i class="fas fa-clock"></i> 1-2 hrs</span>
              <span class="scrim-spec-divider"></span>
              <span class="scrim-spec-item"><i class="fas fa-star"></i> All Levels</span>
              <span class="scrim-spec-divider"></span>
              <span class="scrim-spec-item"><i class="fas fa-sun"></i> 6:30 AM</span>
            </div>
            <div class="scrim-card-action-row">
              <div class="scrim-price-pill"><i class="fas fa-spa me-1"></i> Experience</div>
              <a href="<?= base_url('public/contact.php') ?>" class="btn-scrim-cta">Book Now &rarr;</a>
            </div>
          </div>
        </article>
      </div>

    </div>
  </div>
</section>

<!-- Nearby Attractions as Cards -->
<section class="bg-white py-5 mt-4">
  <div class="container">
    <h2 class="display-6 text-center mb-5"><?= sanitize(site_content('activities', 'attractions_title', 'Nearby Attractions')) ?></h2>
    <div class="row g-4">
      <div class="col-md-6 col-lg-3">
        <article class="scrim-card scrim-card-compact">
          <img class="scrim-card-bg" src="<?= sanitize(site_content_image('activities', 'attraction_1_image', 'images/view.webp')) ?>" alt="<?= sanitize(site_content('activities', 'attraction_1_title', 'Rwenzori Mountains')) ?>" loading="lazy">
          <div class="scrim-badge-top">
            <span class="badge-dot"></span>
            <?= sanitize(site_content('activities', 'attraction_1_meta', 'Trekking • Mountaineering')) ?>
          </div>
          <div class="scrim-card-content">
            <div class="scrim-card-header">
              <h5 class="scrim-card-title"><?= sanitize(site_content('activities', 'attraction_1_title', 'Rwenzori Mountains')) ?></h5>
            </div>
            <p class="scrim-card-subtitle mb-0"><?= sanitize(site_content('activities', 'attraction_1_copy', 'Legendary "Mountains of the Moon" just a short, scenic drive from the resort.')) ?></p>
          </div>
        </article>
      </div>
      <div class="col-md-6 col-lg-3">
        <article class="scrim-card scrim-card-compact">
          <img class="scrim-card-bg" src="<?= sanitize(site_content_image('activities', 'attraction_2_image', 'images/shade.webp')) ?>" alt="<?= sanitize(site_content('activities', 'attraction_2_title', 'Kibale Forest')) ?>" loading="lazy">
          <div class="scrim-badge-top is-gold">
            <span class="badge-dot"></span>
            <?= sanitize(site_content('activities', 'attraction_2_meta', 'Chimp Tracking • Rainforest')) ?>
          </div>
          <div class="scrim-card-content">
            <div class="scrim-card-header">
              <h5 class="scrim-card-title"><?= sanitize(site_content('activities', 'attraction_2_title', 'Kibale Forest')) ?></h5>
            </div>
            <p class="scrim-card-subtitle mb-0"><?= sanitize(site_content('activities', 'attraction_2_copy', 'Pristine rainforest famous for chimpanzees and rich biodiversity.')) ?></p>
          </div>
        </article>
      </div>
      <div class="col-md-6 col-lg-3">
        <article class="scrim-card scrim-card-compact">
          <img class="scrim-card-bg" src="<?= sanitize(site_content_image('activities', 'attraction_3_image', 'images/campfire.webp')) ?>" alt="<?= sanitize(site_content('activities', 'attraction_3_title', 'Sempaya Hot Springs')) ?>" loading="lazy">
          <div class="scrim-badge-top">
            <span class="badge-dot"></span>
            <?= sanitize(site_content('activities', 'attraction_3_meta', 'Semuliki National Park')) ?>
          </div>
          <div class="scrim-card-content">
            <div class="scrim-card-header">
              <h5 class="scrim-card-title"><?= sanitize(site_content('activities', 'attraction_3_title', 'Sempaya Hot Springs')) ?></h5>
            </div>
            <p class="scrim-card-subtitle mb-0"><?= sanitize(site_content('activities', 'attraction_3_copy', 'Natural hot springs surrounded by myths, culture, and lush scenery.')) ?></p>
          </div>
        </article>
      </div>
      <div class="col-md-6 col-lg-3">
        <article class="scrim-card scrim-card-compact">
          <img class="scrim-card-bg" src="<?= sanitize(site_content_image('activities', 'attraction_4_image', 'images/rooms.webp')) ?>" alt="<?= sanitize(site_content('activities', 'attraction_4_title', 'Tooro Palace')) ?>" loading="lazy">
          <div class="scrim-badge-top is-gold">
            <span class="badge-dot"></span>
            <?= sanitize(site_content('activities', 'attraction_4_meta', 'Cultural Heritage')) ?>
          </div>
          <div class="scrim-card-content">
            <div class="scrim-card-header">
              <h5 class="scrim-card-title"><?= sanitize(site_content('activities', 'attraction_4_title', 'Tooro Palace')) ?></h5>
            </div>
            <p class="scrim-card-subtitle mb-0"><?= sanitize(site_content('activities', 'attraction_4_copy', 'Historic hilltop palace of the Tooro Kingdom, overlooking Fort Portal.')) ?></p>
          </div>
        </article>
      </div>
    </div>
  </div>
</section>

<!-- CTA Section -->
<section class="bg-primary text-white py-5 mt-4">
  <div class="container text-center">
    <h2 class="display-6 mb-3"><?= sanitize(site_content('activities', 'cta_title', 'Ready for Your Adventure?')) ?></h2>
    <p class="lead mb-4"><?= sanitize(site_content('activities', 'cta_copy', 'Book your activities now or contact us to create a custom itinerary')) ?></p>
    <a href="<?= base_url('public/booking.php') ?>" class="btn btn-outline-light btn-lg me-2"><?= sanitize(site_content('activities', 'cta_book_label', 'Book Now')) ?></a>
    <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-light btn-lg"><?= sanitize(site_content('activities', 'cta_contact_label', 'Contact Us')) ?></a>
  </div>
</section>

<?php include __DIR__ . '/../includes/layout_footer.php';
