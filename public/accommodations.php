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
<section class="page-intro-hero accommodation-hero bg-soft-mint">
  <div class="container">
    <div class="row align-items-center g-4 g-lg-5">
      <div class="col-md-7">
        <div class="accommodation-hero__content">
          <h1 class="accommodation-hero__title mb-3"><?= sanitize(site_content('accommodations', 'title', 'Our Accommodations')) ?></h1>
          <p class="accommodation-hero__intro lead mb-4"><?= sanitize(site_content('accommodations', 'intro', "From cozy rooms to luxury suites, find the perfect retreat for your stay in Fort Portal's most beautiful setting.")) ?></p>
          <div class="d-flex flex-wrap gap-2">
            <a href="<?= base_url('public/booking.php') ?>" class="btn btn-primary btn-lg">Book Your Stay</a>
            <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-primary btn-lg">Contact Us</a>
          </div>
        </div>
      </div>
      <div class="col-md-5">
        <img src="<?= sanitize(site_content_image('accommodations', 'hero_image', 'images/cabin.webp')) ?>" alt="Accommodation cottages at Kalitusi Nature Resort" class="accommodation-hero__image" fetchpriority="high">
      </div>
    </div>
  </div>
</section>

<style>
  .stay-filter .btn { font-size: 0.85rem; padding: 0.35rem 0.85rem; border-radius: 20px; transition: all 0.2s; }
  .stay-filter .btn.active { background: #1a4d2e; border-color: #1a4d2e; color: #fff; }
  .stay-card { height: 100%; border: 1px solid #e2ebe4; border-radius: 12px; overflow: hidden; transition: transform 0.2s ease, box-shadow 0.2s ease; background: #fff; }
  .stay-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(26, 77, 46, 0.12); }
  .stay-photo-grid { position: relative; height: 210px; overflow: hidden; background: #eaefe9; }
  .stay-photo-grid img { width: 100%; height: 100%; object-fit: cover; cursor: zoom-in; transition: transform 0.35s ease; }
  .stay-card:hover .stay-photo-grid img { transform: scale(1.04); }
  .stay-type-badge { position: absolute; bottom: 10px; left: 10px; background: rgba(26, 77, 46, 0.88); backdrop-filter: blur(4px); color: #fff; font-size: 0.72rem; font-weight: 600; padding: 0.2rem 0.55rem; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
  .stay-card .card-body { padding: 0.85rem 1rem 0.95rem; }
  .stay-card .card-title { font-size: 1.05rem; font-weight: 700; color: #1a4d2e; margin-bottom: 0.3rem; line-height: 1.3; }
  .stay-desc { font-size: 0.82rem; line-height: 1.4; color: #5c6b60; margin-bottom: 0.45rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
  .stay-amenities { gap: 0.35rem !important; margin-bottom: 0.55rem; }
  .stay-amenity-chip { font-size: 0.72rem; background: #f0f5f1; color: #234d30; padding: 0.15rem 0.45rem; border-radius: 4px; display: inline-flex; align-items: center; }
  .stay-price-wrap { border-top: 1px solid #edf2ee; padding-top: 0.5rem; margin-top: auto; }
  .stay-price-val { font-size: 1.05rem; font-weight: 700; color: #1a4d2e; }
</style>

<div class="container py-3">
  <div class="stay-filter d-flex flex-wrap gap-2 mb-3" aria-label="Filter accommodations by type">
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

  <div class="row g-3 mb-4" id="accommodation-grid">
    <?php if ($rooms): foreach ($rooms as $room): $roomImages = resort_accommodation_images($room); $isDormitory = in_array(strtolower(trim($room['name'])), ['ginger', 'rocket'], true); ?>
      <div class="col-12 col-sm-6 col-lg-4 stay-cell" data-room-type="<?= sanitize($room['room_type']) ?>">
        <article class="card stay-card resort-image-card shadow-sm">
          <?php if ($roomImages): ?>
            <div class="stay-photo-grid">
              <img src="<?= sanitize($roomImages[0]['url']) ?>" alt="<?= sanitize($roomImages[0]['alt']) ?>" data-lightbox="<?= sanitize($roomImages[0]['url']) ?>" loading="lazy">
              <span class="stay-type-badge"><?= sanitize($room['room_type']) ?></span>
            </div>
          <?php endif; ?>
          <div class="card-body d-flex flex-column">
            <h2 class="card-title"><?= sanitize($room['name']) ?></h2>
            <?php if (!empty($room['description'])): ?>
              <p class="stay-desc"><?= sanitize($room['description']) ?></p>
            <?php endif; ?>
            <?php $amenities = json_decode($room['amenities'] ?? '[]', true); if (!empty($amenities)): ?>
              <div class="d-flex flex-wrap stay-amenities">
                <?php foreach (array_slice($amenities, 0, 3) as $amenity): ?>
                  <span class="stay-amenity-chip"><i class="fas fa-check text-success me-1"></i><?= sanitize($amenity) ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
            <div class="stay-price-wrap">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                  <?php if ($isDormitory): ?>
                    <span class="stay-price-val">UGX 60,000</span><small class="text-muted"> / bed</small>
                    <div class="text-muted" style="font-size: 0.72rem;">Whole dorm: UGX 360,000</div>
                  <?php else: ?>
                    <span class="stay-price-val">UGX <?= number_format((float)$room['price_per_night']) ?></span><small class="text-muted"> / night</small>
                  <?php endif; ?>
                </div>
                <span class="small text-muted"><i class="fas fa-user-friends me-1"></i><?= (int)$room['capacity_adults'] ?>+<?= (int)$room['capacity_children'] ?></span>
              </div>
              <a href="<?= base_url('public/booking.php?room=' . (int)$room['accommodation_id']) ?>" class="btn btn-success btn-sm w-100 py-1 fw-semibold">View stay & book</a>
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
            
