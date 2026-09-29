<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/permissions.php';

// Check permissions
require_permission('bookings.create');

$title = 'Create Booking | Admin';
$pageTitle = 'Create New Booking';

include __DIR__ . '/../includes/admin_layout_header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Create New Booking</h1>
                <a href="<?= base_url('admin/bookings.php') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Bookings
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Booking Details</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= base_url('admin/bookings.php') ?>">
                        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="guest_name" class="form-label">Guest Name *</label>
                                <input type="text" class="form-control" id="guest_name" name="guest_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="guest_email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="guest_email" name="guest_email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="guest_phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="guest_phone" name="guest_phone" required>
                            </div>
                            <div class="col-md-6">
                                <label for="accommodation_id" class="form-label">Room/Accommodation *</label>
                                <select class="form-select" id="accommodation_id" name="accommodation_id" required>
                                    <option value="">Select Room</option>
                                    <?php
                                    try {
                                        $rooms = $pdo->query("SELECT accommodation_id, name, room_number, room_type FROM accommodations WHERE availability_status = 'available' ORDER BY room_number")->fetchAll();
                                        foreach ($rooms as $room) {
                                            echo "<option value='{$room['accommodation_id']}'>{$room['room_number']} - {$room['name']} ({$room['room_type']})</option>";
                                        }
                                    } catch (Exception $e) {
                                        echo "<option value=''>Error loading rooms</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="check_in" class="form-label">Check-in Date *</label>
                                <input type="date" class="form-control" id="check_in" name="check_in" required>
                            </div>
                            <div class="col-md-6">
                                <label for="check_out" class="form-label">Check-out Date *</label>
                                <input type="date" class="form-control" id="check_out" name="check_out" required>
                            </div>
                            <div class="col-md-6">
                                <label for="adults" class="form-label">Number of Adults *</label>
                                <input type="number" class="form-control" id="adults" name="adults" min="1" value="1" required>
                            </div>
                            <div class="col-md-6">
                                <label for="children" class="form-label">Number of Children</label>
                                <input type="number" class="form-control" id="children" name="children" min="0" value="0">
                            </div>
                            <div class="col-12">
                                <label for="notes" class="form-label">Special Requests or Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Booking
                            </button>
                            <a href="<?= base_url('admin/bookings.php') ?>" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('check_in').setAttribute('min', today);
    document.getElementById('check_out').setAttribute('min', today);
    
    // Update check-out minimum date when check-in changes
    document.getElementById('check_in').addEventListener('change', function() {
        const checkIn = new Date(this.value);
        checkIn.setDate(checkIn.getDate() + 1);
        const minCheckOut = checkIn.toISOString().split('T')[0];
        document.getElementById('check_out').setAttribute('min', minCheckOut);
    });
});
</script>

<?php include __DIR__ . '/../includes/admin_layout_footer.php'; ?>
