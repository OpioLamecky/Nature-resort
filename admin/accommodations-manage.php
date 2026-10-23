<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/permissions.php';

if (!isset($_SESSION['user'])) {
    redirect(base_url('public/login.php'));
}
require_permission('accommodations.view');

$title = 'Manage Accommodations | Admin';
$pageTitle = 'Accommodations Management';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? '');
    
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'create' && has_permission('accommodations.create')) {
            $stmt = $pdo->prepare("
                INSERT INTO accommodations (room_number, room_type, name, description, capacity_adults, capacity_children, 
                                           price_per_night, amenities, display_order, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
            ");
            $stmt->execute([
                $_POST['room_number'],
                $_POST['room_type'],
                $_POST['name'],
                $_POST['description'],
                (int)$_POST['capacity_adults'],
                (int)$_POST['capacity_children'],
                (float)$_POST['price_per_night'],
                json_encode($_POST['amenities'] ?? []),
                (int)($_POST['display_order'] ?? 0)
            ]);
            
            flash('Accommodation added successfully!', 'success');
            log_admin_action('accommodation_created', null, $pdo->lastInsertId());
            redirect(base_url('admin/accommodations-manage.php'));
            
        } elseif ($action === 'update' && has_permission('accommodations.edit')) {
            $stmt = $pdo->prepare("
                UPDATE accommodations 
                SET room_number = ?, room_type = ?, name = ?, description = ?, 
                    capacity_adults = ?, capacity_children = ?, price_per_night = ?, 
                    amenities = ?, display_order = ?, status = ?
                WHERE accommodation_id = ?
            ");
            $stmt->execute([
                $_POST['room_number'],
                $_POST['room_type'],
                $_POST['name'],
                $_POST['description'],
                (int)$_POST['capacity_adults'],
                (int)$_POST['capacity_children'],
                (float)$_POST['price_per_night'],
                json_encode($_POST['amenities'] ?? []),
                (int)$_POST['display_order'],
                $_POST['status'],
                (int)$_POST['accommodation_id']
            ]);
            
            flash('Accommodation updated successfully!', 'success');
            log_admin_action('accommodation_updated', null, $_POST['accommodation_id']);
            redirect(base_url('admin/accommodations-manage.php'));
            
        } elseif ($action === 'update_status' && has_permission('accommodations.status')) {
            $stmt = $pdo->prepare("UPDATE accommodations SET availability_status = ? WHERE accommodation_id = ?");
            $stmt->execute([$_POST['availability_status'], (int)$_POST['accommodation_id']]);
            
            flash('Room status updated!', 'success');
            redirect(base_url('admin/accommodations-manage.php'));
            
        } elseif ($action === 'delete' && has_permission('accommodations.delete')) {
            $stmt = $pdo->prepare("DELETE FROM accommodations WHERE accommodation_id = ?");
            $stmt->execute([(int)$_POST['accommodation_id']]);
            
            flash('Accommodation deleted.', 'warning');
            log_admin_action('accommodation_deleted', null, $_POST['accommodation_id']);
            redirect(base_url('admin/accommodations-manage.php'));
        }
        
    } catch (Exception $e) {
        flash('Error: ' . $e->getMessage(), 'danger');
    }
}

// Filters
$roomTypeFilter = $_GET['room_type'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$availabilityFilter = $_GET['availability'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where = [];
$params = [];

if ($roomTypeFilter) {
    $where[] = "room_type = ?";
    $params[] = $roomTypeFilter;
}

if ($statusFilter) {
    $where[] = "status = ?";
    $params[] = $statusFilter;
}

if ($availabilityFilter) {
    $where[] = "availability_status = ?";
    $params[] = $availabilityFilter;
}

if ($search) {
    $where[] = "(name LIKE ? OR room_number LIKE ? OR description LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get all accommodations with filters
$sql = "SELECT * FROM accommodations $whereClause ORDER BY room_type, display_order";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$accommodations = $stmt->fetchAll();

// Get distinct room types for filter
$roomTypesStmt = $pdo->query("SELECT DISTINCT room_type FROM accommodations ORDER BY room_type");
$roomTypes = $roomTypesStmt->fetchAll(PDO::FETCH_COLUMN);

// Get statistics
$stats = [
    'total' => count($accommodations),
    'available' => $pdo->query("SELECT COUNT(*) FROM accommodations WHERE availability_status = 'available'")->fetchColumn(),
    'occupied' => $pdo->query("SELECT COUNT(*) FROM accommodations WHERE availability_status = 'occupied'")->fetchColumn(),
    'maintenance' => $pdo->query("SELECT COUNT(*) FROM accommodations WHERE availability_status = 'maintenance'")->fetchColumn(),
];

include __DIR__ . '/../includes/admin_layout_header.php';
?>

  
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1">Manage Accommodations</h1>
      <p class="text-muted mb-0">Manage rooms and their availability</p>
    </div>
    <div>
      <?php if (has_permission('accommodations.create')): ?>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccommodationModal">
          <i class="fas fa-plus me-2"></i>Add Room
        </button>
      <?php endif; ?>
    </div>
  </div>

  <!-- Stats Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card">
        <div class="card-body">
          <h6 class="text-muted">Total Rooms</h6>
          <h3><?= $stats['total'] ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-success bg-opacity-10">
        <div class="card-body">
          <h6 class="text-muted">Available</h6>
          <h3 class="text-success"><?= $stats['available'] ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-danger bg-opacity-10">
        <div class="card-body">
          <h6 class="text-muted">Occupied</h6>
          <h3 class="text-danger"><?= $stats['occupied'] ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-warning bg-opacity-10">
        <div class="card-body">
          <h6 class="text-muted">Maintenance</h6>
          <h3 class="text-warning"><?= $stats['maintenance'] ?></h3>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Search</label>
          <input type="text" class="form-control" name="search" value="<?= sanitize($search) ?>" 
                 placeholder="Room name, number, or description">
        </div>
        <div class="col-md-2">
          <label class="form-label">Room Type</label>
          <select class="form-select" name="room_type">
            <option value="">All Types</option>
            <?php foreach ($roomTypes as $type): ?>
              <option value="<?= sanitize($type) ?>" <?= $roomTypeFilter === $type ? 'selected' : '' ?>>
                <?= sanitize($type) ?>
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
        <div class="col-md-2">
          <label class="form-label">Availability</label>
          <select class="form-select" name="availability">
            <option value="">All Availability</option>
            <option value="available" <?= $availabilityFilter === 'available' ? 'selected' : '' ?>>Available</option>
            <option value="occupied" <?= $availabilityFilter === 'occupied' ? 'selected' : '' ?>>Occupied</option>
            <option value="maintenance" <?= $availabilityFilter === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
          </select>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-filter me-2"></i>Filter
          </button>
          <a href="?" class="btn btn-outline-secondary">Reset</a>
          <div class="ms-auto">
            <small class="text-muted">Showing <?= count($accommodations) ?> rooms</small>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Accommodations Table -->
  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0 accommodations-table">
          <thead class="table-light">
            <tr>
              <th style="width: 80px;">Room #</th>
              <th style="min-width: 130px;">Type</th>
              <th>Name</th>
              <th style="min-width: 140px;">Capacity</th>
              <th style="min-width: 130px;">Price/Night</th>
              <th style="min-width: 140px;">Availability</th>
              <th style="width: 90px;">Status</th>
              <th style="width: 100px;" class="text-end pe-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($accommodations as $room): ?>
              <tr>
                <td><span class="badge bg-light text-dark border px-2 py-1 font-monospace fw-semibold"><?= sanitize($room['room_number']) ?></span></td>
                <td><span class="text-nowrap fw-semibold text-secondary" style="font-size: 0.88rem;"><?= sanitize($room['room_type']) ?></span></td>
                <td><div class="fw-semibold text-dark" style="font-size: 0.9rem;"><?= sanitize($room['name']) ?></div></td>
                <td><span class="text-nowrap small text-muted"><i class="fas fa-users me-1 text-secondary"></i><?= $room['capacity_adults'] ?> ad, <?= $room['capacity_children'] ?> ch</span></td>
                <td><span class="text-nowrap fw-bold text-success" style="font-size: 0.88rem;">UGX <?= number_format($room['price_per_night']) ?></span></td>
                <td>
                  <?php if (has_permission('accommodations.status')): ?>
                    <form method="POST" style="display:inline;">
                      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                      <input type="hidden" name="action" value="update_status">
                      <input type="hidden" name="accommodation_id" value="<?= $room['accommodation_id'] ?>">
                      <select name="availability_status" class="form-select form-select-sm py-1 px-2 border-<?= $room['availability_status'] === 'available' ? 'success' : ($room['availability_status'] === 'occupied' ? 'danger' : 'warning') ?>" 
                              onchange="this.form.submit()" style="width:auto; font-size: 0.82rem; font-weight: 500;">
                        <option value="available" <?= $room['availability_status'] === 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="occupied" <?= $room['availability_status'] === 'occupied' ? 'selected' : '' ?>>Occupied</option>
                        <option value="maintenance" <?= $room['availability_status'] === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                        <option value="reserved" <?= $room['availability_status'] === 'reserved' ? 'selected' : '' ?>>Reserved</option>
                      </select>
                    </form>
                  <?php else: ?>
                    <span class="badge bg-<?= $room['availability_status'] === 'available' ? 'success' : 'secondary' ?>">
                      <?= ucfirst($room['availability_status']) ?>
                    </span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($room['status'] === 'active'): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Active</span>
                  <?php else: ?>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">Inactive</span>
                  <?php endif; ?>
                </td>
                <td class="text-end pe-3">
                  <div class="btn-group btn-group-sm">
                    <?php if (has_permission('accommodations.edit')): ?>
                      <button type="button" class="btn btn-outline-primary py-1 px-2" title="Edit"
                              onclick="editRoom(<?= htmlspecialchars(json_encode($room)) ?>)">
                        <i class="fas fa-edit"></i>
                      </button>
                    <?php endif; ?>
                    
                    <?php if (has_permission('accommodations.delete')): ?>
                      <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this room?');">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="accommodation_id" value="<?= $room['accommodation_id'] ?>">
                        <button type="submit" class="btn btn-outline-danger py-1 px-2" title="Delete">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<!-- Add Accommodation Modal -->
<div class="modal fade" id="addAccommodationModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Add New Accommodation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="create">
          
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Room Number *</label>
              <input type="text" class="form-control" name="room_number" required>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Room Type *</label>
              <select class="form-select" name="room_type" required>
                <option value="Standard Room">Standard Room</option>
                <option value="Deluxe Room">Deluxe Room</option>
                <option value="Suite">Suite</option>
                <option value="Chalet">Chalet</option>
                <option value="Glamping Tent">Glamping Tent</option>
              </select>
            </div>
            
            <div class="col-12">
              <label class="form-label">Name *</label>
              <input type="text" class="form-control" name="name" required>
            </div>
            
            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea class="form-control" name="description" rows="3"></textarea>
            </div>
            
            <div class="col-md-4">
              <label class="form-label">Adults Capacity *</label>
              <input type="number" class="form-control" name="capacity_adults" value="2" min="1" required>
            </div>
            
            <div class="col-md-4">
              <label class="form-label">Children Capacity *</label>
              <input type="number" class="form-control" name="capacity_children" value="1" min="0" required>
            </div>
            
            <div class="col-md-4">
              <label class="form-label">Price per Night (UGX) *</label>
              <input type="number" class="form-control" name="price_per_night" required>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Display Order</label>
              <input type="number" class="form-control" name="display_order" value="0">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Accommodation</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Accommodation Modal -->
<div class="modal fade" id="editAccommodationModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Edit Accommodation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="accommodation_id" id="edit_accommodation_id">
          
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Room Number *</label>
              <input type="text" class="form-control" name="room_number" id="edit_room_number" required>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Room Type *</label>
              <select class="form-select" name="room_type" id="edit_room_type" required>
                <option value="Standard Room">Standard Room</option>
                <option value="Deluxe Room">Deluxe Room</option>
                <option value="Suite">Suite</option>
                <option value="Chalet">Chalet</option>
                <option value="Glamping Tent">Glamping Tent</option>
              </select>
            </div>
            
            <div class="col-12">
              <label class="form-label">Name *</label>
              <input type="text" class="form-control" name="name" id="edit_name" required>
            </div>
            
            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
            </div>
            
            <div class="col-md-4">
              <label class="form-label">Adults Capacity *</label>
              <input type="number" class="form-control" name="capacity_adults" id="edit_capacity_adults" required>
            </div>
            
            <div class="col-md-4">
              <label class="form-label">Children Capacity *</label>
              <input type="number" class="form-control" name="capacity_children" id="edit_capacity_children" required>
            </div>
            
            <div class="col-md-4">
              <label class="form-label">Price per Night (UGX) *</label>
              <input type="number" class="form-control" name="price_per_night" id="edit_price_per_night" required>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Display Order</label>
              <input type="number" class="form-control" name="display_order" id="edit_display_order">
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select class="form-select" name="status" id="edit_status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Accommodation</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function editRoom(room) {
    document.getElementById('edit_accommodation_id').value = room.accommodation_id;
    document.getElementById('edit_room_number').value = room.room_number;
    document.getElementById('edit_room_type').value = room.room_type;
    document.getElementById('edit_name').value = room.name;
    document.getElementById('edit_description').value = room.description || '';
    document.getElementById('edit_capacity_adults').value = room.capacity_adults;
    document.getElementById('edit_capacity_children').value = room.capacity_children;
    document.getElementById('edit_price_per_night').value = room.price_per_night;
    document.getElementById('edit_display_order').value = room.display_order;
    document.getElementById('edit_status').value = room.status;
    
    new bootstrap.Modal(document.getElementById('editAccommodationModal')).show();
}
</script>

<?php include __DIR__ . '/../includes/admin_layout_footer.php'; ?>
