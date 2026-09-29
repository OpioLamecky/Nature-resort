# InfinityFree Hosting Setup Guide

This guide will help you deploy the Kalitusi Nature Resort website to InfinityFree.com hosting.

---

## 📋 Pre-Deployment Checklist

- [ ] Review all configuration changes needed
- [ ] Update `includes/config.php` with InfinityFree credentials
- [ ] Create `.htaccess` file for URL rewriting
- [ ] Prepare database for migration
- [ ] Test all functionality locally
- [ ] Backup current database
- [ ] Upload files to InfinityFree
- [ ] Import database on InfinityFree
- [ ] Test website on live server

---

## 🔧 Configuration Changes

### 1. Update `includes/config.php`

Replace the local configuration with InfinityFree credentials:

```php
<?php
return [
  'app_name' => 'Kalitusi Nature Resort',
  'env' => 'production',  // IMPORTANT: Change from 'local' to 'production'
  'debug' => false,       // IMPORTANT: Change from true to false
  // Base URL - Update with your InfinityFree domain
  'base_url' => 'https://yourdomain.infinityfree.com',  // or your custom domain
  'db' => [
    'host' => 'sql.infinityfree.com',  // InfinityFree MySQL host
    'port' => '3306',
    'name' => 'your_database_name',     // Replace with your database name
    'user' => 'your_database_user',     // Replace with your database user
    'pass' => 'your_database_pass',     // Replace with your database password
    'charset' => 'utf8mb4',
  ],
  'security' => [
    'session_name' => 'kalitusi_sess',
    'csrf_key' => 'csrf_token',
    'password_algo' => PASSWORD_BCRYPT,
  ],
];
```

**Where to find InfinityFree credentials:**
1. Log in to InfinityFree control panel
2. Go to **Databases** section
3. Find your MySQL database credentials
4. Copy the host, database name, username, and password

### 2. Create `.htaccess` File

Create a `.htaccess` file in the root directory for URL rewriting and security:

```apache
# Enable mod_rewrite
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Remove index.php from URLs
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [L]
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set Referrer-Policy "no-referrer-when-downgrade"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Disable directory listing
Options -Indexes

# Protect sensitive files
<FilesMatch "\.env|config\.php|\.sql|\.md">
    Order allow,deny
    Deny from all
</FilesMatch>

# Enable GZIP compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>

# Cache static assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType font/ttf "access plus 1 year"
    ExpiresByType font/otf "access plus 1 year"
    ExpiresByType font/woff "access plus 1 year"
    ExpiresByType font/woff2 "access plus 1 year"
</IfModule>
```

---

## 📤 File Upload Instructions

### Step 1: Connect via File Manager or FTP

**Using InfinityFree File Manager:**
1. Log in to InfinityFree control panel
2. Click **File Manager**
3. Navigate to your domain's public directory

**Using FTP Client (Recommended for large uploads):**
1. Download FileZilla or similar FTP client
2. Get FTP credentials from InfinityFree control panel
3. Connect to your server
4. Upload files to the public directory

### Step 2: Upload Project Files

Upload the following directories to your public directory:

```
public/          → /public_html/public/
admin/           → /public_html/admin/
assets/          → /public_html/assets/
includes/        → /public_html/includes/
database/        → /public_html/database/
uploads/         → /public_html/uploads/
.htaccess        → /public_html/.htaccess
```

### Step 3: Set File Permissions

InfinityFree automatically handles permissions, but ensure:
- `uploads/` directory is writable (755)
- `includes/config.php` is readable (644)
- All PHP files are readable (644)

---

## 🗄️ Database Migration

### Step 1: Export Database Locally

```bash
# Using MySQL command line
mysqldump -u root -p kalitusi_resort > kalitusi_backup.sql

# Or use phpMyAdmin:
# 1. Select database 'kalitusi_resort'
# 2. Click Export
# 3. Choose SQL format
# 4. Click Go
```

### Step 2: Create Database on InfinityFree

1. Log in to InfinityFree control panel
2. Click **Databases**
3. Click **Create New Database**
4. Enter database name (e.g., `kalitusi_resort`)
5. Note the credentials provided

### Step 3: Import Database

**Using InfinityFree phpMyAdmin:**
1. Go to InfinityFree control panel → **Databases**
2. Click **phpMyAdmin** for your database
3. Click **Import** tab
4. Upload your `kalitusi_backup.sql` file
5. Click **Go**

**Alternative - Using SQL file:**
1. In phpMyAdmin, go to **SQL** tab
2. Paste the contents of your SQL file
3. Click **Go**

### Step 4: Verify Database

In phpMyAdmin, run this query to verify all tables:

```sql
SHOW TABLES;
```

You should see 14+ tables including:
- users
- posts
- bookings
- accommodations
- meal_reservations
- site_settings
- And others...

---

## 🌐 Domain Configuration

### Option 1: Use InfinityFree Subdomain

Your site will be available at: `https://yourusername.infinityfree.com`

### Option 2: Use Custom Domain

1. In InfinityFree control panel, go to **Domains**
2. Click **Add Domain**
3. Enter your domain name
4. Update your domain's nameservers to InfinityFree's:
   - ns1.infinityfree.com
   - ns2.infinityfree.com
5. Wait 24-48 hours for DNS propagation

### Option 3: Point Existing Domain

If you already own a domain:
1. Go to your domain registrar
2. Update nameservers to InfinityFree's
3. Or add A records pointing to InfinityFree's IP
4. Update `base_url` in `config.php` to your domain

---

## ✅ Post-Deployment Testing

### 1. Test Website Access

- [ ] Visit your domain in browser
- [ ] Check homepage loads correctly
- [ ] Verify all images display
- [ ] Test navigation links

### 2. Test Admin Panel

- [ ] Navigate to `/admin/`
- [ ] Log in with admin credentials
- [ ] Test dashboard loads
- [ ] Verify database queries work

### 3. Test Forms

- [ ] Test contact form
- [ ] Test booking form
- [ ] Verify form submissions work
- [ ] Check email notifications (if configured)

### 4. Test Database

- [ ] Verify all data displays correctly
- [ ] Test filters and searches
- [ ] Check pagination
- [ ] Test admin CRUD operations

### 5. Test Security

- [ ] Verify HTTPS is enabled
- [ ] Test CSRF protection
- [ ] Check sensitive files are protected
- [ ] Verify no error messages expose system info

### 6. Performance Check

- [ ] Check page load times
- [ ] Verify images are optimized
- [ ] Test on mobile devices
- [ ] Check browser console for errors

---

## 🚨 Common Issues & Solutions

### Issue: "Database connection failed"

**Solution:**
1. Verify credentials in `config.php` are correct
2. Check database exists on InfinityFree
3. Verify database user has proper permissions
4. Test connection using phpMyAdmin

### Issue: "404 Not Found" on admin pages

**Solution:**
1. Verify `.htaccess` file is uploaded
2. Check mod_rewrite is enabled (usually is on InfinityFree)
3. Verify directory structure is correct
4. Check file permissions

### Issue: "Permission denied" on uploads

**Solution:**
1. Ensure `uploads/` directory exists
2. Set permissions to 755
3. Check disk space available
4. Verify file ownership

### Issue: "Session not working"

**Solution:**
1. Check `session.save_path` is writable
2. Verify session name in config is unique
3. Clear browser cookies and try again
4. Check PHP session settings in control panel

### Issue: "Images not displaying"

**Solution:**
1. Verify `base_url` in config is correct
2. Check image files are uploaded
3. Verify image paths in database
4. Check file permissions on images

### Issue: "Email not sending"

**Solution:**
1. Check email configuration
2. Verify SMTP settings if using custom email
3. Check spam folder
4. Review email logs in control panel

---

## 📊 InfinityFree Limits & Considerations

### Resource Limits
- **Disk Space:** Check your plan (usually 5GB free)
- **Bandwidth:** Check your plan (usually unlimited)
- **MySQL Databases:** Usually 1-5 depending on plan
- **Email Accounts:** Usually 10-50 depending on plan
- **Subdomains:** Usually unlimited

### PHP Configuration
- **PHP Version:** Usually 7.4+ (check control panel)
- **Max Upload Size:** Usually 128MB
- **Memory Limit:** Usually 256MB
- **Execution Time:** Usually 30-60 seconds

### Restrictions
- No shell access (SSH)
- No cron jobs (use external services)
- No custom PHP extensions
- Limited to Apache with mod_rewrite

---

## 🔒 Security Recommendations

### 1. Change Admin Credentials

After deployment:
1. Log in to admin panel
2. Go to **Users**
3. Change default admin password
4. Create new admin user if needed
5. Delete any test users

### 2. Update Configuration

1. Set `debug` to `false` in production
2. Set `env` to `production`
3. Use strong database password
4. Update session name to something unique

### 3. Protect Sensitive Files

The `.htaccess` file includes protections for:
- `config.php`
- `.env` files
- `.sql` files
- `.md` documentation files

### 4. Enable HTTPS

InfinityFree provides free SSL certificates:
1. Go to control panel
2. Enable AutoSSL/Let's Encrypt
3. Update `base_url` to use `https://`

### 5. Regular Backups

1. Download database backup monthly
2. Download file backup monthly
3. Store backups securely
4. Test restore process

---

## 📞 Support & Troubleshooting

### InfinityFree Support
- **Website:** https://infinityfree.com
- **Support Ticket:** https://infinityfree.com/support
- **Knowledge Base:** https://infinityfree.com/docs

### Common Resources
- phpMyAdmin: Access via control panel → Databases
- File Manager: Access via control panel → File Manager
- Error Logs: Check control panel → Logs section

### Debug Mode

If you encounter issues, temporarily enable debug mode:

```php
// In includes/config.php
'debug' => true,  // Only for troubleshooting!
```

This will display error messages. Remember to disable after fixing!

---

## ✨ Final Checklist

- [ ] Configuration updated with InfinityFree credentials
- [ ] `.htaccess` file created and uploaded
- [ ] All files uploaded to correct directories
- [ ] Database created and imported
- [ ] Website accessible via domain
- [ ] Admin panel working
- [ ] Forms submitting correctly
- [ ] Images displaying properly
- [ ] HTTPS enabled
- [ ] Debug mode disabled
- [ ] Admin password changed
- [ ] Backup created

---

## 🎉 You're Ready!

Your Kalitusi Nature Resort website is now live on InfinityFree!

**Next Steps:**
1. Share your domain with team members
2. Monitor performance and errors
3. Create regular backups
4. Keep content updated
5. Monitor analytics

---

**Last Updated:** November 20, 2025  
**Status:** Ready for InfinityFree Deployment ✅
