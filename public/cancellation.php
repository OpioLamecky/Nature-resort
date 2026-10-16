<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$title = site_content('legal', 'cancellation_title', 'Cancellation Policy') . ' | Kalitusi Nature Resort';
include __DIR__ . '/../includes/layout_header.php';
?>

<!-- Hero Section -->
<section class="page-intro-hero bg-soft-mint">
  <div class="container">
    <div class="row g-4 g-lg-5 align-items-center">
      <div class="col-md-7">
        <h1 class="mb-3"><?= sanitize(site_content('legal', 'cancellation_title', 'Cancellation Policy')) ?></h1>
        <p class="lead mb-0"><?= sanitize(site_content('legal', 'cancellation_subtitle', 'Our fair and transparent cancellation policy for your peace of mind')) ?></p>
      </div>
      <div class="col-md-5">
        <img src="<?= asset_url('images/view.webp') ?>" alt="Nature surrounding Kalitusi Nature Resort">
      </div>
    </div>
  </div>
</section>

<!-- Policy Content -->
<section class="py-5">
  <div class="container">
    <div class="row">
      <div class="col-lg-8 mx-auto">
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-body p-4">
            <?= site_content_markup('legal', 'cancellation_body', "## Cancellation Terms\n### Accommodation Cancellations\n- 7+ days before arrival: Full refund (minus 5% processing fee)\n- 3-6 days before arrival: 50% refund\n- 48-72 hours before arrival: 25% refund\n- Less than 48 hours: No refund\n### Activity Cancellations\n- 24+ hours before: Full refund\n- Less than 24 hours: No refund\n### No-Show Policy\nNo refund will be provided for guests who do not arrive for their reservation without prior notice.\n### Force Majeure\nIn case of unforeseen circumstances (natural disasters, government restrictions, etc.), we will offer rescheduling or full credit for future use.\n### Contact\nAll cancellations must be made in writing via email or by calling the resort.") ?>
          </div>
        </div>

        <div class="text-center">
          <a href="<?= base_url('public/booking.php') ?>" class="btn btn-primary btn-lg me-3">Make a Booking</a>
          <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-secondary btn-lg">Contact Us</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
