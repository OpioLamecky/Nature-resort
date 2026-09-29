# Database Documentation

## Main Database File

**`COMPLETE_SETUP.sql`** - Complete database schema for Kalitusi Nature Resort

This is the **single, unified database file** that contains everything you need:

### What's Included:
✅ **All table definitions** (14 tables)
✅ **All indexes** for optimized performance
✅ **All foreign keys** and relationships
✅ **Database views** for common queries
✅ **Sample data** for About page
✅ **Full-text search** indexes
✅ **Character set** optimization (utf8mb4)

### Tables Created:
1. `users` - User authentication and management
2. `categories` - Content categorization
3. `posts` - Blog posts and news articles
4. `announcements` - Announcement ticker items
5. `tax_information` - Legacy table (can be repurposed)
6. `services` - Resort services
7. `downloads` - Downloadable resources
8. `faqs` - Frequently asked questions
9. `sliders` - Homepage hero sliders
10. `quick_links` - Quick action links
11. `content_blocks` - Dynamic page content
12. `footer_links` - Footer navigation
13. `site_settings` - Site-wide configuration
14. `about_*` tables - About page content (4 tables)

### Database Views:
- `active_announcements` - Currently active announcements
- `active_sliders` - Currently active sliders
- `visible_quick_links` - Visible quick links
- `published_posts` - Published posts with author info

---

## Installation

### Fresh Installation:

```bash
# Using MySQL command line
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS kalitusi_resort CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p kalitusi_resort < COMPLETE_SETUP.sql
```

### Using phpMyAdmin:
1. Create database: `kalitusi_resort`
2. Go to Import tab
3. Choose file: `COMPLETE_SETUP.sql`
4. Click "Go"

---

## Key Features

### Performance Optimizations:
- **Indexes** on all frequently queried columns
- **Composite indexes** for complex queries
- **Full-text search** on posts, FAQs, and services
- **Database views** to simplify complex queries
- **Optimized character sets** (utf8mb4)

### Security:
- Foreign key constraints for data integrity
- Proper indexing for query performance
- ENUM types for controlled values
- Timestamps for audit trails

### Scalability:
- InnoDB engine for ACID compliance
- Efficient data types
- Normalized structure
- Room for future expansion

---

## Database Configuration

### Update Connection Settings:
Edit `includes/config.php`:
```php
'db' => [
  'host' => '127.0.0.1',
  'port' => '3306',
  'name' => 'kalitusi_resort',
  'user' => 'root',
  'pass' => '',
  'charset' => 'utf8mb4',
]
```

---

## Maintenance

### Regular Tasks:
```sql
-- Optimize tables monthly
OPTIMIZE TABLE users, posts, announcements;

-- Analyze tables after major data changes
ANALYZE TABLE posts, sliders, about_content;

-- Check table health
CHECK TABLE users, posts;

-- Repair if needed
REPAIR TABLE tablename;
```

### Backup Command:
```bash
mysqldump -u root -p kalitusi_resort > backup_$(date +%Y%m%d).sql
```

---

## Sample Queries

### Get Active Sliders:
```sql
SELECT * FROM active_sliders;
```

### Get Published Posts:
```sql
SELECT * FROM published_posts LIMIT 10;
```

### Search Posts:
```sql
SELECT * FROM posts 
WHERE MATCH(title, content) AGAINST('nature resort' IN NATURAL LANGUAGE MODE);
```

### Get Active Announcements:
```sql
SELECT * FROM active_announcements;
```

---

## Migration from Old Structure

If you have existing data:

1. **Backup your current database** first!
2. Export existing data
3. Import `COMPLETE_SETUP.sql`
4. Import your data back

---

## Troubleshooting

### Error: Table already exists
**Solution:** Drop existing tables or database first:
```sql
DROP DATABASE IF EXISTS kalitusi_resort;
CREATE DATABASE kalitusi_resort CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Error: Access denied
**Solution:** Check MySQL user permissions:
```sql
GRANT ALL PRIVILEGES ON kalitusi_resort.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
```

### Error: Views not created
**Solution:** Ensure base tables exist first, then run view creation separately.

---

## Version History

**Version 2.0** (November 17, 2025)
- Cleaned up all development and testing files
- Removed backup and migration directories
- Single production-ready database file
- Streamlined documentation

**Version 1.0** (November 3, 2025)
- Consolidated 3 separate files into 1
- Added all indexes and optimizations
- Updated sample data for resort context
- Added comprehensive documentation

---

## Support

For database-related issues:
1. Check this README
2. Review the SQL file comments
3. Test queries in phpMyAdmin first
4. Check MySQL error logs

---

**Last Updated:** November 17, 2025  
**Database Version:** 2.0  
**Status:** Production Ready ✅
