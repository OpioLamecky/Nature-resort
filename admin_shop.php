<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_role(['Super Admin']);
require_once __DIR__ . '/includes/file_upload.php';

$message = null;
$messageType = 'success';
$editItemId = filter_input(INPUT_GET, 'edit_item', FILTER_VALIDATE_INT) ?: 0;
$newUploadPath = null;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verify_csrf_token((string)($_POST['csrf_token'] ?? ''))) {
        http_response_code(400);
        exit('Invalid form token. Reload the page and try again.');
    }

    $action = (string)($_POST['action'] ?? '');
    try {
        if ($action === 'save_settings') {
            $settings = [
                'shop_eyebrow' => trim((string)($_POST['shop_eyebrow'] ?? '')),
                'shop_title' => trim((string)($_POST['shop_title'] ?? '')),
                'shop_tagline' => trim((string)($_POST['shop_tagline'] ?? '')),
            ];
            foreach ($settings as $key => $value) {
                if ($value === '' || mb_strlen($value) > 500) {
                    throw new RuntimeException('Shop page text is required and must be under 500 characters.');
                }
                $stmt = $pdo->prepare(
                    'INSERT INTO shop_settings (setting_key, setting_value) VALUES (?, ?)
                     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
                );
                $stmt->execute([$key, $value]);
            }
            $message = 'Shop page text saved.';
        } elseif ($action === 'add_category' || $action === 'update_category') {
            $name = trim((string)($_POST['name'] ?? ''));
            $categoryId = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT);
            if ($name === '' || mb_strlen($name) > 120) {
                throw new RuntimeException('Enter a category name of 1 to 120 characters.');
            }
            if ($action === 'update_category' && $categoryId) {
                $stmt = $pdo->prepare('UPDATE shop_categories SET name = ? WHERE id = ?');
                $stmt->execute([$name, $categoryId]);
                $message = 'Category updated.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO shop_categories (name) VALUES (?)');
                $stmt->execute([$name]);
                $message = 'Category added.';
            }
        } elseif ($action === 'delete_category') {
            $categoryId = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$categoryId) {
                throw new RuntimeException('Invalid category.');
            }
            $stmt = $pdo->prepare('DELETE FROM shop_categories WHERE id = ?');
            $stmt->execute([$categoryId]);
            $message = 'Category deleted.';
        } elseif ($action === 'save_item') {
            $itemId = filter_var($_POST['item_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
            $name = trim((string)($_POST['name'] ?? ''));
            $description = trim((string)($_POST['description'] ?? ''));
            $categoryId = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT);
            $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);
            $stockStatus = (string)($_POST['stock_status'] ?? 'in_stock');
            if ($name === '' || mb_strlen($name) > 180 || !$categoryId || $price === false || $price < 0 || !in_array($stockStatus, ['in_stock', 'out_of_stock'], true)) {
                throw new RuntimeException('Enter a valid name, category, non-negative price, and stock status.');
            }
            $checkCategory = $pdo->prepare('SELECT id FROM shop_categories WHERE id = ?');
            $checkCategory->execute([$categoryId]);
            if (!$checkCategory->fetchColumn()) {
                throw new RuntimeException('Choose a valid category.');
            }

            $oldImage = null;
            if ($itemId) {
                $findItem = $pdo->prepare('SELECT image_url FROM shop_items WHERE id = ?');
                $findItem->execute([$itemId]);
                $oldImage = $findItem->fetchColumn();
                if ($oldImage === false) {
                    throw new RuntimeException('That shop item no longer exists.');
                }
            }
            $imageUrl = $oldImage ?: null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $upload = secure_upload_file(
                    $_FILES['image'],
                    'shop_products',
                    ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                    5 * 1024 * 1024
                );
                if (!$upload['success']) {
                    throw new RuntimeException($upload['error'] ?: 'Image upload failed.');
                }
                $imageUrl = 'uploads/' . $upload['file_path'];
                $newUploadPath = $imageUrl;
            }

            if ($itemId) {
                $stmt = $pdo->prepare(
                    'UPDATE shop_items SET category_id = ?, name = ?, description = ?, price = ?, image_url = ?, stock_status = ? WHERE id = ?'
                );
                $stmt->execute([$categoryId, $name, $description !== '' ? $description : null, $price, $imageUrl, $stockStatus, $itemId]);
                if ($newUploadPath && $oldImage && str_starts_with($oldImage, 'uploads/shop_products/')) {
                    delete_uploaded_file($oldImage);
                }
                $message = 'Item updated.';
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO shop_items (category_id, name, description, price, image_url, stock_status) VALUES (?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([$categoryId, $name, $description !== '' ? $description : null, $price, $imageUrl, $stockStatus]);
                $message = 'Item added.';
            }
            $newUploadPath = null;
        } elseif ($action === 'delete_item') {
            $itemId = filter_var($_POST['item_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$itemId) {
                throw new RuntimeException('Invalid shop item.');
            }
            $findItem = $pdo->prepare('SELECT image_url FROM shop_items WHERE id = ?');
            $findItem->execute([$itemId]);
            $imageUrl = $findItem->fetchColumn();
            if ($imageUrl === false) {
                throw new RuntimeException('That shop item no longer exists.');
            }
            $stmt = $pdo->prepare('DELETE FROM shop_items WHERE id = ?');
            $stmt->execute([$itemId]);
            if ($imageUrl && str_starts_with($imageUrl, 'uploads/shop_products/')) {
                delete_uploaded_file($imageUrl);
            }
            $message = 'Item deleted.';
        } else {
            throw new RuntimeException('Unknown form action.');
        }
    } catch (RuntimeException $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    } catch (PDOException $e) {
        error_log('Shop admin save failed: ' . $e->getMessage());
        $message = $e->getCode() === '23000'
            ? 'That name is already in use, or the record is referenced by an order.'
            : 'The change could not be saved. Check that schema.sql has been imported.';
        $messageType = 'danger';
    } finally {
        if ($newUploadPath) {
            delete_uploaded_file($newUploadPath);
        }
    }
}

$settings = [
    'shop_eyebrow' => 'Kalitusi Nature Resort',
    'shop_title' => 'E-shop & Gift Shop',
    'shop_tagline' => 'A little piece of Kalitusi to enjoy here or take home.',
];
$settingRows = $pdo->query('SELECT setting_key, setting_value FROM shop_settings')->fetchAll();
foreach ($settingRows as $setting) {
    if (array_key_exists($setting['setting_key'], $settings)) {
        $settings[$setting['setting_key']] = $setting['setting_value'];
    }
}
$categories = $pdo->query('SELECT id, name FROM shop_categories ORDER BY name')->fetchAll();
$items = $pdo->query(
    'SELECT i.id, i.category_id, i.name, i.description, i.price, i.image_url, i.stock_status, i.created_at, c.name AS category_name
     FROM shop_items i INNER JOIN shop_categories c ON c.id = i.category_id
     ORDER BY i.created_at DESC, i.name'
)->fetchAll();
$editingItem = null;
if ($editItemId) {
    $findItem = $pdo->prepare('SELECT * FROM shop_items WHERE id = ?');
    $findItem->execute([$editItemId]);
    $editingItem = $findItem->fetch() ?: null;
}

$title = 'Manage E-shop | Kalitusi Nature Resort';
$pageTitle = 'E-shop Management';
include __DIR__ . '/includes/admin_layout_header.php';
?>
<div class="container-fluid py-4">
  <?php if ($message !== null): ?><div class="alert alert-<?= sanitize($messageType) ?>" role="alert"><?= sanitize($message) ?></div><?php endif; ?>
  <div class="alert alert-info">All changes here appear on the public E-shop page.</div>

  <section class="card mb-4">
    <div class="card-header bg-white"><h2 class="h5 mb-0">Public page text</h2></div>
    <div class="card-body">
      <form method="post" class="row g-3">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_settings">
        <div class="col-md-4"><label class="form-label" for="shop-eyebrow">Eyebrow</label><input class="form-control" id="shop-eyebrow" name="shop_eyebrow" maxlength="500" value="<?= sanitize($settings['shop_eyebrow']) ?>" required></div>
        <div class="col-md-4"><label class="form-label" for="shop-title">Page title</label><input class="form-control" id="shop-title" name="shop_title" maxlength="500" value="<?= sanitize($settings['shop_title']) ?>" required></div>
        <div class="col-md-4"><label class="form-label" for="shop-tagline">Tagline</label><input class="form-control" id="shop-tagline" name="shop_tagline" maxlength="500" value="<?= sanitize($settings['shop_tagline']) ?>" required></div>
        <div class="col-12"><button class="btn btn-success" type="submit">Save page text</button></div>
      </form>
    </div>
  </section>

  <div class="row g-4 mb-4">
    <div class="col-12 col-xl-4">
      <section class="card h-100">
        <div class="card-header bg-white"><h2 class="h5 mb-0">Categories</h2></div>
        <div class="card-body">
          <form method="post" class="input-group mb-3">
            <?= csrf_field() ?><input type="hidden" name="action" value="add_category">
            <input class="form-control form-control-sm" name="name" maxlength="120" placeholder="New category" required>
            <button class="btn btn-sm btn-success" type="submit">Add</button>
          </form>
          <?php foreach ($categories as $category): ?>
            <div class="border-top py-2">
              <form method="post" class="d-flex gap-2 align-items-center">
                <?= csrf_field() ?><input type="hidden" name="action" value="update_category"><input type="hidden" name="category_id" value="<?= (int)$category['id'] ?>">
                <input class="form-control form-control-sm" name="name" maxlength="120" value="<?= sanitize($category['name']) ?>" aria-label="Category name" required>
                <button class="btn btn-sm btn-outline-success" type="submit">Save</button>
              </form>
              <form method="post" class="text-end mt-1" onsubmit="return confirm('Delete this category? Categories with products cannot be deleted.');">
                <?= csrf_field() ?><input type="hidden" name="action" value="delete_category"><input type="hidden" name="category_id" value="<?= (int)$category['id'] ?>">
                <button class="btn btn-sm btn-link text-danger p-0" type="submit">Delete category</button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    </div>

    <div class="col-12 col-xl-8">
      <section class="card h-100" id="item-form">
        <div class="card-header bg-white"><h2 class="h5 mb-0"><?= $editingItem ? 'Edit item' : 'Add item' ?></h2></div>
        <div class="card-body">
          <?php if (!$categories): ?>
            <p class="text-muted mb-0">Create a category before adding products.</p>
          <?php else: ?>
            <form method="post" enctype="multipart/form-data" class="row g-3">
              <?= csrf_field() ?><input type="hidden" name="action" value="save_item">
              <input type="hidden" name="item_id" value="<?= (int)($editingItem['id'] ?? 0) ?>">
              <div class="col-md-6"><label class="form-label" for="item-name">Item name</label><input class="form-control" id="item-name" name="name" maxlength="180" value="<?= sanitize($editingItem['name'] ?? '') ?>" required></div>
              <div class="col-md-6">
                <label class="form-label" for="item-category">Category</label>
                <select class="form-select" id="item-category" name="category_id" required>
                  <option value="">Choose category</option>
                  <?php foreach ($categories as $category): ?><option value="<?= (int)$category['id'] ?>" <?= (int)($editingItem['category_id'] ?? 0) === (int)$category['id'] ? 'selected' : '' ?>><?= sanitize($category['name']) ?></option><?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-4"><label class="form-label" for="item-price">Price (UGX)</label><input class="form-control" type="number" id="item-price" name="price" min="0" step="0.01" value="<?= sanitize((string)($editingItem['price'] ?? '')) ?>" required></div>
              <div class="col-md-4"><label class="form-label" for="item-stock">Availability</label><select class="form-select" id="item-stock" name="stock_status"><option value="in_stock" <?= ($editingItem['stock_status'] ?? 'in_stock') === 'in_stock' ? 'selected' : '' ?>>In stock</option><option value="out_of_stock" <?= ($editingItem['stock_status'] ?? '') === 'out_of_stock' ? 'selected' : '' ?>>Out of stock</option></select></div>
              <div class="col-md-4"><label class="form-label" for="item-image">Image</label><input class="form-control" type="file" id="item-image" name="image" accept="image/jpeg,image/png,image/gif,image/webp"><div class="form-text">JPG, PNG, GIF or WebP, maximum 5 MB.</div></div>
              <?php if (!empty($editingItem['image_url'])): ?><div class="col-12 small text-muted">Current image: <?= sanitize($editingItem['image_url']) ?>. Leave upload empty to keep it.</div><?php endif; ?>
              <div class="col-12"><label class="form-label" for="item-description">Description</label><textarea class="form-control" id="item-description" name="description" rows="3"><?= sanitize($editingItem['description'] ?? '') ?></textarea></div>
              <div class="col-12 d-flex gap-2"><button class="btn btn-success" type="submit"><?= $editingItem ? 'Save item' : 'Add item' ?></button><?php if ($editingItem): ?><a class="btn btn-outline-secondary" href="<?= base_url('admin_shop.php') ?>">Cancel</a><?php endif; ?></div>
            </form>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </div>

  <section class="card">
    <div class="card-header bg-white"><h2 class="h5 mb-0">Products</h2></div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead><tr><th>Item</th><th>Category</th><th>Price</th><th>Availability</th><th>Added</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
          <?php if (!$items): ?><tr><td colspan="6" class="text-center text-muted py-4">No shop items yet.</td></tr>
          <?php else: foreach ($items as $item): ?>
            <tr>
              <td><?= sanitize($item['name']) ?></td><td><?= sanitize($item['category_name']) ?></td><td>UGX <?= number_format((float)$item['price']) ?></td>
              <td><span class="badge text-bg-<?= $item['stock_status'] === 'in_stock' ? 'success' : 'secondary' ?>"><?= $item['stock_status'] === 'in_stock' ? 'In stock' : 'Out of stock' ?></span></td>
              <td><?= sanitize(date('M j, Y', strtotime($item['created_at']))) ?></td>
              <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-outline-primary" href="<?= base_url('admin_shop.php?edit_item=' . (int)$item['id'] . '#item-form') ?>">Edit</a>
                <form method="post" class="d-inline" onsubmit="return confirm('Delete this item?');">
                  <?= csrf_field() ?><input type="hidden" name="action" value="delete_item"><input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>
<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>