<?php
/**
 * Kalitusi Nature Resort - Main Entry Point
 * 
 * This is the primary index file for the website.
 * Hosting providers can easily access this as the main entry point.
 * 
 * The application is organized as follows:
 * - /public/     - Public-facing website pages
 * - /admin/      - Admin panel and management
 * - /includes/   - Shared PHP includes and functions
 * - /assets/     - CSS, JavaScript, images
 * - /database/   - Database schema and setup
 * 
 * All public website requests are routed to /public/index.php
 * All admin requests are routed to /admin/
 */

// Route to public homepage
require __DIR__ . '/public/index.php';
