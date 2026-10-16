<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$title = 'Activities & Experiences | Kalitusi Nature Resort';

include __DIR__ . '/../includes/layout_header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3 mt-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= base_url('public/') ?>">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Activities</li>
  </ol>
</nav>

<!-- Hero Section -->
<section class="page-intro-hero bg-soft-mint">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <h1 class="display-5 mb-3"><?= sanitize(site_content('activities', 'title', 'Activities & Experiences')) ?></h1>
        <p class="lead mb-4"><?= sanitize(site_content('activities', 'intro', "Discover the beauty of Fort Portal through our carefully curated activities. From bird watching to nature walks, every experience is designed to connect you with Uganda's natural wonders.")) ?></p>
        <a href="<?= base_url('public/booking.php') ?>" class="btn btn-primary btn-lg"><?= sanitize(site_content('activities', 'booking_label', 'Book Your Experience')) ?></a>
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
        <div class="card h-100 shadow-sm resort-image-card">
          <img src="<?= sanitize(site_content_image('activities', 'bird_image', 'images/birds.webp')) ?>" class="card-img-top" alt="<?= sanitize(site_content('activities', 'bird_title', 'Bird Watching')) ?>" loading="lazy">
          <div class="card-body">
            <h3 class="h5 card-title"><i class="fas fa-binoculars text-primary me-2"></i><?= sanitize(site_content('activities', 'bird_title', 'Bird Watching')) ?></h3>
            <p class="card-text"><?= sanitize(site_content('activities', 'bird_copy', 'Explore over 300 species of birds in their natural habitat. Our expert guides will help you spot rare and beautiful birds unique to the region.')) ?></p>
            <ul class="list-unstyled small text-muted">
              <?php foreach (site_content_rows('activities', 'bird_details', "3-4 hours\nGuided tours available\nDaily, 6:00 AM start", "\n") as $detail): ?><li><?= sanitize($detail[0] ?? '') ?></li><?php endforeach; ?>
            </ul>
          </div>
          <div class="card-footer bg-white border-top-0">
            <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-primary btn-sm w-100">Book Now</a>
          </div>
        </div>
      </div>

      <!-- Nature Walks -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm resort-image-card">
          <img src="<?= sanitize(site_content_image('activities', 'walk_image', 'images/shade.webp')) ?>" class="card-img-top" alt="<?= sanitize(site_content('activities', 'walk_title', 'Nature Walks')) ?>" loading="lazy">
          <div class="card-body">
            <h3 class="h5 card-title"><i class="fas fa-hiking text-primary me-2"></i><?= sanitize(site_content('activities', 'walk_title', 'Nature Walks')) ?></h3>
            <p class="card-text"><?= sanitize(site_content('activities', 'walk_copy', 'Immerse yourself in the pristine wilderness surrounding our resort. Walk through forests, meadows, and scenic trails with breathtaking views.')) ?></p>
            <ul class="list-unstyled small text-muted">
              <?php foreach (site_content_rows('activities', 'walk_details', "2-3 hours\nSelf-guided or guided\nDaily, flexible times", "\n") as $detail): ?><li><?= sanitize($detail[0] ?? '') ?></li><?php endforeach; ?>
            </ul>
          </div>
          <div class="card-footer bg-white border-top-0">
            <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-primary btn-sm w-100">Book Now</a>
          </div>
        </div>
      </div>

      <!-- Mountain Biking -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm resort-image-card">
          <img src="<?= sanitize(site_content_image('activities', 'bike_image', 'images/camp_van.webp')) ?>" class="card-img-top" alt="<?= sanitize(site_content('activities', 'bike_title', 'Mountain Biking')) ?>" loading="lazy">
          <div class="card-body">
            <h3 class="h5 card-title"><i class="fas fa-bicycle text-primary me-2"></i><?= sanitize(site_content('activities', 'bike_title', 'Mountain Biking')) ?></h3>
            <p class="card-text"><?= sanitize(site_content('activities', 'bike_copy', 'Explore the rugged terrain on two wheels. We provide quality mountain bikes and safety gear for all skill levels.')) ?></p>
            <ul class="list-unstyled small text-muted">
              <?php foreach (site_content_rows('activities', 'bike_details', "2-5 hours\nBikes provided\nDaily, morning/afternoon", "\n") as $detail): ?><li><?= sanitize($detail[0] ?? '') ?></li><?php endforeach; ?>
            </ul>
          </div>
          <div class="card-footer bg-white border-top-0">
            <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-primary btn-sm w-100">Book Now</a>
          </div>
        </div>
      </div>

      <!-- Cultural Tours -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm resort-image-card">
          <img src="<?= sanitize(site_content_image('activities', 'culture_image', 'images/picnic.webp')) ?>" class="card-img-top" alt="<?= sanitize(site_content('activities', 'culture_title', 'Cultural Tours')) ?>" loading="lazy">
          <div class="card-body">
            <h3 class="h5 card-title"><i class="fas fa-monument text-primary me-2"></i><?= sanitize(site_content('activities', 'culture_title', 'Cultural Tours')) ?></h3>
            <p class="card-text"><?= sanitize(site_content('activities', 'culture_copy', 'Visit local communities and experience the rich cultural heritage of Fort Portal. Meet artisans, learn traditional crafts, and enjoy authentic cuisine.')) ?></p>
            <ul class="list-unstyled small text-muted">
              <?php foreach (site_content_rows('activities', 'culture_details', "Half day or full day\nGuided tours with local experts\nAvailable on request", "\n") as $detail): ?><li><?= sanitize($detail[0] ?? '') ?></li><?php endforeach; ?>
            </ul>
          </div>
          <div class="card-footer bg-white border-top-0">
            <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-primary btn-sm w-100">Book Now</a>
          </div>
        </div>
      </div>

      <!-- Crater Lake Tours -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm resort-image-card">
          <img src="<?= sanitize(site_content_image('activities', 'lake_image', 'images/rooms.webp')) ?>" class="card-img-top" alt="<?= sanitize(site_content('activities', 'lake_title', 'Crater Lake Tours')) ?>" loading="lazy">
          <div class="card-body">
            <h3 class="h5 card-title"><i class="fas fa-water text-primary me-2"></i><?= sanitize(site_content('activities', 'lake_title', 'Crater Lake Tours')) ?></h3>
            <p class="card-text"><?= sanitize(site_content('activities', 'lake_copy', 'Explore the stunning crater lakes surrounding Fort Portal. Perfect for photography, picnics, and peaceful reflection.')) ?></p>
            <ul class="list-unstyled small text-muted">
              <?php foreach (site_content_rows('activities', 'lake_details', "Full day tour\nTransportation included\nDaily departures", "\n") as $detail): ?><li><?= sanitize($detail[0] ?? '') ?></li><?php endforeach; ?>
            </ul>
          </div>
          <div class="card-footer bg-white border-top-0">
            <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-primary btn-sm w-100">Book Now</a>
          </div>
        </div>
      </div>

      <!-- Yoga & Wellness -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm resort-image-card">
          <img src="<?= sanitize(site_content_image('activities', 'wellness_image', 'images/activity_wellness.jpg')) ?>" class="card-img-top" alt="<?= sanitize(site_content('activities', 'wellness_title', 'Yoga & Wellness')) ?>" loading="lazy">
          <div class="card-body">
            <h3 class="h5 card-title"><i class="fas fa-spa text-primary me-2"></i><?= sanitize(site_content('activities', 'wellness_title', 'Yoga & Wellness')) ?></h3>
            <p class="card-text"><?= sanitize(site_content('activities', 'wellness_copy', 'Start your day with sunrise yoga sessions in nature. Professional instructors guide you through rejuvenating practices.')) ?></p>
            <ul class="list-unstyled small text-muted">
              <?php foreach (site_content_rows('activities', 'wellness_details', "1-2 hours\nAll levels welcome\nDaily, 6:30 AM", "\n") as $detail): ?><li><?= sanitize($detail[0] ?? '') ?></li><?php endforeach; ?>
            </ul>
          </div>
          <div class="card-footer bg-white border-top-0">
            <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-primary btn-sm w-100">Book Now</a>
          </div>
        </div>
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
        <div class="modern-room-card h-100">
          <div class="card-header-section">
            <div>
              <h5 class="room-title mb-1"><?= sanitize(site_content('activities', 'attraction_1_title', 'Rwenzori Mountains')) ?></h5>
              <p class="room-meta mb-2"><small class="text-muted"><?= sanitize(site_content('activities', 'attraction_1_meta', 'Trekking • Mountaineering')) ?></small></p>
              <p class="room-description mb-0"><?= sanitize(site_content('activities', 'attraction_1_copy', 'Legendary "Mountains of the Moon" just a short, scenic drive from the resort.')) ?></p>
            </div>
          </div>
          <div class="room-image-wrapper">
            <img src="<?= sanitize(site_content_image('activities', 'attraction_1_image', 'images/view.webp')) ?>" alt="<?= sanitize(site_content('activities', 'attraction_1_title', 'Rwenzori Mountains')) ?>" class="room-image">
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="modern-room-card h-100">
          <div class="card-header-section">
            <div>
              <h5 class="room-title mb-1"><?= sanitize(site_content('activities', 'attraction_2_title', 'Kibale Forest')) ?></h5>
              <p class="room-meta mb-2"><small class="text-muted"><?= sanitize(site_content('activities', 'attraction_2_meta', 'Chimp Tracking • Rainforest')) ?></small></p>
              <p class="room-description mb-0"><?= sanitize(site_content('activities', 'attraction_2_copy', 'Pristine rainforest famous for chimpanzees and rich biodiversity.')) ?></p>
            </div>
          </div>
          <div class="room-image-wrapper">
            <img src="<?= sanitize(site_content_image('activities', 'attraction_2_image', 'images/shade.webp')) ?>" alt="<?= sanitize(site_content('activities', 'attraction_2_title', 'Kibale Forest')) ?>" class="room-image">
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="modern-room-card h-100">
          <div class="card-header-section">
            <div>
              <h5 class="room-title mb-1"><?= sanitize(site_content('activities', 'attraction_3_title', 'Sempaya Hot Springs')) ?></h5>
              <p class="room-meta mb-2"><small class="text-muted"><?= sanitize(site_content('activities', 'attraction_3_meta', 'Semuliki National Park')) ?></small></p>
              <p class="room-description mb-0"><?= sanitize(site_content('activities', 'attraction_3_copy', 'Natural hot springs surrounded by myths, culture, and lush scenery.')) ?></p>
            </div>
          </div>
          <div class="room-image-wrapper">
            <img src="<?= sanitize(site_content_image('activities', 'attraction_3_image', 'images/campfire.webp')) ?>" alt="<?= sanitize(site_content('activities', 'attraction_3_title', 'Sempaya Hot Springs')) ?>" class="room-image">
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="modern-room-card h-100">
          <div class="card-header-section">
            <div>
              <h5 class="room-title mb-1"><?= sanitize(site_content('activities', 'attraction_4_title', 'Tooro Palace')) ?></h5>
              <p class="room-meta mb-2"><small class="text-muted"><?= sanitize(site_content('activities', 'attraction_4_meta', 'Cultural Heritage')) ?></small></p>
              <p class="room-description mb-0"><?= sanitize(site_content('activities', 'attraction_4_copy', 'Historic hilltop palace of the Tooro Kingdom, overlooking Fort Portal.')) ?></p>
            </div>
          </div>
          <div class="room-image-wrapper">
            <img src="<?= sanitize(site_content_image('activities', 'attraction_4_image', 'images/rooms.webp')) ?>" alt="<?= sanitize(site_content('activities', 'attraction_4_title', 'Tooro Palace')) ?>" class="room-image">
          </div>
        </div>
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
