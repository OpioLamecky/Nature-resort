<?php
/**
 * PDO Database Connection
 * Credentials come from config.php → $_ENV → .env.local (dev) or .env (prod)
 */

$config = require __DIR__ . '/config.php';

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['db']['host'],
    $config['db']['port'],
    $config['db']['name'],
    $config['db']['charset']
);

try {
    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

} catch (PDOException $e) {
    error_log('Database Connection Error: ' . $e->getMessage());
    http_response_code(500);

    if (defined('APP_DEBUG') && APP_DEBUG) {
        // Development: show full error details so you can fix quickly
        echo '<h1 style="color:#c0392b;font-family:monospace">Database Connection Failed</h1>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>DSN:</strong> ' . htmlspecialchars($dsn) . '</p>';
        echo '<p><strong>User:</strong> ' . htmlspecialchars($config['db']['user']) . '</p>';
        echo '<p><em>This detailed message is shown only in development mode.</em></p>';
    } else {
        // Production: hide all technical details from visitors
        echo '<h1>Service Temporarily Unavailable</h1>';
        echo '<p>We are experiencing technical difficulties. Please try again later.</p>';
    }

    exit(1);
}
