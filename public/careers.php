<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$title = 'Careers | Kalitusi Nature Resort';
include __DIR__ . '/../includes/layout_header.php';
?>
<div id="oppHero" class="carousel slide hero-slider mb-0" data-bs-ride="carousel">
  <div class="carousel-inner">
    <div class="carousel-item active">
      <img src="<?= asset_url('images/hero-careers-1.jpg') ?>" alt="Careers" onerror="this.src='https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=1600&h=360&fit=crop'">
      <div class="carousel-caption text-start hero-caption">
        <h2 class="h3 mb-1">Join Our Team</h2>
        <p>Explore exciting career opportunities at Kalitusi</p>
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
        <li>Guest Relations Officers</li>
        <li>Chefs and Kitchen Staff</li>
        <li>Housekeeping Team</li>
        <li>Tour Guides</li>
        <li>Front Desk Agents</li>
      </ul>
      <a class="btn btn-sm btn-outline-primary" href="<?= base_url('public/contact.php') ?>">Apply Now</a>
    </div></div>
    <div class="card mb-3"><div class="card-body">
      <h3 class="h5">Why Work With Us</h3>
      <p>We offer competitive benefits, ongoing training, and the opportunity to work in one of Uganda's most beautiful natural settings.</p>
      <ul class="mb-0">
        <li>Competitive salary packages</li>
        <li>Staff accommodation available</li>
        <li>Professional development opportunities</li>
        <li>Beautiful work environment</li>
      </ul>
    </div></div>
  </div>
  <div class="col-lg-4">
    <div class="card h-100"><div class="card-body">
      <h3 class="h6">Resources</h3>
      <ul class="mb-0">
        <li><a href="<?= base_url('public/brochures.php') ?>">Download Application Form</a></li>
        <li><a href="<?= base_url('public/contact.php') ?>">Contact HR Department</a></li>
        <li><a href="<?= base_url('public/about.php') ?>">About Our Team</a></li>
      </ul>
    </div></div>
  </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/layout_footer.php';
