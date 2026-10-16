<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/enhanced_validation.php';
require_once __DIR__ . '/../includes/production_security.php';

// Initialize security measures
secure_session_start();
set_security_headers();

// Generate CSRF token before any output
$csrfToken = csrf_generate_tokens()['session'];

$title = 'Book Your Stay | Kalitusi Nature Resort';

// Get room type from URL if specified
$selectedRoom = $_GET['room'] ?? '';
$roomTypeLabels = [
  'standard' => 'Standard Room',
  'deluxe' => 'Deluxe Room',
  'suite' => 'Suite',
  'chalet' => 'Chalet',
  'glamping' => 'Glamping Tent',
];
$roomTypeKeys = array_flip($roomTypeLabels);
$bookingRoomTypes = [];
$roomRatesByType = [];
$bookingDorms = [];
try {
  $roomTypeStmt = $pdo->query(
    "SELECT room_type, MIN(price_per_night) AS price_per_night
     FROM accommodations
     WHERE status = 'active' AND availability_status = 'available'
     GROUP BY room_type ORDER BY room_type"
  );
  foreach ($roomTypeStmt->fetchAll() as $roomTypeRow) {
    $roomTypeKey = $roomTypeKeys[$roomTypeRow['room_type']] ?? null;
    if ($roomTypeKey !== null) {
      $roomRatesByType[$roomTypeKey] = (float)$roomTypeRow['price_per_night'];
      $bookingRoomTypes[] = [
        'key' => $roomTypeKey,
        'label' => $roomTypeRow['room_type'],
        'price' => (float)$roomTypeRow['price_per_night'],
      ];
    }
  }
  $dormStmt = $pdo->query(
    "SELECT accommodation_id, name FROM accommodations
     WHERE LOWER(TRIM(name)) IN ('ginger', 'rocket')
       AND status = 'active' AND availability_status = 'available'"
  );
  foreach ($dormStmt->fetchAll() as $dormRow) {
    $dormKey = strtolower(trim($dormRow['name']));
    $bookingDorms[$dormKey] = [
      'accommodation_id' => (int)$dormRow['accommodation_id'],
      'label' => strtoupper($dormKey),
    ];
    $roomRatesByType[$dormKey] = 60000;
  }
  if (ctype_digit((string)$selectedRoom)) {
    $selectedRoomStmt = $pdo->prepare(
      "SELECT room_type, name FROM accommodations
       WHERE accommodation_id = ? AND status = 'active' AND availability_status = 'available'"
    );
    $selectedRoomStmt->execute([(int)$selectedRoom]);
    $selectedAccommodation = $selectedRoomStmt->fetch();
    if ($selectedAccommodation) {
      $selectedName = strtolower(trim($selectedAccommodation['name']));
      $selectedRoom = isset($bookingDorms[$selectedName])
        ? $selectedName
        : ($roomTypeKeys[$selectedAccommodation['room_type']] ?? '');
    } else {
      $selectedRoom = '';
    }
  }
} catch (PDOException $e) {
  error_log('Booking accommodation lookup failed: ' . $e->getMessage());
  $bookingRoomTypes = [];
  $roomRatesByType = [];
  $bookingDorms = [];
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limiting check
    $rateLimit = rate_limit_enforce('booking_submission', 3, 15); // 3 attempts per 15 minutes
    if (!$rateLimit['allowed']) {
        $errors[] = 'Too many booking attempts. Please try again later.';
        log_security_event('booking_rate_limit_exceeded', ['attempts' => $rateLimit['attempts']]);
    }
    
    // Enhanced CSRF validation
    if (!csrf_verify_request()) {
        $errors[] = 'Security token mismatch. Please refresh the page and try again.';
        log_security_event('csrf_token_mismatch', ['form' => 'booking']);
    }
    
    // Use enhanced validation
    $validation = validate_booking_form($_POST);
    if (!$validation['valid']) {
      $errors = array_merge($errors, $validation['errors']);
    } elseif (empty($errors)) {
        $cleanData = $validation['clean_data'];
        
        try {
            // Generate booking reference
            $bookingRef = 'KNR-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

            // Calculate nights and amount
            $checkInDate = new DateTime($cleanData['check_in']);
            $checkOutDate = new DateTime($cleanData['check_out']);
            $nights = $checkInDate->diff($checkOutDate)->days;

            $roomTypeKey = $cleanData['room_type'];
            $roomTypeLabel = $roomTypeLabels[$roomTypeKey] ?? null;
            $accommodationId = null;
            $specialRequests = trim($cleanData['special_requests'] ?? '');

            if (in_array($roomTypeKey, ['ginger', 'rocket'], true)) {
              $dormStmt = $pdo->prepare(
                "SELECT accommodation_id, name FROM accommodations
                 WHERE LOWER(TRIM(name)) = ? AND status = 'active'
                   AND availability_status = 'available' LIMIT 1"
              );
              $dormStmt->execute([$roomTypeKey]);
              $dorm = $dormStmt->fetch();
              if (!$dorm) {
                throw new RuntimeException('That dormitory is no longer available. Please choose another.');
              }

              $dormBookingType = $_POST['dorm_booking_type'] ?? '';
              if (!in_array($dormBookingType, ['per_bed', 'whole_dorm'], true)) {
                throw new RuntimeException('Choose individual beds or the whole dormitory.');
              }
              if (($_POST['dorm_occupants_confirmed'] ?? '') !== 'yes') {
                $eligibleGroup = $roomTypeKey === 'ginger' ? 'women and girls' : 'men and boys';
                throw new RuntimeException('Confirm that every dorm guest is ' . $eligibleGroup . '.');
              }

              $dormBeds = $dormBookingType === 'whole_dorm' ? 6 : filter_var(
                $_POST['dorm_beds'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1, 'max_range' => 6]]
              );
              if ($dormBeds === false) {
                throw new RuntimeException('Choose between 1 and 6 dorm beds.');
              }

              $adultCount = filter_var($cleanData['adults'], FILTER_VALIDATE_INT);
              $childCount = filter_var($cleanData['children'] ?? 0, FILTER_VALIDATE_INT);
              if ($adultCount === false || $childCount === false || $childCount < 0) {
                throw new RuntimeException('Enter a valid whole-number guest count.');
              }
              $guestCount = $adultCount + $childCount;
              if ($guestCount < 1 || $guestCount > 6 || $guestCount > $dormBeds) {
                throw new RuntimeException('The guest count must fit within the number of dorm beds selected (maximum 6).');
              }

              $overlapStmt = $pdo->prepare(
                "SELECT adults, children, special_requests FROM bookings
                 WHERE accommodation_id = ? AND status IN ('pending', 'confirmed', 'checked_in')
                   AND check_in < ? AND check_out > ?"
              );
              $overlapStmt->execute([
                (int)$dorm['accommodation_id'],
                $cleanData['check_out'],
                $cleanData['check_in'],
              ]);
              $occupiedBeds = 0;
              foreach ($overlapStmt->fetchAll() as $overlappingBooking) {
                $isWholeDorm = str_contains((string)$overlappingBooking['special_requests'], '[WHOLE_DORM]');
                $occupiedBeds += $isWholeDorm
                  ? 6
                  : max(0, (int)$overlappingBooking['adults'] + (int)$overlappingBooking['children']);
              }
              if ($occupiedBeds + $dormBeds > 6) {
                throw new RuntimeException('Not enough beds are available for those dates. Please select fewer beds or different dates.');
              }

              $accommodationId = (int)$dorm['accommodation_id'];
              $roomRate = $dormBookingType === 'whole_dorm' ? 360000 : 60000 * $dormBeds;
              $bookingLabel = $dormBookingType === 'whole_dorm'
                ? 'whole dormitory (6 beds)'
                : $dormBeds . ' bed' . ($dormBeds === 1 ? '' : 's');
              $genderGroup = $roomTypeKey === 'ginger' ? 'women and girls' : 'men and boys';
              $dormRequest = '[DORM:' . strtoupper($roomTypeKey) . ':' . strtoupper($dormBookingType) . ']' .
                ($dormBookingType === 'whole_dorm' ? '[WHOLE_DORM]' : '') .
                ' ' . strtoupper($dorm['name']) . ': ' . $bookingLabel .
                '; all occupants confirmed as ' . $genderGroup . '.';
              $specialRequests = trim($dormRequest . ($specialRequests !== '' ? "\n" . $specialRequests : ''));
            } else {
              if ($roomTypeLabel === null) {
                throw new RuntimeException('Please choose an available accommodation type.');
              }
              $rateStmt = $pdo->prepare(
                "SELECT MIN(price_per_night) FROM accommodations
                 WHERE room_type = ? AND status = 'active' AND availability_status = 'available'"
              );
              $rateStmt->execute([$roomTypeLabel]);
              $currentRate = $rateStmt->fetchColumn();
              if ($currentRate === false || $currentRate === null) {
                throw new RuntimeException('That accommodation type is no longer available. Please choose another.');
              }
              $roomRate = (float)$currentRate;
            }
            $totalAmount = $roomRate * $nights;
            $depositAmount = $totalAmount * 0.5; // 50% deposit

            // Insert booking with prepared statement
            $stmt = $pdo->prepare("
                INSERT INTO bookings (
                    booking_reference, guest_name, guest_email, guest_phone,
                    guest_country, accommodation_id, check_in, check_out, adults, children, room_rate, total_amount,
                    deposit_amount, special_requests, status, payment_status, source, created_at
                  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'unpaid', 'website', NOW())
            ");

            $stmt->execute([
                $bookingRef, 
                $cleanData['guest_name'], 
                $cleanData['guest_email'], 
                $cleanData['guest_phone'],
                trim($_POST['guest_country'] ?? ''),
                $accommodationId,
                $cleanData['check_in'], 
                $cleanData['check_out'], 
                $cleanData['adults'], 
                $cleanData['children'], 
                $roomRate,
                $totalAmount, 
                $depositAmount, 
                $specialRequests
            ]);

            $success = true;
            $bookingId = $pdo->lastInsertId();
            
            log_security_event('booking_created', [
                'booking_id' => $bookingId,
                'booking_ref' => $bookingRef,
                'email' => $cleanData['guest_email']
            ]);

            // Optional meal reservation tied to this booking
            $mealType = trim($_POST['meal_type'] ?? '');
            $mealDate = trim($_POST['meal_date'] ?? '');
            $mealTime = trim($_POST['meal_time'] ?? '');
            $mealGuests = (int)($_POST['meal_guests'] ?? 0);
            $mealNotes = trim($_POST['meal_notes'] ?? '');

            if ($mealType !== '' || $mealDate !== '' || $mealTime !== '' || $mealGuests > 0 || $mealNotes !== '') {
                try {
                    $mealStmt = $pdo->prepare("
                        INSERT INTO meal_reservations (
                            booking_id, guest_name, guest_email, guest_phone,
                            meal_type, meal_date, meal_time, guests, special_requests,
                            status, source, created_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'website', NOW())
                    ");

                    $mealStmt->execute([
                        $bookingId,
                        $cleanData['guest_name'],
                        $cleanData['guest_email'],
                        $cleanData['guest_phone'],
                        $mealType,
                        $mealDate,
                        $mealTime,
                        $mealGuests,
                        $mealNotes,
                    ]);
                } catch (Exception $e) {
                    error_log('Meal reservation error: ' . $e->getMessage());
                }
            }

            // In production, send confirmation email here

        } catch (Exception $e) {
            $errors[] = 'Unable to process booking. Please try again or contact us directly.';
            error_log('Booking error: ' . $e->getMessage());
            log_security_event('booking_error', ['error' => $e->getMessage()]);
        }
    }
}

include __DIR__ . '/../includes/layout_header.php';
?>

<nav aria-label="breadcrumb" class="mb-3 mt-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= base_url('public/') ?>">Home</a></li>
    <li class="breadcrumb-item active">Book Your Stay</li>
  </ol>
</nav>

<div class="container py-4 booking-layout">
  
  <?php if ($success): ?>
    <!-- Success Message -->
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card border-success">
          <div class="card-body text-center py-5">
            <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
            <h1 class="h3 mb-3">Booking Request Received!</h1>
            <p class="lead mb-4">Thank you for choosing Kalitusi Nature Resort</p>
            
            <div class="alert alert-info">
              <h2 class="h5">Booking Reference: <strong><?= sanitize($bookingRef ?? '') ?></strong></h2>
              <p class="mb-0">Please save this reference number for your records</p>
            </div>
            
            <div class="text-start mb-4">
              <h3 class="h6">What Happens Next?</h3>
              <ol>
                <li>You'll receive a confirmation email within 24 hours</li>
                <li>Our team will verify availability and contact you</li>
                <li>Payment instructions will be provided (50% deposit required)</li>
                <li>Once payment is confirmed, your booking is guaranteed</li>
              </ol>
            </div>
            
            <div class="d-grid gap-2 d-md-block">
              <a href="<?= base_url('public/') ?>" class="btn btn-primary">Back to Home</a>
              <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-primary">Contact Us</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    
  <?php else: ?>
    <!-- Booking Form -->
    <div class="row">
      <div class="col-lg-8">
        <h1 class="display-5 mb-4">Book Your Stay</h1>
        
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <strong>Please correct the following errors:</strong>
            <ul class="mb-0 mt-2">
              <?php foreach ($errors as $error): ?>
                <li><?= sanitize($error) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="needs-validation" novalidate>
          <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
          
          <!-- Guest Information -->
          <div class="card mb-4">
            <div class="card-header bg-primary text-white">
              <h2 class="h5 mb-0"><i class="fas fa-user me-2"></i>Guest Information</h2>
            </div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="guest_name" class="form-label">Full Name *</label>
                  <input type="text" class="form-control" id="guest_name" name="guest_name" 
                         value="<?= sanitize($_POST['guest_name'] ?? '') ?>" required>
                </div>
                
                <div class="col-md-6">
                  <label for="guest_email" class="form-label">Email Address *</label>
                  <input type="email" class="form-control" id="guest_email" name="guest_email"
                         value="<?= sanitize($_POST['guest_email'] ?? '') ?>" required>
                </div>
                
                <div class="col-md-6">
                  <label for="guest_phone" class="form-label">Phone Number</label>
                  <input type="tel" class="form-control" id="guest_phone" name="guest_phone"
                         value="<?= sanitize($_POST['guest_phone'] ?? '') ?>" 
                         placeholder="+256 772 957 573">
                </div>
                
                <div class="col-md-6">
                  <label for="guest_country" class="form-label">Country</label>
                  <input type="text" class="form-control" id="guest_country" name="guest_country"
                         value="<?= sanitize($_POST['guest_country'] ?? '') ?>" 
                         placeholder="Uganda">
                </div>
              </div>
            </div>
          </div>

          <!-- Meal Reservation (optional) -->
          <div class="card mb-4">
            <div class="card-header bg-primary text-white">
              <h2 class="h5 mb-0"><i class="fas fa-utensils me-2"></i>Meal Reservation (Optional)</h2>
            </div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="meal_type" class="form-label">Meal Type</label>
                  <select class="form-select" id="meal_type" name="meal_type">
                    <option value="">No Meal Reservation</option>
                    <option value="breakfast" <?= ($_POST['meal_type'] ?? '') === 'breakfast' ? 'selected' : '' ?>>Breakfast</option>
                    <option value="lunch" <?= ($_POST['meal_type'] ?? '') === 'lunch' ? 'selected' : '' ?>>Lunch</option>
                    <option value="dinner" <?= ($_POST['meal_type'] ?? '') === 'dinner' ? 'selected' : '' ?>>Dinner</option>
                    <option value="special_event" <?= ($_POST['meal_type'] ?? '') === 'special_event' ? 'selected' : '' ?>>Special Event / Celebration</option>
                  </select>
                  <div class="form-text">Select a meal to reserve with your stay</div>
                </div>

                <div class="col-md-3">
                  <label for="meal_date" class="form-label">Meal Date</label>
                  <input type="date" class="form-control" id="meal_date" name="meal_date"
                         value="<?= sanitize($_POST['meal_date'] ?? ($_POST['check_in'] ?? '')) ?>">
                </div>

                <div class="col-md-3">
                  <label for="meal_time" class="form-label">Meal Time</label>
                  <input type="time" class="form-control" id="meal_time" name="meal_time"
                         value="<?= sanitize($_POST['meal_time'] ?? '') ?>">
                </div>

                <div class="col-md-4">
                  <label for="meal_guests" class="form-label">Guests for Meal</label>
                  <input type="number" min="1" class="form-control" id="meal_guests" name="meal_guests"
                         value="<?= sanitize($_POST['meal_guests'] ?? '') ?>" placeholder="e.g. 2">
                </div>

                <div class="col-md-8">
                  <label for="meal_notes" class="form-label">Meal Notes</label>
                  <textarea class="form-control" id="meal_notes" name="meal_notes" rows="2" placeholder="E.g., vegetarian options, anniversary dinner setup..."><?= sanitize($_POST['meal_notes'] ?? '') ?></textarea>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Booking Details -->
          <div class="card mb-4">
            <div class="card-header bg-primary text-white">
              <h2 class="h5 mb-0"><i class="fas fa-calendar me-2"></i>Booking Details</h2>
            </div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="check_in" class="form-label">Check-in Date *</label>
                  <input type="date" class="form-control" id="check_in" name="check_in"
                         value="<?= sanitize($_POST['check_in'] ?? '') ?>" 
                         min="<?= date('Y-m-d') ?>" required>
                  <div class="form-text">Check-in time: 2:00 PM</div>
                </div>
                
                <div class="col-md-6">
                  <label for="check_out" class="form-label">Check-out Date *</label>
                  <input type="date" class="form-control" id="check_out" name="check_out"
                         value="<?= sanitize($_POST['check_out'] ?? '') ?>" 
                         min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                  <div class="form-text">Check-out time: 11:00 AM</div>
                </div>
                
                <div class="col-md-4">
                  <label for="adults" class="form-label">Adults *</label>
                  <select class="form-select" id="adults" name="adults" required>
                    <option value="1" <?= ($_POST['adults'] ?? '1') == '1' ? 'selected' : '' ?>>1 Adult</option>
                    <option value="2" <?= ($_POST['adults'] ?? '') == '2' ? 'selected' : '' ?>>2 Adults</option>
                    <option value="3" <?= ($_POST['adults'] ?? '') == '3' ? 'selected' : '' ?>>3 Adults</option>
                    <option value="4" <?= ($_POST['adults'] ?? '') == '4' ? 'selected' : '' ?>>4 Adults</option>
                    <option value="5" <?= ($_POST['adults'] ?? '') == '5' ? 'selected' : '' ?>>5+ Adults</option>
                  </select>
                </div>
                
                <div class="col-md-4">
                  <label for="children" class="form-label">Children</label>
                  <select class="form-select" id="children" name="children">
                    <option value="0" <?= ($_POST['children'] ?? '0') == '0' ? 'selected' : '' ?>>No Children</option>
                    <option value="1" <?= ($_POST['children'] ?? '') == '1' ? 'selected' : '' ?>>1 Child</option>
                    <option value="2" <?= ($_POST['children'] ?? '') == '2' ? 'selected' : '' ?>>2 Children</option>
                    <option value="3" <?= ($_POST['children'] ?? '') == '3' ? 'selected' : '' ?>>3+ Children</option>
                  </select>
                  <div class="form-text">Ages 5-12</div>
                </div>
                
                <div class="col-md-4">
                  <label for="room_type" class="form-label">Room Type *</label>
                  <select class="form-select" id="room_type" name="room_type" required>
                    <option value="">Select Room Type</option>
                    <optgroup label="Rooms and suites">
                      <?php foreach ($bookingRoomTypes as $roomOption): ?>
                        <option value="<?= sanitize($roomOption['key']) ?>" data-rate="<?= (float)$roomOption['price'] ?>" <?= ($selectedRoom === $roomOption['key'] || ($_POST['room_type'] ?? '') === $roomOption['key']) ? 'selected' : '' ?>>
                          <?= sanitize($roomOption['label']) ?> - UGX <?= number_format($roomOption['price']) ?>/night
                        </option>
                      <?php endforeach; ?>
                    </optgroup>
                    <?php if ($bookingDorms): ?>
                    <optgroup label="Dormitories">
                      <?php foreach ($bookingDorms as $dormKey => $dormOption): ?>
                        <option value="<?= sanitize($dormKey) ?>" data-rate="60000" <?= ($selectedRoom === $dormKey || ($_POST['room_type'] ?? '') === $dormKey) ? 'selected' : '' ?>>
                          <?= sanitize($dormOption['label']) ?> Dormitory - UGX 60,000 per bed/night
                        </option>
                      <?php endforeach; ?>
                    </optgroup>
                    <?php endif; ?>
                  </select>
                </div>
                <div class="col-12 d-none" id="dorm-options">
                  <div class="rounded border p-3 bg-light">
                    <p class="mb-2" id="dorm-policy"></p>
                    <div class="row g-3">
                      <div class="col-md-6">
                        <label for="dorm_booking_type" class="form-label">Dorm booking</label>
                        <select class="form-select" id="dorm_booking_type" name="dorm_booking_type">
                          <option value="per_bed" <?= ($_POST['dorm_booking_type'] ?? '') === 'per_bed' ? 'selected' : '' ?>>Book individual beds</option>
                          <option value="whole_dorm" <?= ($_POST['dorm_booking_type'] ?? '') === 'whole_dorm' ? 'selected' : '' ?>>Book the whole dorm (6 beds)</option>
                        </select>
                      </div>
                      <div class="col-md-6" id="dorm-bed-count-wrap">
                        <label for="dorm_beds" class="form-label">Number of beds</label>
                        <select class="form-select" id="dorm_beds" name="dorm_beds">
                          <?php for ($bed = 1; $bed <= 6; $bed++): ?>
                            <option value="<?= $bed ?>" <?= (int)($_POST['dorm_beds'] ?? 1) === $bed ? 'selected' : '' ?>><?= $bed ?> bed<?= $bed === 1 ? '' : 's' ?> - UGX <?= number_format(60000 * $bed) ?>/night</option>
                          <?php endfor; ?>
                        </select>
                      </div>
                      <div class="col-12">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" id="dorm_occupants_confirmed" name="dorm_occupants_confirmed" value="yes" <?= ($_POST['dorm_occupants_confirmed'] ?? '') === 'yes' ? 'checked' : '' ?>>
                          <label class="form-check-label" for="dorm_occupants_confirmed" id="dorm-confirmation-label"></label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="alert alert-light border mt-3 mb-0" id="booking-estimate" aria-live="polite">Select dates and an accommodation to see the nightly estimate.</div>
            </div>
          </div>
          
          <!-- Special Requests -->
          <div class="card mb-4">
            <div class="card-header bg-primary text-white">
              <h2 class="h5 mb-0"><i class="fas fa-comment me-2"></i>Special Requests</h2>
            </div>
            <div class="card-body">
              <label for="special_requests" class="form-label">Any special requirements or requests?</label>
              <textarea class="form-control" id="special_requests" name="special_requests" 
                        rows="4" placeholder="E.g., dietary requirements, accessibility needs, celebration occasions..."><?= sanitize($_POST['special_requests'] ?? '') ?></textarea>
              <div class="form-text">We'll do our best to accommodate your requests (subject to availability)</div>
            </div>
          </div>
          
          <!-- Terms & Submit -->
          <div class="card mb-4">
            <div class="card-body">
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="terms" required>
                <label class="form-check-label" for="terms">
                  I agree to the <a href="<?= base_url('public/terms.php') ?>" target="_blank">Terms & Conditions</a> 
                  and <a href="<?= base_url('public/cancellation.php') ?>" target="_blank">Cancellation Policy</a> *
                </label>
              </div>
              
              <div class="alert alert-info">
                <h3 class="h6"><i class="fas fa-info-circle me-2"></i>Important Information:</h3>
                <ul class="small mb-0">
                  <li>This is a booking request, not a confirmed reservation</li>
                  <li>A 50% deposit is required to confirm your booking</li>
                  <li>You'll receive payment instructions via email</li>
                  <li>Free cancellation up to 14 days before check-in</li>
                </ul>
              </div>
              
              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                  <i class="fas fa-paper-plane me-2"></i>Submit Booking Request
                </button>
                <a href="<?= base_url('public/accommodations.php') ?>" class="btn btn-outline-secondary">
                  <i class="fas fa-arrow-left me-2"></i>Back to Accommodations
                </a>
              </div>
            </div>
          </div>
          
        </form>
      </div>
      
      <!-- Sidebar -->
      <div class="col-lg-4">
        <div class="card sticky-top" style="top: 20px;">
          <div class="card-header bg-light">
            <h2 class="h5 mb-0">Need Help?</h2>
          </div>
          <div class="card-body">
            <h3 class="h6">Contact Our Reservations Team</h3>
            <ul class="list-unstyled">
              <li class="mb-2">
                <i class="fas fa-phone text-primary me-2"></i>
                <strong>Phone:</strong><br>
                <a href="tel:+256772957573">+256 772 957 573</a>
              </li>
              <li class="mb-2">
                <i class="fas fa-envelope text-primary me-2"></i>
                <strong>Email:</strong><br>
                <a href="mailto:reservations@kalitusiresort.com">reservations@kalitusiresort.com</a>
              </li>
              <li class="mb-2">
                <i class="fab fa-whatsapp text-primary me-2"></i>
                <strong>WhatsApp:</strong><br>
                <a href="https://wa.me/256772957573" target="_blank">Chat with us</a>
              </li>
            </ul>
            
            <hr>
            
            <h3 class="h6">Quick Links</h3>
            <ul class="list-unstyled small">
              <li class="mb-2"><a href="<?= base_url('public/accommodations.php') ?>">View Accommodations</a></li>
              <li class="mb-2"><a href="<?= base_url('public/dining.php') ?>">Dining Options</a></li>
              <li class="mb-2"><a href="<?= base_url('public/activities.php') ?>">Activities</a></li>
              <li class="mb-2"><a href="<?= base_url('public/cancellation.php') ?>">Cancellation Policy</a></li>
            </ul>
          </div>
        </div>
      </div>
      
    </div>
  <?php endif; ?>
  
</div>

<script>
// Form validation
(function() {
  'use strict';
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(function(form) {
    form.addEventListener('submit', function(event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
})();

// Auto-calculate nights and estimate
document.getElementById('check_in')?.addEventListener('change', calculateEstimate);
document.getElementById('check_out')?.addEventListener('change', calculateEstimate);
document.getElementById('room_type')?.addEventListener('change', updateDormFields);
document.getElementById('dorm_beds')?.addEventListener('change', calculateEstimate);

function updateDormFields() {
  const roomType = document.getElementById('room_type').value;
  const isDorm = roomType === 'ginger' || roomType === 'rocket';
  const dormOptions = document.getElementById('dorm-options');
  const dormBookingType = document.getElementById('dorm_booking_type');
  const dormBeds = document.getElementById('dorm_beds');
  const confirmation = document.getElementById('dorm_occupants_confirmed');
  const bedCountWrap = document.getElementById('dorm-bed-count-wrap');
  const guestGroup = roomType === 'ginger' ? 'women and girls' : 'men and boys';

  dormOptions.classList.toggle('d-none', !isDorm);
  dormBookingType.required = isDorm;
  confirmation.required = isDorm;
  document.getElementById('dorm-policy').textContent = isDorm
    ? (roomType === 'ginger' ? 'Ginger is reserved for women and girls only.' : 'Rocket is reserved for men and boys only.')
    : '';
  document.getElementById('dorm-confirmation-label').textContent = `I confirm that all guests staying in this dorm are ${guestGroup}.`;
  const wholeDorm = dormBookingType.value === 'whole_dorm';
  bedCountWrap.classList.toggle('d-none', wholeDorm);
  dormBeds.required = isDorm && !wholeDorm;
  calculateEstimate();
}

function calculateEstimate() {
  const checkIn = document.getElementById('check_in').value;
  const checkOut = document.getElementById('check_out').value;
  const roomType = document.getElementById('room_type').value;
  const estimate = document.getElementById('booking-estimate');
  if (!checkIn || !checkOut || !roomType) {
    estimate.textContent = 'Select dates and an accommodation to see the nightly estimate.';
    return;
  }

  const start = new Date(`${checkIn}T00:00:00Z`);
  const end = new Date(`${checkOut}T00:00:00Z`);
  const nights = Math.round((end - start) / 86400000);
  if (nights < 1) {
    estimate.textContent = 'Choose a check-out date after check-in to see the estimate.';
    return;
  }

  const rates = <?= json_encode($roomRatesByType, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
  let nightlyRate = rates[roomType] || 0;
  if (roomType === 'ginger' || roomType === 'rocket') {
    nightlyRate = document.getElementById('dorm_booking_type').value === 'whole_dorm'
      ? 360000
      : 60000 * Number(document.getElementById('dorm_beds').value);
  }
  const total = nightlyRate * nights;
  estimate.textContent = `${nights} night${nights === 1 ? '' : 's'} × UGX ${nightlyRate.toLocaleString()} per night = UGX ${total.toLocaleString()} total`;
}

document.getElementById('dorm_booking_type')?.addEventListener('change', updateDormFields);
updateDormFields();
</script>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>
