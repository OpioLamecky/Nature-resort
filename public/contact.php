<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$title = 'Contact Us - Kalitusi Nature Resort';

// Load site settings when available
$settings = [];
try {
  $st = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
  foreach ($st->fetchAll() as $row) { $settings[$row['setting_key']] = $row['setting_value']; }
} catch (Throwable $e) { /* ignore */ }

include __DIR__ . '/../includes/layout_header.php';
?>

<nav aria-label="breadcrumb" class="mb-2 mt-2">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= base_url('public/') ?>">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Contact Us</li>
  </ol>
</nav>

<div class="mb-2">
  <a class="btn btn-sm btn-outline-secondary" href="javascript:history.back()">Back</a>
</div>

<!-- Tabs -->
<ul class="nav nav-pills mb-3" id="contactTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="support-tab" data-bs-toggle="pill" data-bs-target="#support" type="button" role="tab" aria-controls="support" aria-selected="true">Contact & Bookings</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="findus-tab" data-bs-toggle="pill" data-bs-target="#findus" type="button" role="tab" aria-controls="findus" aria-selected="false">Find Us</button>
  </li>
</ul>

<div class="tab-content contact-layout" id="contactTabContent">
  <!-- Support / Report tab -->
  <div class="tab-pane fade show active" id="support" role="tabpanel" aria-labelledby="support-tab" tabindex="0">
    <div class="row g-3">
      <!-- Write to us for support -->
      <div class="col-lg-4">
        <div class="card h-100"><div class="card-body">
          <h2 class="h6 border-bottom pb-2">Get in Touch</h2>
          <ul class="list-unstyled mb-0 small">
            <li class="mb-2"><i class="fas fa-envelope text-primary"></i> <strong>Email</strong><br><a href="mailto:<?= sanitize($settings['contact_email'] ?? 'info@kalitusiresort.com') ?>"><?= sanitize($settings['contact_email'] ?? 'info@kalitusiresort.com') ?></a></li>
            <li class="mb-2"><i class="fab fa-whatsapp text-success"></i> <strong>WhatsApp</strong><br><a href="https://wa.me/256772957573">+256 772 957 573</a></li>
            <li class="mb-2"><i class="fab fa-facebook text-primary"></i> <strong>Facebook</strong><br><a href="#" target="_blank" rel="noopener">@KalitusiResort</a></li>
            <li class="mb-2"><i class="fab fa-instagram text-danger"></i> <strong>Instagram</strong><br><a href="#" target="_blank" rel="noopener">@kalitusiresort</a></li>
            <li class="mb-2"><i class="fab fa-twitter text-info"></i> <strong>Twitter</strong><br><a href="#" target="_blank" rel="noopener">@KalitusiResort</a></li>
          </ul>
        </div></div>
      </div>

      <!-- Call us for support -->
      <div class="col-lg-4">
        <div class="card h-100"><div class="card-body">
          <h2 class="h6 border-bottom pb-2">Call Us</h2>
          <ul class="list-unstyled mb-0 small">
            <li class="mb-2"><i class="fas fa-phone text-primary"></i> <strong>Main Line</strong><br>+256 772 957 573</li>
            <li class="mb-2">📞 <strong>Non Toll-free</strong><br>0323444602 / 0323444603<br>Or: 0417444602 / 0417444603</li>
          </ul>
        </div></div>
      </div>

      <!-- Call to report / whistleblow -->
      <div class="col-lg-4">
        <div class="card h-100"><div class="card-body">
          <h2 class="h6 border-bottom pb-2">Call us to report/whistleblow</h2>
          <ul class="list-unstyled mb-0 small">
            <li class="mb-2">📞 <strong>Tax Evasion</strong><br>0323442055</li>
            <li class="mb-2">📞 <strong>Report Staff</strong><br>0323443033</li>
          </ul>
        </div></div>
      </div>
    </div>

    <div class="d-flex align-items-center gap-2 mt-3 small">
      <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">Print</button>
      <a class="btn btn-sm btn-outline-secondary" href="#">PDF</a>
      <a class="btn btn-sm btn-outline-secondary" href="mailto:<?= sanitize($settings['contact_email'] ?? 'services@ura.go.ug') ?>">Email</a>
    </div>
  </div>

  <!-- Find Us tab -->
  <div class="tab-pane fade" id="findus" role="tabpanel" aria-labelledby="findus-tab" tabindex="0">
    <div class="card"><div class="card-body">
      <h2 class="h6">Find Us</h2>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="small">Resort Address</div>
          <div class="small">
            <?= $settings['address_html'] ?? 'Kalitusi Nature Resort<br>Fort Portal Tourism City<br>Western Uganda' ?>
          </div>
        </div>
        <div class="col-md-6">
          <div class="ratio ratio-16x9">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4857.0702359726465!2d30.266143909138492!3d0.663576786071649!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1763da16b94562db%3A0x8733760ab8484d15!2sKalitusi%20Nature%20Resort!5e1!3m2!1sen!2sug!4v1763354534971!5m2!1sen!2sug" 
                    style="border:0; width: 100%; height: 100%;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Kalitusi Nature Resort Location">
            </iframe>
          </div>
        </div>
      </div>
    </div></div>
  </div>
</div>

<?php include __DIR__ . '/../includes/layout_footer.php';
