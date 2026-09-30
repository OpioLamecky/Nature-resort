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
  ?>
  <!-- Footer -->
  <footer class="footer-main pt-4 pb-3 border-top">
    <div class="container">
      <div class="row g-4 small">
        <!-- Contact Us -->
        <div class="col-12 col-lg-4">
          <h6 class="fw-bold mb-3">Contact Us</h6>
          <?php
            // WhatsApp link for contact section
            $waLink = site_content('contact', 'whatsapp_link', 'https://wa.me/' . preg_replace('/[^0-9]/', '', $settings['whatsapp_number'] ?? '256772957573'));
            $waMsg = urlencode('Hello Kalitusi Resort, I would like to inquire about...');
            if (!str_contains($waLink, '?')) {
              $waLink .= '?text=' . $waMsg;
            }
          ?>
          <ul class="list-unstyled mb-0 footer-list">
            <li><i class="fas fa-phone me-2"></i>Phone: <?= sanitize(site_content('contact', 'phone', $settings['contact_phone'] ?? '+256 772 957 573')) ?></li>
            <li><i class="fas fa-envelope me-2"></i>Email: <a href="mailto:<?= sanitize(site_content('contact', 'email', $settings['contact_email'] ?? 'info@kalitusiresort.com')) ?>"><?= sanitize(site_content('contact', 'email', $settings['contact_email'] ?? 'info@kalitusiresort.com')) ?></a></li>
            <li><i class="fab fa-whatsapp me-2"></i>WhatsApp: <a href="<?= sanitize($waLink) ?>" target="_blank" rel="noopener"><?= sanitize(site_content('contact', 'phone', '+256 772 957 573')) ?></a></li>
            <li class="mt-2"><i class="fas fa-map-marker-alt me-2"></i>Address:</li>
            <li><?= nl2br(sanitize(str_ireplace(['<br>', '<br/>', '<br />'], "\n", site_content('contact', 'address', $settings['address_html'] ?? "Kalitusi Nature Resort\nFort Portal Tourism City\nWestern Uganda")))) ?></li>
          </ul>
        </div>

        <!-- Useful Links -->
        <div class="col-12 col-lg-4">
          <h6 class="fw-bold mb-3">Useful Links</h6>
          <ul class="list-unstyled footer-links">
            <?php if (!empty($footerBySection['useful'])): foreach ($footerBySection['useful'] as $link): ?>
              <li><a href="<?= sanitize($link['url']) ?>"><?= sanitize($link['label']) ?></a></li>
            <?php endforeach; else: ?>
              <li><a href="<?= base_url('public/accommodations.php') ?>">Accommodations</a></li>
              <li><a href="<?= base_url('public/activities.php') ?>">Activities & Experiences</a></li>
              <li><a href="<?= base_url('public/dining.php') ?>">Dining Options</a></li>
              <li><a href="<?= base_url('public/gallery.php') ?>">Photo Gallery</a></li>
              <li><a href="<?= base_url('public/booking.php') ?>">Book Your Stay</a></li>
            <?php endif; ?>
          </ul>
        </div>

        <!-- Social Links -->
        <div class="col-12 col-lg-4">
          <h6 class="fw-bold mb-3">Follow Us</h6>
          <ul class="list-unstyled footer-list">
            <li><a href="<?= sanitize(site_content('contact', 'facebook_url', '#')) ?>" target="_blank" rel="noopener"><i class="fab fa-facebook me-2"></i>Facebook</a></li>
            <li><a href="<?= sanitize(site_content('contact', 'instagram_url', '#')) ?>" target="_blank" rel="noopener"><i class="fab fa-instagram me-2"></i>Instagram</a></li>
            <li><a href="<?= sanitize(site_content('contact', 'twitter_url', '#')) ?>" target="_blank" rel="noopener"><i class="fab fa-twitter me-2"></i>X / Twitter</a></li>
            <li class="mt-3">
              <p class="mb-2">Subscribe to our newsletter:</p>
              <form id="newsletterForm" class="d-flex gap-2">
                <input type="email" name="email" id="newsletterEmail" class="form-control form-control-sm" placeholder="Your email" required>
                <button class="btn btn-sm btn-primary" type="submit" id="newsletterBtn">
                  <span class="btn-text">Subscribe</span>
                  <span class="btn-loading d-none">
                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                    Subscribing...
                  </span>
                </button>
              </form>
              <div id="newsletterMessage" class="mt-2 small"></div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </footer>

  <!-- Accent copyright bar and chat pill -->
  <?php
    // WhatsApp number from settings or default (remove spaces and dashes for wa.me link)
    $whatsappNumber = preg_replace('/[^0-9]/', '', $settings['whatsapp_number'] ?? '256772957573');
    $whatsappMessage = urlencode('Hello Kalitusi Resort, I would like to inquire about...');
    $whatsappLink = site_content('contact', 'whatsapp_link', 'https://wa.me/' . $whatsappNumber . '?text=' . $whatsappMessage);
  ?>
  <div class="footer-accent position-relative py-2">
    <div class="container text-center small fw-semibold">&copy;<?= date('Y') ?> Kalitusi Nature Resort - Fort Portal, Uganda. All rights reserved.</div>
    <a class="chat-pill btn btn-success btn-sm" href="<?= sanitize($whatsappLink) ?>" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
        <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
      </svg>
      <span>Chat with us</span>
    </a>
  </div>

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
