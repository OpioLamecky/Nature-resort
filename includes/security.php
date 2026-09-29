<?php
// Security helpers - session handling moved to bootstrap.php

function csrf_token(): string {
    $key = 'csrf_token';
    if (empty($_SESSION[$key])) {
        $_SESSION[$key] = bin2hex(random_bytes(32));
    }
    return $_SESSION[$key];
}

function verify_csrf(?string $token): void {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$token)) {
        http_response_code(419);
        echo 'CSRF token mismatch';
        exit;
    }
}

function sanitize(string $html): string {
    return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
}

function require_post(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        echo 'Method Not Allowed';
        exit;
    }
}

function rate_limit(string $key, int $maxAttempts = 5, int $decayMinutes = 15): bool {
    $attempts = $_SESSION["rate_limit_{$key}"] ?? [];
    $now = time();
    
    // Remove old attempts
    $attempts = array_filter($attempts, fn($time) => $time > ($now - ($decayMinutes * 60)));
    
    if (count($attempts) >= $maxAttempts) {
        return false;
    }
    
    $attempts[] = $now;
    $_SESSION["rate_limit_{$key}"] = $attempts;
    return true;
}
