<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role(['Super Admin','Content Manager']);

$action = $_GET['action'] ?? 'index';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  verify_csrf($_POST['csrf'] ?? '');
}

function block_find(PDO $pdo, int $id): ?array {
  $st = $pdo->prepare('SELECT * FROM content_blocks WHERE block_id=?');
  $st->execute([$id]);
  $r = $st->fetch();
  return $r ?: null;
}

if ($action === 'create' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $body = (string)($_POST['body'] ?? '');
  $icon = trim($_POST['icon'] ?? '');
  $order = (int)($_POST['display_order'] ?? 0);
  $status = $_POST['status'] ?? 'visible';
  if ($title !== '' && $body !== '') {
    $pdo->prepare('INSERT INTO content_blocks (page,title,body,icon,display_order,status) VALUES ("services",?,?,?,?,?)')
        ->execute([$title,$body,$icon,$order,$status]);
    flash('Service block created.');
    redirect(base_url('admin/pages-services.php'));
  }
  flash('Title and body are required.');
  redirect(base_url('admin/pages-services.php?action=new'));
}

if ($action === 'update' && $id && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $body = (string)($_POST['body'] ?? '');
  $icon = trim($_POST['icon'] ?? '');
  $order = (int)($_POST['display_order'] ?? 0);
  $status = in_array($_POST['status'] ?? '', ['visible', 'hidden'], true) ? $_POST['status'] : 'hidden';
  if ($title !== '' && $body !== '') {
    $pdo->prepare('UPDATE content_blocks SET title=?,body=?,icon=?,display_order=?,status=? WHERE block_id=? AND page="services"')
        ->execute([$title,$body,$icon,$order,$status,$id]);
    flash('Service block updated.');
    redirect(base_url('admin/pages-services.php'));
  }
  flash('Title and body are required.');
  redirect(base_url('admin/pages-services.php?action=edit&id=' . $id));
}

if ($action === 'delete' && $id) {
  verify_csrf($_GET['csrf'] ?? '');
  $pdo->prepare('DELETE FROM content_blocks WHERE block_id=? AND page="services"')->execute([$id]);
  flash('Service block deleted.');
  redirect(base_url('admin/pages-services.php'));
}

$title = 'Pages - Services';
$pageTitle = 'Services Management';
include __DIR__ . '/../includes/admin_layout_header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Services Page Blocks</h1>
  <a class="btn btn-primary btn-sm" href="?action=new">New Block</a>
</div>
<?php if (in_array($action, ['new', 'edit'], true)):
  $row = $action === 'edit' ? block_find($pdo, $id) : ['title' => '', 'body' => '', 'icon' => '', 'display_order' => 0, 'status' => 'visible'];
  if (!$row) {
    flash('Service block not found.', 'danger');
    redirect(base_url('admin/pages-services.php'));
  }
  $formAction = $action === 'edit' ? '?action=update&id=' . $id : '?action=create';
?>
<form method="post" class="card p-3" action="<?= $formAction ?>">
  <input type="hidden" name="csrf" value="<?= sanitize(csrf_token()) ?>">
  <?php if ($action === 'new'): ?><input type="hidden" name="action" value="create"><?php endif; ?>
  <div class="mb-3">
    <label class="form-label">Title</label>
    <input name="title" class="form-control" value="<?= sanitize($row['title']) ?>" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Body (HTML allowed)</label>
    <textarea name="body" rows="6" class="form-control" required><?= sanitize($row['body']) ?></textarea>
  </div>
  <div class="row g-3">
    <div class="col-md-4"><label class="form-label">Icon (optional)</label><input name="icon" class="form-control" value="<?= sanitize($row['icon'] ?? '') ?>"></div>
    <div class="col-md-2"><label class="form-label">Order</label><input name="display_order" type="number" class="form-control" value="<?= (int)$row['display_order'] ?>"></div>
    <div class="col-md-3"><label class="form-label">Status</label>
      <select name="status" class="form-select"><option value="visible" <?= $row['status'] === 'visible' ? 'selected' : '' ?>>Visible</option><option value="hidden" <?= $row['status'] === 'hidden' ? 'selected' : '' ?>>Hidden</option></select>
    </div>
  </div>
  <div class="mt-3"><button class="btn btn-success">Save</button> <a class="btn btn-link" href="<?= base_url('admin/pages-services.php') ?>">Cancel</a></div>
</form>
<?php else: $rows = $pdo->query('SELECT * FROM content_blocks WHERE page="services" ORDER BY display_order, block_id ASC')->fetchAll(); ?>
<table class="table table-striped align-middle">
  <thead><tr><th>Title</th><th>Order</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= sanitize($r['title']) ?></td>
        <td><?= (int)$r['display_order'] ?></td>
        <td><?= sanitize($r['status']) ?></td>
        <td class="text-end">
          <a class="btn btn-sm btn-outline-primary" href="?action=edit&id=<?= (int)$r['block_id'] ?>">Edit</a>
          <a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?= (int)$r['block_id'] ?>&csrf=<?= sanitize(csrf_token()) ?>" onclick="return confirm('Delete block?')">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php include __DIR__ . '/../includes/layout_footer.php';
