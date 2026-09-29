<?php
/**
 * Bootstrap — Environment Detection & Application Startup
 *
 * HOW IT WORKS:
 *   Development  →  .env.local exists  →  local WAMP DB, debug ON, localhost URLs
 *   Production   →  .env.local absent  →  live DB, debug OFF, HTTPS enforced
 *
 * TO SWITCH MODES:
 *   - Development : ensure .env.local exists in the project root (already created for you)
 *   - Production  : delete or rename .env.local before deploying to the live server
 */

// ─── 1. Locate the Project Root ──────────────────────────────────────────────
define('APP_ROOT', dirname(__DIR__));

// ─── 2. Choose the Right .env File ───────────────────────────────────────────
//   .env.local  →  development (WAMP / local)
//   .env        →  production  (live server)
$envFile = file_exists(APP_ROOT . '/.env.local')
    ? APP_ROOT . '/.env.local'
    : APP_ROOT . '/.env';

// ─── 3. Parse and Load Environment Variables ─────────────────────────────────
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Skip comments and empty lines
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        // Only process lines with an = sign
        if (strpos($line, '=') === false) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $name  = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'"); // strip quotes too
        if (!empty($name)) {
            $_ENV[$name]    = $value;
            $_SERVER[$name] = $value;
            putenv("$name=$value");
        }
    }
}

// ─── 4. Determine Environment ────────────────────────────────────────────────
$appEnv   = $_ENV['APP_ENV']   ?? 'production';
$appDebug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);

define('APP_ENV',   $appEnv);
define('APP_DEBUG', $appDebug);
define('IS_DEV',    $appEnv === 'development');
define('IS_PROD',   $appEnv === 'production');

// ─── 5. PHP Error Reporting Based on Environment ─────────────────────────────
if (IS_DEV) {
    // Development: show all errors on screen for fast debugging
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    // Production: hide errors from visitors, log them to file only
    error_reporting(E_ALL);
    ini_set('display_errors',         '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors',             '1');
    ini_set('error_log',              APP_ROOT . '/logs/php_errors.log');
}

// ─── 6. Session Configuration (before session_start) ─────────────────────────
$sessionName = $_ENV['SESSION_NAME'] ?? (IS_PROD ? 'kalitusi_prod_sess' : 'kalitusi_dev_sess');

if (IS_PROD) {
    ini_set('session.cookie_secure',   1);       // HTTPS only
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.cookie_httponly', 1);
    ini_set('session.gc_maxlifetime',  7200);    // 2 hours
} else {
    ini_set('session.cookie_secure',   0);       // HTTP ok for localhost
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_httponly', 1);
    ini_set('session.gc_maxlifetime',  3600);    // 1 hour
}

session_name($sessionName);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── 7. Load Core Files ───────────────────────────────────────────────────────
require_once __DIR__ . '/php8_compat.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/security.php';
