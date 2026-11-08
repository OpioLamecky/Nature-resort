# Uploads Directory

This directory stores files uploaded through the URA website admin panel.

## Structure

- `sliders/` - Homepage slider images (max 10MB, JPG/PNG/GIF/WEBP)
- `downloads/` - Downloadable documents (forms, guides, etc.)

## Security

- `.htaccess` files control access per directory
- Only specific file types are allowed
- Files are validated on upload (MIME type, size, extension)
- Unique filenames prevent overwrites

## Permissions

Ensure this directory has write permissions (755 or 775) for the web server user.
