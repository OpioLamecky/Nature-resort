// Kalitusi Nature Resort - Modern Frontend Enhancements
(() => {
  'use strict';
  
  console.log('🌿 Kalitusi Nature Resort - Enhanced Experience');
  
  // ===== SMOOTH SCROLL =====
  document.documentElement.style.scrollBehavior = 'smooth';
  
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const target = this.getAttribute('href');
      if (target !== '#' && target.length > 1) {
        e.preventDefault();
        const element = document.querySelector(target);
        if (element) {
          element.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }
    });
  });
  
  // ===== BACK TO TOP BUTTON =====
  const backToTop = document.createElement('button');
  backToTop.innerHTML = '<i class="fas fa-arrow-up"></i>';
  backToTop.className = 'back-to-top';
  backToTop.setAttribute('aria-label', 'Back to top');
  backToTop.setAttribute('title', 'Back to top');
  document.body.appendChild(backToTop);
  
  const toggleBackToTop = () => {
    if (window.pageYOffset > 300) {
      backToTop.classList.add('show');
    } else {
      backToTop.classList.remove('show');
    }
  };
  
  window.addEventListener('scroll', toggleBackToTop);
  backToTop.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
  
  // ===== LAZY LOADING IMAGES =====
  const lazyImages = document.querySelectorAll('img[data-src]');
  
  const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.dataset.src;
        img.classList.add('loaded');
        observer.unobserve(img);
      }
    });
  }, { rootMargin: '50px' });
  
  lazyImages.forEach(img => imageObserver.observe(img));
  
  // ===== LOADING STATE FOR FORMS =====
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
      const submitBtn = form.querySelector('[type="submit"]');
      if (submitBtn && !submitBtn.disabled) {
        submitBtn.disabled = true;
        submitBtn.dataset.originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        // Re-enable after 10 seconds as fallback
        setTimeout(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = submitBtn.dataset.originalText;
        }, 10000);
      }
    });
  });
  
  // ===== ANIMATED COUNTERS =====
  const animateCounter = (element, target, duration = 2000) => {
    const start = 0;
    const increment = target / (duration / 16);
    let current = start;
    
    const updateCounter = () => {
      current += increment;
      if (current < target) {
        element.textContent = Math.ceil(current).toLocaleString();
        requestAnimationFrame(updateCounter);
      } else {
        element.textContent = target.toLocaleString();
      }
    };
    
    updateCounter();
  };
  
  const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
        entry.target.classList.add('counted');
        const target = parseInt(entry.target.dataset.count);
        if (!isNaN(target)) {
          animateCounter(entry.target, target);
        }
      }
    });
  }, { threshold: 0.5 });
  
  document.querySelectorAll('[data-count]').forEach(counter => {
    counterObserver.observe(counter);
  });
  
  // ===== FADE IN ANIMATIONS ON SCROLL =====
  const fadeElements = document.querySelectorAll('.fade-in, .slide-up');
  
  const fadeObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animate');
        fadeObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
  
  fadeElements.forEach(el => fadeObserver.observe(el));
  
  // ===== LIGHTBOX FOR GALLERY =====
  const createLightbox = () => {
    const lightbox = document.createElement('div');
    lightbox.className = 'lightbox';
    lightbox.innerHTML = `
      <div class="lightbox-content">
        <button class="lightbox-close" aria-label="Close">&times;</button>
        <button class="lightbox-prev" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
        <img src="" alt="" class="lightbox-image">
        <button class="lightbox-next" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
        <div class="lightbox-caption"></div>
      </div>
    `;
    document.body.appendChild(lightbox);
    return lightbox;
  };
  
  let currentLightboxIndex = 0;
  let lightboxImages = [];
  
  const initLightbox = () => {
    const galleryImages = document.querySelectorAll('[data-lightbox]');
    if (galleryImages.length === 0) return;
    
    const lightbox = createLightbox();
    const img = lightbox.querySelector('.lightbox-image');
    const caption = lightbox.querySelector('.lightbox-caption');
    const closeBtn = lightbox.querySelector('.lightbox-close');
    const prevBtn = lightbox.querySelector('.lightbox-prev');
    const nextBtn = lightbox.querySelector('.lightbox-next');
    
    lightboxImages = Array.from(galleryImages);
    
    const showImage = (index) => {
      const image = lightboxImages[index];
      img.src = image.dataset.lightbox || image.src;
      caption.textContent = image.alt || '';
      currentLightboxIndex = index;
      
      prevBtn.style.display = index > 0 ? 'block' : 'none';
      nextBtn.style.display = index < lightboxImages.length - 1 ? 'block' : 'none';
    };
    
    galleryImages.forEach((image, index) => {
      image.style.cursor = 'pointer';
      image.addEventListener('click', () => {
        lightbox.classList.add('active');
        showImage(index);
        document.body.style.overflow = 'hidden';
      });
    });
    
    closeBtn.addEventListener('click', () => {
      lightbox.classList.remove('active');
      document.body.style.overflow = '';
    });
    
    prevBtn.addEventListener('click', () => {
      if (currentLightboxIndex > 0) {
        showImage(currentLightboxIndex - 1);
      }
    });
    
    nextBtn.addEventListener('click', () => {
      if (currentLightboxIndex < lightboxImages.length - 1) {
        showImage(currentLightboxIndex + 1);
      }
    });
    
    lightbox.addEventListener('click', (e) => {
      if (e.target === lightbox) {
        lightbox.classList.remove('active');
        document.body.style.overflow = '';
      }
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
      if (!lightbox.classList.contains('active')) return;
      
      if (e.key === 'Escape') {
        lightbox.classList.remove('active');
        document.body.style.overflow = '';
      } else if (e.key === 'ArrowLeft') {
        prevBtn.click();
      } else if (e.key === 'ArrowRight') {
        nextBtn.click();
      }
    });
  };
  
  // Initialize lightbox when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLightbox);
  } else {
    initLightbox();
  }
  
  // ===== DARK MODE TOGGLE =====
  const initDarkMode = () => {
    const darkModeToggle = document.createElement('button');
    darkModeToggle.className = 'dark-mode-toggle';
    darkModeToggle.setAttribute('aria-label', 'Toggle dark mode');
    darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
    document.body.appendChild(darkModeToggle);
    
    // Check for saved preference
    const currentMode = localStorage.getItem('darkMode');
    if (currentMode === 'enabled') {
      document.body.classList.add('dark-mode');
      darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
    }
    
    darkModeToggle.addEventListener('click', () => {
      document.body.classList.toggle('dark-mode');
      
      if (document.body.classList.contains('dark-mode')) {
        darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        localStorage.setItem('darkMode', 'enabled');
      } else {
        darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
        localStorage.setItem('darkMode', 'disabled');
      }
    });
  };
  
  initDarkMode();
  
  // ===== SKIP TO CONTENT LINK =====
  const skipLink = document.createElement('a');
  skipLink.href = '#main-content';
  skipLink.className = 'skip-to-content';
  skipLink.textContent = 'Skip to main content';
  document.body.insertBefore(skipLink, document.body.firstChild);
  
  // ===== FORM VALIDATION ENHANCEMENT =====
  document.querySelectorAll('input[required], textarea[required]').forEach(field => {
    field.addEventListener('blur', function() {
      if (!this.value.trim()) {
        this.classList.add('is-invalid');
      } else {
        this.classList.remove('is-invalid');
      }
    });
    
    field.addEventListener('input', function() {
      if (this.classList.contains('is-invalid') && this.value.trim()) {
        this.classList.remove('is-invalid');
      }
    });
  });
  
  // ===== NAVBAR SCROLL EFFECT =====
  const navbar = document.querySelector('.nav-blue');
  if (navbar) {
    let lastScroll = 0;
    window.addEventListener('scroll', () => {
      const currentScroll = window.pageYOffset;
      
      if (currentScroll > 100) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
      
      // Hide on scroll down, show on scroll up
      if (currentScroll > lastScroll && currentScroll > 200) {
        navbar.style.transform = 'translateY(-100%)';
      } else {
        navbar.style.transform = 'translateY(0)';
      }
      
      lastScroll = currentScroll;
    });
  }
  
  // ===== ANNOUNCEMENT TICKER ANIMATION =====
  const ticker = document.querySelector('.announcement-ticker');
  if (ticker) {
    const tickerContent = ticker.querySelector('.ticker-content');
    if (tickerContent && tickerContent.scrollWidth > ticker.clientWidth) {
      ticker.classList.add('animated');
    }
  }
  
  // ===== TOAST NOTIFICATIONS =====
  window.showToast = (message, type = 'info') => {
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = `
      <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
      <span>${message}</span>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  };
  
  // ===== PERFORMANCE MONITORING =====
  if ('PerformanceObserver' in window) {
    const perfObserver = new PerformanceObserver((list) => {
      for (const entry of list.getEntries()) {
        if (entry.loadTime > 2500) {
          console.warn('Slow resource detected:', entry.name, entry.loadTime + 'ms');
        }
      }
    });
    perfObserver.observe({ entryTypes: ['resource'] });
  }
  
  console.log('✅ All enhancements loaded successfully');
})();
