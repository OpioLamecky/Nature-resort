<?php
require_once __DIR__ . '/../includes/bootstrap.php';
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
        <h1 class="display-4 mb-3">Our Accommodations</h1>
        <p class="lead mb-4">From cozy rooms to luxury suites, find the perfect retreat for your stay in Fort Portal's most beautiful setting.</p>
        <a href="<?= base_url('public/booking.php') ?>" class="btn btn-primary btn-lg">Book Your Stay</a>
        <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-primary btn-lg ms-2">Contact Us</a>
      </div>
      <div class="col-lg-4 d-none d-lg-block">
        <img src="<?= asset_url('images/rooms.webp') ?>" alt="Kalitusi Rooms" class="img-fluid rounded shadow">
      </div>
    </div>
  </div>
</section>

<div class="container py-4">
  
  <!-- Room Types Grid -->
  <div class="row g-4 mb-5">
    <?php if ($rooms): ?>
      <?php foreach ($rooms as $room): ?>
        <div class="col-lg-6 mb-4">
          <div class="card room-card h-100 overflow-hidden shadow-lg border-0">
            <?php 
              $roomImages = json_decode($room['images'] ?? '[]', true);
              $imagePath = !empty($roomImages) ? $roomImages[0] : 'images/rooms.webp';
            ?>
            <div class="row g-0">
              <div class="col-md-5">
                <img src="<?= asset_url($imagePath) ?>" class="room-card-img" alt="<?= sanitize($room['name']) ?>">
              </div>
              <div class="col-md-7 d-flex flex-column">
                <div class="card-body d-flex flex-column flex-grow-1">
                  <div class="d-flex justify-content-between align-items-start">
                    <h3 class="card-title h5 mb-1 text-dark-green"><?= sanitize($room['name']) ?></h3>
                    <span class="badge bg-soft-mint text-dark-green border border-light-green ms-2"><?= sanitize($room['room_type']) ?></span>
                  </div>
                  <p class="card-text text-muted mb-3"><small><?= sanitize($room['description']) ?></small></p>
                  
                  <div class="mt-auto">
                    <?php 
                      $amenities = json_decode($room['amenities'] ?? '[]', true);
                      if (!empty($amenities)): 
                    ?>
                      <div class="amenities-grid mb-3">
                        <?php foreach (array_slice($amenities, 0, 4) as $amenity): ?>
                          <div class="amenity-item" title="<?= sanitize($amenity) ?>">
                            <i class="fas fa-check text-success"></i>
                            <span><?= sanitize($amenity) ?></span>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <span class="price-label">From</span>
                        <span class="price-value">UGX <?= number_format($room['price_per_night']) ?></span>
                        <span class="price-per">/ night</span>
                      </div>
                      <a href="<?= base_url('public/booking.php?room=' . $room['accommodation_id']) ?>" class="btn btn-primary btn-sm">Details</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <!-- Fallback static rooms if database is empty -->
      <div class="col-12">
        <div class="alert alert-info">
          <i class="fas fa-info-circle me-2"></i>
          Room information is being updated. Please contact us directly for booking inquiries.
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Child Pricing Information -->
  <div class="row mt-5">
    <div class="col-12">
      <div class="card border-info">
        <div class="card-header bg-info text-white">
          <h4 class="mb-0"><i class="fas fa-child me-2"></i>Child Pricing Policy</h4>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <h5 class="text-primary">Children 10-14 years</h5>
              <p class="mb-3">Pay <strong>50% of the adult rate</strong> for any room type</p>
              
              <h5 class="text-success">Children 4-10 years</h5>
              <p class="mb-3">Pay <strong>fixed 20,000 UGX</strong> per night regardless of room type</p>
              
              <h5 class="text-muted">Children under 4 years</h5>
              <p class="mb-0">Stay <strong>free of charge</strong></p>
            </div>
            <div class="col-md-6">
              <div class="alert alert-light">
                <h6 class="fw-bold">Example Pricing:</h6>
                <ul class="mb-0">
                  <li><strong>Standard Room (2P):</strong> 2 adults + 1 child (10-14) = UGX 180,000 + 90,000 = 270,000</li>
                  <li><strong>Deluxe Room (2P):</strong> 2 adults + 1 child (4-10) = UGX 300,000 + 20,000 = 320,000</li>
                  <li><strong>Suite (3P):</strong> 3 adults + 2 children (4-10) = UGX 450,000 + 40,000 = 490,000</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
          
          

  <?php include __DIR__ . '/../includes/layout_footer.php'; ?>
            
