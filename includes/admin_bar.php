<?php
/**
 * Admin Bar - Shows on public pages when admin is logged in
 * Provides quick navigation back to admin dashboard
 */

// Only show if user is logged in as admin
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    return;
}

$user = $_SESSION['user'];
$isAdmin = in_array($user['role'], ['Super Admin', 'Manager', 'Content Manager', 'Receptionist', 'Editor']);

if (!$isAdmin) {
    return;
}
?>

<!-- Admin Bar Styles -->
<style>
#admin-bar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 10px 20px;
    z-index: 99999;
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 14px;
}

#admin-bar a {
    color: white;
    text-decoration: none;
    padding: 6px 12px;
    border-radius: 4px;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

#admin-bar a:hover {
    background: rgba(255,255,255,0.2);
}

#admin-bar .admin-links {
    display: flex;
    gap: 10px;
    align-items: center;
}

#admin-bar .admin-user {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
}

#admin-bar .user-badge {
    background: rgba(255,255,255,0.2);
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

#admin-bar-toggle {
    position: fixed;
    top: 10px;
    right: 10px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    z-index: 99998;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    font-size: 12px;
    display: none;
}

#admin-bar.hidden {
    transform: translateY(-100%);
    transition: transform 0.3s;
}

#admin-bar.visible {
    transform: translateY(0);
    transition: transform 0.3s;
}

/* Push body content down when admin bar is visible */
body.admin-logged-in {
    padding-top: 45px;
}

@media (max-width: 768px) {
    #admin-bar {
        flex-direction: column;
        gap: 10px;
        padding: 12px 15px;
    }
    
    #admin-bar .admin-links {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    body.admin-logged-in {
        padding-top: 90px;
    }
}
</style>

<!-- Admin Bar HTML -->
<div id="admin-bar" class="visible">
    <div class="admin-user">
        <i class="fas fa-user-shield"></i>
        <span><?= sanitize($user['username']) ?></span>
        <span class="user-badge"><?= sanitize($user['role']) ?></span>
    </div>
    
    <div class="admin-links">
        <a href="<?= base_url('admin/dashboard.php') ?>" title="Back to Dashboard">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        
        <a href="<?= base_url('admin/bookings.php') ?>" title="Manage Bookings">
            <i class="fas fa-calendar-check"></i>
            <span>Bookings</span>
        </a>
        
        <a href="<?= base_url('admin/accommodations-manage.php') ?>" title="Manage Rooms">
            <i class="fas fa-bed"></i>
            <span>Rooms</span>
        </a>
        
        <a href="<?= base_url('admin/testimonials.php') ?>" title="Manage Reviews">
            <i class="fas fa-star"></i>
            <span>Reviews</span>
        </a>
        
        <a href="<?= base_url('admin/profile.php') ?>" title="My Profile">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
        
        <a href="<?= base_url('public/logout.php') ?>" title="Logout" style="background: rgba(255,255,255,0.1);">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<button id="admin-bar-toggle" onclick="toggleAdminBar()">
    <i class="fas fa-tools"></i> Admin
</button>

<script>
// Add class to body
document.body.classList.add('admin-logged-in');

// Toggle admin bar visibility
function toggleAdminBar() {
    const bar = document.getElementById('admin-bar');
    if (bar.classList.contains('visible')) {
        bar.classList.remove('visible');
        bar.classList.add('hidden');
        document.body.classList.remove('admin-logged-in');
    } else {
        bar.classList.remove('hidden');
        bar.classList.add('visible');
        document.body.classList.add('admin-logged-in');
    }
}

// Optional: Hide admin bar on scroll down, show on scroll up
let lastScrollTop = 0;
window.addEventListener('scroll', function() {
    // Uncomment below to enable auto-hide on scroll
    /*
    const st = window.pageYOffset || document.documentElement.scrollTop;
    const bar = document.getElementById('admin-bar');
    
    if (st > lastScrollTop && st > 100) {
        // Scrolling down
        bar.classList.remove('visible');
        bar.classList.add('hidden');
    } else {
        // Scrolling up
        bar.classList.remove('hidden');
        bar.classList.add('visible');
    }
    lastScrollTop = st <= 0 ? 0 : st;
    */
}, false);
</script>

<?php
// Make Font Awesome available if not already loaded
if (!defined('ADMIN_BAR_FA_LOADED')):
    define('ADMIN_BAR_FA_LOADED', true);
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<?php endif; ?>
