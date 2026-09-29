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
<section class="bg-soft-mint py-5 rounded mb-4">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <h1 class="display-5 mb-3">Activities & Experiences</h1>
        <p class="lead mb-4">Discover the beauty of Fort Portal through our carefully curated activities. From bird watching to nature walks, every experience is designed to connect you with Uganda's natural wonders.</p>
        <a href="<?= base_url('public/booking.php') ?>" class="btn btn-primary btn-lg">Book Your Experience</a>
      </div>
      <div class="col-lg-5">
        <img src="<?= asset_url('images/view.webp') ?>" alt="Nature Activities" class="img-fluid rounded shadow">
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
        <div class="card h-100 shadow-sm">
          <img src="<?= asset_url('images/birds.webp') ?>" class="card-img-top" alt="Bird Watching" style="height: 250px; object-fit: cover;">
          <div class="card-body">
            <h3 class="h5 card-title"><i class="fas fa-binoculars text-primary me-2"></i>Bird Watching</h3>
            <p class="card-text">Explore over 300 species of birds in their natural habitat. Our expert guides will help you spot rare and beautiful birds unique to the region.</p>
            <ul class="list-unstyled small text-muted">
              <li><i class="fas fa-clock me-1"></i> 3-4 hours</li>
              <li><i class="fas fa-users me-1"></i> Guided tours available</li>
              <li><i class="fas fa-calendar me-1"></i> Daily, 6:00 AM start</li>
            </ul>
          </div>
          <div class="card-footer bg-white border-top-0">
            <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-primary btn-sm w-100">Book Now</a>
          </div>
        </div>
      </div>

      <!-- Nature Walks -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm">
          <img src="<?= asset_url('images/shade.webp') ?>" class="card-img-top" alt="Nature Walks" style="height: 250px; object-fit: cover;">
          <div class="card-body">
            <h3 class="h5 card-title"><i class="fas fa-hiking text-primary me-2"></i>Nature Walks</h3>
            <p class="card-text">Immerse yourself in the pristine wilderness surrounding our resort. Walk through forests, meadows, and scenic trails with breathtaking views.</p>
            <ul class="list-unstyled small text-muted">
              <li><i class="fas fa-clock me-1"></i> 2-3 hours</li>
              <li><i class="fas fa-users me-1"></i> Self-guided or guided</li>
              <li><i class="fas fa-calendar me-1"></i> Daily, flexible times</li>
            </ul>
          </div>
          <div class="card-footer bg-white border-top-0">
            <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-primary btn-sm w-100">Book Now</a>
          </div>
        </div>
      </div>

      <!-- Mountain Biking -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm">
          <img src="<?= asset_url('images/camp_van.webp') ?>" class="card-img-top" alt="Mountain Biking" style="height: 250px; object-fit: cover;">
          <div class="card-body">
            <h3 class="h5 card-title"><i class="fas fa-bicycle text-primary me-2"></i>Mountain Biking</h3>
            <p class="card-text">Explore the rugged terrain on two wheels. We provide quality mountain bikes and safety gear for all skill levels.</p>
            <ul class="list-unstyled small text-muted">
              <li><i class="fas fa-clock me-1"></i> 2-5 hours</li>
              <li><i class="fas fa-users me-1"></i> Bikes provided</li>
              <li><i class="fas fa-calendar me-1"></i> Daily, morning/afternoon</li>
            </ul>
          </div>
          <div class="card-footer bg-white border-top-0">
            <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-primary btn-sm w-100">Book Now</a>
          </div>
        </div>
      </div>

      <!-- Cultural Tours -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm">
          <img src="<?= asset_url('images/picnic.webp') ?>" class="card-img-top" alt="Cultural Tours" style="height: 250px; object-fit: cover;">
          <div class="card-body">
            <h3 class="h5 card-title"><i class="fas fa-monument text-primary me-2"></i>Cultural Tours</h3>
            <p class="card-text">Visit local communities and experience the rich cultural heritage of Fort Portal. Meet artisans, learn traditional crafts, and enjoy authentic cuisine.</p>
            <ul class="list-unstyled small text-muted">
              <li><i class="fas fa-clock me-1"></i> Half day or full day</li>
              <li><i class="fas fa-users me-1"></i> Guided tours with local experts</li>
              <li><i class="fas fa-calendar me-1"></i> Available on request</li>
            </ul>
          </div>
          <div class="card-footer bg-white border-top-0">
            <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-primary btn-sm w-100">Book Now</a>
          </div>
        </div>
      </div>

      <!-- Crater Lake Tours -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm">
          <img src="<?= asset_url('images/rooms.webp') ?>" class="card-img-top" alt="Crater Lakes" style="height: 250px; object-fit: cover;">
          <div class="card-body">
            <h3 class="h5 card-title"><i class="fas fa-water text-primary me-2"></i>Crater Lake Tours</h3>
            <p class="card-text">Explore the stunning crater lakes surrounding Fort Portal. Perfect for photography, picnics, and peaceful reflection.</p>
            <ul class="list-unstyled small text-muted">
              <li><i class="fas fa-clock me-1"></i> Full day tour</li>
              <li><i class="fas fa-users me-1"></i> Transportation included</li>
              <li><i class="fas fa-calendar me-1"></i> Daily departures</li>
            </ul>
          </div>
          <div class="card-footer bg-white border-top-0">
            <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-primary btn-sm w-100">Book Now</a>
          </div>
        </div>
      </div>

      <!-- Yoga & Wellness -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm">
          <img src="<?= asset_url('images/activity_wellness.jpg') ?>" class="card-img-top" alt="Yoga" style="height: 250px; object-fit: cover;">
          <div class="card-body">
            <h3 class="h5 card-title"><i class="fas fa-spa text-primary me-2"></i>Yoga & Wellness</h3>
            <p class="card-text">Start your day with sunrise yoga sessions in nature. Professional instructors guide you through rejuvenating practices.</p>
            <ul class="list-unstyled small text-muted">
              <li><i class="fas fa-clock me-1"></i> 1-2 hours</li>
              <li><i class="fas fa-users me-1"></i> All levels welcome</li>
              <li><i class="fas fa-calendar me-1"></i> Daily, 6:30 AM</li>
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
    <h2 class="display-6 text-center mb-5">Nearby Attractions</h2>
    <div class="row g-4">
      <div class="col-md-6 col-lg-3">
        <div class="modern-room-card h-100">
          <div class="card-header-section">
            <div>
              <h5 class="room-title mb-1">Rwenzori Mountains</h5>
              <p class="room-meta mb-2"><small class="text-muted">Trekking • Mountaineering</small></p>
              <p class="room-description mb-0">Legendary "Mountains of the Moon" just a short, scenic drive from the resort.</p>
            </div>
          </div>
          <div class="room-image-wrapper">
            <img src="<?= asset_url('images/view.webp') ?>" alt="Rwenzori Mountains" class="room-image">
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="modern-room-card h-100">
          <div class="card-header-section">
            <div>
              <h5 class="room-title mb-1">Kibale Forest</h5>
              <p class="room-meta mb-2"><small class="text-muted">Chimp Tracking • Rainforest</small></p>
              <p class="room-description mb-0">Pristine rainforest famous for chimpanzees and rich biodiversity.</p>
            </div>
          </div>
          <div class="room-image-wrapper">
            <img src="<?= asset_url('images/shade.webp') ?>" alt="Kibale Forest" class="room-image">
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="modern-room-card h-100">
          <div class="card-header-section">
            <div>
              <h5 class="room-title mb-1">Sempaya Hot Springs</h5>
              <p class="room-meta mb-2"><small class="text-muted">Semuliki National Park</small></p>
              <p class="room-description mb-0">Natural hot springs surrounded by myths, culture, and lush scenery.</p>
            </div>
          </div>
          <div class="room-image-wrapper">
            <img src="<?= asset_url('images/campfire.webp') ?>" alt="Sempaya Hot Springs" class="room-image">
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="modern-room-card h-100">
          <div class="card-header-section">
            <div>
              <h5 class="room-title mb-1">Tooro Palace</h5>
              <p class="room-meta mb-2"><small class="text-muted">Cultural Heritage</small></p>
              <p class="room-description mb-0">Historic hilltop palace of the Tooro Kingdom, overlooking Fort Portal.</p>
            </div>
          </div>
          <div class="room-image-wrapper">
            <img src="<?= asset_url('images/rooms.webp') ?>" alt="Tooro Palace" class="room-image">
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA Section -->
<section class="bg-primary text-white py-5 mt-4">
  <div class="container text-center">
    <h2 class="display-6 mb-3">Ready for Your Adventure?</h2>
    <p class="lead mb-4">Book your activities now or contact us to create a custom itinerary</p>
    <a href="<?= base_url('public/booking.php') ?>" class="btn btn-outline-light btn-lg me-2">Book Now</a>
    <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-light btn-lg">Contact Us</a>
  </div>
</section>

<?php include __DIR__ . '/../includes/layout_footer.php';
