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
        <h1 class="display-4 mb-3"><?= sanitize(site_content('dining', 'title', 'Dining & Cuisine')) ?></h1>
        <p class="lead mb-4"><?= sanitize(site_content('dining', 'intro', 'Experience authentic Ugandan flavors and international cuisine in our scenic restaurant overlooking the mountains.')) ?></p>
        <a href="<?= base_url('public/booking.php') ?>" class="btn btn-primary btn-lg">Make a Reservation</a>
      </div>
      <div class="col-lg-4 d-none d-lg-block">
        <img src="<?= sanitize(site_content_image('dining', 'hero_image', 'images/food1.webp')) ?>" alt="Dining at Kalitusi" class="img-fluid rounded shadow">
      </div>
    </div>
  </div>
</section>

<div class="container py-4">

  <!-- Main Restaurant -->
  <section class="mb-5">
    <div class="row align-items-center">
      <div class="col-md-6">
        <img src="<?= sanitize(site_content_image('dining', 'restaurant_image', 'images/food2.webp')) ?>" alt="<?= sanitize(site_content('dining', 'restaurant_title', 'The Mountain View Restaurant')) ?>" class="img-fluid rounded shadow">
      </div>
      <div class="col-md-6">
        <h2 class="h3 mb-3"><i class="fas fa-utensils text-primary me-2"></i><?= sanitize(site_content('dining', 'restaurant_title', 'The Mountain View Restaurant')) ?></h2>
        <p><?= sanitize(site_content('dining', 'restaurant_copy', 'Our main restaurant offers a refined dining experience with panoramic views of the Rwenzori Mountains. Enjoy fresh, locally-sourced ingredients prepared by our expert chefs.')) ?></p>
        <ul class="list-unstyled">
          <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i><strong>Breakfast:</strong> <?= sanitize(site_content('dining', 'breakfast_hours', '6:30 AM - 10:30 AM')) ?></li>
          <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i><strong>Lunch:</strong> <?= sanitize(site_content('dining', 'lunch_hours', '12:00 PM - 3:00 PM')) ?></li>
          <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i><strong>Dinner:</strong> <?= sanitize(site_content('dining', 'dinner_hours', '6:30 PM - 10:00 PM')) ?></li>
        </ul>
        <p class="text-muted"><i class="fas fa-users me-2"></i><?= sanitize(site_content('dining', 'restaurant_capacity', 'Capacity: 80 guests | Wheelchair accessible')) ?></p>
      </div>
    </div>
  </section>

  <hr class="my-5">

  <!-- Cuisine Types -->
  <section class="mb-5">
    <h2 class="h3 mb-4 text-center"><?= sanitize(site_content('dining', 'cuisine_title', 'Our Cuisine')) ?></h2>
    <div class="row g-4">
      
      <div class="col-md-4">
        <div class="card h-100 text-center">
          <div class="card-body">
            <i class="fas fa-globe fa-3x text-primary mb-3"></i>
            <h3 class="h5"><?= sanitize(site_content('dining', 'international_title', 'International')) ?></h3>
            <p class="small"><?= sanitize(site_content('dining', 'international_copy', 'Classic dishes from around the world, expertly prepared to suit every palate.')) ?></p>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card h-100 text-center">
          <div class="card-body">
            <i class="fas fa-drumstick-bite fa-3x text-primary mb-3"></i>
            <h3 class="h5"><?= sanitize(site_content('dining', 'ugandan_title', 'Traditional Ugandan')) ?></h3>
            <p class="small"><?= sanitize(site_content('dining', 'ugandan_copy', 'Authentic local dishes including matooke, luwombo, and fresh tilapia from Lake Victoria.')) ?></p>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card h-100 text-center">
          <div class="card-body">
            <i class="fas fa-leaf fa-3x text-primary mb-3"></i>
            <h3 class="h5"><?= sanitize(site_content('dining', 'vegetarian_title', 'Vegetarian & Vegan')) ?></h3>
            <p class="small"><?= sanitize(site_content('dining', 'vegetarian_copy', 'Fresh garden-to-table options with organic vegetables from local farms.')) ?></p>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- Sample Menu -->
  <section class="mb-5">
    <h2 class="h3 mb-4 text-center"><?= sanitize(site_content('dining', 'menu_title', 'Sample Menu Highlights')) ?></h2>
    
    <div class="row">
      <div class="col-md-6">
        <div class="card mb-4">
          <div class="card-header bg-primary text-white">
            <h3 class="h6 mb-0"><i class="fas fa-coffee me-2"></i><?= sanitize(site_content('dining', 'menu_breakfast_title', 'Breakfast')) ?></h3>
          </div>
          <div class="card-body">
            <?php foreach (site_content_rows('dining', 'menu_breakfast', "Full English Breakfast | 25,000 | Eggs, sausage, bacon, beans, toast, mushrooms\nUganda Breakfast | 20,000 | Katogo, cassava, sweet potatoes, tea\nContinental Breakfast | 18,000 | Fresh pastries, fruits, yogurt, juice, coffee") as $dish): if (count($dish) < 3) continue; ?>
              <div class="d-flex justify-content-between mb-2"><span><strong><?= sanitize($dish[0]) ?></strong></span><span class="text-primary">UGX <?= sanitize($dish[1]) ?></span></div>
              <p class="small text-muted mb-3"><?= sanitize($dish[2]) ?></p>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card mb-4">
          <div class="card-header bg-primary text-white">
            <h3 class="h6 mb-0"><i class="fas fa-sun me-2"></i><?= sanitize(site_content('dining', 'menu_lunch_title', 'Lunch')) ?></h3>
          </div>
          <div class="card-body">
            <?php foreach (site_content_rows('dining', 'menu_lunch', "Grilled Tilapia | 35,000 | Fresh fish with steamed vegetables and rice\nChicken Luwombo | 32,000 | Traditional steamed chicken with groundnut sauce\nGarden Salad Bowl | 22,000 | Fresh greens, avocado, nuts, house dressing") as $dish): if (count($dish) < 3) continue; ?>
              <div class="d-flex justify-content-between mb-2"><span><strong><?= sanitize($dish[0]) ?></strong></span><span class="text-primary">UGX <?= sanitize($dish[1]) ?></span></div>
              <p class="small text-muted mb-3"><?= sanitize($dish[2]) ?></p>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card mb-4">
          <div class="card-header bg-primary text-white">
            <h3 class="h6 mb-0"><i class="fas fa-moon me-2"></i><?= sanitize(site_content('dining', 'menu_dinner_title', 'Dinner')) ?></h3>
          </div>
          <div class="card-body">
            <?php foreach (site_content_rows('dining', 'menu_dinner', "Beef Steak | 45,000 | Prime cut with mashed potatoes and vegetables\nVegetable Curry | 28,000 | Spiced vegetables with rice or chapati\nPasta Primavera | 30,000 | Fresh pasta with seasonal vegetables") as $dish): if (count($dish) < 3) continue; ?>
              <div class="d-flex justify-content-between mb-2"><span><strong><?= sanitize($dish[0]) ?></strong></span><span class="text-primary">UGX <?= sanitize($dish[1]) ?></span></div>
              <p class="small text-muted mb-3"><?= sanitize($dish[2]) ?></p>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card mb-4">
          <div class="card-header bg-primary text-white">
            <h3 class="h6 mb-0"><i class="fas fa-wine-glass me-2"></i><?= sanitize(site_content('dining', 'menu_beverages_title', 'Beverages')) ?></h3>
          </div>
          <div class="card-body">
            <?php foreach (site_content_rows('dining', 'menu_beverages', "Fresh Juices | 8,000 | Passion fruit, mango, pineapple, watermelon\nLocal Beers | 6,000 | Nile Special, Bell Lager, Club Pilsner\nHouse Wine | 35,000 | Red or white, per bottle") as $dish): if (count($dish) < 3) continue; ?>
              <div class="d-flex justify-content-between mb-2"><span><strong><?= sanitize($dish[0]) ?></strong></span><span class="text-primary">UGX <?= sanitize($dish[1]) ?></span></div>
              <p class="small text-muted mb-3"><?= sanitize($dish[2]) ?></p>
            <?php endforeach; ?>
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
    <h2 class="h3 mb-4 text-center"><?= sanitize(site_content('dining', 'special_title', 'Special Dining Services')) ?></h2>
    <div class="row g-4">
      
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <h3 class="h5"><i class="fas fa-concierge-bell text-primary me-2"></i><?= sanitize(site_content('dining', 'service_1_title', 'Room Service')) ?></h3>
            <p><?= sanitize(site_content('dining', 'service_1_copy', 'Enjoy meals in the comfort of your room. Available 7:00 AM - 10:00 PM daily.')) ?></p>
            <ul class="small mb-0">
              <?php foreach (site_content_rows('dining', 'service_1_details', "Full menu available\n15% service charge applies\n30-45 minute delivery time", "\n") as $detail): ?><li><?= sanitize($detail[0] ?? '') ?></li><?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <h3 class="h5"><i class="fas fa-heart text-primary me-2"></i><?= sanitize(site_content('dining', 'service_2_title', 'Private Dining')) ?></h3>
            <p><?= sanitize(site_content('dining', 'service_2_copy', 'Celebrate special occasions with our private dining experiences for 2-10 guests.')) ?></p>
            <ul class="small mb-0">
              <?php foreach (site_content_rows('dining', 'service_2_details', "Customized menus available\nScenic outdoor locations\nAdvance booking required", "\n") as $detail): ?><li><?= sanitize($detail[0] ?? '') ?></li><?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <h3 class="h5"><i class="fas fa-birthday-cake text-primary me-2"></i><?= sanitize(site_content('dining', 'service_3_title', 'Special Events')) ?></h3>
            <p><?= sanitize(site_content('dining', 'service_3_copy', 'Host weddings, anniversaries, or corporate events with our catering services.')) ?></p>
            <ul class="small mb-0">
              <?php foreach (site_content_rows('dining', 'service_3_details', "Custom menus for groups\nIndoor & outdoor venues\nProfessional event planning", "\n") as $detail): ?><li><?= sanitize($detail[0] ?? '') ?></li><?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <h3 class="h5"><i class="fas fa-seedling text-primary me-2"></i><?= sanitize(site_content('dining', 'service_4_title', 'Dietary Requirements')) ?></h3>
            <p><?= sanitize(site_content('dining', 'service_4_copy', 'We accommodate all dietary needs and preferences with advance notice.')) ?></p>
            <ul class="small mb-0">
              <?php foreach (site_content_rows('dining', 'service_4_details', "Vegetarian & vegan options\nGluten-free meals\nAllergy-friendly preparation", "\n") as $detail): ?><li><?= sanitize($detail[0] ?? '') ?></li><?php endforeach; ?>
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
        <img src="<?= sanitize(site_content_image('dining', 'bar_image', 'images/food3.webp')) ?>" alt="<?= sanitize(site_content('dining', 'bar_title', 'The Sunset Bar')) ?>" class="img-fluid rounded shadow">
      </div>
      <div class="col-md-6 order-md-1">
        <h2 class="h3 mb-3"><i class="fas fa-glass-martini-alt text-primary me-2"></i><?= sanitize(site_content('dining', 'bar_title', 'The Sunset Bar')) ?></h2>
        <p><?= sanitize(site_content('dining', 'bar_copy', 'Unwind with a drink as you watch the sun set over the mountains. Our bar offers a selection of local and imported beverages.')) ?></p>
        <ul class="list-unstyled">
          <li class="mb-2"><i class="fas fa-clock text-success me-2"></i><strong>Hours:</strong> <?= sanitize(site_content('dining', 'bar_hours', '12:00 PM - 11:00 PM')) ?></li>
          <?php foreach (site_content_rows('dining', 'bar_details', "Signature cocktails & mocktails\nLocal & imported beers\nCarefully curated wine list\nPremium coffee & tea", "\n") as $detail): ?><li class="mb-2"><?= sanitize($detail[0] ?? '') ?></li><?php endforeach; ?>
        </ul>
        <p class="text-muted small"><?= sanitize(site_content('dining', 'bar_happy_hour', 'Happy Hour: 5:00 PM - 7:00 PM daily (20% off selected drinks)')) ?></p>
      </div>
    </div>
  </section>

</div>

<!-- CTA Section -->
<section class="bg-primary text-white py-5">
  <div class="container text-center">
    <h2 class="display-6 mb-3"><?= sanitize(site_content('dining', 'cta_title', 'Reserve Your Table')) ?></h2>
    <p class="lead mb-4"><?= sanitize(site_content('dining', 'cta_copy', 'For private dining or special events, contact us in advance')) ?></p>
    <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-light btn-lg me-2"><?= sanitize(site_content('dining', 'cta_contact_label', 'Contact Us')) ?></a>
    <a href="<?= base_url('public/booking.php') ?>" class="btn btn-outline-light btn-lg"><?= sanitize(site_content('dining', 'cta_booking_label', 'Book Your Stay')) ?></a>
  </div>
</section>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
