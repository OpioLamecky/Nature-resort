<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$title = 'Careers | Kalitusi Nature Resort';
$openingRoles = array_map(static fn(array $row): string => $row[0] ?? '', site_content_rows('careers', 'openings_list', "Guest Relations Officers\nChefs and Kitchen Staff\nHousekeeping Team\nTour Guides\nFront Desk Agents", "\n"));
$careerBenefits = array_map(static fn(array $row): string => $row[0] ?? '', site_content_rows('careers', 'benefits_list', "Competitive salary packages\nStaff accommodation available\nProfessional development opportunities\nBeautiful work environment", "\n"));
include __DIR__ . '/../includes/layout_header.php';
?>
<div id="oppHero" class="carousel slide hero-slider mb-0" data-bs-ride="carousel">
  <div class="carousel-inner">
    <div class="carousel-item active">
      <img src="<?= sanitize(site_content_image('careers', 'hero_image', 'images/rooms.webp')) ?>" alt="Careers">
      <div class="carousel-caption text-start hero-caption">
        <h2 class="h3 mb-1"><?= sanitize(site_content('careers', 'title', 'Join Our Team')) ?></h2>
        <p><?= sanitize(site_content('careers', 'subtitle', 'Build a rewarding career in hospitality and conservation')) ?></p>
      </div>
    </div>
  </div>
</div>
<div class="container py-3">
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= base_url('public/') ?>">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Careers</li>
    </ol>
  </nav>
  <div class="row g-3">
  <div class="col-lg-8">
    <div class="card mb-3"><div class="card-body">
      <h3 class="h5">Current Openings</h3>
      <p>Join our passionate team and be part of Uganda's premier nature resort. We're looking for hospitality professionals who share our commitment to excellence and sustainability.</p>
      <ul class="mb-3">
          <?php foreach ($openingRoles as $role): if ($role !== ''): ?><li><?= sanitize($role) ?></li><?php endif; endforeach; ?>
      </ul>
        <a class="btn btn-sm btn-outline-primary" href="<?= base_url('public/contact.php') ?>"><?= sanitize(site_content('careers', 'apply_label', 'Apply Now')) ?></a>
    </div></div>
    <div class="card mb-3"><div class="card-body">
      <h3 class="h5"><?= sanitize(site_content('careers', 'benefits_title', 'Why Work With Us')) ?></h3>
      <p><?= sanitize(site_content('careers', 'benefits_copy', "We offer competitive benefits, ongoing training, and the opportunity to work in one of Uganda's most beautiful natural settings.")) ?></p>
      <ul class="mb-0">
        <?php foreach ($careerBenefits as $benefit): if ($benefit !== ''): ?><li><?= sanitize($benefit) ?></li><?php endif; endforeach; ?>
      </ul>
    </div></div>
  </div>
  <div class="col-lg-4">
    <div class="card h-100"><div class="card-body">
      <h3 class="h6"><?= sanitize(site_content('careers', 'resources_title', 'Resources')) ?></h3>
      <ul class="mb-0">
        <li><a href="<?= base_url('public/brochures.php') ?>"><?= sanitize(site_content('careers', 'resource_application', 'Download Application Form')) ?></a></li>
        <li><a href="<?= base_url('public/contact.php') ?>"><?= sanitize(site_content('careers', 'resource_hr', 'Contact HR Department')) ?></a></li>
        <li><a href="<?= base_url('public/about.php') ?>"><?= sanitize(site_content('careers', 'resource_team', 'About Our Team')) ?></a></li>
      </ul>
    </div></div>
  </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/layout_footer.php';
