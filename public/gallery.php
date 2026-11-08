<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/accommodation_images.php';
$title = 'Photo Gallery | Kalitusi Nature Resort';

$rooms = [];
try {
    $rooms = $pdo->query(
        "SELECT accommodation_id, room_type, name, description, images
         FROM accommodations WHERE status = 'active'
         ORDER BY room_type, display_order, name"
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Gallery accommodation query failed: ' . $e->getMessage());
}

$galleryDefaults = [
  'Nature' => ['field' => 'nature_photos', 'value' => "Forest Landscape | images/view.webp\nBird Watching | images/birds.webp\nForest Canopy | images/birds1.webp\nNature Walk | images/shade.webp"],
  'Dining' => ['field' => 'dining_photos', 'value' => "Resort Dining 1 | images/food1.webp\nResort Dining 2 | images/food2.webp\nResort Dining 3 | images/food3.webp\nResort Dining 4 | images/food4.webp\nResort Dining 5 | images/food5.webp\nResort Dining 6 | images/food6.webp\nResort Dining 7 | images/food7.webp\nResort Dining 8 | images/food8.webp\nResort Dining 9 | images/food9.webp"],
  'Activities' => ['field' => 'activity_photos', 'value' => "Glamping Tents | images/tent.webp\nSafari & Tours | images/camp_van.webp\nEvening Campfire | images/campfire.webp\nOutdoor Picnic | images/picnic.webp\nResort Grounds | images/DSC00026.webp"],
];
$photoGroups = [];
foreach ($galleryDefaults as $category => $definition) {
  $rows = site_content_rows('gallery', $definition['field'], $definition['value']);
  $photoGroups[$category] = array_map(static function (array $row): array {
    return ['name' => $row[0] ?? '', 'path' => $row[1] ?? ''];
  }, $rows);
}

$roomTypes = array_values(array_unique(array_column($rooms, 'room_type')));
include __DIR__ . '/../includes/layout_header.php';
?>
<style>
  .gallery-filter .btn { border-radius: 999px; font-size: 0.85rem; padding: 0.35rem 0.95rem; transition: all 0.2s ease; }
  .gallery-filter .btn.active { background: #244f3b; border-color: #244f3b; color: #fff; }
</style>

<div class="container mt-3">
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= base_url('public/') ?>">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Gallery</li>
    </ol>
  </nav>
</div>

<!-- Hero Section -->
<section class="page-intro-hero bg-soft-mint">
  <div class="container">
    <div class="row align-items-center g-4 g-lg-5">
      <div class="col-lg-7 text-center text-lg-start">
        <span class="badge rounded-pill px-3 py-2 mb-2" style="background: rgba(27, 138, 90, 0.12); color: #1b8a5a; font-weight: 600;">
          <i class="fas fa-camera-retro me-1"></i> Visual Journey
        </span>
        <h1 class="display-5 fw-bold mb-3"><?= sanitize(site_content('gallery', 'title', 'Photo Gallery')) ?></h1>
        <p class="lead mb-4 mx-auto mx-lg-0"><?= sanitize(site_content('gallery', 'intro', 'Explore the luxury cottages, lush botanical gardens, farm-to-table cuisine, and unforgettable nature experiences at Kalitusi.')) ?></p>
        <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-lg-start">
          <a href="<?= base_url('public/booking.php') ?>" class="btn btn-primary rounded-pill px-4">Book Your Stay</a>
          <a href="<?= base_url('public/accommodations.php') ?>" class="btn btn-outline-primary rounded-pill px-4">View Rooms</a>
        </div>
      </div>
      <div class="col-lg-5">
        <img src="<?= sanitize(site_content_image('gallery', 'hero_image', 'images/view.webp')) ?>" alt="Kalitusi Nature Resort Gallery" onerror="this.src='<?= asset_url('images/rooms.webp') ?>'">
      </div>
    </div>
  </div>
</section>

<section class="container py-4">

  <div class="gallery-filter d-flex flex-wrap gap-2 mb-4" role="group" aria-label="Gallery categories">
    <button type="button" class="btn btn-outline-success active" data-filter="all" aria-pressed="true">All photos</button>
    <button type="button" class="btn btn-outline-success" data-filter="accommodations" aria-pressed="false">Accommodations</button>
    <?php foreach ($roomTypes as $roomType): ?>
      <button type="button" class="btn btn-outline-success" data-filter="<?= sanitize($roomType) ?>" aria-pressed="false"><?= sanitize($roomType) ?></button>
    <?php endforeach; ?>
    <?php foreach (array_keys($photoGroups) as $category): ?>
      <button type="button" class="btn btn-outline-success" data-filter="<?= sanitize($category) ?>" aria-pressed="false"><?= sanitize($category) ?></button>
    <?php endforeach; ?>
  </div>

  <div class="row g-4 gallery-grid">
    <?php foreach ($rooms as $room): $roomImages = resort_accommodation_images($room); if (!$roomImages) continue; $primaryImage = $roomImages[0]; ?>
      <div class="col-12 col-sm-6 col-lg-4 gallery-item" data-categories="accommodations" data-room-type="<?= sanitize($room['room_type']) ?>">
        <article class="scrim-card scrim-card-compact gallery-scrim-card">
          <img class="scrim-card-bg" src="<?= sanitize($primaryImage['url']) ?>" data-lightbox="<?= sanitize($primaryImage['url']) ?>" alt="<?= sanitize($room['name']) ?>" loading="lazy">
          <?php foreach (array_slice($roomImages, 1) as $extraImg): ?>
            <img src="<?= sanitize($extraImg['url']) ?>" data-lightbox="<?= sanitize($extraImg['url']) ?>" alt="<?= sanitize($extraImg['alt']) ?>" class="d-none" loading="lazy">
          <?php endforeach; ?>
          <div class="scrim-badge-top">
            <span class="badge-dot"></span>
            <?= sanitize($room['room_type']) ?>
          </div>
          <div class="scrim-badge-right" title="Click to enlarge"><i class="fas fa-expand-alt"></i></div>
          <div class="scrim-card-content">
            <div class="scrim-card-header">
              <h2 class="scrim-card-title"><?= sanitize($room['name']) ?></h2>
            </div>
            <?php if (!empty($room['description'])): ?>
              <p class="scrim-card-subtitle mb-0"><?= sanitize($room['description']) ?></p>
            <?php endif; ?>
          </div>
        </article>
      </div>
    <?php endforeach; ?>

    <?php foreach ($photoGroups as $category => $photos): foreach ($photos as $photo):
      $url = resort_local_image_url($photo['path']);
      if (!$url) continue;
    ?>
      <div class="col-12 col-sm-6 col-lg-4 gallery-item" data-categories="<?= sanitize($category) ?>">
        <article class="scrim-card scrim-card-compact gallery-scrim-card">
          <img class="scrim-card-bg" src="<?= sanitize($url) ?>" data-lightbox="<?= sanitize($url) ?>" alt="<?= sanitize($photo['name']) ?>" loading="lazy">
          <div class="scrim-badge-top is-gold">
            <span class="badge-dot"></span>
            <?= sanitize($category) ?>
          </div>
          <div class="scrim-badge-right" title="Click to enlarge"><i class="fas fa-expand-alt"></i></div>
          <div class="scrim-card-content">
            <div class="scrim-card-header">
              <h2 class="scrim-card-title"><?= sanitize($photo['name']) ?></h2>
            </div>
            <p class="scrim-card-subtitle mb-0"><i class="fas fa-camera"></i> <?= sanitize($category) ?> Collection</p>
          </div>
        </article>
      </div>
    <?php endforeach; endforeach; ?>
  </div>
</section>

<section class="bg-primary text-white py-5">
  <div class="container text-center">
    <h2 class="h2 mb-3"><?= sanitize(site_content('gallery', 'cta_title', 'Experience It Yourself')) ?></h2>
    <p class="lead mb-4"><?= sanitize(site_content('gallery', 'cta_copy', 'Come stay among the gardens and forest of Fort Portal.')) ?></p>
    <a href="<?= base_url('public/booking.php') ?>" class="btn btn-outline-light me-2">Book your stay</a>
    <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-light">Contact us</a>
  </div>
</section>

<script>
document.querySelectorAll('.gallery-filter [data-filter]').forEach(button => button.addEventListener('click', () => {
  document.querySelectorAll('.gallery-filter [data-filter]').forEach(filter => {
    const active = filter === button;
    filter.classList.toggle('active', active);
    filter.setAttribute('aria-pressed', String(active));
  });
  document.querySelectorAll('.gallery-item').forEach(card => {
    const categories = card.dataset.categories.split(' ');
    const matches = categories.includes(button.dataset.filter) || card.dataset.roomType === button.dataset.filter;
    card.classList.toggle('d-none', button.dataset.filter !== 'all' && !matches);
  });
}));
</script>
<?php include __DIR__ . '/../includes/layout_footer.php'; ?>