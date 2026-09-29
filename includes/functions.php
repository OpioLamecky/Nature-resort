<?php
// Shared functions
function app_config(): array { return require __DIR__ . '/config.php'; }

function base_url(string $path = ''): string {
    // Read from $_ENV directly (loaded by bootstrap.php) to avoid re-parsing config
    $base = rtrim($_ENV['APP_URL'] ?? 'https://kalitusi.com', '/');
    $p    = $path !== '' ? '/' . ltrim($path, '/') : '';
    return $base . $p;
}

function asset_url(string $path): string {
    return base_url('assets/' . ltrim($path, '/'));
}

/**
 * Flash message helper — stores a message + severity in the session.
 *
 * Set:  flash('Something went wrong', 'danger')
 * Get:  $f = flash();  // returns ['msg' => '...', 'type' => 'danger'] or null
 */
function flash(?string $msg = null, string $type = 'info'): array|null {
    if ($msg !== null) {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
        return null;
    }
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        // Back-compat: if old code stored a plain string, wrap it
        return is_array($f) ? $f : ['msg' => $f, 'type' => 'info'];
    }
    return null;
}

function redirect(string $to): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    if (headers_sent()) {
        echo "<script>window.location.href='{$to}';</script>";
        exit;
    }
    header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    header('Location: ' . $to);
    exit;
}

function is_logged_in(): bool { return !empty($_SESSION['user']); }

function require_role(array $roles): void {
    $user = $_SESSION['user'] ?? null;
    if (!$user || !in_array($user['role'] ?? 'public', $roles, true)) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return $text ?: 'post';
}

function paginate_params(int $defaultPerPage = 10): array {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $per = max(1, min(100, (int)($_GET['per'] ?? $defaultPerPage)));
    $offset = ($page - 1) * $per;
    return [$page, $per, $offset];
}

// Image URL helper: accepts relative CMS paths (e.g. 'images/home/hero.jpg')
// and returns a full URL using asset_url, but leaves absolute URLs untouched.
function cms_image_url(?string $path): ?string {
    if (!$path) {
        return null;
    }
    // If already absolute (http/https) or root-relative, or already includes "assets/" at start, return properly
    if (preg_match('~^https?://~i', $path) || str_starts_with($path, '/')) {
        return $path;
    }
    // If path already starts with 'assets/', convert to full URL using asset_url
    if (str_starts_with($path, 'assets/')) {
        return base_url(ltrim('/' . $path, '/'));
    }
    // Otherwise treat as relative under assets/ (e.g. 'images/...') and attempt smart extension fallback
    $full = asset_url($path);
    $localRoot = dirname(__DIR__) . '/assets/';
    $relPath = ltrim($path, '/');
    if (!file_exists($localRoot . $relPath)) {
        // Try swapping common extensions
        $extMap = ['jpg'=>'webp','jpeg'=>'webp','png'=>'webp','webp'=>'jpg'];
        $currentExt = strtolower(pathinfo($relPath, PATHINFO_EXTENSION));
        if (isset($extMap[$currentExt])) {
            $altRel = preg_replace('/\.'.$currentExt.'$/i', '.' . $extMap[$currentExt], $relPath);
            if (file_exists($localRoot . $altRel)) {
                return asset_url($altRel);
            }
        }
    }
    return $full;
}

// Security: CSRF Token Generation & Validation
function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(string $token): bool {
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

// Note: sanitize() function exists in security.php
// Note: rate_limit() function exists in security.php

// Cache helper
function cache_get(string $key): mixed {
    $file = __DIR__ . '/../cache/' . md5($key) . '.cache';
    if (file_exists($file)) {
        $data = unserialize(file_get_contents($file));
        if ($data['expires'] > time()) {
            return $data['value'];
        }
        unlink($file);
    }
    return null;
}

function cache_set(string $key, mixed $value, int $ttl = 3600): bool {
    $dir = __DIR__ . '/../cache';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $file = $dir . '/' . md5($key) . '.cache';
    $data = ['value' => $value, 'expires' => time() + $ttl];
    return file_put_contents($file, serialize($data)) !== false;
}

