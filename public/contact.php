<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$title = 'Contact Us | Kalitusi Nature Resort';

// Load site settings when available
$settings = [];
try {
  $st = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
  foreach ($st->fetchAll() as $row) { $settings[$row['setting_key']] = $row['setting_value']; }
} catch (Throwable $e) { /* ignore */ }

$contactEmail = site_content('contact', 'email', $settings['contact_email'] ?? 'info@kalitusi.com');
$contactPhone = site_content('contact', 'phone', $settings['contact_phone'] ?? '+256 772 957 573');
$whatsappLink = site_content('contact', 'whatsapp_link', 'https://wa.me/256772957573');
$facebookUrl  = site_content('contact', 'facebook_url', 'https://www.facebook.com/kalitusinatureresort');
$instagramUrl = site_content('contact', 'instagram_url', 'https://www.instagram.com/kalitusinatureresort');
$twitterUrl   = site_content('contact', 'twitter_url', 'https://x.com/kalitusifortportal');
$addressText  = site_content('contact', 'address', $settings['address_html'] ?? "Kalitusi Nature Resort\nFort Portal Tourism City\nWestern Uganda");

$formSuccess = false;
$formError   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
  if (!verify_csrf()) {
    $formError = 'Security token expired. Please refresh the page and try again.';
  } else {
    $senderName    = trim((string)($_POST['name'] ?? ''));
    $senderEmail   = trim((string)($_POST['email'] ?? ''));
    $senderPhone   = trim((string)($_POST['phone'] ?? ''));
    $inquirySubject = trim((string)($_POST['subject'] ?? 'General Inquiry'));
    $senderMessage = trim((string)($_POST['message'] ?? ''));

    if ($senderName === '' || $senderEmail === '' || $senderMessage === '') {
      $formError = 'Please fill in your name, email address, and message.';
    } elseif (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
      $formError = 'Please enter a valid email address.';
    } else {
      $formSuccess = true;
    }
  }
}

include __DIR__ . '/../includes/layout_header.php';
?>

<style>
  .contact-bento-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid rgba(15, 23, 42, 0.07);
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
    padding: 1.5rem;
    height: 100%;
    transition: transform 0.22s ease, box-shadow 0.22s ease;
  }
  .contact-bento-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 32px rgba(15, 23, 42, 0.09);
  }
  .contact-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    margin-bottom: 1rem;
  }
  .contact-icon-box.is-green { background: rgba(27, 138, 90, 0.12); color: #1b8a5a; }
  .contact-icon-box.is-emerald { background: rgba(37, 211, 102, 0.14); color: #16a34a; }
  .contact-icon-box.is-blue { background: rgba(37, 99, 235, 0.11); color: #2563eb; }
  .contact-icon-box.is-amber { background: rgba(217, 119, 6, 0.12); color: #d97706; }

  .contact-social-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.85rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
    background: #f8faf9;
    border: 1px solid rgba(15, 23, 42, 0.08);
    color: #1e293b;
    text-decoration: none;
    transition: all 0.2s ease;
  }
  .contact-social-pill:hover {
    background: #1b8a5a;
    color: #fff;
    border-color: #1b8a5a;
  }
  .contact-form-control {
    border-radius: 12px;
    border: 1px solid rgba(15, 23, 42, 0.14);
    padding: 0.65rem 0.95rem;
    font-size: 0.95rem;
  }
  .contact-form-control:focus {
    border-color: #1b8a5a;
    box-shadow: 0 0 0 3px rgba(27, 138, 90, 0.15);
  }
</style>

<div class="container mt-3">
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= base_url('public/') ?>">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Contact Us</li>
    </ol>
  </nav>
</div>

<!-- Hero Section -->
<section class="page-intro-hero bg-soft-mint">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-7 text-center text-lg-start">
        <span class="badge rounded-pill px-3 py-2 mb-2" style="background: rgba(27, 138, 90, 0.12); color: #1b8a5a; font-weight: 600;">
          <i class="fas fa-concierge-bell me-1"></i> We're Here to Help
        </span>
        <h1 class="display-5 fw-bold mb-3"><?= sanitize(site_content('contact', 'title', 'Get in Touch With Kalitusi')) ?></h1>
        <p class="lead mb-4 mx-auto mx-lg-0"><?= sanitize(site_content('contact', 'intro', 'Have questions about room availability, private dining, or guided nature experiences in Fort Portal? Reach out to our hospitality team anytime.')) ?></p>
        <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-lg-start">
          <a href="<?= sanitize($whatsappLink) ?>" target="_blank" rel="noopener" class="btn btn-primary rounded-pill px-4">
            <i class="fab fa-whatsapp me-2"></i>Chat on WhatsApp
          </a>
          <a href="tel:<?= sanitize(preg_replace('/\s+/', '', $contactPhone)) ?>" class="btn btn-outline-primary rounded-pill px-4">
            <i class="fas fa-phone-alt me-2"></i><?= sanitize($contactPhone) ?>
          </a>
        </div>
      </div>
      <div class="col-lg-5">
        <img src="<?= sanitize(site_content_image('contact', 'hero_image', 'images/view.webp')) ?>" alt="Kalitusi Nature Resort Grounds" onerror="this.src='<?= asset_url('images/rooms.webp') ?>'">
      </div>
    </div>
  </div>
</section>

<div class="container py-4">
  <!-- 4 Bento Contact Channel Cards -->
  <div class="row g-4 mb-5">
    <div class="col-sm-6 col-lg-3">
      <div class="contact-bento-card">
        <div class="contact-icon-box is-green"><i class="fas fa-phone-volume"></i></div>
        <h2 class="h6 fw-bold mb-1">Call Front Desk</h2>
        <p class="text-muted small mb-3">Direct reservations &amp; guest assistance</p>
        <a href="tel:<?= sanitize(preg_replace('/\s+/', '', $contactPhone)) ?>" class="fw-bold text-decoration-none d-block mb-1" style="color: #1b8a5a; font-size: 1.02rem;">
          <?= sanitize($contactPhone) ?>
        </a>
        <div class="text-muted small"><i class="fas fa-clock me-1"></i>Available Daily, 24/7</div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="contact-bento-card">
        <div class="contact-icon-box is-emerald"><i class="fab fa-whatsapp"></i></div>
        <h2 class="h6 fw-bold mb-1">WhatsApp Concierge</h2>
        <p class="text-muted small mb-3">Fast responses for bookings &amp; inquiries</p>
        <a href="<?= sanitize($whatsappLink) ?>" target="_blank" rel="noopener" class="fw-bold text-decoration-none d-inline-flex align-items-center gap-1 mb-1" style="color: #16a34a; font-size: 1.02rem;">
          Message Us Now <i class="fas fa-arrow-up-right-from-square small"></i>
        </a>
        <div class="text-muted small"><i class="fas fa-bolt me-1"></i>Typical reply in &lt; 15 mins</div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="contact-bento-card">
        <div class="contact-icon-box is-blue"><i class="fas fa-envelope-open-text"></i></div>
        <h2 class="h6 fw-bold mb-1">Email Inquiries</h2>
        <p class="text-muted small mb-3">Group stays, events &amp; custom packages</p>
        <a href="mailto:<?= sanitize($contactEmail) ?>" class="fw-bold text-decoration-none d-block text-break mb-1" style="color: #2563eb; font-size: 0.98rem;">
          <?= sanitize($contactEmail) ?>
        </a>
        <div class="text-muted small"><i class="fas fa-check-circle me-1"></i>Reservations &amp; Support</div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="contact-bento-card">
        <div class="contact-icon-box is-amber"><i class="fas fa-map-marked-alt"></i></div>
        <h2 class="h6 fw-bold mb-1">Resort Location</h2>
        <p class="text-muted small mb-2">
          <?= nl2br(sanitize(str_ireplace(['<br>', '<br/>', '<br />'], "\n", $addressText))) ?>
        </p>
        <div class="d-flex flex-wrap gap-1 mt-2">
          <a href="<?= sanitize($facebookUrl) ?>" target="_blank" rel="noopener" class="contact-social-pill" title="Facebook"><i class="fab fa-facebook-f text-primary"></i></a>
          <a href="<?= sanitize($instagramUrl) ?>" target="_blank" rel="noopener" class="contact-social-pill" title="Instagram"><i class="fab fa-instagram text-danger"></i></a>
          <a href="<?= sanitize($twitterUrl) ?>" target="_blank" rel="noopener" class="contact-social-pill" title="X / Twitter"><i class="fab fa-twitter text-info"></i></a>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Split Layout: Inquiry Form + Interactive Map -->
  <div class="row g-4 mb-5">
    <!-- Left Column: Send a Message -->
    <div class="col-lg-6">
      <div class="contact-bento-card p-4">
        <div class="mb-4">
          <span class="badge rounded-pill px-3 py-1 mb-2" style="background: rgba(27, 138, 90, 0.1); color: #1b8a5a; font-weight: 600;">Direct Inquiry</span>
          <h2 class="h4 fw-bold mb-1">Send Us a Message</h2>
          <p class="text-muted small mb-0">Fill out the form below and our reservations team will get back to you promptly.</p>
        </div>

        <?php if ($formSuccess): ?>
          <div class="alert alert-success rounded-4 border-0 d-flex align-items-start gap-3 p-3 mb-4" style="background: rgba(22, 163, 74, 0.12); color: #14532d;">
            <i class="fas fa-check-circle fs-4 mt-1 text-success"></i>
            <div>
              <strong class="d-block mb-1">Thank you for contacting Kalitusi Nature Resort!</strong>
              <span class="small">We have received your inquiry and will respond to <strong><?= sanitize($senderEmail ?? '') ?></strong> shortly.</span>
            </div>
          </div>
        <?php elseif ($formError !== ''): ?>
          <div class="alert alert-danger rounded-4 border-0 small mb-4">
            <i class="fas fa-exclamation-circle me-1"></i> <?= sanitize($formError) ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="">
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Your Full Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control contact-form-control" placeholder="e.g. Sarah Johnson" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Email Address <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control contact-form-control" placeholder="name@example.com" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Phone / WhatsApp</label>
              <input type="text" name="phone" class="form-control contact-form-control" placeholder="+256 700 000 000">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Inquiry Topic</label>
              <select name="subject" class="form-select contact-form-control">
                <option value="Room Booking & Availability">Room Booking &amp; Availability</option>
                <option value="Dining & Private Events">Dining &amp; Private Events</option>
                <option value="Activities & Guided Tours">Activities &amp; Guided Tours</option>
                <option value="General Inquiry">General Inquiry</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold text-secondary">How can we help you? <span class="text-danger">*</span></label>
              <textarea name="message" rows="4" class="form-control contact-form-control" placeholder="Tell us about your planned dates, number of guests, or any special requests..." required></textarea>
            </div>
            <div class="col-12 d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2">
              <button type="submit" name="contact_submit" value="1" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                <i class="fas fa-paper-plane me-2"></i>Send Message
              </button>
              <span class="text-muted small"><i class="fas fa-lock me-1"></i>Your details are kept private</span>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Right Column: Interactive Map & Arrival Info -->
    <div class="col-lg-6">
      <div class="contact-bento-card p-4 d-flex flex-column justify-content-between">
        <div>
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <div>
              <span class="badge rounded-pill px-3 py-1 mb-2" style="background: rgba(27, 138, 90, 0.1); color: #1b8a5a; font-weight: 600;">Directions &amp; Map</span>
              <h2 class="h4 fw-bold mb-1">Find Us in Fort Portal</h2>
            </div>
            <a href="https://maps.google.com/?q=Kalitusi+Nature+Resort+Fort+Portal" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success rounded-pill px-3">
              <i class="fas fa-directions me-1"></i>Open in Google Maps
            </a>
          </div>

          <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow-sm mb-4" style="border: 1px solid rgba(15, 23, 42, 0.08);">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4857.0702359726465!2d30.266143909138492!3d0.663576786071649!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1763da16b94562db%3A0x8733760ab8484d15!2sKalitusi%20Nature%20Resort!5e1!3m2!1sen!2sug!4v1763354534971!5m2!1sen!2sug"
                    style="border:0; width: 100%; height: 100%;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Kalitusi Nature Resort Location">
            </iframe>
          </div>
        </div>

        <div class="row g-3 pt-2 border-top">
          <div class="col-sm-6">
            <div class="d-flex align-items-start gap-2">
              <i class="fas fa-clock text-success mt-1"></i>
              <div class="small">
                <strong class="d-block text-dark">Check-In &amp; Check-Out</strong>
                <span class="text-muted">Check-in: 2:00 PM &bull; Check-out: 11:00 AM</span>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="d-flex align-items-start gap-2">
              <i class="fas fa-shuttle-van text-success mt-1"></i>
              <div class="small">
                <strong class="d-block text-dark">Airport &amp; Town Transfers</strong>
                <span class="text-muted">Pickup available upon prior request</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
