# InfinityFree Quick Start Guide

Fast-track deployment to InfinityFree in 5 steps.

---

## 🚀 5-Minute Quick Start

### Step 1: Get InfinityFree Credentials (2 minutes)

1. Sign up at https://infinityfree.com
2. Go to **Control Panel** → **Databases**
3. Create new database, note:
   - Database name
   - Database user
   - Database password
   - Host: `sql.infinityfree.com`

### Step 2: Update Configuration (1 minute)

Edit `includes/config.php`:

```php
'base_url' => 'https://yourusername.infinityfree.com',  // Your domain
'db' => [
    'host' => 'sql.infinityfree.com',
    'name' => 'YOUR_DB_NAME',      // From step 1
    'user' => 'YOUR_DB_USER',      // From step 1
    'pass' => 'YOUR_DB_PASSWORD',  // From step 1
]
```

Also change:
```php
'env' => 'production',
'debug' => false,
```

### Step 3: Upload Files (1 minute)

Using InfinityFree **File Manager**:
1. Go to Control Panel → **File Manager**
2. Navigate to public directory
3. Upload these folders:
   - `public/`
   - `admin/`
   - `assets/`
   - `includes/`
   - `database/` (optional)
4. Upload `.htaccess` file

### Step 4: Import Database (30 seconds)

1. Go to Control Panel → **Databases** → **phpMyAdmin**
2. Select your database
3. Click **Import** tab
4. Upload `database/COMPLETE_SETUP.sql`
5. Click **Go**

### Step 5: Test Website (30 seconds)

1. Visit your domain: `https://yourusername.infinityfree.com`
2. Check homepage loads
3. Visit `/admin/` and login
4. Done! 🎉

---

## 📋 Credentials Checklist

Save these values from InfinityFree:

```
Domain: ___________________________
Database Host: sql.infinityfree.com
Database Name: ___________________________
Database User: ___________________________
Database Password: ___________________________
FTP Host: ___________________________
FTP User: ___________________________
FTP Password: ___________________________
```

---

## 🔧 Troubleshooting

### "Database connection failed"
- Check credentials in config.php
- Verify database exists in InfinityFree
- Test in phpMyAdmin

### "404 Not Found"
- Verify .htaccess uploaded
- Check directory structure
- Verify file permissions

### "Images not showing"
- Check base_url in config.php
- Verify images uploaded
- Check image file permissions

### "Admin won't load"
- Verify admin/ directory uploaded
- Check database connection
- Clear browser cache

---

## 📞 Support

- **InfinityFree Help:** https://infinityfree.com/support
- **phpMyAdmin:** Access via Control Panel → Databases
- **File Manager:** Access via Control Panel → File Manager

---

## ✅ Success Indicators

Your site is live when:
- ✅ Homepage loads at your domain
- ✅ Navigation links work
- ✅ Images display
- ✅ Admin panel accessible at `/admin/`
- ✅ Forms submit without errors

---

**Ready to deploy? Follow the 5 steps above!**
