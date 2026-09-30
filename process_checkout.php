<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && isset($_GET['complete'])) {
        $confirmation = $_SESSION['shop_order_confirmation'] ?? null;
        unset($_SESSION['shop_order_confirmation']);
        if (!$confirmation) {
                redirect(base_url('eshop.php'));
        }

        $title = 'Order received | Kalitusi Nature Resort';
        include __DIR__ . '/includes/layout_header.php';
        ?>
        <section class="container py-5">
            <div class="mx-auto text-center" style="max-width: 640px">
                <div class="text-success mb-3" aria-hidden="true"><i class="fas fa-circle-check fa-3x"></i></div>
                <h1 class="h2">Thank you. We received your order.</h1>
                <p class="text-muted">Order #<?= (int)$confirmation['id'] ?> · Total UGX <?= number_format((float)$confirmation['total']) ?></p>
                <?php if ($confirmation['payment_method'] === 'mobile_money'): ?>
                    <div class="alert alert-info text-start" role="status">
                        Mobile money via <?= sanitize((string)$confirmation['provider']) ?> is not yet connected. Your order is pending; please wait for the resort to contact you with payment instructions.
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-start" role="status">Your order is pending. Payment is due at the resort.</div>
                <?php endif; ?>
                <a class="btn btn-success" href="<?= base_url('eshop.php') ?>">Back to the shop</a>
            </div>
        </section>
        <script>try { localStorage.removeItem('kalitusiShopCart'); } catch (_) {}</script>
        <?php
        include __DIR__ . '/includes/layout_footer.php';
        exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

if (!verify_csrf_token((string)($_POST['csrf_token'] ?? ''))) {
    http_response_code(400);
    exit('Your session expired. Return to the shop and try again.');
}

$customerName = trim((string)($_POST['customer_name'] ?? ''));
$customerPhone = trim((string)($_POST['customer_phone'] ?? ''));
$customerEmail = trim((string)($_POST['customer_email'] ?? ''));
$paymentMethod = (string)($_POST['payment_method'] ?? '');
$provider = (string)($_POST['payment_provider'] ?? '');
$cart = json_decode((string)($_POST['cart_json'] ?? ''), true);

if ($customerName === '' || mb_strlen($customerName) > 160 || $customerPhone === '' || mb_strlen($customerPhone) > 40) {
    http_response_code(422);
    exit('Enter your name and a valid phone number.');
}
if ($customerEmail !== '' && (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL) || mb_strlen($customerEmail) > 190)) {
    http_response_code(422);
    exit('Enter a valid email address.');
}
if (!in_array($paymentMethod, ['onsite', 'mobile_money'], true)) {
    http_response_code(422);
    exit('Choose a valid payment option.');
}
if ($paymentMethod === 'mobile_money' && !in_array($provider, ['MTN', 'Airtel'], true)) {
    http_response_code(422);
    exit('Choose MTN or Airtel for mobile money.');
}
if ($paymentMethod === 'onsite') {
    $provider = '';
}
if (!is_array($cart) || !$cart || count($cart) > 50) {
    http_response_code(422);
    exit('Your basket is empty or invalid. Return to the shop and try again.');
}

$quantities = [];
foreach ($cart as $line) {
    if (!is_array($line)) {
        http_response_code(422);
        exit('Invalid basket data.');
    }
    $id = filter_var($line['id'] ?? null, FILTER_VALIDATE_INT);
    $quantity = filter_var($line['quantity'] ?? null, FILTER_VALIDATE_INT);
    if (!$id || !$quantity || $quantity > 99) {
        http_response_code(422);
        exit('Invalid item quantity.');
    }
    $quantities[$id] = ($quantities[$id] ?? 0) + $quantity;
    if ($quantities[$id] > 99) {
        http_response_code(422);
        exit('Item quantity is too large.');
    }
}

try {
    $pdo->beginTransaction();
    $findItem = $pdo->prepare(
        'SELECT id, name, price, stock_status FROM shop_items WHERE id = ? FOR UPDATE'
    );
    $orderLines = [];
    $total = 0.0;
    foreach ($quantities as $itemId => $quantity) {
        $findItem->execute([$itemId]);
        $item = $findItem->fetch();
        if (!$item || $item['stock_status'] !== 'in_stock') {
            throw new DomainException('An item in your basket is no longer available. Please review your basket.');
        }
        $unitPrice = (float)$item['price'];
        $subtotal = round($unitPrice * $quantity, 2);
        $total = round($total + $subtotal, 2);
        $orderLines[] = [
            'id' => (int)$item['id'],
            'name' => $item['name'],
            'price' => $unitPrice,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
        ];
    }

    $order = $pdo->prepare(
        'INSERT INTO orders (customer_name, customer_email, customer_phone, payment_method, payment_provider, total_amount, status, payment_status)
         VALUES (?, ?, ?, ?, ?, ?, \'pending\', \'pending\')'
    );
    $order->execute([
        $customerName,
        $customerEmail !== '' ? $customerEmail : null,
        $customerPhone,
        $paymentMethod,
        $provider !== '' ? $provider : null,
        $total,
    ]);
    $orderId = (int)$pdo->lastInsertId();
    $insertLine = $pdo->prepare(
        'INSERT INTO order_items (order_id, shop_item_id, item_name, unit_price, quantity, subtotal)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    foreach ($orderLines as $line) {
        $insertLine->execute([$orderId, $line['id'], $line['name'], $line['price'], $line['quantity'], $line['subtotal']]);
    }
    $pdo->commit();
} catch (DomainException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(409);
    exit(sanitize($e->getMessage()));
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Shop checkout failed: ' . $e->getMessage());
    http_response_code(500);
    exit('We could not place your order. Please try again or contact the resort.');
}

$_SESSION['shop_order_confirmation'] = [
    'id' => $orderId,
    'total' => $total,
    'payment_method' => $paymentMethod,
    'provider' => $provider,
];
redirect(base_url('process_checkout.php?complete=1'));
