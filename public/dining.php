<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$title = 'Dining & Cuisine | Kalitusi Nature Resort';
include __DIR__ . '/../includes/layout_header.php';
?>

<nav aria-label="breadcrumb" class="mb-3 mt-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= base_url('public/') ?>">Home</a></li>
    <li class="breadcrumb-item active">Dining</li>
  </ol>
</nav>

<!-- Hero Section -->
<section class="bg-soft-mint py-5 rounded mb-4">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1 class="display-4 mb-3">Dining & Cuisine</h1>
        <p class="lead mb-4">Experience authentic Ugandan flavors and international cuisine in our scenic restaurant overlooking the mountains.</p>
        <a href="<?= base_url('public/booking.php') ?>" class="btn btn-primary btn-lg">Make a Reservation</a>
      </div>
      <div class="col-lg-4 d-none d-lg-block">
        <img src="<?= asset_url('images/food1.webp') ?>" alt="Dining at Kalitusi" class="img-fluid rounded shadow">
      </div>
    </div>
  </div>
</section>

<div class="container py-4">

  <!-- Main Restaurant -->
  <section class="mb-5">
    <div class="row align-items-center">
      <div class="col-md-6">
        <img src="<?= asset_url('images/food2.webp') ?>" alt="Restaurant" class="img-fluid rounded shadow">
      </div>
      <div class="col-md-6">
        <h2 class="h3 mb-3"><i class="fas fa-utensils text-primary me-2"></i>The Mountain View Restaurant</h2>
        <p>Our main restaurant offers a refined dining experience with panoramic views of the Rwenzori Mountains. Enjoy fresh, locally-sourced ingredients prepared by our expert chefs.</p>
        <ul class="list-unstyled">
          <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i><strong>Breakfast:</strong> 6:30 AM - 10:30 AM</li>
          <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i><strong>Lunch:</strong> 12:00 PM - 3:00 PM</li>
          <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i><strong>Dinner:</strong> 6:30 PM - 10:00 PM</li>
        </ul>
        <p class="text-muted"><i class="fas fa-users me-2"></i>Capacity: 80 guests | <i class="fas fa-wheelchair ms-3 me-2"></i>Wheelchair accessible</p>
      </div>
    </div>
  </section>

  <hr class="my-5">

  <!-- Cuisine Types -->
  <section class="mb-5">
    <h2 class="h3 mb-4 text-center">Our Cuisine</h2>
    <div class="row g-4">
      
      <div class="col-md-4">
        <div class="card h-100 text-center">
          <div class="card-body">
            <i class="fas fa-globe fa-3x text-primary mb-3"></i>
            <h3 class="h5">International</h3>
            <p class="small">Classic dishes from around the world, expertly prepared to suit every palate.</p>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card h-100 text-center">
          <div class="card-body">
            <i class="fas fa-drumstick-bite fa-3x text-primary mb-3"></i>
            <h3 class="h5">Traditional Ugandan</h3>
            <p class="small">Authentic local dishes including matooke, luwombo, and fresh tilapia from Lake Victoria.</p>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card h-100 text-center">
          <div class="card-body">
            <i class="fas fa-leaf fa-3x text-primary mb-3"></i>
            <h3 class="h5">Vegetarian & Vegan</h3>
            <p class="small">Fresh garden-to-table options with organic vegetables from local farms.</p>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- Sample Menu -->
  <section class="mb-5">
    <h2 class="h3 mb-4 text-center">Sample Menu Highlights</h2>
    
    <div class="row">
      <div class="col-md-6">
        <div class="card mb-4">
          <div class="card-header bg-primary text-white">
            <h3 class="h6 mb-0"><i class="fas fa-coffee me-2"></i>Breakfast</h3>
          </div>
          <div class="card-body">
            <div class="d-flex justify-content-between mb-2">
              <span><strong>Full English Breakfast</strong></span>
              <span class="text-primary">UGX 25,000</span>
            </div>
            <p class="small text-muted mb-3">Eggs, sausage, bacon, beans, toast, mushrooms</p>

            <div class="d-flex justify-content-between mb-2">
              <span><strong>Uganda Breakfast</strong></span>
              <span class="text-primary">UGX 20,000</span>
            </div>
            <p class="small text-muted mb-3">Katogo, cassava, sweet potatoes, tea</p>

            <div class="d-flex justify-content-between mb-2">
              <span><strong>Continental Breakfast</strong></span>
              <span class="text-primary">UGX 18,000</span>
            </div>
            <p class="small text-muted mb-0">Fresh pastries, fruits, yogurt, juice, coffee</p>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card mb-4">
          <div class="card-header bg-primary text-white">
            <h3 class="h6 mb-0"><i class="fas fa-sun me-2"></i>Lunch</h3>
          </div>
          <div class="card-body">
            <div class="d-flex justify-content-between mb-2">
              <span><strong>Grilled Tilapia</strong></span>
              <span class="text-primary">UGX 35,000</span>
            </div>
            <p class="small text-muted mb-3">Fresh fish with steamed vegetables and rice</p>

            <div class="d-flex justify-content-between mb-2">
              <span><strong>Chicken Luwombo</strong></span>
              <span class="text-primary">UGX 32,000</span>
            </div>
            <p class="small text-muted mb-3">Traditional steamed chicken with groundnut sauce</p>

            <div class="d-flex justify-content-between mb-2">
              <span><strong>Garden Salad Bowl</strong></span>
              <span class="text-primary">UGX 22,000</span>
            </div>
            <p class="small text-muted mb-0">Fresh greens, avocado, nuts, house dressing</p>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card mb-4">
          <div class="card-header bg-primary text-white">
            <h3 class="h6 mb-0"><i class="fas fa-moon me-2"></i>Dinner</h3>
          </div>
          <div class="card-body">
            <div class="d-flex justify-content-between mb-2">
              <span><strong>Beef Steak</strong></span>
              <span class="text-primary">UGX 45,000</span>
            </div>
            <p class="small text-muted mb-3">Prime cut with mashed potatoes and vegetables</p>

            <div class="d-flex justify-content-between mb-2">
              <span><strong>Vegetable Curry</strong></span>
              <span class="text-primary">UGX 28,000</span>
            </div>
            <p class="small text-muted mb-3">Spiced vegetables with rice or chapati</p>

            <div class="d-flex justify-content-between mb-2">
              <span><strong>Pasta Primavera</strong></span>
              <span class="text-primary">UGX 30,000</span>
            </div>
            <p class="small text-muted mb-0">Fresh pasta with seasonal vegetables</p>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card mb-4">
          <div class="card-header bg-primary text-white">
            <h3 class="h6 mb-0"><i class="fas fa-wine-glass me-2"></i>Beverages</h3>
          </div>
          <div class="card-body">
            <div class="d-flex justify-content-between mb-2">
              <span><strong>Fresh Juices</strong></span>
              <span class="text-primary">UGX 8,000</span>
            </div>
            <p class="small text-muted mb-3">Passion fruit, mango, pineapple, watermelon</p>

            <div class="d-flex justify-content-between mb-2">
              <span><strong>Local Beers</strong></span>
              <span class="text-primary">UGX 6,000</span>
            </div>
            <p class="small text-muted mb-3">Nile Special, Bell Lager, Club Pilsner</p>

            <div class="d-flex justify-content-between mb-2">
              <span><strong>House Wine</strong></span>
              <span class="text-primary">UGX 35,000</span>
            </div>
            <p class="small text-muted mb-0">Red or white, per bottle</p>
          </div>
        </div>
      </div>
    </div>

    <div class="text-center">
      <a href="<?= base_url('public/brochures.php') ?>" class="btn btn-outline-primary">
        <i class="fas fa-download me-2"></i>Download Full Menu (PDF)
      </a>
    </div>
  </section>

  <!-- Special Services -->
  <section class="mb-5">
    <h2 class="h3 mb-4 text-center">Special Dining Services</h2>
    <div class="row g-4">
      
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <h3 class="h5"><i class="fas fa-concierge-bell text-primary me-2"></i>Room Service</h3>
            <p>Enjoy meals in the comfort of your room. Available 7:00 AM - 10:00 PM daily.</p>
            <ul class="small mb-0">
              <li>Full menu available</li>
              <li>15% service charge applies</li>
              <li>30-45 minute delivery time</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <h3 class="h5"><i class="fas fa-heart text-primary me-2"></i>Private Dining</h3>
            <p>Celebrate special occasions with our private dining experiences for 2-10 guests.</p>
            <ul class="small mb-0">
              <li>Customized menus available</li>
              <li>Scenic outdoor locations</li>
              <li>Advance booking required</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <h3 class="h5"><i class="fas fa-birthday-cake text-primary me-2"></i>Special Events</h3>
            <p>Host weddings, anniversaries, or corporate events with our catering services.</p>
            <ul class="small mb-0">
              <li>Custom menus for groups</li>
              <li>Indoor & outdoor venues</li>
              <li>Professional event planning</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <h3 class="h5"><i class="fas fa-seedling text-primary me-2"></i>Dietary Requirements</h3>
            <p>We accommodate all dietary needs and preferences with advance notice.</p>
            <ul class="small mb-0">
              <li>Vegetarian & vegan options</li>
              <li>Gluten-free meals</li>
              <li>Allergy-friendly preparation</li>
            </ul>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- The Bar -->
  <section class="mb-5">
    <div class="row align-items-center">
      <div class="col-md-6 order-md-2">
        <img src="<?= asset_url('images/food3.webp') ?>" alt="Bar" class="img-fluid rounded shadow">
      </div>
      <div class="col-md-6 order-md-1">
        <h2 class="h3 mb-3"><i class="fas fa-glass-martini-alt text-primary me-2"></i>The Sunset Bar</h2>
        <p>Unwind with a drink as you watch the sun set over the mountains. Our bar offers a selection of local and imported beverages.</p>
        <ul class="list-unstyled">
          <li class="mb-2"><i class="fas fa-clock text-success me-2"></i><strong>Hours:</strong> 12:00 PM - 11:00 PM</li>
          <li class="mb-2"><i class="fas fa-cocktail text-success me-2"></i>Signature cocktails & mocktails</li>
          <li class="mb-2"><i class="fas fa-beer text-success me-2"></i>Local & imported beers</li>
          <li class="mb-2"><i class="fas fa-wine-bottle text-success me-2"></i>Carefully curated wine list</li>
          <li class="mb-2"><i class="fas fa-coffee text-success me-2"></i>Premium coffee & tea</li>
        </ul>
        <p class="text-muted small">Happy Hour: 5:00 PM - 7:00 PM daily (20% off selected drinks)</p>
      </div>
    </div>
  </section>

</div>

<!-- CTA Section -->
<section class="bg-primary text-white py-5">
  <div class="container text-center">
    <h2 class="display-6 mb-3">Reserve Your Table</h2>
    <p class="lead mb-4">For private dining or special events, contact us in advance</p>
    <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-light btn-lg me-2">Contact Us</a>
    <a href="<?= base_url('public/booking.php') ?>" class="btn btn-outline-light btn-lg">Book Your Stay</a>
  </div>
</section>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
