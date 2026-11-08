<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$title = 'About Us | Kalitusi Nature Resort';

// Fetch About Us content from database
$content = [];
try {
  $stmt = $pdo->query("SELECT section, title, content FROM about_content WHERE status='active' ORDER BY display_order");
  foreach ($stmt->fetchAll() as $row) {
    $content[$row['section']] = $row;
  }
} catch (Throwable $e) { /* ignore */ }

// Fetch leadership
$leaders = [];
try {
  $stmt = $pdo->query("SELECT * FROM about_leadership WHERE status='active' ORDER BY display_order, leader_id LIMIT 6");
  $leaders = $stmt->fetchAll();
} catch (Throwable $e) { /* ignore */ }

// Fetch achievements
$achievements = [];
try {
  $stmt = $pdo->query("SELECT * FROM about_achievements WHERE status='active' ORDER BY display_order, achievement_id LIMIT 4");
  $achievements = $stmt->fetchAll();
} catch (Throwable $e) { /* ignore */ }

// Fetch timeline
$timeline = [];
try {
  $stmt = $pdo->query("SELECT * FROM about_timeline WHERE status='active' ORDER BY display_order, timeline_id");
  $timeline = $stmt->fetchAll();
} catch (Throwable $e) { /* ignore */ }

if (empty($timeline)) {
  $timeline = [
    ['year' => '2010', 'title' => 'Kalitusi Vision Born', 'description' => 'Founded as an eco-conscious sanctuary in Fort Portal Tourism City.'],
    ['year' => '2016', 'title' => 'Luxury Cottages & Dining', 'description' => 'Expanded with forest chalets and the scenic Mountain View Restaurant.'],
    ['year' => '2022', 'title' => 'Glamping & Conservation Hub', 'description' => 'Introduced luxury safari tents and guided birdwatching trails for 300+ species.'],
  ];
}

if (empty($achievements)) {
  $achievements = [
    ['stat_value' => '500+', 'stat_label' => 'Happy International Guests', 'description' => 'Five-star hospitality across every stay'],
    ['stat_value' => '300+', 'stat_label' => 'Bird Species Recorded', 'description' => 'Protected native forest habitat'],
    ['stat_value' => '100%', 'stat_label' => 'Locally Sourced Harvest', 'description' => 'Supporting Fort Portal farmers & artisans'],
    ['stat_value' => '4.9★', 'stat_label' => 'Guest Satisfaction Score', 'description' => 'Top-rated eco-resort in Western Uganda'],
  ];
}

include __DIR__ . '/../includes/layout_header.php';
?>

<style>
  .about-bento-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid rgba(15, 23, 42, 0.07);
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
    padding: 1.75rem;
    height: 100%;
    transition: transform 0.22s ease, box-shadow 0.22s ease;
  }
  .about-bento-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 32px rgba(15, 23, 42, 0.09);
  }
  .about-timeline-step {
    position: relative;
    padding: 1.1rem 1.25rem;
    background: #f8faf9;
    border-radius: 16px;
    border: 1px solid rgba(15, 23, 42, 0.06);
    margin-bottom: 0.85rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
  }
  .about-timeline-step:last-child {
    margin-bottom: 0;
  }
  .about-timeline-year {
    background: linear-gradient(135deg, #1a4d2e, #286e44);
    color: #ffffff;
    font-weight: 700;
    font-size: 0.82rem;
    padding: 0.35rem 0.75rem;
    border-radius: 999px;
    flex-shrink: 0;
  }
</style>

<div class="container mt-3">
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= base_url('public/') ?>">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">About Us</li>
    </ol>
  </nav>
</div>

<!-- Hero Section -->
<section class="page-intro-hero bg-soft-mint">
  <div class="container">
    <div class="row align-items-center g-4 g-lg-5">
      <div class="col-lg-7 text-center text-lg-start">
        <span class="badge rounded-pill px-3 py-2 mb-2" style="background: rgba(27, 138, 90, 0.12); color: #1b8a5a; font-weight: 600;">
          <i class="fas fa-leaf me-1"></i> Our Heritage &amp; Purpose
        </span>
        <h1 class="display-5 fw-bold mb-3"><?= sanitize(site_content('about', 'hero_title', 'About Kalitusi Nature Resort')) ?></h1>
        <p class="lead mb-4 mx-auto mx-lg-0"><?= sanitize(site_content('about', 'hero_subtitle', 'Your Gateway to Nature, Tranquility, and Authentic Hospitality in Fort Portal Tourism City.')) ?></p>
        <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-lg-start">
          <a href="<?= base_url('public/accommodations.php') ?>" class="btn btn-primary rounded-pill px-4">Explore Stays</a>
          <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-primary rounded-pill px-4">Get in Touch</a>
        </div>
      </div>
      <div class="col-lg-5">
        <img src="<?= sanitize(site_content_image('about', 'hero_image', 'images/view.webp')) ?>" alt="Kalitusi Nature Resort" onerror="this.src='<?= asset_url('images/rooms.webp') ?>'">
      </div>
    </div>
  </div>
</section>

<div class="container py-4">
  <!-- Our Story & Milestones Split Section -->
  <div class="row g-4 align-items-center mb-5">
    <div class="col-lg-6">
      <div class="about-bento-card">
        <span class="badge rounded-pill px-3 py-1 mb-2" style="background: rgba(27, 138, 90, 0.1); color: #1b8a5a; font-weight: 600;">Who We Are</span>
        <h2 class="h3 fw-bold mb-3"><?= sanitize($content['history']['title'] ?? 'Our Story') ?></h2>
        <p class="text-secondary mb-4" style="line-height: 1.7;">
          <?= sanitize($content['history']['content'] ?? 'Kalitusi Nature Resort was born from a passion for Uganda\'s natural beauty and a vision to share Fort Portal\'s pristine wilderness with the world. Established in the heart of Fort Portal Tourism City, our resort has grown from a simple eco-lodge to a premier destination that seamlessly blends luxury hospitality with environmental conservation. Every detail of Kalitusi reflects our commitment to sustainable tourism and our deep respect for the land and local communities.') ?>
        </p>
        <div class="row g-2">
          <div class="col-6">
            <img src="<?= asset_url('images/shade.webp') ?>" alt="Forest Shade at Kalitusi" class="w-100 rounded-4 object-fit-cover" style="height: 140px;">
          </div>
          <div class="col-6">
            <img src="<?= asset_url('images/picnic.webp') ?>" alt="Resort Gardens" class="w-100 rounded-4 object-fit-cover" style="height: 140px;">
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="about-bento-card">
        <span class="badge rounded-pill px-3 py-1 mb-2" style="background: rgba(217, 119, 6, 0.12); color: #b45309; font-weight: 600;">Milestones</span>
        <h3 class="h4 fw-bold mb-3">Our Journey Through the Years</h3>
        <div>
          <?php foreach ($timeline as $t): ?>
            <div class="about-timeline-step">
              <span class="about-timeline-year"><?= sanitize($t['year'] ?? '2010') ?></span>
              <div>
                <h4 class="h6 fw-bold mb-1" style="color: #0f172a;"><?= sanitize($t['title'] ?? 'Milestone') ?></h4>
                <p class="small text-muted mb-0"><?= sanitize($t['description'] ?? '') ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Mission & Vision Bento Cards -->
  <div class="row g-4 mb-5">
    <div class="col-lg-6">
      <div class="about-bento-card" style="background: linear-gradient(145deg, #ffffff 0%, #f3f9f5 100%);">
        <div class="d-flex align-items-center gap-3 mb-3">
          <span class="d-inline-flex align-items-center justify-content-center rounded-4" style="width: 56px; height: 56px; background: rgba(27, 138, 90, 0.14); color: #1b8a5a; font-size: 1.5rem;">
            <i class="fas fa-bullseye"></i>
          </span>
          <div>
            <span class="small text-uppercase fw-bold text-success" style="letter-spacing: 0.06em;">Purpose</span>
            <h3 class="h4 fw-bold mb-0"><?= sanitize($content['mission']['title'] ?? 'Our Mission') ?></h3>
          </div>
        </div>
        <p class="text-secondary mb-0" style="line-height: 1.65;">
          <?= sanitize($content['mission']['content'] ?? 'To provide an exceptional eco-tourism experience that harmonizes luxury with nature conservation, offering our guests unforgettable moments while preserving the pristine beauty of Fort Portal for future generations.') ?>
        </p>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="about-bento-card" style="background: linear-gradient(145deg, #ffffff 0%, #fdfaf2 100%);">
        <div class="d-flex align-items-center gap-3 mb-3">
          <span class="d-inline-flex align-items-center justify-content-center rounded-4" style="width: 56px; height: 56px; background: rgba(201, 169, 97, 0.22); color: #8c6d23; font-size: 1.5rem;">
            <i class="fas fa-eye"></i>
          </span>
          <div>
            <span class="small text-uppercase fw-bold" style="letter-spacing: 0.06em; color: #8c6d23;">Future</span>
            <h3 class="h4 fw-bold mb-0"><?= sanitize($content['vision']['title'] ?? 'Our Vision') ?></h3>
          </div>
        </div>
        <p class="text-secondary mb-0" style="line-height: 1.65;">
          <?= sanitize($content['vision']['content'] ?? 'To become Uganda\'s premier eco-resort destination, renowned for exceptional hospitality, sustainable practices, and unique nature-based experiences that inspire environmental stewardship.') ?>
        </p>
      </div>
    </div>
  </div>

  <!-- Core Values Section -->
  <div class="mb-5">
    <div class="text-center mb-4">
      <span class="badge rounded-pill px-3 py-2 mb-2" style="background: rgba(27, 138, 90, 0.12); color: #1b8a5a; font-weight: 600;">What Guides Us</span>
      <h2 class="h3 fw-bold mb-1"><?= sanitize(site_content('about', 'values_title', 'Our Core Values')) ?></h2>
      <p class="text-muted mb-0"><?= sanitize(site_content('about', 'values_subtitle', 'Guiding principles that define our hospitality and conservation')) ?></p>
    </div>
    <div class="row g-4">
      <div class="col-lg-3 col-md-6">
        <div class="about-bento-card text-center">
          <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-3" style="width: 54px; height: 54px; background: rgba(27, 138, 90, 0.12); color: #1b8a5a;">
            <i class="fas fa-shield-alt" style="font-size: 1.4rem;"></i>
          </div>
          <h4 class="h5 fw-bold"><?= sanitize($content['value_integrity']['title'] ?? 'Integrity') ?></h4>
          <p class="small text-muted mb-0"><?= sanitize($content['value_integrity']['content'] ?? 'We maintain honesty, transparency, and ethical standards in all our operations.') ?></p>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="about-bento-card text-center">
          <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-3" style="width: 54px; height: 54px; background: rgba(37, 99, 235, 0.1); color: #2563eb;">
            <i class="fas fa-users" style="font-size: 1.4rem;"></i>
          </div>
          <h4 class="h5 fw-bold"><?= sanitize($content['value_teamwork']['title'] ?? 'Teamwork') ?></h4>
          <p class="small text-muted mb-0"><?= sanitize($content['value_teamwork']['content'] ?? 'Working hand-in-hand with our staff and local community to deliver excellence.') ?></p>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="about-bento-card text-center">
          <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-3" style="width: 54px; height: 54px; background: rgba(217, 119, 6, 0.12); color: #d97706;">
            <i class="fas fa-lightbulb" style="font-size: 1.4rem;"></i>
          </div>
          <h4 class="h5 fw-bold"><?= sanitize($content['value_innovation']['title'] ?? 'Sustainability') ?></h4>
          <p class="small text-muted mb-0"><?= sanitize($content['value_innovation']['content'] ?? 'Embracing eco-friendly solutions that protect our forest and wildlife.') ?></p>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="about-bento-card text-center">
          <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-3" style="width: 54px; height: 54px; background: rgba(225, 29, 72, 0.1); color: #e11d48;">
            <i class="fas fa-heart" style="font-size: 1.4rem;"></i>
          </div>
          <h4 class="h5 fw-bold"><?= sanitize($content['value_patriotism']['title'] ?? 'Warm Hospitality') ?></h4>
          <p class="small text-muted mb-0"><?= sanitize($content['value_patriotism']['content'] ?? 'Welcoming every guest with authentic Ugandan warmth and care.') ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Resort Services Section (Visual Scrim Header + Checklist) -->
  <div class="mb-5">
    <div class="text-center mb-4">
      <span class="badge rounded-pill px-3 py-2 mb-2" style="background: rgba(27, 138, 90, 0.12); color: #1b8a5a; font-weight: 600;">Everything Under One Canopy</span>
      <h2 class="h3 fw-bold mb-1"><?= sanitize(site_content('about', 'services_title', 'Our Resort Offerings')) ?></h2>
    </div>
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="about-bento-card p-0 overflow-hidden">
          <div class="scrim-card scrim-card-compact rounded-0" style="min-height: 190px;">
            <img class="scrim-card-bg" src="<?= asset_url('images/cabin.webp') ?>" alt="Accommodation" loading="lazy">
            <div class="scrim-badge-top"><span class="badge-dot"></span> Stays</div>
            <div class="scrim-card-content">
              <h3 class="scrim-card-title mb-0"><i class="fas fa-bed me-2" style="color: var(--scrim-lime);"></i><?= sanitize(site_content('about', 'service_stays_title', 'Accommodation')) ?></h3>
            </div>
          </div>
          <div class="p-4">
            <ul class="list-unstyled mb-0">
              <?php foreach (site_content_rows('about', 'service_stays_items', "Luxury Suites\nStandard Rooms\nFamily Cottages\nEco-Friendly Lodges\nConference Facilities", "\n") as $service): ?>
                <li class="py-2 border-bottom small d-flex align-items-center gap-2"><i class="fas fa-check-circle text-success"></i><span><?= sanitize($service[0] ?? '') ?></span></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="about-bento-card p-0 overflow-hidden">
          <div class="scrim-card scrim-card-compact rounded-0" style="min-height: 190px;">
            <img class="scrim-card-bg" src="<?= asset_url('images/birds.webp') ?>" alt="Nature Activities" loading="lazy">
            <div class="scrim-badge-top"><span class="badge-dot"></span> Adventure</div>
            <div class="scrim-card-content">
              <h3 class="scrim-card-title mb-0"><i class="fas fa-tree me-2" style="color: var(--scrim-lime);"></i><?= sanitize(site_content('about', 'service_activities_title', 'Nature Activities')) ?></h3>
            </div>
          </div>
          <div class="p-4">
            <ul class="list-unstyled mb-0">
              <?php foreach (site_content_rows('about', 'service_activities_items', "Guided Nature Walks\nBird Watching Tours\nWildlife Photography\nHiking Adventures\nCultural Experiences", "\n") as $service): ?>
                <li class="py-2 border-bottom small d-flex align-items-center gap-2"><i class="fas fa-check-circle text-success"></i><span><?= sanitize($service[0] ?? '') ?></span></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="about-bento-card p-0 overflow-hidden">
          <div class="scrim-card scrim-card-compact rounded-0" style="min-height: 190px;">
            <img class="scrim-card-bg" src="<?= asset_url('images/food1.webp') ?>" alt="Wellness & Dining" loading="lazy">
            <div class="scrim-badge-top"><span class="badge-dot"></span> Dining &amp; Rest</div>
            <div class="scrim-card-content">
              <h3 class="scrim-card-title mb-0"><i class="fas fa-spa me-2" style="color: var(--scrim-lime);"></i><?= sanitize(site_content('about', 'service_wellness_title', 'Wellness & Dining')) ?></h3>
            </div>
          </div>
          <div class="p-4">
            <ul class="list-unstyled mb-0">
              <?php foreach (site_content_rows('about', 'service_wellness_items', "Spa & Relaxation\nLocal Cuisine Restaurant\nEvening Bonfires\nBotanical Gardens\nPrivate Event Hosting", "\n") as $service): ?>
                <li class="py-2 border-bottom small d-flex align-items-center gap-2"><i class="fas fa-check-circle text-success"></i><span><?= sanitize($service[0] ?? '') ?></span></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Achievements Section -->
  <div class="mb-5">
    <div class="text-center mb-4">
      <span class="badge rounded-pill px-3 py-2 mb-2" style="background: rgba(27, 138, 90, 0.12); color: #1b8a5a; font-weight: 600;">Impact &amp; Recognition</span>
      <h2 class="h3 fw-bold mb-1"><?= sanitize(site_content('about', 'achievements_title', 'Key Achievements')) ?></h2>
    </div>
    <div class="row g-4 text-center">
      <?php foreach ($achievements as $ach): ?>
        <div class="col-lg-3 col-md-6">
          <div class="about-bento-card">
            <div class="display-6 fw-bold mb-1" style="color: #1b8a5a;"><?= sanitize($ach['stat_value']) ?></div>
            <h3 class="h6 fw-bold text-dark mb-1"><?= sanitize($ach['stat_label']) ?></h3>
            <?php if (!empty($ach['description'])): ?>
              <small class="text-muted d-block"><?= sanitize($ach['description']) ?></small>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Leadership Section (Shown when configured or with Resort Host card) -->
  <?php if (!empty($leaders)): ?>
  <div class="mb-5">
    <div class="text-center mb-4">
      <span class="badge rounded-pill px-3 py-2 mb-2" style="background: rgba(27, 138, 90, 0.12); color: #1b8a5a; font-weight: 600;">Hospitality Team</span>
      <h2 class="h3 fw-bold mb-1"><?= sanitize(site_content('about', 'leaders_title', 'Our Leadership')) ?></h2>
    </div>
    <div class="row g-4 justify-content-center">
      <?php foreach ($leaders as $leader): ?>
        <div class="col-lg-4 col-md-6">
          <div class="about-bento-card text-center">
            <div class="mb-3">
              <?php 
                $imgPath = !empty($leader['image_url'])
                  ? cms_image_url($leader['image_url'])
                  : asset_url('images/resort-host-manager.jpg');
              ?>
              <img src="<?= sanitize($imgPath) ?>" alt="<?= sanitize($leader['name']) ?>" style="width: 96px; height: 96px; border-radius: 50%; object-fit: cover; border: 3px solid rgba(27,138,90,0.2);" onerror="this.src='<?= asset_url('images/default-avatar.png') ?>'">
            </div>
            <h4 class="h5 fw-bold mb-1"><?= sanitize($leader['name']) ?></h4>
            <p class="text-success small fw-semibold mb-2"><?= sanitize($leader['position']) ?></p>
            <?php if (!empty($leader['bio'])): ?>
              <p class="small text-muted mb-0"><?= sanitize($leader['bio']) ?></p>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Contact CTA Section -->
  <div class="rounded-4 p-4 p-lg-5 text-white text-center shadow-lg mb-4" style="background: linear-gradient(115deg, rgba(15, 41, 30, 0.88) 0%, rgba(26, 77, 46, 0.78) 100%), url('<?= asset_url('images/campfire.webp') ?>') center / cover no-repeat;">
    <h2 class="h3 fw-bold text-white mb-2"><?= sanitize(site_content('about', 'cta_title', 'Visit Kalitusi Nature Resort')) ?></h2>
    <p class="lead mb-4 mx-auto" style="max-width: 38rem; color: rgba(255,255,255,0.9);"><?= sanitize(site_content('about', 'cta_copy', "We're here to make your stay unforgettable. Contact us for bookings or custom group retreats.")) ?></p>
    <div class="d-flex flex-wrap justify-content-center gap-3">
      <a href="<?= base_url('public/booking.php') ?>" class="btn btn-light btn-lg rounded-pill px-4 fw-semibold" style="color: #143625;"><?= sanitize(site_content('about', 'cta_booking_label', 'Book Your Stay')) ?></a>
      <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-light btn-lg rounded-pill px-4"><?= sanitize(site_content('about', 'cta_contact_label', 'Contact Us')) ?></a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
