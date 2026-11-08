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

<div class="container mt-3">
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= base_url('public/') ?>">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Accommodations</li>
    </ol>
  </nav>
</div>

<!-- Hero Section -->
<section class="page-intro-hero accommodation-hero bg-soft-mint">
  <div class="container">
    <div class="row align-items-center g-4 g-lg-5">
      <div class="col-md-7 text-center text-md-start">
        <div class="accommodation-hero__content">
          <span class="badge rounded-pill px-3 py-2 mb-2" style="background: rgba(27, 138, 90, 0.12); color: #1b8a5a; font-weight: 600;">
            <i class="fas fa-bed me-1"></i> Sanctuary in the Forest
          </span>
          <h1 class="accommodation-hero__title fw-bold mb-3"><?= sanitize(site_content('accommodations', 'title', 'Our Accommodations')) ?></h1>
          <p class="accommodation-hero__intro lead mb-4"><?= sanitize(site_content('accommodations', 'intro', "From cozy rooms to luxury suites, find the perfect retreat for your stay in Fort Portal's most beautiful setting.")) ?></p>
          <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start mb-3">
            <a href="<?= base_url('public/booking.php') ?>" class="btn btn-primary btn-lg rounded-pill px-4">Book Your Stay</a>
            <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-primary btn-lg rounded-pill px-4">Inquire With Us</a>
          </div>
          <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-md-start small text-muted pt-1">
            <span><i class="fas fa-wifi text-success me-1"></i>Free High-Speed Wi-Fi</span>
            <span><i class="fas fa-coffee text-success me-1"></i>Breakfast Included</span>
            <span><i class="fas fa-mountain text-success me-1"></i>Scenic Views</span>
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
  .stay-filter .btn { font-size: 0.85rem; padding: 0.42rem 1rem; border-radius: 999px; font-weight: 600; transition: all 0.2s; }
  .stay-filter .btn.active { background: #1a4d2e; border-color: #1a4d2e; color: #fff; box-shadow: 0 6px 16px rgba(26, 77, 46, 0.2); }
  .accom-scrim-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 1.35rem; }
  @media (max-width: 575px) { .accom-scrim-grid { grid-template-columns: 1fr; } }
  .accom-policy-bento {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid rgba(15, 23, 42, 0.07);
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
    padding: 1.5rem;
    height: 100%;
  }
</style>

<div class="container py-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div class="stay-filter d-flex flex-wrap gap-2" aria-label="Filter accommodations by type">
      <button class="btn btn-outline-success active" type="button" data-room-type="all" aria-pressed="true">
        All Stays <span class="badge rounded-pill text-bg-light ms-1"><?= count($rooms) ?></span>
      </button>
      <?php
        $roomTypes = ['Standard Room', 'Deluxe Room', 'Suite', 'Chalet', 'Glamping Tent'];
        foreach ($roomTypes as $roomType):
          $typeCount = count(array_filter($rooms, static fn($room) => $room['room_type'] === $roomType));
          if ($typeCount):
      ?>
        <button class="btn btn-outline-success" type="button" data-room-type="<?= sanitize($roomType) ?>" aria-pressed="false">
          <?= sanitize($roomType) ?> <span class="badge rounded-pill text-bg-light ms-1"><?= $typeCount ?></span>
        </button>
      <?php endif; endforeach; ?>
    </div>
    <span class="text-muted small"><i class="fas fa-check-circle text-success me-1"></i>Instant confirmation available</span>
  </div>

  <div class="accom-scrim-grid mb-5" id="accommodation-grid">
    <?php if ($rooms): foreach ($rooms as $room): $roomImages = resort_accommodation_images($room); $isDormitory = in_array(strtolower(trim($room['name'])), ['ginger', 'rocket'], true); ?>
      <div class="stay-cell" data-room-type="<?= sanitize($room['room_type']) ?>">
        <article class="scrim-card">
          <?php if ($roomImages): ?>
            <img class="scrim-card-bg" src="<?= sanitize($roomImages[0]['url']) ?>" alt="<?= sanitize($roomImages[0]['alt']) ?>" loading="lazy">
          <?php endif; ?>
          <div class="scrim-badge-top">
            <span class="badge-dot"></span>
            <?= sanitize($room['room_type']) ?>
          </div>
          <div class="scrim-badge-right" title="<?= (int)$room['capacity_adults'] ?> Adults + <?= (int)$room['capacity_children'] ?> Children">
            <i class="fas fa-user-friends"></i>
          </div>
          <div class="scrim-card-content">
            <div class="scrim-card-header">
              <h2 class="scrim-card-title"><?= sanitize($room['name']) ?></h2>
            </div>
            <?php if (!empty($room['description'])): ?>
              <p class="scrim-card-subtitle"><?= sanitize($room['description']) ?></p>
            <?php endif; ?>
            <div class="scrim-card-specs">
              <span class="scrim-spec-item"><i class="fas fa-user"></i> <?= (int)$room['capacity_adults'] ?> Adults</span>
              <span class="scrim-spec-divider"></span>
              <span class="scrim-spec-item"><i class="fas fa-child"></i> <?= (int)$room['capacity_children'] ?> Children</span>
              <span class="scrim-spec-divider"></span>
              <span class="scrim-spec-item"><i class="fas fa-bed"></i> <?= sanitize($room['room_type']) ?></span>
            </div>
            <div class="scrim-card-action-row">
              <div class="scrim-price-pill">
                <?php if ($isDormitory): ?>
                  UGX 60k <small>/bed</small>
                <?php else: ?>
                  UGX <?= number_format((float)$room['price_per_night']) ?> <small>/night</small>
                <?php endif; ?>
              </div>
              <a href="<?= base_url('public/booking.php?room=' . (int)$room['accommodation_id']) ?>" class="btn-scrim-cta">
                Reserve Now &rarr;
              </a>
            </div>
          </div>
        </article>
      </div>
    <?php endforeach; else: ?>
      <div class="col-12"><div class="alert alert-info">Room information is being updated. Please contact us directly for booking inquiries.</div></div>
    <?php endif; ?>
  </div>

  <!-- Family & Child Pricing Policy + Included Amenities (Pinterest Bento Section) -->
  <div class="row g-4 mt-2 mb-4">
    <div class="col-lg-7">
      <div class="accom-policy-bento">
        <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
          <span class="d-inline-flex align-items-center justify-content-center rounded-4" style="width: 48px; height: 48px; background: rgba(27, 138, 90, 0.12); color: #1b8a5a; font-size: 1.25rem;">
            <i class="fas fa-child"></i>
          </span>
          <div>
            <span class="badge rounded-pill px-2 py-1 mb-1" style="background: rgba(27, 138, 90, 0.1); color: #1b8a5a; font-size: 0.7rem;">Family Friendly</span>
            <h3 class="h5 fw-bold mb-0"><?= sanitize(site_content('accommodations', 'child_policy_title', 'Child & Family Pricing Policy')) ?></h3>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-md-4">
            <div class="p-3 rounded-4 h-100" style="background: #f0fdf4; border: 1px solid rgba(22, 163, 74, 0.14);">
              <span class="badge rounded-pill bg-success mb-2">Free Stay</span>
              <h4 class="h6 fw-bold mb-1"><?= sanitize(site_content('accommodations', 'child_under_4_title', 'Children under 4 years')) ?></h4>
              <p class="small text-muted mb-0"><?= sanitize(site_content('accommodations', 'child_under_4_copy', 'Stay free of charge when sharing with parents')) ?></p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3 rounded-4 h-100" style="background: #f8fafc; border: 1px solid rgba(15, 23, 42, 0.08);">
              <span class="badge rounded-pill mb-2" style="background: rgba(37, 99, 235, 0.12); color: #1d4ed8;">Flat Rate</span>
              <h4 class="h6 fw-bold mb-1"><?= sanitize(site_content('accommodations', 'child_4_10_title', 'Children 4-10 years')) ?></h4>
              <p class="small text-muted mb-0"><?= sanitize(site_content('accommodations', 'child_4_10_copy', 'Pay fixed 20,000 UGX per night regardless of room type')) ?></p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3 rounded-4 h-100" style="background: #fffbeb; border: 1px solid rgba(245, 158, 11, 0.2);">
              <span class="badge rounded-pill mb-2" style="background: rgba(217, 119, 6, 0.15); color: #b45309;">50% Rate</span>
              <h4 class="h6 fw-bold mb-1"><?= sanitize(site_content('accommodations', 'child_10_14_title', 'Children 10-14 years')) ?></h4>
              <p class="small text-muted mb-0"><?= sanitize(site_content('accommodations', 'child_10_14_copy', 'Pay 50% of the adult rate for any room type')) ?></p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="accom-policy-bento" style="background: linear-gradient(145deg, #ffffff 0%, #f4f9f6 100%);">
        <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
          <span class="d-inline-flex align-items-center justify-content-center rounded-4" style="width: 48px; height: 48px; background: rgba(37, 99, 235, 0.1); color: #2563eb; font-size: 1.2rem;">
            <i class="fas fa-calculator"></i>
          </span>
          <div>
            <span class="badge rounded-pill px-2 py-1 mb-1" style="background: rgba(37, 99, 235, 0.1); color: #2563eb; font-size: 0.7rem;">Transparent Rates</span>
            <h3 class="h5 fw-bold mb-0"><?= sanitize(site_content('accommodations', 'example_title', 'Example Family Calculations')) ?></h3>
          </div>
        </div>
        <ul class="list-unstyled mb-0 small">
          <li class="py-2 border-bottom d-flex align-items-start gap-2">
            <i class="fas fa-check-circle text-success mt-1"></i>
            <span><?= sanitize(site_content('accommodations', 'example_standard', 'Standard Room (2P): 2 adults + 1 child (10-14) = UGX 180,000 + 90,000 = 270,000')) ?></span>
          </li>
          <li class="py-2 border-bottom d-flex align-items-start gap-2">
            <i class="fas fa-check-circle text-success mt-1"></i>
            <span><?= sanitize(site_content('accommodations', 'example_deluxe', 'Deluxe Room (2P): 2 adults + 1 child (4-10) = UGX 300,000 + 20,000 = 320,000')) ?></span>
          </li>
          <li class="pt-2 d-flex align-items-start gap-2">
            <i class="fas fa-check-circle text-success mt-1"></i>
            <span><?= sanitize(site_content('accommodations', 'example_suite', 'Suite (3P): 3 adults + 2 children (4-10) = UGX 450,000 + 40,000 = 490,000')) ?></span>
          </li>
        </ul>
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
            
