# Environment Setup Guide

Configure your application for different environments (local, staging, production).

---

## 🎯 Environment Overview

Your application supports three environments:

| Environment | Purpose | Debug | Base URL |
|------------|---------|-------|----------|
| **local** | Development | true | `/Nature%20resort` |
| **staging** | Testing | true | `https://staging.domain.com` |
| **production** | Live | false | `https://yourdomain.infinityfree.com` |

---

## 🏠 Local Development Setup

### Configuration

Edit `includes/config.php`:

```php
<?php
return [
  'app_name' => 'Kalitusi Nature Resort',
  'env' => 'local',      // Local environment
  'debug' => true,       // Enable debug mode
  'base_url' => '/Nature%20resort',  // Local path
  'db' => [
    'host' => '127.0.0.1',
    'port' => '3306',
    'name' => 'kalitusi_resort',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4',
  ],
  'security' => [
    'session_name' => 'kalitusi_sess',
    'csrf_key' => 'csrf_token',
    'password_algo' => PASSWORD_BCRYPT,
  ],
];
```

### Features in Local Mode

- ✅ Debug mode enabled - shows detailed error messages
- ✅ Database errors displayed in browser
- ✅ Full stack traces for debugging
- ✅ CSRF protection active
- ✅ Session management active
- ✅ All features available

### Local Development Best Practices

1. **Use WAMP/LAMP Stack**
   - Apache with mod_rewrite
   - PHP 8.0+
   - MySQL 5.7+

2. **Enable Error Reporting**
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

3. **Use Version Control**
   ```bash
   git init
   git add .
   git commit -m "Initial commit"
   ```

4. **Create Local Database**
   ```bash
   mysql -u root -p -e "CREATE DATABASE kalitusi_resort CHARACTER SET utf8mb4;"
   mysql -u root -p kalitusi_resort < database/COMPLETE_SETUP.sql
   ```

5. **Test All Features**
   - Test forms
   - Test admin panel
   - Test database operations
   - Test file uploads

---

## 🧪 Staging Environment Setup

### Purpose

Staging is a production-like environment for testing before live deployment.

### Configuration

Edit `includes/config.php`:

```php
<?php
return [
  'app_name' => 'Kalitusi Nature Resort',
  'env' => 'staging',    // Staging environment
  'debug' => true,       // Keep debug for testing
  'base_url' => 'https://staging.yourdomain.com',  // Staging domain
  'db' => [
    'host' => 'staging-db.example.com',
    'port' => '3306',
    'name' => 'staging_kalitusi',
    'user' => 'staging_user',
    'pass' => 'staging_password',
    'charset' => 'utf8mb4',
  ],
  'security' => [
    'session_name' => 'kalitusi_staging',
    'csrf_key' => 'csrf_token',
    'password_algo' => PASSWORD_BCRYPT,
  ],
];
```

### Staging Setup Steps

1. **Create Staging Database**
   - Use separate database from production
   - Import test data
   - Verify all tables created

2. **Deploy to Staging Server**
   - Upload files to staging domain
   - Update configuration
   - Test all functionality

3. **Run Full Test Suite**
   - Test all pages
   - Test all forms
   - Test admin panel
   - Test database operations
   - Test security features

4. **Performance Testing**
   - Measure page load times
   - Test under load
   - Optimize if needed

5. **Security Testing**
   - Test CSRF protection
   - Test XSS prevention
   - Test SQL injection prevention
   - Test authentication

---

## 🚀 Production Environment Setup

### Configuration

Edit `includes/config.php`:

```php
<?php
return [
  'app_name' => 'Kalitusi Nature Resort',
  'env' => 'production',  // Production environment
  'debug' => false,       // NEVER enable in production
  'base_url' => 'https://yourdomain.infinityfree.com',  // Production domain
  'db' => [
    'host' => 'sql.infinityfree.com',
    'port' => '3306',
    'name' => 'your_database_name',
    'user' => 'your_database_user',
    'pass' => 'your_database_password',
    'charset' => 'utf8mb4',
  ],
  'security' => [
    'session_name' => 'kalitusi_prod_sess',
    'csrf_key' => 'csrf_token',
    'password_algo' => PASSWORD_BCRYPT,
  ],
];
```

### Production Requirements

1. **HTTPS Enabled**
   - Enable SSL certificate
   - Update base_url to https://
   - Redirect HTTP to HTTPS

2. **Debug Mode Disabled**
   - Set `debug` to `false`
   - No error messages shown to users
   - Errors logged to file instead

3. **Strong Credentials**
   - Strong database password
   - Strong admin password
   - Unique session name

4. **Security Headers**
   - X-Content-Type-Options
   - X-Frame-Options
   - Referrer-Policy
   - X-XSS-Protection

5. **Caching Enabled**
   - Browser caching
   - Server-side caching
   - Database query caching

### Production Deployment Checklist

- [ ] Configuration updated
- [ ] Debug mode disabled
- [ ] HTTPS enabled
- [ ] Database backed up
- [ ] Files backed up
- [ ] Admin password changed
- [ ] Test accounts deleted
- [ ] Error logging enabled
- [ ] Monitoring set up
- [ ] Backups scheduled

---

## 🔄 Environment Variables

### Using Environment Variables (Optional)

For enhanced security, use environment variables instead of hardcoding credentials:

```php
<?php
// includes/config.php
return [
  'app_name' => 'Kalitusi Nature Resort',
  'env' => $_ENV['APP_ENV'] ?? 'local',
  'debug' => $_ENV['APP_DEBUG'] ?? false,
  'base_url' => $_ENV['APP_URL'] ?? '/Nature%20resort',
  'db' => [
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'port' => $_ENV['DB_PORT'] ?? '3306',
    'name' => $_ENV['DB_NAME'] ?? 'kalitusi_resort',
    'user' => $_ENV['DB_USER'] ?? 'root',
    'pass' => $_ENV['DB_PASS'] ?? '',
    'charset' => 'utf8mb4',
  ],
  'security' => [
    'session_name' => $_ENV['SESSION_NAME'] ?? 'kalitusi_sess',
    'csrf_key' => 'csrf_token',
    'password_algo' => PASSWORD_BCRYPT,
  ],
];
```

### Setting Environment Variables

**On InfinityFree:**
1. Create `.env` file (if supported)
2. Or use control panel environment variables
3. Or hardcode in config.php (less secure)

**On Local Machine:**
```bash
# .env file
APP_ENV=local
APP_DEBUG=true
APP_URL=/Nature%20resort
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=kalitusi_resort
DB_USER=root
DB_PASS=
```

---

## 🔐 Security by Environment

### Local Environment

```php
'debug' => true,        // Show errors
'env' => 'local',       // Local mode
// Database errors visible
// Stack traces visible
// All features enabled
```

**Security Level:** 🟡 Low (for development only)

### Staging Environment

```php
'debug' => true,        // Show errors for testing
'env' => 'staging',     // Staging mode
// Similar to production
// But with debug enabled
// For testing purposes
```

**Security Level:** 🟠 Medium (testing environment)

### Production Environment

```php
'debug' => false,       // Hide errors
'env' => 'production',  // Production mode
// Errors logged to file
// No stack traces shown
// All security features active
```

**Security Level:** 🟢 High (production)

---

## 📊 Configuration Comparison

| Setting | Local | Staging | Production |
|---------|-------|---------|------------|
| `env` | local | staging | production |
| `debug` | true | true | false |
| `base_url` | /Nature%20resort | https://staging.* | https://yourdomain.* |
| DB Host | 127.0.0.1 | staging-db | sql.infinityfree.com |
| DB Name | kalitusi_resort | staging_kalitusi | prod_kalitusi |
| HTTPS | No | Yes | Yes |
| Error Display | Browser | Browser | Log File |
| Caching | Minimal | Full | Full |
| Backups | Manual | Daily | Daily |

---

## 🔄 Switching Environments

### From Local to Staging

1. **Update config.php**
   ```php
   'env' => 'staging',
   'debug' => true,
   'base_url' => 'https://staging.yourdomain.com',
   // Update database credentials
   ```

2. **Export local database**
   ```bash
   mysqldump -u root -p kalitusi_resort > staging_data.sql
   ```

3. **Import to staging database**
   - Use phpMyAdmin or command line
   - Verify data integrity

4. **Upload files to staging**
   - Use FTP or File Manager
   - Verify all files uploaded

5. **Test thoroughly**
   - Test all pages
   - Test all forms
   - Test admin panel

### From Staging to Production

1. **Final testing on staging**
   - [ ] All pages working
   - [ ] All forms working
   - [ ] Admin panel working
   - [ ] Database queries working
   - [ ] Security features working

2. **Backup production database**
   ```bash
   mysqldump -u prod_user -p prod_kalitusi > prod_backup.sql
   ```

3. **Update config.php**
   ```php
   'env' => 'production',
   'debug' => false,
   'base_url' => 'https://yourdomain.infinityfree.com',
   // Update database credentials
   ```

4. **Upload files to production**
   - Use FTP or File Manager
   - Verify all files uploaded

5. **Verify production**
   - [ ] Website accessible
   - [ ] Admin panel working
   - [ ] Database connected
   - [ ] HTTPS working
   - [ ] No errors in logs

---

## 🛠️ Troubleshooting by Environment

### Local Environment Issues

**Problem:** Database connection fails
- Check MySQL is running
- Verify credentials
- Check database exists

**Problem:** Pages show 404
- Check Apache is running
- Verify mod_rewrite enabled
- Check .htaccess exists

### Staging Environment Issues

**Problem:** Staging database empty
- Verify import completed
- Check database user permissions
- Verify file uploaded correctly

**Problem:** Staging pages slow
- Check server resources
- Optimize queries
- Enable caching

### Production Environment Issues

**Problem:** Production database connection fails
- Verify InfinityFree credentials
- Check database exists
- Test in phpMyAdmin

**Problem:** Production pages show errors
- Check debug is false
- Check error logs
- Verify configuration

---

## 📋 Environment Checklist

### Before Deploying to Staging

- [ ] All tests pass locally
- [ ] Code reviewed
- [ ] Database backed up
- [ ] Files backed up
- [ ] Configuration ready
- [ ] Security features tested

### Before Deploying to Production

- [ ] All tests pass on staging
- [ ] Performance acceptable
- [ ] Security verified
- [ ] Database backed up
- [ ] Files backed up
- [ ] Configuration ready
- [ ] Admin password changed
- [ ] HTTPS enabled
- [ ] Monitoring set up

---

## 🎯 Best Practices

1. **Never commit credentials**
   - Use environment variables
   - Use .env files
   - Use control panel settings

2. **Always backup before changes**
   - Backup database
   - Backup files
   - Test restore process

3. **Test in staging first**
   - Deploy to staging
   - Test thoroughly
   - Then deploy to production

4. **Monitor production**
   - Check error logs
   - Monitor performance
   - Monitor security

5. **Keep environments consistent**
   - Same PHP version
   - Same MySQL version
   - Same configuration structure

---

## 📞 Support

- **Local Issues:** Check WAMP/LAMP setup
- **Staging Issues:** Contact hosting provider
- **Production Issues:** Contact InfinityFree support

---

**Last Updated:** November 20, 2025
