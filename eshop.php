<?php
require_once __DIR__ . '/includes/bootstrap.php';

$title = 'E-shop | Kalitusi Nature Resort';
$demoMode = false;
$shopReady = false;
$categories = [];
$items = [];
$settings = [
  'shop_eyebrow' => 'Kalitusi Nature Resort',
  'shop_title' => 'E-shop & Gift Shop',
  'shop_tagline' => 'A little piece of Kalitusi to enjoy here or take home.',
];

try {
    $categories = $pdo->query('SELECT id, name FROM shop_categories ORDER BY name')->fetchAll();
    $items = $pdo->query(
        "SELECT i.id, i.category_id, i.name, i.description, i.price, i.image_url, i.stock_status, c.name AS category_name
         FROM shop_items i
         INNER JOIN shop_categories c ON c.id = i.category_id
         ORDER BY i.created_at DESC, i.name"
    )->fetchAll();
      $settingRows = $pdo->query('SELECT setting_key, setting_value FROM shop_settings')->fetchAll();
      foreach ($settingRows as $setting) {
        if (array_key_exists($setting['setting_key'], $settings)) {
          $settings[$setting['setting_key']] = $setting['setting_value'];
        }
      }
      $shopReady = true;
} catch (PDOException $e) {
      error_log('Shop catalog query failed; showing demo preview: ' . $e->getMessage());
      $demoMode = true;
    }

    if (!$shopReady) {
      $categories = [
        ['id' => 1, 'name' => 'Pastries'],
        ['id' => 2, 'name' => 'Crafts'],
        ['id' => 3, 'name' => 'Potted Plants'],
        ['id' => 4, 'name' => 'Groceries'],
      ];
      $items = [
        ['id' => 900001, 'category_id' => 1, 'name' => 'Fresh Banana Bread', 'description' => 'House-baked banana bread, made fresh for your stay.', 'price' => 18000, 'image_url' => 'assets/images/food1.webp', 'stock_status' => 'in_stock', 'category_name' => 'Pastries'],
        ['id' => 900002, 'category_id' => 1, 'name' => 'Pineapple Cake Slice', 'description' => 'A soft, tropical cake slice from the resort kitchen.', 'price' => 12000, 'image_url' => 'assets/images/food4.webp', 'stock_status' => 'in_stock', 'category_name' => 'Pastries'],
        ['id' => 900003, 'category_id' => 2, 'name' => 'Handwoven Coaster Set', 'description' => 'A set of locally made woven coasters to take home.', 'price' => 25000, 'image_url' => 'assets/images/unnamed (9).webp', 'stock_status' => 'in_stock', 'category_name' => 'Crafts'],
        ['id' => 900004, 'category_id' => 2, 'name' => 'Beaded Bracelet', 'description' => 'A colorful handmade bracelet from a local craft maker.', 'price' => 18000, 'image_url' => 'assets/images/unnamed (11).webp', 'stock_status' => 'in_stock', 'category_name' => 'Crafts'],
        ['id' => 900005, 'category_id' => 3, 'name' => 'African Violet', 'description' => 'A flowering potted plant, ready for a sunny windowsill.', 'price' => 25000, 'image_url' => 'assets/images/2021-10-31.webp', 'stock_status' => 'in_stock', 'category_name' => 'Potted Plants'],
        ['id' => 900006, 'category_id' => 3, 'name' => 'Garden Succulent', 'description' => 'An easy-care little plant in a simple nursery pot.', 'price' => 20000, 'image_url' => 'assets/images/unnamed (14).webp', 'stock_status' => 'in_stock', 'category_name' => 'Potted Plants'],
        ['id' => 900007, 'category_id' => 4, 'name' => 'Rwenzori Forest Honey', 'description' => 'Locally sourced honey in a take-home jar.', 'price' => 28000, 'image_url' => 'assets/images/food2.webp', 'stock_status' => 'in_stock', 'category_name' => 'Groceries'],
        ['id' => 900008, 'category_id' => 4, 'name' => 'Ugandan Arabica Coffee', 'description' => 'Freshly roasted Arabica coffee from western Uganda.', 'price' => 35000, 'image_url' => 'assets/images/food3.webp', 'stock_status' => 'in_stock', 'category_name' => 'Groceries'],
      ];
}

include __DIR__ . '/includes/layout_header.php';
?>
<style>
  .shop-intro { background: linear-gradient(115deg, #173c31 0%, #285c45 58%, #64895c 100%); color: #fff; }
  .shop-intro h1 { color: #fff !important; }
  .shop-intro .shop-tagline { color: #edf4e7; }
  .shop-intro .eyebrow { color: #d9e8bd; letter-spacing: .12em; text-transform: uppercase; }
  .shop-filter .btn.active { background: #244f3b; border-color: #244f3b; color: #fff; }
  .shop-card { border: 1px solid #e4e8df; border-radius: 6px; overflow: hidden; height: 100%; }
  .shop-card img { aspect-ratio: 4 / 3; object-fit: cover; background: #f0f2eb; }
  .shop-card .card-title { color: #233b30; }
  .shop-price { color: #285c45; font-weight: 700; }
</style>

<section class="shop-intro py-5">
  <div class="container py-3">
    <p class="eyebrow small mb-2"><?= sanitize($settings['shop_eyebrow']) ?></p>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
      <div>
        <h1 class="display-5 fw-semibold mb-2"><?= sanitize($settings['shop_title']) ?></h1>
        <p class="shop-tagline mb-0"><?= sanitize($settings['shop_tagline']) ?></p>
      </div>
      <button class="btn btn-light position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#shopCart" aria-controls="shopCart">
        <i class="fas fa-basket-shopping me-2" aria-hidden="true"></i>View basket
        <span class="badge text-bg-success ms-1" id="cart-count">0</span>
      </button>
    </div>
  </div>
</section>

<section class="container py-5">
  <?php if ($demoMode): ?>
    <div class="alert alert-warning" role="status">Demo preview. Import <code>schema.sql</code> to enable live inventory, admin editing, and checkout.</div>
  <?php endif; ?>
    <div class="shop-filter d-flex flex-wrap gap-2 mb-4" aria-label="Filter products by category">
      <button class="btn btn-outline-success active" type="button" data-category="all" aria-pressed="true">All items</button>
      <?php foreach ($categories as $category): ?>
        <button class="btn btn-outline-success" type="button" data-category="<?= (int)$category['id'] ?>" aria-pressed="false"><?= sanitize($category['name']) ?></button>
      <?php endforeach; ?>
    </div>

      <?php if (!$items): ?>
        <div class="text-center py-5">
          <h2 class="h4">No products are available right now</h2>
          <p class="text-muted mb-0">Please check back soon.</p>
        </div>
      <?php else: ?>
      <div class="row g-4" id="product-grid">
        <?php foreach ($items as $item):
          $image = trim((string)($item['image_url'] ?? ''));
          if ($image !== '' && !preg_match('~^(https?://|/)~i', $image)) {
              $image = base_url($image);
          }
        ?>
          <div class="col-12 col-sm-6 col-lg-4 col-xl-3 product-cell" data-category="<?= (int)$item['category_id'] ?>">
            <article class="card shop-card">
              <?php if ($image !== ''): ?>
                <img src="<?= sanitize($image) ?>" class="card-img-top" alt="<?= sanitize($item['name']) ?>" loading="lazy">
              <?php else: ?>
                <div class="d-flex align-items-center justify-content-center text-success" style="aspect-ratio:4/3;background:#eef1e8" aria-hidden="true"><i class="fas fa-leaf fa-2x"></i></div>
              <?php endif; ?>
              <div class="card-body d-flex flex-column">
                <p class="small text-muted mb-1"><?= sanitize($item['category_name']) ?></p>
                <h2 class="card-title h5"><?= sanitize($item['name']) ?></h2>
                <?php if (!empty($item['description'])): ?><p class="card-text small text-muted"><?= sanitize($item['description']) ?></p><?php endif; ?>
                <div class="mt-auto d-flex justify-content-between align-items-center gap-2">
                  <span class="shop-price">UGX <?= number_format((float)$item['price']) ?></span>
                  <?php if ($item['stock_status'] === 'in_stock' && !$demoMode): ?>
                    <button class="btn btn-success btn-sm add-to-cart" type="button" data-id="<?= (int)$item['id'] ?>" data-name="<?= sanitize($item['name']) ?>" data-price="<?= sanitize((string)$item['price']) ?>">Add to basket</button>
                  <?php elseif ($demoMode): ?>
                    <button class="btn btn-outline-secondary btn-sm" type="button" disabled>Preview only</button>
                  <?php else: ?>
                    <button class="btn btn-outline-secondary btn-sm" type="button" disabled>Out of stock</button>
                  <?php endif; ?>
                </div>
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
</section>

<div class="offcanvas offcanvas-end" tabindex="-1" id="shopCart" aria-labelledby="shopCartLabel">
  <div class="offcanvas-header border-bottom">
    <h2 class="offcanvas-title h5" id="shopCartLabel">Your basket</h2>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close basket"></button>
  </div>
  <div class="offcanvas-body d-flex flex-column">
    <div id="cart-lines" class="mb-4"></div>
    <div class="border-top pt-3 mt-auto">
      <div class="d-flex justify-content-between fw-semibold mb-3"><span>Estimated total</span><span id="cart-total">UGX 0</span></div>
      <form method="post" action="<?= base_url('process_checkout.php') ?>" id="checkout-form">
        <input type="hidden" name="csrf_token" value="<?= sanitize(generate_csrf_token()) ?>">
        <input type="hidden" name="cart_json" id="cart-json">
        <div class="mb-3">
          <label class="form-label" for="customer-name">Full name</label>
          <input class="form-control" id="customer-name" name="customer_name" maxlength="160" autocomplete="name" required>
        </div>
        <div class="mb-3">
          <label class="form-label" for="customer-phone">Phone number</label>
          <input class="form-control" id="customer-phone" name="customer_phone" maxlength="40" autocomplete="tel" required>
        </div>
        <div class="mb-3">
          <label class="form-label" for="customer-email">Email <span class="text-muted">(optional)</span></label>
          <input class="form-control" type="email" id="customer-email" name="customer_email" maxlength="190" autocomplete="email">
        </div>
        <fieldset class="mb-3">
          <legend class="form-label">Payment option</legend>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="payment_method" id="pay-onsite" value="onsite" checked>
            <label class="form-check-label" for="pay-onsite">Pay at the resort</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="payment_method" id="pay-online" value="mobile_money">
            <label class="form-check-label" for="pay-online">Pay online with mobile money</label>
          </div>
        </fieldset>
        <div class="mb-3 d-none" id="provider-wrap">
          <label class="form-label" for="payment-provider">Mobile money provider</label>
          <select class="form-select" id="payment-provider" name="payment_provider" disabled>
            <option value="">Choose a provider</option>
            <option value="MTN">MTN Mobile Money</option>
            <option value="Airtel">Airtel Money</option>
          </select>
        </div>
        <button class="btn btn-success w-100" type="submit" id="checkout-button">Place order</button>
        <p class="small text-muted mt-2 mb-0">Online payment is a pending request until a payment gateway is connected.</p>
      </form>
    </div>
  </div>
</div>

<script>
(() => {
  const cartKey = 'kalitusiShopCart';
  const cartCount = document.getElementById('cart-count');
  const cartLines = document.getElementById('cart-lines');
  const cartTotal = document.getElementById('cart-total');
  const cartJson = document.getElementById('cart-json');
  const checkoutButton = document.getElementById('checkout-button');
  let cart = {};
  try { cart = JSON.parse(localStorage.getItem(cartKey) || '{}') || {}; } catch (_) { cart = {}; }

  const money = value => 'UGX ' + Math.round(value).toLocaleString();
  const save = () => { localStorage.setItem(cartKey, JSON.stringify(cart)); render(); };
  const render = () => {
    cartLines.replaceChildren();
    let count = 0;
    let total = 0;
    Object.values(cart).forEach(item => {
      const quantity = Math.max(0, parseInt(item.quantity, 10) || 0);
      if (!quantity) return;
      count += quantity;
      total += Number(item.price) * quantity;
      const row = document.createElement('div');
      row.className = 'd-flex justify-content-between align-items-center gap-3 border-bottom py-3';
      const details = document.createElement('div');
      const name = document.createElement('strong');
      name.textContent = item.name;
      const subtotal = document.createElement('div');
      subtotal.className = 'small text-muted';
      subtotal.textContent = quantity + ' × ' + money(item.price);
      details.append(name, subtotal);
      const controls = document.createElement('div');
      controls.className = 'btn-group btn-group-sm';
      [['−', -1, 'Remove one'], ['+', 1, 'Add one']].forEach(([label, delta, title]) => {
        const button = document.createElement('button');
        button.type = 'button'; button.className = 'btn btn-outline-secondary'; button.textContent = label; button.title = title;
        button.addEventListener('click', () => {
          item.quantity += delta;
          if (item.quantity <= 0) delete cart[item.id];
          save();
        });
        controls.append(button);
      });
      row.append(details, controls);
      cartLines.append(row);
    });
    if (count === 0) cartLines.innerHTML = '<p class="text-muted">Your basket is empty.</p>';
    cartCount.textContent = count;
    cartTotal.textContent = money(total);
    cartJson.value = JSON.stringify(Object.values(cart).map(item => ({ id: item.id, quantity: item.quantity })));
    checkoutButton.disabled = count === 0;
  };

  document.querySelectorAll('.add-to-cart').forEach(button => button.addEventListener('click', () => {
    const id = button.dataset.id;
    if (!cart[id]) cart[id] = { id, name: button.dataset.name, price: Number(button.dataset.price), quantity: 0 };
    cart[id].quantity += 1;
    save();
  }));
  document.querySelectorAll('[data-category]').forEach(button => button.addEventListener('click', () => {
    document.querySelectorAll('.shop-filter [data-category]').forEach(filter => {
      const active = filter === button;
      filter.classList.toggle('active', active);
      filter.setAttribute('aria-pressed', String(active));
    });
    document.querySelectorAll('.product-cell').forEach(product => {
      product.classList.toggle('d-none', button.dataset.category !== 'all' && product.dataset.category !== button.dataset.category);
    });
  }));
  const providerWrap = document.getElementById('provider-wrap');
  const providerSelect = document.getElementById('payment-provider');
  document.querySelectorAll('[name="payment_method"]').forEach(radio => radio.addEventListener('change', () => {
    const online = document.getElementById('pay-online').checked;
    providerWrap.classList.toggle('d-none', !online);
    providerSelect.disabled = !online;
    providerSelect.required = online;
  }));
  document.getElementById('checkout-form').addEventListener('submit', event => {
    if (Object.keys(cart).length === 0) event.preventDefault();
    cartJson.value = JSON.stringify(Object.values(cart).map(item => ({ id: item.id, quantity: item.quantity })));
  });
  render();
})();
</script>
<?php include __DIR__ . '/includes/layout_footer.php'; ?>