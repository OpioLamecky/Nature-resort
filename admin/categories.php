<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role(['Super Admin','Content Manager','Tax Officer','Editor']);

// Basic CRUD for categories
$action = $_GET['action'] ?? 'index';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  verify_csrf($_POST['csrf'] ?? '');
}

function categories_all(PDO $pdo): array {
  $stmt = $pdo->query('SELECT category_id, name, slug, category_type FROM categories ORDER BY name');
  return $stmt->fetchAll();
}

function category_find(PDO $pdo, int $id): ?array {
  $stmt = $pdo->prepare('SELECT * FROM categories WHERE category_id = ?');
  $stmt->execute([$id]);
  $row = $stmt->fetch();
  return $row ?: null;
}

if ($action === 'create' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $slug = trim($_POST['slug'] ?? '');
  $type = $_POST['category_type'] ?? 'post';
  if ($name !== '' && $slug !== '') {
    $stmt = $pdo->prepare('INSERT INTO categories (name, slug, description, category_type) VALUES (?,?,?,?)');
    $stmt->execute([$name, $slug, $_POST['description'] ?? null, $type]);
    flash('Category created.');
    redirect('/kellen/ura-website/admin/categories.php');
  }
  flash('Name and slug are required.');
  redirect('/kellen/ura-website/admin/categories.php?action=new');
}

if ($action === 'update' && $id && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $slug = trim($_POST['slug'] ?? '');
  $type = $_POST['category_type'] ?? 'post';
  if ($name !== '' && $slug !== '') {
    $stmt = $pdo->prepare('UPDATE categories SET name=?, slug=?, description=?, category_type=? WHERE category_id=?');
    $stmt->execute([$name, $slug, $_POST['description'] ?? null, $type, $id]);
    flash('Category updated.');
    redirect('/kellen/ura-website/admin/categories.php');
  }
  flash('Name and slug are required.');
  redirect('/kellen/ura-website/admin/categories.php?action=edit&id=' . $id);
}

if ($action === 'delete' && $id) {
  verify_csrf($_GET['csrf'] ?? '');
  $stmt = $pdo->prepare('DELETE FROM categories WHERE category_id=?');
  $stmt->execute([$id]);
  flash('Category deleted.');
  redirect('/kellen/ura-website/admin/categories.php');
}

$title = 'Categories - Admin';
$pageTitle = 'Categories Management';
include __DIR__ . '/../includes/admin_layout_header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Categories</h1>
  <a class="btn btn-primary btn-sm" href="?action=new">New Category</a>
</div>
<?php if ($action === 'new' || ($action === 'edit' && $id)): 
  $cat = $action === 'edit' ? category_find($pdo, $id) : ['name'=>'','slug'=>'','description'=>'','category_type'=>'post'];
  $formAction = $action === 'edit' ? ('?action=update&id=' . $id) : '?action=create';
?>
<form method="post" class="card p-3">
  <input type="hidden" name="csrf" value="<?= sanitize(csrf_token()) ?>">
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Name</label>
      <input type="text" name="name" class="form-control" value="<?= sanitize($cat['name'] ?? '') ?>" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Slug</label>
      <input type="text" name="slug" class="form-control" value="<?= sanitize($cat['slug'] ?? '') ?>" required>
    </div>
    <div class="col-md-12">
      <label class="form-label">Type</label>
      <select name="category_type" class="form-select">
        <?php foreach (['post','tax','service','download','faq'] as $t): ?>
          <option value="<?= $t ?>" <?= ($cat['category_type'] ?? '') === $t ? 'selected' : '' ?>><?= strtoupper($t) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="4"><?= sanitize($cat['description'] ?? '') ?></textarea>
    </div>
  </div>
  <div class="mt-3"><button class="btn btn-success">Save</button></div>
  <a href="/kellen/ura-website/admin/categories.php" class="btn btn-link">Cancel</a>
</form>
<?php else: 
  $rows = categories_all($pdo);
?>
<table class="table table-striped align-middle">
  <thead><tr>
    <th>Name</th><th>Slug</th><th>Type</th><th class="text-end">Actions</th>
  </tr></thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= sanitize($r['name']) ?></td>
      <td><code><?= sanitize($r['slug']) ?></code></td>
      <td><?= sanitize($r['category_type']) ?></td>
      <td class="text-end">
        <a class="btn btn-sm btn-outline-primary" href="?action=edit&id=<?= (int)$r['category_id'] ?>">Edit</a>
        <a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?= (int)$r['category_id'] ?>&csrf=<?= sanitize(csrf_token()) ?>" onclick="return confirm('Delete this category?')">Delete</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php include __DIR__ . '/../includes/layout_footer.php';
