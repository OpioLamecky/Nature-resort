<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';

// Log the start of the login process
error_log('Login page accessed: ' . date('Y-m-d H:i:s'));

// Helper to validate internal redirect destination
function safe_next(string $next): string {
    global $config;
    $base_url = $config['base_url'];
    $default_redirect = $base_url . '/admin/dashboard.php';

    if (empty($next)) {
        return $default_redirect;
    }

    // Prevent protocol-relative URLs (e.g., //example.com) and other schemes
    if (preg_match('#^((//)|([a-z]+:))#i', $next)) {
        // Allow redirects only to our own domain
        if (strpos($next, $base_url) !== 0) {
            return $default_redirect;
        }
        // URL is valid (starts with our base_url)
        return $next;
    }

    // Relative URL or path - make it absolute
    return $base_url . '/' . ltrim($next, '/');
}

// Capture next from query for form and redirects
$next = isset($_GET['next']) ? (string)$_GET['next'] : ($_SERVER['HTTP_REFERER'] ?? base_url('admin/dashboard.php'));

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    error_log('Login form submitted');
    error_log('POST data: ' . print_r($_POST, true));
    $debugSkipCsrf = ($config['debug'] ?? false) && (($_GET['debug_nocsrf'] ?? '') === '1' || ($_POST['debug_nocsrf'] ?? '') === '1');
    if (!$debugSkipCsrf) {
        verify_csrf($_POST['csrf'] ?? '');
    }

    // Debug logs
    error_log('[login] headers_sent=' . (headers_sent() ? '1' : '0') . ' sid=' . session_id());

    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);
    $dest = safe_next((string)($_POST['next'] ?? base_url('admin/dashboard.php')));

    if (filter_var($email, FILTER_VALIDATE_EMAIL) && $password !== '') {
        error_log('Email and password provided, attempting login...');
        // Temporary Debugging
        $user_for_debug = $pdo->prepare('SELECT user_id, password_hash FROM users WHERE email = ? LIMIT 1');
        $user_for_debug->execute([$email]);
        $user_data = $user_for_debug->fetch();
        error_log('Login Attempt for: ' . $email);
        if (!$user_data) {
            error_log('Debug: User not found.');
        } else {
            error_log('Debug: User found. Verifying password...');
            if (password_verify($password, $user_data['password_hash'])) {
                error_log('Debug: Password verification SUCCESSFUL.');
            } else {
                error_log('Debug: Password verification FAILED.');
            }
        }
        // End Temporary Debugging

        error_log('Calling login function...');
        $login_result = login($email, $password);
        error_log('Login function result: ' . ($login_result ? 'SUCCESS' : 'FAILED'));
        
        if ($login_result) {
            error_log('Login successful, before safe_next. dest = ' . var_export($dest, true));
            // Remember me: extend session cookie lifetime
            if ($remember) {
                $p = session_get_cookie_params();
                setcookie(session_name(), session_id(), [
                    'expires' => time() + 30 * 24 * 60 * 60,
                    'path' => $p['path'] ?? '/',
                    'domain' => $p['domain'] ?? '',
                    'secure' => (bool)($p['secure'] ?? false),
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }
            flash('Welcome back!');
            error_log('Login successful, redirecting to: ' . $dest);
            error_log('Calling redirect() with destination: ' . $dest);
            redirect($dest);
        }
    }
    error_log('Login failed: Invalid credentials');
    flash('Invalid credentials.');
    $q = '?next=' . urlencode($dest) . (($debugSkipCsrf) ? '&debug_nocsrf=1' : '');
    redirect(base_url('public/login.php') . $q);
}

$title = 'Login | Kalitusi Nature Resort';
// Don't include header/footer for login page - use custom design
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= sanitize($title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>?v=<?= (int)filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body>

<div class="login-container">
  <div class="login-card">
    <div class="login-header">
      <img src="<?= asset_url('images/kalitusi-logo-transparent.png') ?>" alt="Kalitusi Nature Resort" class="login-logo">
      <h1>Kalitusi Resort</h1>
      <p>Administrator Access Portal</p>
    </div>
    
    <div class="login-body">
      <form method="post" class="login-form">
        <input type="hidden" name="csrf" value="<?= sanitize(csrf_token()) ?>">
        <input type="hidden" name="next" value="<?= sanitize(safe_next($next)) ?>">
        <?php if (($config['debug'] ?? false) && (($_GET['debug_nocsrf'] ?? '') === '1')): ?>
          <input type="hidden" name="debug_nocsrf" value="1">
        <?php endif; ?>
        
        <div class="mb-4">
          <label for="email" class="form-label">
            <i class="fas fa-envelope me-2"></i>Email Address
          </label>
          <input type="email" id="email" class="form-control" name="email" required placeholder="admin@kalitusiresort.com" autocomplete="email">
        </div>
        
        <div class="mb-4">
          <label for="password" class="form-label">
            <i class="fas fa-lock me-2"></i>Password
          </label>
          <input type="password" id="password" class="form-control" name="password" required placeholder="Enter your password" autocomplete="current-password">
        </div>
        
        <div class="form-check mb-4">
          <input class="form-check-input" type="checkbox" name="remember" id="rememberChk">
          <label class="form-check-label" for="rememberChk">
            <small>Keep me signed in for 30 days</small>
          </label>
        </div>
        
        <button type="submit" class="btn btn-login w-100 text-white">
          <i class="fas fa-sign-in-alt me-2"></i>Sign In
        </button>
      </form>
    </div>
    
    <div class="login-footer">
      <small class="text-muted">
        <i class="fas fa-shield-alt me-1"></i>Secure Login • Protected Access
      </small>
      <br>
      <a href="<?= base_url('public/') ?>" class="text-decoration-none mt-2 d-inline-block">
        <i class="fas fa-home me-1"></i>Back to Website
      </a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// End script - no footer needed
