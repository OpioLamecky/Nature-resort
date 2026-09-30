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
  .gallery-filter .btn.active { background: #244f3b; border-color: #244f3b; color: #fff; }
  .resort-photo-card { height: 100%; overflow: hidden; border: 1px solid #e4e8df; border-radius: 6px; }
  .resort-photo-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 3px; background: #eef1e8; }
  .resort-photo-grid img { display: block; width: 100%; aspect-ratio: 4 / 3; object-fit: cover; cursor: zoom-in; }
  .resort-photo-card .card-title { color: #233b30; }
</style>

<nav aria-label="breadcrumb" class="container mb-3 mt-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= base_url('public/') ?>">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Gallery</li>
  </ol>
</nav>

<section class="container py-4">
  <div class="bg-soft-mint rounded p-4 p-md-5 mb-4">
    <h1 class="display-5 mb-2"><?= sanitize(site_content('gallery', 'title', 'Photo Gallery')) ?></h1>
    <p class="lead mb-0"><?= sanitize(site_content('gallery', 'intro', 'Explore the rooms, gardens, food, and experiences at Kalitusi.')) ?></p>
  </div>

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
    <?php foreach ($rooms as $room): $roomImages = resort_accommodation_images($room); ?>
      <div class="col-12 col-sm-6 col-xl-4 gallery-item" data-categories="accommodations" data-room-type="<?= sanitize($room['room_type']) ?>">
        <article class="card resort-photo-card resort-image-card">
          <?php if ($roomImages): ?><div class="resort-photo-grid">
            <?php foreach ($roomImages as $image): ?>
              <img src="<?= sanitize($image['url']) ?>" data-lightbox="<?= sanitize($image['url']) ?>" alt="<?= sanitize($image['alt']) ?>" loading="lazy">
            <?php endforeach; ?>
          </div><?php endif; ?>
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-2">
              <h2 class="card-title h5 mb-1"><?= sanitize($room['name']) ?></h2>
              <span class="badge text-bg-success-subtle text-success-emphasis"><?= sanitize($room['room_type']) ?></span>
            </div>
            <?php if (!empty($room['description'])): ?><p class="card-text small text-muted mb-0"><?= sanitize($room['description']) ?></p><?php endif; ?>
          </div>
        </article>
      </div>
    <?php endforeach; ?>

    <?php foreach ($photoGroups as $category => $photos): foreach ($photos as $photo):
      $url = resort_local_image_url($photo['path']);
      if (!$url) continue;
    ?>
      <div class="col-12 col-sm-6 col-xl-4 gallery-item" data-categories="<?= sanitize($category) ?>">
        <article class="card resort-photo-card resort-image-card">
          <div class="resort-photo-grid"><img src="<?= sanitize($url) ?>" data-lightbox="<?= sanitize($url) ?>" alt="<?= sanitize($photo['name']) ?>" loading="lazy"></div>
          <div class="card-body"><h2 class="card-title h5 mb-1"><?= sanitize($photo['name']) ?></h2><p class="small text-muted mb-0"><?= sanitize($category) ?></p></div>
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