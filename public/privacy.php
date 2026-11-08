<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$title = site_content('legal', 'privacy_title', 'Privacy Policy') . ' | Kalitusi Nature Resort';
include __DIR__ . '/../includes/layout_header.php';
?>

<!-- Hero Section -->
<section class="page-intro-hero bg-soft-mint">
  <div class="container">
    <div class="row g-4 g-lg-5 align-items-center">
      <div class="col-md-7">
        <h1 class="mb-3"><?= sanitize(site_content('legal', 'privacy_title', 'Privacy Policy')) ?></h1>
        <p class="lead mb-0"><?= sanitize(site_content('legal', 'privacy_subtitle', 'How we collect, use, and protect your personal information')) ?></p>
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
            <h2 class="h4 mb-4"><?= sanitize(site_content('legal', 'privacy_title', 'Privacy Policy')) ?></h2>
            <p class="text-muted small">Last updated: <?= date('F j, Y') ?></p>
            <?= site_content_markup('legal', 'privacy_body', "## Information We Collect\nWe collect the following types of information:\n- Name, email address, and contact details\n- Booking and accommodation preferences\n- Payment information (processed securely)\n- IP address and browser information\n## How We Use Your Information\n- To process bookings and reservations\n- To communicate with you about your stay\n- To improve our services\n- To send marketing communications (with consent)\n## Data Protection\nWe implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.\n## Third-Party Sharing\nWe do not sell, trade, or otherwise transfer your personal information to third parties without your consent, except as required by law.\n## Your Rights\n- Access to your personal information\n- Correction of inaccurate information\n- Deletion of your information\n- Opt-out of marketing communications\n## Cookies\nOur website uses cookies to enhance your experience. You can choose to disable cookies through your browser settings.\n## Contact Us\nIf you have questions about this Privacy Policy, contact the resort by email or phone.") ?>
          </div>
        </div>

        <div class="text-center">
          <a href="<?= base_url('public/booking.php') ?>" class="btn btn-primary btn-lg me-3">Book Now</a>
          <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-secondary btn-lg">Contact Us</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
