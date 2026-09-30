<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/accommodation_images.php';
$title = 'Accommodations | Kalitusi Nature Resort';

// Fetch accommodations from database
$rooms = [];
try {
    $stmt = $pdo->query("
        SELECT * FROM accommodations 
        WHERE status = 'active' 
        ORDER BY display_order, price_per_night
    ");
    $rooms = $stmt->fetchAll();
} catch (Exception $e) {
    // Fallback data if table doesn't exist yet
    $rooms = [];
}

include __DIR__ . '/../includes/layout_header.php';
?>

<nav aria-label="breadcrumb" class="mb-3 mt-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= base_url('public/') ?>">Home</a></li>
    <li class="breadcrumb-item active">Accommodations</li>
  </ol>
</nav>

<!-- Hero Section -->
<section class="bg-soft-mint py-5 rounded mb-4">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1 class="display-4 mb-3"><?= sanitize(site_content('accommodations', 'title', 'Our Accommodations')) ?></h1>
        <p class="lead mb-4"><?= sanitize(site_content('accommodations', 'intro', "From cozy rooms to luxury suites, find the perfect retreat for your stay in Fort Portal's most beautiful setting.")) ?></p>
        <a href="<?= base_url('public/booking.php') ?>" class="btn btn-primary btn-lg">Book Your Stay</a>
        <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-primary btn-lg ms-2">Contact Us</a>
      </div>
      <div class="col-lg-4 d-none d-lg-block">
        <img src="<?= sanitize(site_content_image('accommodations', 'hero_image', 'images/rooms.webp')) ?>" alt="Kalitusi Rooms" class="img-fluid rounded shadow">
      </div>
    </div>
  </div>
</section>

<style>
  .stay-filter .btn.active { background: #244f3b; border-color: #244f3b; color: #fff; }
  .stay-card { height: 100%; overflow: hidden; border: 1px solid #e4e8df; border-radius: 6px; }
  .stay-photo-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 3px; background: #eef1e8; }
  .stay-photo-grid img { display: block; width: 100%; aspect-ratio: 4 / 3; object-fit: cover; cursor: zoom-in; }
  .stay-card .card-title { color: #233b30; }
</style>

<div class="container py-4">
  <div class="stay-filter d-flex flex-wrap gap-2 mb-4" aria-label="Filter accommodations by type">
    <button class="btn btn-outline-success active" type="button" data-room-type="all" aria-pressed="true">All stays <span class="badge text-bg-light ms-1"><?= count($rooms) ?></span></button>
    <?php
      $roomTypes = ['Standard Room', 'Deluxe Room', 'Suite', 'Chalet', 'Glamping Tent'];
      foreach ($roomTypes as $roomType):
        $typeCount = count(array_filter($rooms, static fn($room) => $room['room_type'] === $roomType));
        if ($typeCount):
    ?>
      <button class="btn btn-outline-success" type="button" data-room-type="<?= sanitize($roomType) ?>" aria-pressed="false"><?= sanitize($roomType) ?> <span class="badge text-bg-light ms-1"><?= $typeCount ?></span></button>
    <?php endif; endforeach; ?>
  </div>

  <div class="row g-4 mb-5" id="accommodation-grid">
    <?php if ($rooms): foreach ($rooms as $room): $roomImages = resort_accommodation_images($room); ?>
      <div class="col-12 col-sm-6 col-xl-4 stay-cell" data-room-type="<?= sanitize($room['room_type']) ?>">
        <article class="card stay-card resort-image-card">
          <?php if ($roomImages): ?>
            <div class="stay-photo-grid">
              <?php foreach ($roomImages as $image): ?>
                <img src="<?= sanitize($image['url']) ?>" alt="<?= sanitize($image['alt']) ?>" data-lightbox="<?= sanitize($image['url']) ?>" loading="lazy">
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
              <h2 class="card-title h5 mb-0"><?= sanitize($room['name']) ?></h2>
              <span class="badge text-bg-success-subtle text-success-emphasis"><?= sanitize($room['room_type']) ?></span>
            </div>
            <p class="text-muted small mb-3"><?= sanitize($room['description'] ?? '') ?></p>
            <?php $amenities = json_decode($room['amenities'] ?? '[]', true); if (!empty($amenities)): ?>
              <div class="d-flex flex-wrap gap-2 mb-3">
                <?php foreach (array_slice($amenities, 0, 4) as $amenity): ?><span class="small text-muted"><i class="fas fa-check text-success me-1"></i><?= sanitize($amenity) ?></span><?php endforeach; ?>
              </div>
            <?php endif; ?>
            <div class="mt-auto d-flex justify-content-between align-items-center gap-2">
              <span class="fw-semibold text-success">UGX <?= number_format((float)$room['price_per_night']) ?><small class="text-muted fw-normal"> / night</small></span>
              <a href="<?= base_url('public/booking.php?room=' . (int)$room['accommodation_id']) ?>" class="btn btn-success btn-sm">View stay</a>
            </div>
          </div>
        </article>
      </div>
    <?php endforeach; else: ?>
      <div class="col-12"><div class="alert alert-info">Room information is being updated. Please contact us directly for booking inquiries.</div></div>
    <?php endif; ?>
  </div>

  <!-- Child Pricing Information -->
  <div class="row mt-5">
    <div class="col-12">
      <div class="card border-info">
        <div class="card-header bg-info text-white">
          <h4 class="mb-0"><i class="fas fa-child me-2"></i><?= sanitize(site_content('accommodations', 'child_policy_title', 'Child Pricing Policy')) ?></h4>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <h5 class="text-primary"><?= sanitize(site_content('accommodations', 'child_10_14_title', 'Children 10-14 years')) ?></h5>
              <p class="mb-3"><?= sanitize(site_content('accommodations', 'child_10_14_copy', 'Pay 50% of the adult rate for any room type')) ?></p>
              
              <h5 class="text-success"><?= sanitize(site_content('accommodations', 'child_4_10_title', 'Children 4-10 years')) ?></h5>
              <p class="mb-3"><?= sanitize(site_content('accommodations', 'child_4_10_copy', 'Pay fixed 20,000 UGX per night regardless of room type')) ?></p>
              
              <h5 class="text-muted"><?= sanitize(site_content('accommodations', 'child_under_4_title', 'Children under 4 years')) ?></h5>
              <p class="mb-0"><?= sanitize(site_content('accommodations', 'child_under_4_copy', 'Stay free of charge')) ?></p>
            </div>
            <div class="col-md-6">
              <div class="alert alert-light">
                <h6 class="fw-bold"><?= sanitize(site_content('accommodations', 'example_title', 'Example Pricing')) ?>:</h6>
                <ul class="mb-0">
                  <li><?= sanitize(site_content('accommodations', 'example_standard', 'Standard Room (2P): 2 adults + 1 child (10-14) = UGX 180,000 + 90,000 = 270,000')) ?></li>
                  <li><?= sanitize(site_content('accommodations', 'example_deluxe', 'Deluxe Room (2P): 2 adults + 1 child (4-10) = UGX 300,000 + 20,000 = 320,000')) ?></li>
                  <li><?= sanitize(site_content('accommodations', 'example_suite', 'Suite (3P): 3 adults + 2 children (4-10) = UGX 450,000 + 40,000 = 490,000')) ?></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
          
<script>
document.querySelectorAll('[data-room-type]').forEach(button => button.addEventListener('click', () => {
  document.querySelectorAll('.stay-filter [data-room-type]').forEach(filter => {
    const active = filter === button;
    filter.classList.toggle('active', active);
    filter.setAttribute('aria-pressed', String(active));
  });
  document.querySelectorAll('.stay-cell').forEach(card => {
    card.classList.toggle('d-none', button.dataset.roomType !== 'all' && card.dataset.roomType !== button.dataset.roomType);
  });
}));
</script>

  <?php include __DIR__ . '/../includes/layout_footer.php'; ?>
            
