  </main>

  <?php
  // Load footer data from DB (with graceful fallbacks if tables are empty)
  $settings = [];
  $footerBySection = ['about'=>[], 'useful'=>[], 'social'=>[], 'legal'=>[]];
  try {
    if (isset($pdo)) {
      $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
      foreach ($stmt->fetchAll() as $row) { $settings[$row['setting_key']] = $row['setting_value']; }
      $rows = $pdo->query("SELECT section,label,url FROM footer_links WHERE status='visible' ORDER BY section, display_order, footer_link_id")->fetchAll();
      foreach ($rows as $r) { $footerBySection[$r['section']][] = $r; }
    }
  } catch (Throwable $e) { /* ignore */ }

  $footerPhone = site_content('contact', 'phone', $settings['contact_phone'] ?? '+256 772 957 573');
  $footerEmail = site_content('contact', 'email', $settings['contact_email'] ?? 'info@kalitusi.com');
  $footerAddress = site_content('contact', 'address', $settings['address_html'] ?? "Kalitusi Nature Resort\nFort Portal Tourism City, Western Uganda");
  $whatsappNumber = preg_replace('/[^0-9]/', '', $settings['whatsapp_number'] ?? '256772957573');
  $whatsappMessage = urlencode('Hello Kalitusi Resort, I would like to inquire about...');
  $whatsappLink = site_content('contact', 'whatsapp_link', 'https://wa.me/' . $whatsappNumber . '?text=' . $whatsappMessage);
  if (!str_contains($whatsappLink, '?')) {
    $whatsappLink .= '?text=' . $whatsappMessage;
  }
  $fbUrl = site_content('contact', 'facebook_url', 'https://www.facebook.com/kalitusinatureresort');
  $igUrl = site_content('contact', 'instagram_url', 'https://www.instagram.com/kalitusinatureresort');
  $twUrl = site_content('contact', 'twitter_url', 'https://x.com/kalitusifortportal');
  ?>

  <style>
    .luxury-footer {
      background: linear-gradient(165deg, #0d2118 0%, #132f22 55%, #0a1912 100%);
      color: rgba(248, 250, 252, 0.82);
      position: relative;
      overflow: hidden;
      border-top: 1px solid rgba(163, 230, 53, 0.18);
    }
    .luxury-footer::before {
      content: '';
      position: absolute;
      top: -120px;
      right: -120px;
      width: 380px;
      height: 380px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(52, 211, 153, 0.08) 0%, transparent 70%);
      pointer-events: none;
    }
    .luxury-footer-newsletter {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 20px;
      padding: 1.6rem 2rem;
      margin-bottom: 3rem;
    }
    .luxury-footer-input {
      background: rgba(15, 23, 42, 0.45) !important;
      border: 1px solid rgba(255, 255, 255, 0.18) !important;
      color: #ffffff !important;
      border-radius: 999px !important;
      padding: 0.65rem 1.25rem !important;
      font-size: 0.92rem;
    }
    .luxury-footer-input::placeholder {
      color: rgba(255, 255, 255, 0.5) !important;
    }
    .luxury-footer-input:focus {
      border-color: #a3e635 !important;
      box-shadow: 0 0 0 3px rgba(163, 230, 53, 0.18) !important;
    }
    .luxury-footer-btn {
      border-radius: 999px !important;
      background: linear-gradient(135deg, #22c55e, #15803d) !important;
      border: none !important;
      color: #ffffff !important;
      font-weight: 600 !important;
      padding: 0.65rem 1.5rem !important;
      white-space: nowrap;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .luxury-footer-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 8px 20px rgba(34, 197, 94, 0.3);
    }
    .luxury-footer-heading {
      color: #ffffff;
      font-size: 0.88rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      margin-bottom: 1.25rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .luxury-footer-heading::after {
      content: '';
      height: 2px;
      width: 24px;
      background: #a3e635;
      border-radius: 999px;
      display: inline-block;
    }
    .luxury-footer-nav {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .luxury-footer-nav li {
      margin-bottom: 0.65rem;
    }
    .luxury-footer-nav a {
      color: rgba(248, 250, 252, 0.72);
      text-decoration: none;
      font-size: 0.9rem;
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      transition: color 0.2s ease, transform 0.2s ease;
    }
    .luxury-footer-nav a:hover {
      color: #a3e635;
      transform: translateX(4px);
    }
    .luxury-footer-social {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.07);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: #ffffff;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
      transition: all 0.2s ease;
    }
    .luxury-footer-social:hover {
      background: #1b8a5a;
      border-color: #34d399;
      color: #ffffff;
      transform: translateY(-3px);
    }
    .luxury-contact-item {
      display: flex;
      align-items: flex-start;
      gap: 0.85rem;
      margin-bottom: 0.9rem;
      font-size: 0.88rem;
    }
    .luxury-contact-icon {
      width: 34px;
      height: 34px;
      border-radius: 10px;
      background: rgba(163, 230, 53, 0.12);
      color: #a3e635;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      font-size: 0.88rem;
    }
    .luxury-contact-item a {
      color: rgba(248, 250, 252, 0.86);
      text-decoration: none;
      transition: color 0.2s ease;
    }
    .luxury-contact-item a:hover {
      color: #a3e635;
    }
    .luxury-footer-bottom {
      background: rgba(6, 15, 11, 0.65);
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      padding: 1.15rem 0;
      font-size: 0.83rem;
      color: rgba(248, 250, 252, 0.65);
    }
    .luxury-footer-bottom a {
      color: rgba(248, 250, 252, 0.72);
      text-decoration: none;
      transition: color 0.2s ease;
    }
    .luxury-footer-bottom a:hover {
      color: #a3e635;
    }
  </style>

  <!-- Luxury Eco-Resort Footer -->
  <footer class="luxury-footer pt-5">
    <div class="container pb-4">
      <!-- Top Newsletter & Exclusive Offers Bento Bar -->
      <div class="luxury-footer-newsletter">
        <div class="row align-items-center g-4">
          <div class="col-lg-6">
            <div class="d-flex align-items-center gap-2 mb-1">
              <span class="badge rounded-pill" style="background: rgba(163, 230, 53, 0.18); color: #d9f99d; font-size: 0.72rem; font-weight: 600;">
                <i class="fas fa-envelope-open-text me-1"></i> Resort Journal
              </span>
              <span class="small" style="color: rgba(255,255,255,0.6);">Seasonal Offers &amp; Stories</span>
            </div>
            <h3 class="h5 fw-bold text-white mb-1">Stay Connected With Kalitusi Nature Resort</h3>
            <p class="small mb-0" style="color: rgba(255,255,255,0.7);">Subscribe for exclusive getaway packages, birdwatching updates, and Fort Portal travel guides.</p>
          </div>
          <div class="col-lg-6">
            <form id="newsletterForm" class="d-flex flex-column flex-sm-row gap-2">
              <input type="email" name="email" id="newsletterEmail" class="form-control luxury-footer-input" placeholder="Enter your email address..." required>
              <button class="btn luxury-footer-btn" type="submit" id="newsletterBtn">
                <span class="btn-text"><i class="fas fa-paper-plane me-2"></i>Subscribe</span>
                <span class="btn-loading d-none">
                  <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                  Sending...
                </span>
              </button>
            </form>
            <div id="newsletterMessage" class="mt-2 small"></div>
          </div>
        </div>
      </div>

      <!-- Main 4-Column Footer Content -->
      <div class="row g-4 g-lg-5">
        <!-- Column 1: Brand Identity -->
        <div class="col-12 col-lg-4">
          <a href="<?= base_url('public/') ?>" class="d-inline-flex align-items-center gap-2 text-decoration-none mb-3">
            <img src="<?= asset_url('images/kalitusi-logo-transparent.png') ?>" alt="Kalitusi Nature Resort" style="height: 44px; width: auto;" onerror="this.style.display='none'">
            <span class="fw-bold text-white" style="font-size: 1.15rem; letter-spacing: 0.04em;">KALITUSI NATURE RESORT</span>
          </a>
          <p class="small mb-3" style="color: rgba(248, 250, 252, 0.72); line-height: 1.65;">
            A tranquil eco-luxury sanctuary nestled in the lush foothills of Fort Portal Tourism City, Uganda. Experience refined cottages, farm-to-table mountain dining, and over 300 species of native birds.
          </p>
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="badge rounded-pill px-3 py-2" style="background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12); color: #d9f99d; font-weight: 600;">
              <i class="fas fa-leaf me-1"></i> Certified Eco-Sanctuary
            </span>
            <span class="badge rounded-pill px-3 py-2" style="background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12); color: #fde68a; font-weight: 600;">
              <i class="fas fa-star me-1"></i> 4.9 Guest Rating
            </span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <a href="<?= sanitize($fbUrl) ?>" target="_blank" rel="noopener" class="luxury-footer-social" title="Facebook" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="<?= sanitize($igUrl) ?>" target="_blank" rel="noopener" class="luxury-footer-social" title="Instagram" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="<?= sanitize($twUrl) ?>" target="_blank" rel="noopener" class="luxury-footer-social" title="X / Twitter" aria-label="X / Twitter"><i class="fab fa-twitter"></i></a>
            <a href="<?= sanitize($whatsappLink) ?>" target="_blank" rel="noopener" class="luxury-footer-social" title="WhatsApp" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
          </div>
        </div>

        <!-- Column 2: Explore Resort -->
        <div class="col-6 col-md-4 col-lg-2">
          <h6 class="luxury-footer-heading">Explore</h6>
          <ul class="luxury-footer-nav">
            <?php if (!empty($footerBySection['useful'])): foreach ($footerBySection['useful'] as $link): ?>
              <li><a href="<?= sanitize($link['url']) ?>"><i class="fas fa-chevron-right small opacity-50"></i> <?= sanitize($link['label']) ?></a></li>
            <?php endforeach; else: ?>
              <li><a href="<?= base_url('public/accommodations.php') ?>"><i class="fas fa-chevron-right small opacity-50"></i> Accommodations</a></li>
              <li><a href="<?= base_url('public/activities.php') ?>"><i class="fas fa-chevron-right small opacity-50"></i> Activities &amp; Tours</a></li>
              <li><a href="<?= base_url('public/dining.php') ?>"><i class="fas fa-chevron-right small opacity-50"></i> Dining &amp; Cuisine</a></li>
              <li><a href="<?= base_url('eshop.php') ?>"><i class="fas fa-chevron-right small opacity-50"></i> E-Shop &amp; Gifts</a></li>
              <li><a href="<?= base_url('public/gallery.php') ?>"><i class="fas fa-chevron-right small opacity-50"></i> Photo Gallery</a></li>
              <li><a href="<?= base_url('public/news.php') ?>"><i class="fas fa-chevron-right small opacity-50"></i> Resort Blog</a></li>
            <?php endif; ?>
          </ul>
        </div>

        <!-- Column 3: Guest Services -->
        <div class="col-6 col-md-4 col-lg-3">
          <h6 class="luxury-footer-heading">Guest Services</h6>
          <ul class="luxury-footer-nav">
            <li><a href="<?= base_url('public/booking.php') ?>"><i class="fas fa-calendar-check small text-success"></i> Book Your Stay</a></li>
            <li><a href="<?= base_url('public/about.php') ?>"><i class="fas fa-chevron-right small opacity-50"></i> Our Heritage &amp; Story</a></li>
            <li><a href="<?= base_url('public/accommodations.php') ?>"><i class="fas fa-chevron-right small opacity-50"></i> Family &amp; Child Policy</a></li>
            <li><a href="<?= base_url('public/dining.php') ?>"><i class="fas fa-chevron-right small opacity-50"></i> Table Reservations</a></li>
            <li><a href="<?= base_url('public/contact.php') ?>"><i class="fas fa-chevron-right small opacity-50"></i> Directions &amp; Transfers</a></li>
            <li><a href="<?= base_url('public/contact.php') ?>"><i class="fas fa-chevron-right small opacity-50"></i> Contact Concierge</a></li>
          </ul>
        </div>

        <!-- Column 4: Direct Contact & Location -->
        <div class="col-12 col-md-4 col-lg-3">
          <h6 class="luxury-footer-heading">Get in Touch</h6>
          <div class="luxury-contact-item">
            <span class="luxury-contact-icon"><i class="fas fa-map-marker-alt"></i></span>
            <div>
              <strong class="d-block text-white small">Resort Location</strong>
              <span style="color: rgba(248,250,252,0.72);"><?= nl2br(sanitize(str_ireplace(['<br>', '<br/>', '<br />'], "\n", $footerAddress))) ?></span>
            </div>
          </div>
          <div class="luxury-contact-item">
            <span class="luxury-contact-icon"><i class="fas fa-phone-alt"></i></span>
            <div>
              <strong class="d-block text-white small">24/7 Front Desk</strong>
              <a href="tel:<?= sanitize(preg_replace('/\s+/', '', $footerPhone)) ?>"><?= sanitize($footerPhone) ?></a>
            </div>
          </div>
          <div class="luxury-contact-item">
            <span class="luxury-contact-icon"><i class="fas fa-envelope"></i></span>
            <div>
              <strong class="d-block text-white small">Reservations Email</strong>
              <a href="mailto:<?= sanitize($footerEmail) ?>"><?= sanitize($footerEmail) ?></a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Copyright & Legal Bar -->
    <div class="luxury-footer-bottom mt-3">
      <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">
        <div>
          &copy; <?= date('Y') ?> <strong class="text-white">Kalitusi Nature Resort</strong> &mdash; Fort Portal Tourism City, Uganda. All rights reserved.
        </div>
        <div class="d-flex flex-wrap align-items-center gap-3">
          <span class="small"><i class="far fa-clock me-1" style="color: #a3e635;"></i>Check-in: 2:00 PM &bull; Check-out: 11:00 AM</span>
          <?php if (!empty($footerBySection['legal'])): foreach ($footerBySection['legal'] as $legalLink): ?>
            <a href="<?= sanitize($legalLink['url']) ?>"><?= sanitize($legalLink['label']) ?></a>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>
  </footer>

  <!-- Floating WhatsApp Chat Pill -->
  <a class="chat-pill btn btn-success btn-sm" href="<?= sanitize($whatsappLink) ?>" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
      <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
    </svg>
    <span>Chat with us</span>
  </a>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= base_url('assets/js/app.js') ?>"></script>
  <script>
    // Back to top button
    (function(){
      const btn = document.createElement('a');
      btn.href = '#';
      btn.className = 'back-to-top btn btn-primary btn-sm';
      btn.textContent = '↑';
      btn.title = 'Back to top';
      document.body.appendChild(btn);
      function toggle(){ btn.style.display = (window.scrollY > 300) ? 'inline-flex' : 'none'; }
      window.addEventListener('scroll', toggle);
      window.addEventListener('load', toggle);
      btn.addEventListener('click', function(e){ e.preventDefault(); window.scrollTo({top:0, behavior:'smooth'}); });
    })();

    // Newsletter subscription
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('newsletterForm');
      const emailInput = document.getElementById('newsletterEmail');
      const submitBtn = document.getElementById('newsletterBtn');
      const btnText = submitBtn.querySelector('.btn-text');
      const btnLoading = submitBtn.querySelector('.btn-loading');
      const messageDiv = document.getElementById('newsletterMessage');

      if (form) {
        form.addEventListener('submit', async function(e) {
          e.preventDefault();
          
          const email = emailInput.value.trim();
          if (!email) return;

          // Show loading state
          submitBtn.disabled = true;
          btnText.classList.add('d-none');
          btnLoading.classList.remove('d-none');
          messageDiv.innerHTML = '';

          try {
            const response = await fetch('<?= base_url('public/subscribe.php') ?>', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
              },
              body: JSON.stringify({ email: email })
            });

            const result = await response.json();
            
            if (result.success) {
              messageDiv.innerHTML = '<div class="text-success"><i class="fas fa-check-circle me-1"></i>' + result.message + '</div>';
              emailInput.value = '';
            } else {
              messageDiv.innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>' + result.message + '</div>';
            }
          } catch (error) {
            messageDiv.innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>An error occurred. Please try again.</div>';
          } finally {
            // Reset button state
            submitBtn.disabled = false;
            btnText.classList.remove('d-none');
            btnLoading.classList.add('d-none');
          }
        });
      }
    });
  </script>
</body>
</html>
