<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$title = site_content('legal', 'terms_title', 'Terms & Conditions') . ' | Kalitusi Nature Resort';
include __DIR__ . '/../includes/layout_header.php';
?>
<div id="legalHero" class="carousel slide hero-slider mb-0" data-bs-ride="carousel">
  <div class="carousel-inner">
    <div class="carousel-item hero-slide active">
      <div class="hero-slide__bg" style="background-image: url('<?= asset_url('images/rooms.webp') ?>');"></div>
      <div class="hero-slide__overlay"></div>
      <div class="hero-slide__content container">
        <div class="hero-slide__text">
          <h2 class="hero-slide__title"><?= sanitize(site_content('legal', 'terms_title', 'Terms & Conditions')) ?></h2>
          <p class="hero-slide__subtitle"><?= sanitize(site_content('legal', 'terms_subtitle', 'Please read our booking terms and resort policies.')) ?></p>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="container py-3">
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= base_url('public/') ?>">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Terms & Conditions</li>
    </ol>
  </nav>
  <div class="row g-3">
  <div class="col-lg-8">
    <div class="card mb-3"><div class="card-body">
      <?= site_content_markup('legal', 'terms_body', "## Booking Terms & Conditions\n### 1. Reservations & Deposits\nAll reservations require a 50% deposit to confirm your booking. The remaining balance must be paid upon check-in or 7 days before arrival for extended stays.\n### 2. Cancellation Policy\nCancellations made 14 days or more before check-in date: Full refund minus 10% processing fee.\nCancellations made 7-13 days before: 50% refund.\nCancellations made less than 7 days before: No refund.\n### 3. Check-In & Check-Out\nCheck-in: 2:00 PM | Check-out: 11:00 AM\nEarly check-in or late check-out may be available upon request (subject to availability and additional charges).\n### 4. Guest Responsibilities\n- Guests must present valid identification at check-in\n- Respect resort property and other guests\n- Follow all resort policies and local regulations\n- Report any damages immediately to management\n### 5. Liability\nThe resort is not liable for loss or damage to personal belongings. We recommend securing valuables in room safes or at reception.\n### 6. Contact Information\nFor questions about our terms, please contact us through the resort contact page.") ?>
    </div></div>
  </div>
  <div class="col-lg-4">
    <div class="card h-100"><div class="card-body">
      <h3 class="h6">Related Pages</h3>
      <ul class="mb-0">
        <li><a href="<?= base_url('public/privacy.php') ?>">Privacy Policy</a></li>
        <li><a href="<?= base_url('public/cancellation.php') ?>">Cancellation Policy</a></li>
        <li><a href="<?= base_url('public/contact.php') ?>">Contact Us</a></li>
        <li><a href="<?= base_url('public/booking.php') ?>">Make a Reservation</a></li>
      </ul>
    </div></div>
  </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/layout_footer.php';
