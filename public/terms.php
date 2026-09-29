<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$title = 'Terms & Conditions | Kalitusi Nature Resort';
include __DIR__ . '/../includes/layout_header.php';
?>
<div id="legalHero" class="carousel slide hero-slider mb-0" data-bs-ride="carousel">
  <div class="carousel-inner">
    <div class="carousel-item hero-slide active">
      <div class="hero-slide__bg" style="background-image: url('<?= asset_url('images/rooms.webp') ?>');"></div>
      <div class="hero-slide__overlay"></div>
      <div class="hero-slide__content container">
        <div class="hero-slide__text">
          <h2 class="hero-slide__title">Terms &amp; Conditions</h2>
          <p class="hero-slide__subtitle">Please read our booking terms and resort policies.</p>
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
      <h3 class="h5">Booking Terms & Conditions</h3>
      <h4 class="h6 mt-3">1. Reservations & Deposits</h4>
      <p>All reservations require a 50% deposit to confirm your booking. The remaining balance must be paid upon check-in or 7 days before arrival for extended stays.</p>
      
      <h4 class="h6 mt-3">2. Cancellation Policy</h4>
      <p>Cancellations made 14 days or more before check-in date: Full refund minus 10% processing fee.<br>
Cancellations made 7-13 days before: 50% refund.<br>
Cancellations made less than 7 days before: No refund.</p>
      
      <h4 class="h6 mt-3">3. Check-In & Check-Out</h4>
      <p>Check-in: 2:00 PM | Check-out: 11:00 AM<br>
Early check-in or late check-out may be available upon request (subject to availability and additional charges).</p>
      
      <h4 class="h6 mt-3">4. Guest Responsibilities</h4>
      <ul>
        <li>Guests must present valid identification at check-in</li>
        <li>Respect resort property and other guests</li>
        <li>Follow all resort policies and local regulations</li>
        <li>Report any damages immediately to management</li>
      </ul>
      
      <h4 class="h6 mt-3">5. Liability</h4>
      <p>The resort is not liable for loss or damage to personal belongings. We recommend securing valuables in room safes or at reception.</p>
      
      <h4 class="h6 mt-3">6. Contact Information</h4>
      <p>For questions about our terms, please contact us at <a href="<?= base_url('public/contact.php') ?>">our contact page</a>.</p>
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
