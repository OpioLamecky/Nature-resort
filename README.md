# 🌿 Kalitusi Nature Resort Website

A modern, feature-rich website for Kalitusi Nature Resort in Fort Portal, Uganda. Built with PHP, Bootstrap 5, and enhanced with advanced UI/UX features.

![Version](https://img.shields.io/badge/version-2.0-green.svg)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple.svg)
![License](https://img.shields.io/badge/license-MIT-orange.svg)

---

## ✨ Features

### 🎨 Frontend
- **Modern UI/UX** - Clean, elegant design with smooth animations
- **Dark Mode** - Toggle between light and dark themes
- **Responsive Design** - Mobile-first approach, works on all devices
- **Lightbox Gallery** - Full-screen image viewer with keyboard navigation
- **Animated Counters** - Statistics that count up on scroll
- **Scroll Animations** - Fade-in and slide-up effects
- **Back to Top Button** - Smooth scroll to top
- **Toast Notifications** - Elegant user feedback system
- **Loading States** - Visual feedback for form submissions

### 🔒 Security
- **CSRF Protection** - Secure forms with token validation
- **Rate Limiting** - Prevent brute force attacks
- **XSS Protection** - Enhanced input sanitization
- **Secure Sessions** - Configured session management
- **Password Hashing** - BCrypt algorithm

### ⚡ Performance
- **Lazy Loading** - Images load as you scroll
- **Caching System** - Reduce database queries
- **Database Optimization** - Indexes and views for fast queries
- **Resource Preloading** - Faster external resource loading
- **Minified Assets** - Optimized CSS and JavaScript

### ♿ Accessibility
- **WCAG 2.1 AA Compliant** - Meets accessibility standards
- **Keyboard Navigation** - Full keyboard support
- **ARIA Labels** - Screen reader friendly
- **Skip to Content** - Quick navigation for screen readers
- **Focus Indicators** - Clear visual focus states
- **Reduced Motion** - Respects user preferences

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (optional)

### Installation

1. **Clone or Download**
   ```bash
   git clone https://github.com/yourusername/kalitusi-resort.git
   cd kalitusi-resort
   ```

2. **Configure Database**
   - Create database: `kalitusi_resort`
   - Import schema: `database/schema.sql`
   - Import optimizations: `database/optimizations.sql`

3. **Update Configuration**
   Edit `includes/config.php`:
   ```php
   'db' => [
     'host' => 'your_host',
     'name' => 'kalitusi_resort',
     'user' => 'your_username',
     'pass' => 'your_password',
   ]
   ```

4. **Set Permissions**
   ```bash
   # Create cache directory
   mkdir cache
   chmod 755 cache
   
   # Set upload directory permissions
   chmod 755 assets/uploads
   ```

5. **Access Website**
   - Navigate to: `http://localhost/Nature%20resort/public/`
   - Admin panel: `http://localhost/Nature%20resort/admin/`

📚 **Detailed Setup:** See [DOCUMENTATION.md](DOCUMENTATION.md)

---

## 📁 Project Structure

```
Nature resort/
├── admin/                  # Admin panel
│   ├── dashboard.php
│   ├── posts.php
│   └── ...
├── assets/                 # Static assets
│   ├── css/
│   │   └── style.css      # Main stylesheet (1200+ lines)
│   ├── js/
│   │   └── app.js         # Frontend enhancements (340+ lines)
│   ├── images/
│   └── uploads/
├── cache/                  # Cache storage (auto-created)
├── database/              # Database files
│   ├── schema.sql         # Main database schema
│   └── optimizations.sql  # Performance optimizations
├── docs/                  # Documentation
├── includes/              # PHP includes
│   ├── bootstrap.php      # Bootstrap file
│   ├── config.php         # Configuration
│   ├── database.php       # Database connection
│   ├── functions.php      # Helper functions
│   ├── layout_header.php  # Header template
│   └── layout_footer.php  # Footer template
├── public/                # Public pages
│   ├── index.php          # Homepage
│   ├── about.php
│   ├── accommodations.php
│   ├── activities.php
│   ├── booking.php
│   ├── contact.php
│   ├── dining.php
│   └── gallery.php
├── DOCUMENTATION.md             # Complete system documentation
└── README.md                    # This file
```

---

## 🎯 Key Pages

| Page | Description | URL |
|------|-------------|-----|
| Homepage | Hero slider, features, accommodations, statistics, testimonials | `/public/index.php` |
| About Us | Resort story, mission, team, achievements | `/public/about.php` |
| Accommodations | Rooms, chalets, glamping tents | `/public/accommodations.php` |
| Activities | Nature walks, bird watching, tours | `/public/activities.php` |
| Dining | Restaurant, menu, dining experiences | `/public/dining.php` |
| Gallery | Photo gallery with lightbox | `/public/gallery.php` |
| Booking | Reservation form | `/public/booking.php` |
| Contact | Contact form, map, info | `/public/contact.php` |
| Blog | News and updates | `/public/blog.php` |

---

## 🛠️ Technology Stack

### Backend
- **PHP 8.0+** - Server-side logic
- **MySQL 5.7+** - Database
- **PDO** - Database abstraction layer

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Modern styling with custom properties
- **JavaScript (ES6+)** - Interactive features
- **Bootstrap 5.3** - Responsive framework
- **Font Awesome 6** - Icon library

### Features
- **Intersection Observer API** - Scroll animations
- **Local Storage API** - Dark mode persistence
- **Fetch API** - AJAX requests (future use)

---

## 📖 Documentation

- **[DOCUMENTATION.md](DOCUMENTATION.md)** - Complete system documentation and setup guide
- **Code Comments** - Inline documentation throughout codebase

---

## 🎨 Design System

### Color Palette
```css
--primary-green: #1a4d2e      /* Primary brand color */
--forest-green: #0d3b2e       /* Dark variant */
--sage-green: #8faa96         /* Accent */
--champagne-gold: #c9a961     /* Gold accents */
--cream: #faf8f3              /* Light background */
```

### Typography
- **Headings:** Playfair Display (Serif)
- **Body:** Montserrat (Sans-serif)

### Breakpoints
- **Mobile:** < 768px
- **Tablet:** 768px - 991px
- **Desktop:** 992px - 1199px
- **Large Desktop:** ≥ 1200px

---

## 🔧 Configuration

### Environment Settings
Edit `includes/config.php`:

```php
return [
  'app_name' => 'Kalitusi Nature Resort',
  'env' => 'production', // or 'local'
  'debug' => false, // Set to true for development
  'base_url' => '/Nature%20resort',
  'db' => [...],
  'security' => [...],
];
```

### Security Configuration
```php
'security' => [
  'session_name' => 'kalitusi_sess',
  'csrf_key' => 'csrf_token',
  'password_algo' => PASSWORD_BCRYPT,
]
```

---

## 🧪 Testing

### Manual Testing Checklist
- [ ] All pages load without errors
- [ ] Forms submit correctly with CSRF protection
- [ ] Dark mode toggles and persists
- [ ] Animations trigger on scroll
- [ ] Lightbox works in gallery
- [ ] Mobile responsive on all screen sizes
- [ ] Keyboard navigation works
- [ ] No console errors
- [ ] Links work correctly
- [ ] Images load properly

### Browser Compatibility
- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

---

## 📈 Performance

### Lighthouse Scores (Expected)
- **Performance:** 85-95
- **Accessibility:** 90-100
- **Best Practices:** 90-100
- **SEO:** 85-95

### Optimization Features
- Lazy loading images
- Database query caching
- Optimized database indexes
- Minified assets
- Resource preloading
- Efficient CSS/JS

---

## 🔐 Security Features

### Implemented
- ✅ CSRF token protection
- ✅ XSS prevention with sanitization
- ✅ SQL injection prevention (PDO prepared statements)
- ✅ Rate limiting on sensitive endpoints
- ✅ Secure password hashing
- ✅ Session security
- ✅ Input validation

### Recommendations
- Enable HTTPS in production
- Regular security audits
- Keep dependencies updated
- Monitor error logs
- Implement 2FA for admin

---

## 📱 Responsive Design

### Mobile Features
- Touch-friendly buttons (min 45x45px)
- Collapsible navigation menu
- Optimized images for mobile
- Fast loading on 3G/4G
- Swipe gestures in lightbox

### Tablet Optimization
- Adaptive layouts
- Touch and mouse support
- Optimized spacing

### Desktop Enhancement
- Hover effects
- Keyboard shortcuts
- Multi-column layouts
- Large hero images

---

## 🌐 Browser Support

### Fully Supported
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### Partial Support
- IE 11 (basic functionality, no modern features)

### Progressive Enhancement
- Modern browsers get full experience
- Older browsers get basic functionality
- Graceful degradation for features

---

## 🐛 Known Issues

None currently. Report issues via GitHub Issues or contact support.

---

## 🚧 Roadmap

### Version 2.1 (Planned)
- [ ] Online booking system with payment integration
- [ ] Guest reviews and ratings
- [ ] Multi-language support (English, French, Luganda)
- [ ] Progressive Web App (PWA)
- [ ] Email notifications
- [ ] Social media integration

### Version 2.2 (Future)
- [ ] Virtual tour feature
- [ ] Live chat support
- [ ] Advanced analytics dashboard
- [ ] Mobile app (React Native)
- [ ] API for third-party integrations

---

## 👥 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Coding Standards
- Follow PSR-12 for PHP
- Use meaningful variable names
- Comment complex logic
- Test before committing

---

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

---

## 👨‍💻 Authors

- **Development Team** - Initial work and improvements
- **Contributors** - See contributor list

---

## 🙏 Acknowledgments

- Bootstrap team for the excellent framework
- Font Awesome for beautiful icons
- Unsplash for placeholder images
- PHP and MySQL communities

---

## 📞 Support

### Contact
- **Email:** info@kalitusiresort.com
- **Phone:** +256 772 123 456
- **Website:** [kalitusiresort.com](https://kalitusiresort.com)

### Documentation
- Complete documentation: `DOCUMENTATION.md`
- Inline code comments

---

## 📊 Statistics

- **Total Lines of Code:** 15,000+
- **PHP Functions:** 50+
- **CSS Classes:** 200+
- **JavaScript Functions:** 30+
- **Database Tables:** 15+
- **Features Implemented:** 50+

---

## ⭐ Show Your Support

Give a ⭐ if this project helped you!

---

**Made with ❤️ for Kalitusi Nature Resort**

*Last Updated: November 3, 2025*
