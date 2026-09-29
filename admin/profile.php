<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/activity_logger.php';
require_once __DIR__ . '/../includes/file_upload.php';

// Check authentication
if (!isset($_SESSION['user'])) {
    redirect(base_url('public/login.php'));
}

$title = 'My Profile | Admin';
$currentUser = $_SESSION['user'];
$userId = $currentUser['user_id'];
$errors = [];
$success = '';

// Get user data first
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$userData = $stmt->fetch();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? '');
    
    $action = $_POST['action'] ?? '';
    
    try {
        // Upload Profile Photo
        if ($action === 'upload_photo' && isset($_FILES['profile_photo'])) {
            $uploadResult = upload_profile_photo($_FILES['profile_photo'], $userId);
            
            if ($uploadResult['success']) {
                // Delete old photo if exists
                if (!empty($userData['profile_photo'])) {
                    delete_profile_photo($userData['profile_photo']);
                }
                
                // Update database
                $stmt = $pdo->prepare("UPDATE users SET profile_photo = ? WHERE user_id = ?");
                $stmt->execute([$uploadResult['file_path'], $userId]);
                
                // Resize image
                $fullPath = __DIR__ . '/../' . $uploadResult['file_path'];
                resize_image($fullPath, 400, 400);
                
                // Update session
                $_SESSION['user']['profile_photo'] = $uploadResult['file_path'];
                
                // Log activity
                log_activity('photo_upload', 'Uploaded new profile photo', 'user', $userId);
                
                $success = 'Profile photo uploaded successfully!';
                $userData['profile_photo'] = $uploadResult['file_path'];
            } else {
                $errors[] = $uploadResult['error'];
            }
        }
        
        // Delete Profile Photo
        elseif ($action === 'delete_photo') {
            if (!empty($userData['profile_photo'])) {
                delete_profile_photo($userData['profile_photo']);
                
                $stmt = $pdo->prepare("UPDATE users SET profile_photo = NULL WHERE user_id = ?");
                $stmt->execute([$userId]);
                
                $_SESSION['user']['profile_photo'] = null;
                
                log_activity('photo_delete', 'Deleted profile photo', 'user', $userId);
                
                $success = 'Profile photo deleted successfully!';
                $userData['profile_photo'] = null;
            }
        }
        
        // Update Profile Information
        elseif ($action === 'update_profile') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $department = trim($_POST['department'] ?? '');
            
            // Validation
            if (empty($username)) {
                $errors[] = 'Username is required.';
            }
            if (empty($email)) {
                $errors[] = 'Email is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format.';
            }
            
            // Check if email is already used by another user
            if (empty($errors)) {
                $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
                $stmt->execute([$email, $userId]);
                if ($stmt->fetch()) {
                    $errors[] = 'Email is already in use by another user.';
                }
            }
            
            // Update if no errors
            if (empty($errors)) {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET username = ?, email = ?, department = ?
                    WHERE user_id = ?
                ");
                $stmt->execute([$username, $email, $department, $userId]);
                
                // Update session
                $_SESSION['user']['username'] = $username;
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['department'] = $department;
                
                // Log activity
                log_activity('profile_update', 'Updated profile information', 'user', $userId);
                
                $success = 'Profile updated successfully!';
                $currentUser = $_SESSION['user'];
            }
        }
        
        // Change Password
        elseif ($action === 'change_password') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validation
            if (empty($currentPassword)) {
                $errors[] = 'Current password is required.';
            }
            if (empty($newPassword)) {
                $errors[] = 'New password is required.';
            }
            if (strlen($newPassword) < 6) {
                $errors[] = 'New password must be at least 6 characters.';
            }
            if ($newPassword !== $confirmPassword) {
                $errors[] = 'New passwords do not match.';
            }
            
            // Verify current password
            if (empty($errors)) {
                $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
                
                if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
                    $errors[] = 'Current password is incorrect.';
                }
            }
            
            // Update password if no errors
            if (empty($errors)) {
                $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                $stmt->execute([$newPasswordHash, $userId]);
                
                // Log activity
                log_activity('password_change', 'Changed account password', 'user', $userId);
                
                $success = 'Password changed successfully!';
            }
        }
        
    } catch (Exception $e) {
        $errors[] = 'Error: ' . $e->getMessage();
    }
}

// Get user activity logs
$activityLogs = get_user_activity($userId, 20);
$activityCount = get_user_activity_count($userId);

$pageTitle = 'My Profile';
include __DIR__ . '/../includes/admin_layout_header.php';
?>

  
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1">My Profile</h1>
      <p class="text-muted mb-0">Manage your account settings</p>
    </div>
    <a href="<?= base_url('admin/dashboard.php') ?>" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
    </a>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <strong>Error!</strong>
      <ul class="mb-0">
        <?php foreach ($errors as $error): ?>
          <li><?= sanitize($error) ?></li>
        <?php endforeach; ?>
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <strong>Success!</strong> <?= sanitize($success) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="row g-4">
    
    <!-- Profile Photo -->
    <div class="col-lg-4">
      <div class="card">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0"><i class="fas fa-camera me-2"></i>Profile Photo</h5>
        </div>
        <div class="card-body text-center">
          <div class="mb-3">
            <?php 
            $photoUrl = get_profile_photo_url($userData['profile_photo'] ?? null);
            ?>
            <img src="<?= $photoUrl ?>" 
                 alt="Profile Photo" 
                 class="img-fluid rounded-circle mb-3" 
                 style="width: 200px; height: 200px; object-fit: cover; border: 4px solid var(--pine-dark);"
                 id="profilePhotoPreview">
          </div>
          
          <form method="POST" enctype="multipart/form-data" id="photoUploadForm">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="upload_photo">
            
            <div class="mb-3">
              <label for="profilePhotoInput" class="btn btn-success btn-sm w-100">
                <i class="fas fa-upload me-2"></i>Choose New Photo
              </label>
              <input type="file" 
                     class="d-none" 
                     id="profilePhotoInput" 
                     name="profile_photo" 
                     accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                     onchange="previewPhoto(this); document.getElementById('photoUploadForm').submit();">
            </div>
          </form>
          
          <?php if (!empty($userData['profile_photo'])): ?>
          <form method="POST" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="delete_photo">
            <button type="submit" class="btn btn-outline-danger btn-sm w-100" 
                    onclick="return confirm('Are you sure you want to delete your profile photo?')">
              <i class="fas fa-trash me-2"></i>Delete Photo
            </button>
          </form>
          <?php endif; ?>
          
          <div class="alert alert-info mt-3 text-start">
            <small>
              <i class="fas fa-info-circle me-1"></i>
              <strong>Photo Guidelines:</strong><br>
              • Max size: 5MB<br>
              • Formats: JPG, PNG, GIF, WebP<br>
              • Recommended: Square image, 400x400px
            </small>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Profile Information -->
    <div class="col-lg-4">
      <div class="card">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><i class="fas fa-user me-2"></i>Profile Information</h5>
        </div>
        <div class="card-body">
          <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="update_profile">
            
            <div class="mb-3">
              <label class="form-label">Full Name *</label>
              <input type="text" class="form-control" name="username" 
                     value="<?= sanitize($userData['username']) ?>" required>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Email Address *</label>
              <input type="email" class="form-control" name="email" 
                     value="<?= sanitize($userData['email']) ?>" required>
              <small class="text-muted">Used for login and notifications</small>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Department</label>
              <input type="text" class="form-control" name="department" 
                     value="<?= sanitize($userData['department'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
              <label class="form-label">Role</label>
              <input type="text" class="form-control" 
                     value="<?= sanitize($userData['role']) ?>" disabled readonly>
              <small class="text-muted">Contact an administrator to change your role</small>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Account Status</label>
              <input type="text" class="form-control" 
                     value="<?= ucfirst($userData['status'] ?? 'active') ?>" disabled readonly>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Last Login</label>
              <input type="text" class="form-control" 
                     value="<?= $userData['last_login'] ? date('M j, Y g:i A', strtotime($userData['last_login'])) : 'Never' ?>" 
                     disabled readonly>
            </div>
            
            <div class="d-grid">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Update Profile
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Change Password -->
    <div class="col-lg-4">
      <div class="card">
        <div class="card-header bg-warning text-dark">
          <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Change Password</h5>
        </div>
        <div class="card-body">
          <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="change_password">
            
            <div class="mb-3">
              <label class="form-label">Current Password *</label>
              <input type="password" class="form-control" name="current_password" required>
            </div>
            
            <div class="mb-3">
              <label class="form-label">New Password *</label>
              <input type="password" class="form-control" name="new_password" 
                     minlength="6" required>
              <small class="text-muted">Minimum 6 characters</small>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Confirm New Password *</label>
              <input type="password" class="form-control" name="confirm_password" 
                     minlength="6" required>
            </div>
            
            <div class="alert alert-info">
              <i class="fas fa-info-circle me-2"></i>
              <strong>Password Tips:</strong>
              <ul class="mb-0 mt-2">
                <li>Use at least 6 characters</li>
                <li>Include uppercase and lowercase letters</li>
                <li>Add numbers and special characters</li>
                <li>Don't use common words or personal info</li>
              </ul>
            </div>
            
            <div class="d-grid">
              <button type="submit" class="btn btn-warning">
                <i class="fas fa-key me-2"></i>Change Password
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Account Info Card -->
      <div class="card mt-4">
        <div class="card-header bg-light">
          <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Account Information</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-6">
              <strong>User ID:</strong>
              <p class="mb-0">#<?= $userData['user_id'] ?></p>
            </div>
            <div class="col-6">
              <strong>Member Since:</strong>
              <p class="mb-0"><?= date('M Y', strtotime($userData['created_at'])) ?></p>
            </div>
            <div class="col-12">
              <strong>Permissions:</strong>
              <p class="mb-0 text-muted">Based on your role: <span class="badge bg-primary"><?= sanitize($userData['role']) ?></span></p>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Additional Information -->
  <div class="row mt-4">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <h5><i class="fas fa-shield-alt me-2 text-success"></i>Security & Privacy</h5>
          <hr>
          <div class="row">
            <div class="col-md-4">
              <h6>Two-Factor Authentication</h6>
              <p class="text-muted">Currently not enabled</p>
              <button class="btn btn-sm btn-outline-secondary" disabled>
                <i class="fas fa-mobile-alt me-1"></i>Enable 2FA (Coming Soon)
              </button>
            </div>
            <div class="col-md-4">
              <h6>Login History</h6>
              <p class="text-muted">Last login: <?= $userData['last_login'] ? date('M j, Y g:i A', strtotime($userData['last_login'])) : 'Never' ?></p>
              <button class="btn btn-sm btn-outline-secondary" disabled>
                <i class="fas fa-history me-1"></i>View History (Coming Soon)
              </button>
            </div>
            <div class="col-md-4">
              <h6>Active Sessions</h6>
              <p class="text-muted">Manage your active sessions</p>
              <button class="btn btn-sm btn-outline-secondary" disabled>
                <i class="fas fa-desktop me-1"></i>Manage Sessions (Coming Soon)
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Activity Logs -->
  <div class="row mt-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header" style="background: linear-gradient(135deg, var(--pine-dark), var(--forest-green)); color: var(--white);">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
              <i class="fas fa-history me-2"></i>Recent Activity
            </h5>
            <span class="badge" style="background: var(--moss-light); color: var(--charcoal);">
              <?= $activityCount ?> Total Activities
            </span>
          </div>
        </div>
        <div class="card-body">
          <?php if (empty($activityLogs)): ?>
            <div class="text-center text-muted py-4">
              <i class="fas fa-inbox fa-3x mb-3"></i>
              <p>No activity recorded yet</p>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th style="width: 40px;"></th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP Address</th>
                    <th>Date & Time</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($activityLogs as $log): ?>
                    <tr>
                      <td>
                        <i class="fas <?= get_action_icon($log['action']) ?> text-<?= get_action_color($log['action']) ?>"></i>
                      </td>
                      <td>
                        <span class="badge bg-<?= get_action_color($log['action']) ?>">
                          <?= sanitize(format_action_name($log['action'])) ?>
                        </span>
                      </td>
                      <td><?= sanitize($log['description'] ?? '-') ?></td>
                      <td>
                        <small class="text-muted">
                          <i class="fas fa-network-wired me-1"></i>
                          <?= sanitize($log['ip_address'] ?? 'Unknown') ?>
                        </small>
                      </td>
                      <td>
                        <small class="text-muted">
                          <i class="fas fa-clock me-1"></i>
                          <?= date('M j, Y g:i A', strtotime($log['created_at'])) ?>
                        </small>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            
            <?php if ($activityCount > 20): ?>
              <div class="text-center mt-3">
                <p class="text-muted">
                  Showing 20 of <?= $activityCount ?> activities
                </p>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
function previewPhoto(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('profilePhotoPreview').src = e.target.result;
    }
    reader.readAsDataURL(input.files[0]);
  }
}
</script>

<?php include __DIR__ . '/../includes/admin_layout_footer.php'; ?>
