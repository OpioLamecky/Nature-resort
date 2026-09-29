<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/permissions.php';

// Check authentication and permissions
if (!isset($_SESSION['user'])) {
    redirect(base_url('public/login.php'));
}
require_permission('users.view');

$title = 'Staff Management | Admin';
$pageTitle = 'Staff Management';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? '');
    
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'create' && has_permission('users.create')) {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $role = $_POST['role'];
            $department = trim($_POST['department'] ?? '');
            
            // Validate
            if (empty($username) || empty($email) || empty($password)) {
                throw new Exception('All fields are required');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email address');
            }
            
            // Check if role is assignable
            if (!in_array($role, get_assignable_roles())) {
                throw new Exception('You cannot assign this role');
            }
            
            // Check if email exists
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception('Email already exists');
            }
            
            // Create user
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password_hash, role, department, status)
                VALUES (?, ?, ?, ?, ?, 'active')
            ");
            $stmt->execute([$username, $email, $passwordHash, $role, $department]);
            
            flash('Staff member added successfully!', 'success');
            log_admin_action('staff_created', ['username' => $username, 'role' => $role], $pdo->lastInsertId());
            redirect(base_url('admin/staff.php'));
            
        } elseif ($action === 'update' && has_permission('users.edit')) {
            $userId = (int)$_POST['user_id'];
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $role = $_POST['role'];
            $department = trim($_POST['department'] ?? '');
            $status = $_POST['status'];
            
            // Get target user's current role
            $stmt = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            $targetUser = $stmt->fetch();
            
            if (!$targetUser) {
                throw new Exception('User not found');
            }
            
            // Check if can manage this user
            if (!can_manage_user($targetUser['role'])) {
                throw new Exception('You cannot manage this user');
            }
            
            // Check if new role is assignable
            if ($role !== $targetUser['role'] && !in_array($role, get_assignable_roles())) {
                throw new Exception('You cannot assign this role');
            }
            
            // Update user
            $stmt = $pdo->prepare("
                UPDATE users 
                SET username = ?, email = ?, role = ?, department = ?, status = ?
                WHERE user_id = ?
            ");
            $stmt->execute([$username, $email, $role, $department, $status, $userId]);
            
            // Update password if provided
            if (!empty($_POST['new_password'])) {
                $passwordHash = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                $stmt->execute([$passwordHash, $userId]);
            }
            
            flash('Staff member updated successfully!', 'success');
            log_admin_action('staff_updated', ['user_id' => $userId, 'role' => $role]);
            redirect(base_url('admin/staff.php'));
            
        } elseif ($action === 'delete' && has_permission('users.delete')) {
            $userId = (int)$_POST['user_id'];
            
            // Cannot delete yourself
            if ($userId === $_SESSION['user']['user_id']) {
                throw new Exception('You cannot delete your own account');
            }
            
            // Get target user's role
            $stmt = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            $targetUser = $stmt->fetch();
            
            if (!can_manage_user($targetUser['role'])) {
                throw new Exception('You cannot delete this user');
            }
            
            // Delete user
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            flash('Staff member deleted.', 'warning');
            log_admin_action('staff_deleted', ['user_id' => $userId]);
            redirect(base_url('admin/staff.php'));
        }
        
    } catch (Exception $e) {
        flash('Error: ' . $e->getMessage(), 'danger');
    }
}

// Filters
$roleFilter = $_GET['role'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where = [];
$params = [];

if ($roleFilter) {
    $where[] = "role = ?";
    $params[] = $roleFilter;
}

if ($statusFilter) {
    $where[] = "status = ?";
    $params[] = $statusFilter;
}

if ($search) {
    $where[] = "(username LIKE ? OR email LIKE ? OR department LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get all staff with filters
$sql = "SELECT * FROM users $whereClause ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$staff = $stmt->fetchAll();

// Get role counts
$roleCounts = [];
foreach (get_all_roles() as $role) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = ?");
    $stmt->execute([$role]);
    $roleCounts[$role] = $stmt->fetchColumn();
}

include __DIR__ . '/../includes/admin_layout_header.php';
?>

  
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1">Staff Management</h1>
      <p class="text-muted mb-0">Manage staff accounts and permissions</p>
    </div>
    <div>
      <?php if (has_permission('users.create')): ?>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
          <i class="fas fa-user-plus me-2"></i>Add Staff
        </button>
      <?php endif; ?>
    </div>
  </div>

  <!-- Role Distribution -->
  <div class="row g-3 mb-4">
    <?php foreach ($roleCounts as $role => $count): ?>
      <div class="col-md-2">
        <div class="card">
          <div class="card-body text-center">
            <h4 class="mb-1"><?= $count ?></h4>
            <p class="text-muted mb-0 small"><?= sanitize($role) ?></p>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Filters -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Search</label>
          <input type="text" class="form-control" name="search" value="<?= sanitize($search) ?>" 
                 placeholder="Username, email, or department">
        </div>
        <div class="col-md-2">
          <label class="form-label">Role</label>
          <select class="form-select" name="role">
            <option value="">All Roles</option>
            <?php foreach (get_all_roles() as $role): ?>
              <option value="<?= sanitize($role) ?>" <?= $roleFilter === $role ? 'selected' : '' ?>>
                <?= sanitize($role) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Status</label>
          <select class="form-select" name="status">
            <option value="">All Status</option>
            <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
          </select>
        </div>
        <div class="col-md-5 d-flex align-items-end gap-2">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-filter me-2"></i>Filter
          </button>
          <a href="?" class="btn btn-outline-secondary">Reset</a>
          <div class="ms-auto">
            <small class="text-muted">Showing <?= count($staff) ?> staff members</small>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Staff Table -->
  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Department</th>
              <th>Status</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($staff as $member): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" 
                         style="width: 40px; height: 40px;">
                      <?= strtoupper(substr($member['username'], 0, 1)) ?>
                    </div>
                    <div>
                      <strong><?= sanitize($member['username']) ?></strong>
                      <?php if ($member['user_id'] === $_SESSION['user']['user_id']): ?>
                        <span class="badge bg-info ms-2">You</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>
                <td><?= sanitize($member['email']) ?></td>
                <td>
                  <?php
                  $roleColors = [
                      'Super Admin' => 'danger',
                      'Manager' => 'primary',
                      'Receptionist' => 'info',
                      'Content Manager' => 'success',
                      'Staff' => 'secondary'
                  ];
                  $roleColor = $roleColors[$member['role']] ?? 'secondary';
                  ?>
                  <span class="badge bg-<?= $roleColor ?>">
                    <?= sanitize($member['role']) ?>
                  </span>
                </td>
                <td><?= sanitize($member['department'] ?? '-') ?></td>
                <td>
                  <?php if ($member['status'] === 'active'): ?>
                    <span class="badge bg-success">Active</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Inactive</span>
                  <?php endif; ?>
                </td>
                <td><small><?= date('M j, Y', strtotime($member['created_at'])) ?></small></td>
                <td>
                  <?php if (can_manage_user($member['role'])): ?>
                    <div class="btn-group btn-group-sm">
                      <?php if (has_permission('users.edit')): ?>
                        <button type="button" class="btn btn-outline-primary" 
                                onclick="editStaff(<?= htmlspecialchars(json_encode($member)) ?>)">
                          <i class="fas fa-edit"></i>
                        </button>
                      <?php endif; ?>
                      
                      <?php if (has_permission('users.delete') && $member['user_id'] !== $_SESSION['user']['user_id']): ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this staff member?');">
                          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="user_id" value="<?= $member['user_id'] ?>">
                          <button type="submit" class="btn btn-outline-danger">
                            <i class="fas fa-trash"></i>
                          </button>
                        </form>
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <span class="text-muted small">No access</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Permissions Guide -->
  <div class="card mt-4">
    <div class="card-header">
      <h3 class="h5 mb-0">Role Permissions Guide</h3>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Role</th>
              <th>Permissions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="badge bg-danger">Super Admin</span></td>
              <td>Full system access - All permissions</td>
            </tr>
            <tr>
              <td><span class="badge bg-primary">Manager</span></td>
              <td>Bookings, Accommodations, Staff management, Analytics, Payments</td>
            </tr>
            <tr>
              <td><span class="badge bg-info">Receptionist</span></td>
              <td>View & manage bookings, Check-in/out, Process payments, View rooms</td>
            </tr>
            <tr>
              <td><span class="badge bg-success">Content Manager</span></td>
              <td>Manage website content, posts, testimonials, pages</td>
            </tr>
            <tr>
              <td><span class="badge bg-secondary">Staff</span></td>
              <td>Limited read-only access to basic information</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Add New Staff Member</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="create">
          
          <div class="mb-3">
            <label class="form-label">Full Name *</label>
            <input type="text" class="form-control" name="username" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Email *</label>
            <input type="email" class="form-control" name="email" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Password *</label>
            <input type="password" class="form-control" name="password" required minlength="6">
            <div class="form-text">Minimum 6 characters</div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Role *</label>
            <select class="form-select" name="role" required>
              <?php foreach (get_assignable_roles() as $role): ?>
                <option value="<?= sanitize($role) ?>"><?= sanitize($role) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Department</label>
            <input type="text" class="form-control" name="department" 
                   placeholder="e.g., Front Desk, Housekeeping">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Staff Member</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Staff Modal -->
<div class="modal fade" id="editStaffModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Edit Staff Member</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="user_id" id="edit_user_id">
          
          <div class="mb-3">
            <label class="form-label">Full Name *</label>
            <input type="text" class="form-control" name="username" id="edit_username" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Email *</label>
            <input type="email" class="form-control" name="email" id="edit_email" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label">New Password (leave blank to keep current)</label>
            <input type="password" class="form-control" name="new_password" minlength="6">
          </div>
          
          <div class="mb-3">
            <label class="form-label">Role *</label>
            <select class="form-select" name="role" id="edit_role" required>
              <?php foreach (get_assignable_roles() as $role): ?>
                <option value="<?= sanitize($role) ?>"><?= sanitize($role) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Department</label>
            <input type="text" class="form-control" name="department" id="edit_department">
          </div>
          
          <div class="mb-3">
            <label class="form-label">Status *</label>
            <select class="form-select" name="status" id="edit_status" required>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Staff Member</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function editStaff(staff) {
    document.getElementById('edit_user_id').value = staff.user_id;
    document.getElementById('edit_username').value = staff.username;
    document.getElementById('edit_email').value = staff.email;
    document.getElementById('edit_role').value = staff.role;
    document.getElementById('edit_department').value = staff.department || '';
    document.getElementById('edit_status').value = staff.status;
    
    new bootstrap.Modal(document.getElementById('editStaffModal')).show();
}
</script>

<?php include __DIR__ . '/../includes/admin_layout_footer.php'; ?>
