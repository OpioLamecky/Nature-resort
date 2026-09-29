<?php
/**
 * Application Configuration
 *
 * ALL values come from $_ENV (loaded by bootstrap.php from .env.local or .env).
 * There are NO hardcoded credentials here — that is intentional.
 * Edit .env.local for development settings, .env for production settings.
 */
return [

    'app_name' => 'Kalitusi Nature Resort',

    // APP_ENV / APP_DEBUG are set as PHP constants in bootstrap.php (IS_DEV, IS_PROD)
    // but we also expose them here for any code that reads $config directly.
    'env'   => $_ENV['APP_ENV']   ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),

    // Base URL — http://localhost/Nature resort in dev, https://kalitusi.com in prod
    'base_url' => rtrim($_ENV['APP_URL'] ?? 'https://kalitusi.com', '/'),

    // ── Database ─────────────────────────────────────────────────────────────
    'db' => [
        'host'    => $_ENV['DB_HOST']    ?? 'localhost',
        'port'    => $_ENV['DB_PORT']    ?? '3306',
        'name'    => $_ENV['DB_NAME']    ?? 'kalitusi_resort',
        'user'    => $_ENV['DB_USER']    ?? 'root',
        'pass'    => $_ENV['DB_PASS']    ?? '',
        'charset' => 'utf8mb4',
    ],

    // ── Security ─────────────────────────────────────────────────────────────
    'security' => [
        'session_name'  => $_ENV['SESSION_NAME'] ?? 'kalitusi_sess',
        'csrf_key'      => 'csrf_token',
        'password_algo' => PASSWORD_BCRYPT,
    ],

    // ── SMTP / Email ─────────────────────────────────────────────────────────
    'smtp' => [
        'host'       => $_ENV['SMTP_HOST']       ?? 'localhost',
        'port'       => (int)($_ENV['SMTP_PORT'] ?? 587),
        'user'       => $_ENV['SMTP_USER']       ?? '',
        'pass'       => $_ENV['SMTP_PASS']       ?? '',
        'from_email' => $_ENV['SMTP_FROM_EMAIL'] ?? 'noreply@kalitusi.com',
        'from_name'  => $_ENV['SMTP_FROM_NAME']  ?? 'Kalitusi Nature Resort',
    ],

];
