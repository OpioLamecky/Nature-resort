<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$title = 'Privacy Policy | Kalitusi Nature Resort';
include __DIR__ . '/../includes/layout_header.php';
?>

<!-- Hero Section -->
<section class="hero-section" style="background: linear-gradient(135deg, var(--pine-dark) 0%, var(--charcoal) 100%); color: white; padding: 80px 0;">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1 class="display-4 fw-bold mb-4">Privacy Policy</h1>
        <p class="lead mb-0">How we collect, use, and protect your personal information</p>
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
            <h2 class="h4 mb-4">Privacy Policy</h2>
            <p class="text-muted small">Last updated: <?= date('F j, Y') ?></p>
            
            <div class="mb-4">
              <h3 class="h5 text-primary mb-3">Information We Collect</h3>
              <p>We collect the following types of information:</p>
              <ul>
                <li>Name, email address, and contact details</li>
                <li>Booking and accommodation preferences</li>
                <li>Payment information (processed securely)</li>
                <li>IP address and browser information</li>
              </ul>
            </div>

            <div class="mb-4">
              <h3 class="h5 text-primary mb-3">How We Use Your Information</h3>
              <ul>
                <li>To process bookings and reservations</li>
                <li>To communicate with you about your stay</li>
                <li>To improve our services</li>
                <li>To send marketing communications (with consent)</li>
              </ul>
            </div>

            <div class="mb-4">
              <h3 class="h5 text-primary mb-3">Data Protection</h3>
              <p>We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.</p>
            </div>

            <div class="mb-4">
              <h3 class="h5 text-primary mb-3">Third-Party Sharing</h3>
              <p>We do not sell, trade, or otherwise transfer your personal information to third parties without your consent, except as required by law.</p>
            </div>

            <div class="mb-4">
              <h3 class="h5 text-primary mb-3">Your Rights</h3>
              <ul>
                <li>Access to your personal information</li>
                <li>Correction of inaccurate information</li>
                <li>Deletion of your information</li>
                <li>Opt-out of marketing communications</li>
              </ul>
            </div>

            <div class="mb-4">
              <h3 class="h5 text-primary mb-3">Cookies</h3>
              <p>Our website uses cookies to enhance your experience. You can choose to disable cookies through your browser settings.</p>
            </div>

            <div class="alert alert-info">
              <strong>Contact Us:</strong> If you have questions about this Privacy Policy, please contact us at info@kalitusiresort.com or +256 772 957 573.
            </div>
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
