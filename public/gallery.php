<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$title = 'Photo Gallery | Kalitusi Nature Resort';

include __DIR__ . '/../includes/layout_header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3 mt-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= base_url('public/') ?>">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Gallery</li>
  </ol>
</nav>

<!-- Page Header -->
<section class="text-center py-5 bg-soft-mint rounded mb-4">
  <div class="container">
    <h1 class="display-4 mb-3">Photo Gallery</h1>
    <p class="lead">Experience the beauty of Kalitusi Nature Resort through our collection of stunning images</p>
  </div>
</section>

<!-- Gallery Categories -->
<div class="container mb-4">
  <div class="btn-group btn-group-sm" role="group" aria-label="Gallery filters">
    <button type="button" class="btn btn-outline-primary active" data-filter="all">All Photos</button>
    <button type="button" class="btn btn-outline-primary" data-filter="accommodations">Accommodations</button>
    <button type="button" class="btn btn-outline-primary" data-filter="nature">Nature</button>
    <button type="button" class="btn btn-outline-primary" data-filter="dining">Dining</button>
    <button type="button" class="btn btn-outline-primary" data-filter="activities">Activities</button>
  </div>
</div>

<!-- Gallery Grid -->
<div class="container mb-5">
  <div class="row g-3 gallery-grid">
    
    <!-- Accommodations -->
    <div class="col-md-4 col-sm-6 gallery-item" data-category="accommodations">
      <div class="card shadow-sm h-100">
        <img src="<?= asset_url('images/rooms.webp') ?>" 
             data-lightbox="<?= asset_url('images/rooms.webp') ?>" 
             alt="Luxury Suite" 
             class="card-img-top" 
             style="height: 250px; object-fit: cover; cursor: pointer;">
        <div class="card-body p-2">
          <p class="small mb-0"><strong>Luxury Suite</strong></p>
        </div>
      </div>
    </div>
    
    <div class="col-md-4 col-sm-6 gallery-item" data-category="accommodations">
      <div class="card shadow-sm h-100">
        <img src="<?= asset_url('images/cabin.webp') ?>" 
             data-lightbox="<?= asset_url('images/cabin.webp') ?>" 
             alt="Forest Cabin" 
             class="card-img-top" 
             style="height: 250px; object-fit: cover; cursor: pointer;">
        <div class="card-body p-2">
          <p class="small mb-0"><strong>Forest Cabin</strong></p>
        </div>
      </div>
    </div>
    
    <div class="col-md-4 col-sm-6 gallery-item" data-category="accommodations">
      <div class="card shadow-sm h-100">
        <img src="<?= asset_url('images/shade.webp') ?>" 
             data-lightbox="<?= asset_url('images/shade.webp') ?>" 
             alt="Forest Chalet" 
             class="card-img-top" 
             style="height: 250px; object-fit: cover; cursor: pointer;">
        <div class="card-body p-2">
          <p class="small mb-0"><strong>Forest Chalet</strong></p>
        </div>
      </div>
    </div>
    
    <!-- Nature -->
    <div class="col-md-4 col-sm-6 gallery-item" data-category="nature">
      <div class="card shadow-sm h-100">
        <img src="<?= asset_url('images/view.webp') ?>" 
             data-lightbox="<?= asset_url('images/view.webp') ?>" 
             alt="Forest View" 
             class="card-img-top" 
             style="height: 250px; object-fit: cover; cursor: pointer;">
        <div class="card-body p-2">
          <p class="small mb-0"><strong>Forest Landscape</strong></p>
        </div>
      </div>
    </div>
    
    <div class="col-md-4 col-sm-6 gallery-item" data-category="nature">
      <div class="card shadow-sm h-100">
        <img src="<?= asset_url('images/birds.webp') ?>" 
             data-lightbox="<?= asset_url('images/birds.webp') ?>" 
             alt="Birds" 
             class="card-img-top" 
             style="height: 250px; object-fit: cover; cursor: pointer;">
        <div class="card-body p-2">
          <p class="small mb-0"><strong>Bird Watching</strong></p>
        </div>
      </div>
    </div>
    
    <div class="col-md-4 col-sm-6 gallery-item" data-category="nature">
      <div class="card shadow-sm h-100">
        <img src="<?= asset_url('images/birds1.webp') ?>" 
             data-lightbox="<?= asset_url('images/birds1.webp') ?>" 
             alt="Birds in Flight" 
             class="card-img-top" 
             style="height: 250px; object-fit: cover; cursor: pointer;">
        <div class="card-body p-2">
          <p class="small mb-0"><strong>Forest Canopy</strong></p>
        </div>
      </div>
    </div>
    
    <!-- Dining -->
    <div class="col-md-4 col-sm-6 gallery-item" data-category="dining">
      <div class="card shadow-sm h-100">
        <img src="<?= asset_url('images/food1.webp') ?>" 
             data-lightbox="<?= asset_url('images/food1.webp') ?>" 
             alt="Dining" 
             class="card-img-top" 
             style="height: 250px; object-fit: cover; cursor: pointer;">
        <div class="card-body p-2">
          <p class="small mb-0"><strong>Restaurant Interior</strong></p>
        </div>
      </div>
    </div>
    
    <div class="col-md-4 col-sm-6 gallery-item" data-category="dining">
      <div class="card shadow-sm h-100">
        <img src="<?= asset_url('images/food2.webp') ?>" 
             data-lightbox="<?= asset_url('images/food2.webp') ?>" 
             alt="Cuisine" 
             class="card-img-top" 
             style="height: 250px; object-fit: cover; cursor: pointer;">
        <div class="card-body p-2">
          <p class="small mb-0"><strong>Gourmet Cuisine</strong></p>
        </div>
      </div>
    </div>
    
    <div class="col-md-4 col-sm-6 gallery-item" data-category="dining">
      <div class="card shadow-sm h-100">
        <img src="<?= asset_url('images/food3.webp') ?>" 
             data-lightbox="<?= asset_url('images/food3.webp') ?>" 
             alt="Outdoor Dining" 
             class="card-img-top" 
             style="height: 250px; object-fit: cover; cursor: pointer;">
        <div class="card-body p-2">
          <p class="small mb-0"><strong>Outdoor Dining</strong></p>
        </div>
      </div>
    </div>
    
    <!-- Activities -->
    <div class="col-md-4 col-sm-6 gallery-item" data-category="activities">
      <div class="card shadow-sm h-100">
        <img src="<?= asset_url('images/tent.webp') ?>" 
             data-lightbox="<?= asset_url('images/tent.webp') ?>" 
             alt="Glamping" 
             class="card-img-top" 
             style="height: 250px; object-fit: cover; cursor: pointer;">
        <div class="card-body p-2">
          <p class="small mb-0"><strong>Glamping Tents</strong></p>
        </div>
      </div>
    </div>
    
    <div class="col-md-4 col-sm-6 gallery-item" data-category="activities">
      <div class="card shadow-sm h-100">
        <img src="<?= asset_url('images/camp_van.webp') ?>" 
             data-lightbox="<?= asset_url('images/camp_van.webp') ?>" 
             alt="Safari Vehicle" 
             class="card-img-top" 
             style="height: 250px; object-fit: cover; cursor: pointer;">
        <div class="card-body p-2">
          <p class="small mb-0"><strong>Safari & Tours</strong></p>
        </div>
      </div>
    </div>
    
    <div class="col-md-4 col-sm-6 gallery-item" data-category="activities">
      <div class="card shadow-sm h-100">
        <img src="<?= asset_url('images/campfire.webp') ?>" 
             data-lightbox="<?= asset_url('images/campfire.webp') ?>" 
             alt="Campfire" 
             class="card-img-top" 
             style="height: 250px; object-fit: cover; cursor: pointer;">
        <div class="card-body p-2">
          <p class="small mb-0"><strong>Evening Campfire</strong></p>
        </div>
      </div>
    </div>
    
  </div>
</div>

<!-- Gallery Filter Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const filterButtons = document.querySelectorAll('[data-filter]');
  const galleryItems = document.querySelectorAll('.gallery-item');
  
  filterButtons.forEach(button => {
    button.addEventListener('click', function() {
      const filter = this.getAttribute('data-filter');
      
      // Update active button
      filterButtons.forEach(btn => btn.classList.remove('active'));
      this.classList.add('active');
      
      // Filter items
      galleryItems.forEach(item => {
        if (filter === 'all' || item.getAttribute('data-category') === filter) {
          item.style.display = '';
        } else {
          item.style.display = 'none';
        }
      });
    });
  });
});
</script>

<!-- CTA Section -->
<section class="bg-primary text-white py-5">
  <div class="container text-center">
    <h2 class="display-6 mb-3">Experience It Yourself</h2>
    <p class="lead mb-4">These photos are just a glimpse. Visit us and create your own memories</p>
    <a href="<?= base_url('public/booking.php') ?>" class="btn btn-outline-light btn-lg me-2">Book Your Stay</a>
    <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-light btn-lg">Contact Us</a>
  </div>
</section>

<?php include __DIR__ . '/../includes/layout_footer.php';
