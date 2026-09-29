# InfinityFree Deployment Checklist

Complete this checklist before and after deploying to InfinityFree hosting.

---

## 📋 Pre-Deployment Phase

### Configuration & Security

- [ ] **Review config.php**
  - [ ] Verify all database credentials are correct
  - [ ] Set `env` to `production`
  - [ ] Set `debug` to `false`
  - [ ] Update `base_url` to your InfinityFree domain

- [ ] **Create .htaccess file**
  - [ ] Copy provided `.htaccess` to root directory
  - [ ] Verify mod_rewrite rules
  - [ ] Check security headers are configured

- [ ] **Backup current database**
  ```bash
  mysqldump -u root -p kalitusi_resort > kalitusi_backup_$(date +%Y%m%d).sql
  ```

- [ ] **Backup current files**
  - [ ] Create ZIP of entire project
  - [ ] Store in safe location
  - [ ] Test ZIP integrity

### Code Review

- [ ] **Check for hardcoded paths**
  - [ ] Search for `/localhost/`
  - [ ] Search for `C:\wamp64\`
  - [ ] Search for absolute file paths
  - [ ] Replace with relative paths or config values

- [ ] **Check for debug code**
  - [ ] Search for `var_dump`
  - [ ] Search for `print_r`
  - [ ] Search for `die()` statements
  - [ ] Search for `echo` in production code

- [ ] **Verify all includes use correct paths**
  - [ ] Check `require_once` statements
  - [ ] Verify `__DIR__` usage
  - [ ] Test all page loads locally

- [ ] **Check for environment-specific code**
  - [ ] Search for `$_SERVER['HTTP_HOST']`
  - [ ] Search for `$_SERVER['DOCUMENT_ROOT']`
  - [ ] Ensure code works with different domains

### Database Preparation

- [ ] **Verify database structure**
  ```sql
  SHOW TABLES;
  ```
  - [ ] All 14+ tables present
  - [ ] All indexes created
  - [ ] All foreign keys configured

- [ ] **Check data integrity**
  ```sql
  SELECT COUNT(*) FROM users;
  SELECT COUNT(*) FROM posts;
  SELECT COUNT(*) FROM bookings;
  ```

- [ ] **Verify character set**
  ```sql
  SHOW CREATE DATABASE kalitusi_resort;
  ```
  - [ ] Should be `utf8mb4`

- [ ] **Test critical queries**
  - [ ] Test user authentication queries
  - [ ] Test booking queries
  - [ ] Test content queries
  - [ ] Test admin queries

### File & Asset Verification

- [ ] **Check all images exist**
  - [ ] `assets/images/` directory populated
  - [ ] All referenced images accessible
  - [ ] No broken image links

- [ ] **Verify CSS and JavaScript**
  - [ ] All CSS files present
  - [ ] All JS files present
  - [ ] External CDN links working
  - [ ] No console errors locally

- [ ] **Check upload directory**
  - [ ] `uploads/` directory exists
  - [ ] Directory is writable
  - [ ] No sensitive files in uploads

### Testing Locally

- [ ] **Test all pages**
  - [ ] Homepage loads
  - [ ] All navigation links work
  - [ ] All pages render correctly
  - [ ] No 404 errors

- [ ] **Test forms**
  - [ ] Contact form submits
  - [ ] Booking form submits
  - [ ] Form validation works
  - [ ] CSRF protection active

- [ ] **Test admin panel**
  - [ ] Admin login works
  - [ ] Dashboard loads
  - [ ] All admin pages accessible
  - [ ] CRUD operations work

- [ ] **Test database operations**
  - [ ] Create operations work
  - [ ] Read operations work
  - [ ] Update operations work
  - [ ] Delete operations work

- [ ] **Test security**
  - [ ] CSRF tokens generated
  - [ ] Session management works
  - [ ] Password hashing works
  - [ ] No SQL injection vulnerabilities

---

## 🚀 Deployment Phase

### InfinityFree Account Setup

- [ ] **Create InfinityFree account**
  - [ ] Sign up at infinityfree.com
  - [ ] Verify email
  - [ ] Access control panel

- [ ] **Create database**
  - [ ] Go to Databases section
  - [ ] Create new MySQL database
  - [ ] Note database name, user, password
  - [ ] Note database host (usually sql.infinityfree.com)

- [ ] **Set up domain**
  - [ ] Choose domain (infinityfree.com subdomain or custom)
  - [ ] Configure domain settings
  - [ ] Enable SSL/HTTPS
  - [ ] Note your domain URL

### File Upload

- [ ] **Connect to server**
  - [ ] Use File Manager OR
  - [ ] Use FTP client (FileZilla, WinSCP, etc.)
  - [ ] Navigate to public directory

- [ ] **Upload project files**
  - [ ] Upload `public/` directory
  - [ ] Upload `admin/` directory
  - [ ] Upload `assets/` directory
  - [ ] Upload `includes/` directory
  - [ ] Upload `database/` directory (optional, for reference)
  - [ ] Upload `.htaccess` file
  - [ ] Upload `README.md` (optional)

- [ ] **Verify file structure**
  - [ ] Check all directories uploaded
  - [ ] Check all files present
  - [ ] Verify file permissions (755 for dirs, 644 for files)

### Database Migration

- [ ] **Export local database**
  ```bash
  mysqldump -u root -p kalitusi_resort > kalitusi_export.sql
  ```

- [ ] **Import to InfinityFree**
  - [ ] Go to phpMyAdmin in control panel
  - [ ] Select your database
  - [ ] Click Import tab
  - [ ] Upload SQL file
  - [ ] Click Go
  - [ ] Wait for import to complete

- [ ] **Verify database import**
  ```sql
  SHOW TABLES;
  SELECT COUNT(*) FROM users;
  SELECT COUNT(*) FROM posts;
  ```

### Configuration Update

- [ ] **Update config.php on server**
  - [ ] Edit via File Manager or FTP
  - [ ] Update database host to `sql.infinityfree.com`
  - [ ] Update database name
  - [ ] Update database user
  - [ ] Update database password
  - [ ] Update base_url to your domain
  - [ ] Set env to `production`
  - [ ] Set debug to `false`
  - [ ] Save file

- [ ] **Verify .htaccess**
  - [ ] File exists in root directory
  - [ ] File is readable
  - [ ] Rewrite rules are active

---

## ✅ Post-Deployment Testing

### Website Access

- [ ] **Test domain access**
  - [ ] Visit your domain in browser
  - [ ] Homepage loads without errors
  - [ ] HTTPS works (if enabled)
  - [ ] No 404 errors

- [ ] **Test page navigation**
  - [ ] All navigation links work
  - [ ] All pages load correctly
  - [ ] Images display properly
  - [ ] CSS and JS load correctly

- [ ] **Test responsive design**
  - [ ] Test on desktop (1920x1080)
  - [ ] Test on tablet (768x1024)
  - [ ] Test on mobile (375x667)
  - [ ] All layouts responsive

### Functionality Testing

- [ ] **Test contact form**
  - [ ] Form displays
  - [ ] Form submits successfully
  - [ ] Validation works
  - [ ] Success message displays

- [ ] **Test booking form**
  - [ ] Form displays
  - [ ] Date pickers work
  - [ ] Form submits successfully
  - [ ] Booking saved to database

- [ ] **Test admin panel**
  - [ ] Navigate to `/admin/`
  - [ ] Login page displays
  - [ ] Login with admin credentials
  - [ ] Dashboard loads
  - [ ] All admin pages accessible

- [ ] **Test database operations**
  - [ ] View bookings in admin
  - [ ] View posts in admin
  - [ ] Create new content
  - [ ] Edit existing content
  - [ ] Delete content

### Security Verification

- [ ] **Test HTTPS**
  - [ ] HTTPS enabled
  - [ ] No mixed content warnings
  - [ ] SSL certificate valid
  - [ ] Green lock icon shows

- [ ] **Test security headers**
  - [ ] Open browser DevTools
  - [ ] Check Network tab
  - [ ] Verify X-Content-Type-Options header
  - [ ] Verify X-Frame-Options header
  - [ ] Verify Referrer-Policy header

- [ ] **Test CSRF protection**
  - [ ] Submit form
  - [ ] Verify CSRF token in form
  - [ ] Try submitting without token (should fail)

- [ ] **Test session security**
  - [ ] Login to admin
  - [ ] Check session cookie
  - [ ] Verify session timeout works
  - [ ] Verify logout works

### Performance Testing

- [ ] **Check page load times**
  - [ ] Homepage: < 3 seconds
  - [ ] Admin pages: < 2 seconds
  - [ ] Database queries: < 1 second

- [ ] **Check browser console**
  - [ ] No JavaScript errors
  - [ ] No CSS errors
  - [ ] No 404 errors
  - [ ] No security warnings

- [ ] **Test on different browsers**
  - [ ] Chrome/Chromium
  - [ ] Firefox
  - [ ] Safari
  - [ ] Edge

- [ ] **Test on mobile browsers**
  - [ ] iOS Safari
  - [ ] Chrome Mobile
  - [ ] Firefox Mobile

### Database Verification

- [ ] **Verify all data present**
  ```sql
  SELECT COUNT(*) FROM users;
  SELECT COUNT(*) FROM posts;
  SELECT COUNT(*) FROM bookings;
  SELECT COUNT(*) FROM accommodations;
  ```

- [ ] **Test database queries**
  - [ ] User authentication works
  - [ ] Posts display correctly
  - [ ] Bookings save correctly
  - [ ] Filters work correctly

- [ ] **Check database performance**
  - [ ] Queries execute quickly
  - [ ] No timeout errors
  - [ ] No connection errors

---

## 🔧 Troubleshooting

### If Database Connection Fails

- [ ] Verify credentials in config.php
- [ ] Check database exists in InfinityFree
- [ ] Verify database user has permissions
- [ ] Test connection using phpMyAdmin
- [ ] Check MySQL host is correct
- [ ] Verify port is 3306

### If Pages Show 404 Errors

- [ ] Verify .htaccess file is uploaded
- [ ] Check mod_rewrite is enabled
- [ ] Verify directory structure
- [ ] Check file permissions
- [ ] Test with direct file access

### If Images Don't Display

- [ ] Verify base_url in config.php
- [ ] Check image files are uploaded
- [ ] Verify image paths in database
- [ ] Check file permissions on images
- [ ] Test image URLs directly

### If Admin Panel Won't Load

- [ ] Verify admin directory uploaded
- [ ] Check admin/index.php exists
- [ ] Verify database connection
- [ ] Check session settings
- [ ] Review error logs

### If Forms Don't Submit

- [ ] Verify form action URL
- [ ] Check CSRF token present
- [ ] Verify database connection
- [ ] Check file upload permissions
- [ ] Review error logs

---

## 📊 Performance Optimization

After deployment, optimize performance:

- [ ] **Enable GZIP compression**
  - [ ] Verify in .htaccess
  - [ ] Check response headers

- [ ] **Enable browser caching**
  - [ ] Verify cache headers
  - [ ] Check expiration times

- [ ] **Optimize images**
  - [ ] Use WebP format where possible
  - [ ] Compress images
  - [ ] Use lazy loading

- [ ] **Minify assets**
  - [ ] Minify CSS
  - [ ] Minify JavaScript
  - [ ] Remove unused code

---

## 🔒 Security Hardening

After deployment, harden security:

- [ ] **Change admin password**
  - [ ] Log in to admin
  - [ ] Go to User Settings
  - [ ] Change password to strong value
  - [ ] Save changes

- [ ] **Create backup user**
  - [ ] Create secondary admin account
  - [ ] Use different password
  - [ ] Store credentials securely

- [ ] **Delete test accounts**
  - [ ] Remove any test users
  - [ ] Remove demo accounts
  - [ ] Verify only needed users exist

- [ ] **Review file permissions**
  - [ ] Verify config.php is readable only
  - [ ] Verify uploads directory is writable
  - [ ] Verify database directory is protected

- [ ] **Enable logging**
  - [ ] Enable error logging
  - [ ] Monitor error logs regularly
  - [ ] Set up alerts for errors

---

## 📈 Post-Deployment Maintenance

### Weekly Tasks

- [ ] Check website loads correctly
- [ ] Verify admin panel works
- [ ] Monitor error logs
- [ ] Check for security alerts

### Monthly Tasks

- [ ] Create database backup
- [ ] Create file backup
- [ ] Review analytics
- [ ] Update content as needed
- [ ] Check for updates

### Quarterly Tasks

- [ ] Full security audit
- [ ] Performance review
- [ ] Database optimization
- [ ] Backup restoration test

---

## ✨ Final Sign-Off

- [ ] All tests passed
- [ ] Website is live and accessible
- [ ] Admin panel is working
- [ ] Database is functioning
- [ ] Security is configured
- [ ] Performance is acceptable
- [ ] Backups are created
- [ ] Team notified of launch

---

**Deployment Date:** _______________  
**Deployed By:** _______________  
**Domain:** _______________  
**Status:** ✅ LIVE

---

**Last Updated:** November 20, 2025
