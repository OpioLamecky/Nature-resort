<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$title = 'Cancellation Policy | Kalitusi Nature Resort';
include __DIR__ . '/../includes/layout_header.php';
?>

<!-- Hero Section -->
<section class="hero-section" style="background: linear-gradient(135deg, var(--pine-dark) 0%, var(--charcoal) 100%); color: white; padding: 80px 0;">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1 class="display-4 fw-bold mb-4">Cancellation Policy</h1>
        <p class="lead mb-0">Our fair and transparent cancellation policy for your peace of mind</p>
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
            <h2 class="h4 mb-4">Cancellation Terms</h2>
            
            <div class="mb-4">
              <h3 class="h5 text-primary mb-3">Accommodation Cancellations</h3>
              <ul class="list-unstyled">
                <li class="mb-2"><strong>7+ days before arrival:</strong> Full refund (minus 5% processing fee)</li>
                <li class="mb-2"><strong>3-6 days before arrival:</strong> 50% refund</li>
                <li class="mb-2"><strong>48-72 hours before arrival:</strong> 25% refund</li>
                <li class="mb-2"><strong>Less than 48 hours:</strong> No refund</li>
              </ul>
            </div>

            <div class="mb-4">
              <h3 class="h5 text-primary mb-3">Activity Cancellations</h3>
              <ul class="list-unstyled">
                <li class="mb-2"><strong>24+ hours before:</strong> Full refund</li>
                <li class="mb-2"><strong>Less than 24 hours:</strong> No refund</li>
              </ul>
            </div>

            <div class="mb-4">
              <h3 class="h5 text-primary mb-3">No-Show Policy</h3>
              <p>No refund will be provided for guests who do not arrive for their reservation without prior notice.</p>
            </div>

            <div class="mb-4">
              <h3 class="h5 text-primary mb-3">Force Majeure</h3>
              <p>In case of unforeseen circumstances (natural disasters, government restrictions, etc.), we will offer rescheduling or full credit for future use.</p>
            </div>

            <div class="alert alert-info">
              <strong>Note:</strong> All cancellations must be made in writing via email to info@kalitusiresort.com or by calling +256 772 957 573.
            </div>
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
